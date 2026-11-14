<?php

namespace App\Libraries\Ppdb;

use App\Models\PpdbIntegrationModel;
use App\Models\PpdbSyncLogModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/**
 * Tarik siswa diterima dari API PPDB lalu upsert ke tb_siswa (spec §5.2).
 *
 * - Baris tidak pernah dihapus; `aktif:false` hanya membuat is_active = 0.
 * - rfid_code dan unique_code siswa lama tidak pernah diubah.
 * - synced_until hanya maju setelah halaman terakhir tersimpan, jadi kegagalan
 *   di tengah jalan mengulang dari titik yang sama (upsert idempoten).
 */
class PpdbSync
{
    public const TRIGGERS = ['manual', 'webhook', 'cron'];

    private const PATH_SISWA = '/api/v1/siswa';
    private const PATH_ACK   = '/api/v1/sync-ack';
    private const MAX_PAGES  = 1000;

    /** Detik mundur dari synced_until agar baris yang commit telat di PPDB tetap terambil. */
    private const OVERLAP_SECONDS = 60;

    private ?PpdbApiClient $api;
    private ?string $configError = null;
    private BaseConnection $db;
    private PpdbIntegrationModel $integration;

    /** @var array<string, int> cache id_kelas per "tingkat|index" selama satu run */
    private array $kelasCache = [];

    /** @var list<string> */
    private array $skipNotes = [];

    public function __construct(?PpdbApiClient $api = null)
    {
        $this->db          = db_connect();
        $this->integration = new PpdbIntegrationModel();
        $this->api         = $api;

        if ($api === null) {
            $config = $this->integration->config();

            if (! empty($config['token_unreadable'])) {
                $this->configError = 'Token PPDB tidak bisa dibaca (kunci enkripsi berubah?). Silakan isi ulang token di halaman Integrasi PPDB.';
            } elseif (trim((string) ($config['base_url'] ?? '')) === '' || (string) ($config['token'] ?? '') === '') {
                $this->configError = 'Konfigurasi PPDB belum lengkap: isi URL dan token di halaman Integrasi PPDB.';
            } else {
                $this->api = new HttpPpdbApiClient((string) $config['base_url'], (string) $config['token']);
            }
        }
    }

    public function run(string $trigger): PpdbSyncResult
    {
        if (! in_array($trigger, self::TRIGGERS, true)) {
            throw new \InvalidArgumentException("Trigger sinkron tidak dikenal: {$trigger}");
        }

        if ($this->api === null) {
            return $this->finish($trigger, new PpdbSyncResult('failed', $this->configError ?? 'Klien API PPDB tidak tersedia.'));
        }

        $lock = @fopen(WRITEPATH . 'ppdb-sync.lock', 'c');
        if ($lock === false) {
            return $this->finish($trigger, new PpdbSyncResult('failed', 'Tidak bisa membuka file lock sinkron di writable/.'));
        }

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                return $this->finish($trigger, new PpdbSyncResult('skipped', 'Sinkron lain sedang berjalan; dilewati.'));
            }

            try {
                return $this->finish($trigger, $this->pull());
            } finally {
                flock($lock, LOCK_UN);
            }
        } finally {
            fclose($lock);
        }
    }

    private function pull(): PpdbSyncResult
    {
        $result           = new PpdbSyncResult('ok');
        $this->kelasCache = [];
        $this->skipNotes  = [];

        $config       = $this->integration->config();
        $syncedUntil  = $config['synced_until'] ?? null;
        $baseQuery    = [];
        if ($syncedUntil !== null && $syncedUntil !== '') {
            // synced_until tetap server_time asli; hanya permintaan yang dimundurkan (upsert idempoten).
            $baseQuery['updated_since'] = $this->toIso($syncedUntil, self::OVERLAP_SECONDS);
        }

        $serverTime = null;
        $cursor     = null;

        try {
            for ($page = 1; ; $page++) {
                if ($page > self::MAX_PAGES) {
                    throw new RuntimeException('Jumlah halaman PPDB melebihi batas ' . self::MAX_PAGES . '.');
                }

                $query = $baseQuery;
                if ($cursor !== null) {
                    $query['cursor'] = $cursor;
                }

                $response = $this->api->get(self::PATH_SISWA, $query);
                [$items, $next, $pageTime] = $this->parsePage($response);

                // Kontrak: server_time halaman pertama jadi titik mulai penarikan berikutnya.
                $serverTime ??= $pageTime;

                $this->processPage($items, $result);

                if ($next === null) {
                    break;
                }
                if ($next === $cursor) {
                    throw new RuntimeException('next_cursor PPDB tidak berubah; penarikan dihentikan.');
                }
                $cursor = $next;
            }
        } catch (Throwable $e) {
            $result->status  = 'failed';
            $result->message = 'Sinkron gagal: ' . $e->getMessage()
                . ' Data yang sudah tersimpan aman; sinkron berikutnya mengulang dari titik terakhir.';

            return $result;
        }

        // Semua halaman tersimpan: simpan synced_until (UTC) dulu, baru ack.
        $syncedUntil = $serverTime->format('Y-m-d H:i:s');
        try {
            $this->integration->markResult('ok', 'Menyimpan titik sinkron.', $syncedUntil);
        } catch (Throwable $e) {
            $result->status  = 'failed';
            $result->message = 'Sinkron gagal menyimpan titik sinkron: ' . $e->getMessage();

            return $result;
        }
        $result->syncedUntil = $syncedUntil;

        $parts = [sprintf(
            'Sinkron selesai: %d baru, %d diperbarui, %d dinonaktifkan, %d dilewati.',
            $result->created,
            $result->updated,
            $result->deactivated,
            $result->skipped
        )];

        if ($this->skipNotes !== []) {
            $notes = array_slice($this->skipNotes, 0, 5);
            $more  = count($this->skipNotes) - count($notes);
            $parts[] = 'Dilewati: ' . implode('; ', $notes) . ($more > 0 ? " (+{$more} lainnya)" : '') . '.';
        }

        try {
            $this->api->post(self::PATH_ACK, ['synced_until' => $serverTime->format('Y-m-d\TH:i:s\Z')]);
        } catch (Throwable $e) {
            $parts[] = 'Ack ke PPDB gagal (' . $e->getMessage() . '). Data tetap tersimpan; sinkron berikutnya mengirim ack sendiri.';
        }

        $result->message = implode(' ', $parts);

        return $result;
    }

    /**
     * @return array{0: list<array>, 1: ?string, 2: DateTimeImmutable}
     */
    private function parsePage(array $response): array
    {
        if (! isset($response['data']) || ! is_array($response['data']) || ! array_key_exists('server_time', $response)) {
            throw new RuntimeException('Respons /api/v1/siswa tidak sesuai kontrak (data/server_time).');
        }

        $next = $response['next_cursor'] ?? null;
        if ($next !== null && (! is_string($next) || $next === '')) {
            throw new RuntimeException('next_cursor PPDB tidak valid.');
        }

        return [array_values($response['data']), $next, $this->parseServerTime($response['server_time'])];
    }

    private function parseServerTime(mixed $value): DateTimeImmutable
    {
        if (! is_string($value) || $value === '') {
            throw new RuntimeException('server_time PPDB kosong.');
        }

        try {
            // String tanpa zona dianggap UTC (kontrak: server_time selalu UTC).
            $time = new DateTimeImmutable($value, new DateTimeZone('UTC'));
        } catch (Throwable $e) {
            throw new RuntimeException("server_time PPDB tidak valid: {$value}");
        }

        return $time->setTimezone(new DateTimeZone('UTC'));
    }

    /** "Y-m-d H:i:s" (UTC) dari DB menjadi ISO 8601 dengan Z, opsional dimundurkan beberapa detik. */
    private function toIso(string $utc, int $minusSeconds = 0): string
    {
        return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))
            ->modify("-{$minusSeconds} seconds")
            ->format('Y-m-d\TH:i:s\Z');
    }

    private function processPage(array $items, PpdbSyncResult $result): void
    {
        $counts = $result->counts();

        $this->db->transBegin();

        try {
            foreach ($items as $item) {
                $this->upsert(is_array($item) ? $item : [], $result);
            }

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal saat menyimpan halaman.');
            }

            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();
            // Tanpa reset, transStatus tetap false dan transaksi berikutnya di koneksi ini ikut gagal.
            $this->db->resetTransStatus();
            // Kelas yang baru dibuat ikut di-rollback; jangan pakai id dari cache.
            $this->kelasCache = [];
            [$result->created, $result->updated, $result->deactivated, $result->skipped] = array_values($counts);

            throw $e;
        }
    }

    private function upsert(array $item, PpdbSyncResult $result): void
    {
        $ref = trim((string) ($item['ppdb_ref'] ?? ''));
        if ($ref === '') {
            $this->skip($result, '(tanpa ppdb_ref)', 'ppdb_ref kosong');

            return;
        }

        $nama = trim((string) ($item['nama'] ?? ''));
        $jk   = $item['jenis_kelamin'] ?? null;
        if ($nama === '' || ! in_array($jk, ['Laki-laki', 'Perempuan'], true)) {
            $this->skip($result, $ref, 'nama/jenis kelamin tidak valid');

            return;
        }

        $nis    = isset($item['nis']) && trim((string) $item['nis']) !== '' ? trim((string) $item['nis']) : null;
        $noHp   = (string) ($item['no_hp'] ?? '');
        $aktif  = ($item['aktif'] ?? false) === true;
        $rombelRaw = $item['rombel'] ?? null;
        $rombel    = $this->normalizeRombel($rombelRaw);
        // Rombel ada tapi rusak: data PPDB, bukan kegagalan infrastruktur; jangan blokir sinkron.
        $rombelBroken = $rombelRaw !== null && $rombel === null;
        $now    = date('Y-m-d H:i:s');

        $existing = $this->db->table('tb_siswa')->where('ppdb_ref', $ref)->get()->getRowArray();

        if ($existing === null) {
            if ($rombelBroken) {
                $this->skip($result, $ref, 'rombel tidak lengkap');

                return;
            }
            if ($rombel === null) {
                $this->skip($result, $ref, 'belum punya rombel');

                return;
            }
            if (! $aktif) {
                $this->skip($result, $ref, 'tidak aktif');

                return;
            }
            if ($nis === null) {
                $this->skip($result, $ref, 'NIS kosong');

                return;
            }

            $this->write($this->db->table('tb_siswa')->insert([
                'nis'            => $nis,
                'nama_siswa'     => $nama,
                'id_kelas'       => $this->kelasId($rombel),
                'jenis_kelamin'  => $jk,
                'no_hp'          => $noHp,
                'unique_code'    => generateToken(),
                'ppdb_ref'       => $ref,
                'ppdb_rombel_id' => $rombel['id'],
                'ppdb_kelas_key' => $this->kelasKey($rombel),
                'is_active'      => 1,
                'ppdb_synced_at' => $now,
            ]), "insert siswa {$ref}");
            $result->created++;

            return;
        }

        // Sengaja tanpa rfid_code dan unique_code.
        $data = [
            'nama_siswa'     => $nama,
            'jenis_kelamin'  => $jk,
            'no_hp'          => $noHp,
            'is_active'      => $aktif ? 1 : 0,
            'ppdb_synced_at' => $now,
        ];
        if ($nis !== null) {
            $data['nis'] = $nis;
        }

        // Rombel null atau rusak: kelas lama tetap.
        if ($rombel !== null) {
            $data += $this->kelasChange($existing, $rombel);
        }

        $this->write(
            $this->db->table('tb_siswa')->where('id_siswa', $existing['id_siswa'])->update($data),
            "update siswa {$ref}"
        );
        if ($rombelBroken) {
            $this->skipNotes[] = "{$ref} (rombel tidak lengkap; kelas tidak diubah)";
        }

        if (! $aktif && (int) $existing['is_active'] === 1) {
            $result->deactivated++;
        } else {
            $result->updated++;
        }
    }

    /**
     * Rombel valid menjadi ['id' => int, 'tingkat' => string, 'index' => string];
     * null bila rombel null atau tidak lengkap (id/tingkat/index kosong).
     *
     * @return array{id: int, tingkat: string, index: string}|null
     */
    private function normalizeRombel(mixed $rombel): ?array
    {
        if (! is_array($rombel)) {
            return null;
        }

        $id      = $rombel['id'] ?? null;
        $tingkat = trim((string) ($rombel['tingkat'] ?? ''));
        $index   = trim((string) ($rombel['index'] ?? ''));

        $validId = is_int($id) || (is_string($id) && ctype_digit($id));
        if (! $validId || (int) $id <= 0 || $tingkat === '' || $index === '') {
            return null;
        }

        return ['id' => (int) $id, 'tingkat' => $tingkat, 'index' => $index];
    }

    /** @param array{tingkat: string, index: string} $rombel */
    private function kelasKey(array $rombel): string
    {
        return $rombel['tingkat'] . '|' . $rombel['index'];
    }

    /**
     * Kolom kelas yang berubah untuk siswa lama.
     *
     * - Rombel diganti di PPDB (id beda): pindah ke kelas rombel baru.
     * - Rombel sama tapi tingkat/index berubah (ganti nama): pindah hanya bila siswa masih di kelas
     *   dari key lama, artinya belum dinaikkan manual di siab. Key tetap diperbarui.
     * - Key lama kosong (data sebelum kolom ada): kelas tidak diubah, key diisi.
     *
     * @param array{id: int, tingkat: string, index: string} $rombel
     */
    private function kelasChange(array $existing, array $rombel): array
    {
        $newKey = $this->kelasKey($rombel);

        if ($rombel['id'] !== (int) ($existing['ppdb_rombel_id'] ?? 0)) {
            return ['id_kelas' => $this->kelasId($rombel), 'ppdb_rombel_id' => $rombel['id'], 'ppdb_kelas_key' => $newKey];
        }

        $oldKey = $existing['ppdb_kelas_key'] ?? null;
        if ($oldKey === $newKey) {
            return [];
        }

        $data = ['ppdb_kelas_key' => $newKey];
        if ($oldKey !== null && $oldKey !== '') {
            [$tingkat, $index] = array_pad(explode('|', $oldKey, 2), 2, '');
            $oldKelas          = $this->findKelasId($tingkat, $index);

            if ($oldKelas !== null && $oldKelas === (int) $existing['id_kelas']) {
                $data['id_kelas'] = $this->kelasId($rombel);
            }
        }

        return $data;
    }

    /** Hasil query tulis false (DBDebug mati) dijadikan exception agar halaman di-rollback. */
    private function write(mixed $ok, string $what): void
    {
        if ($ok === false) {
            $error  = $this->db->error();
            // SQLite bisa sudah mengosongkan errornya ("not an error"); detailnya tetap di log CI.
            $detail = ! empty($error['code']) && ! empty($error['message']) ? $error['message'] : 'kesalahan database (lihat log)';
            throw new RuntimeException("Gagal {$what}: {$detail}.");
        }
    }

    /** @param array{id: int, tingkat: string, index: string} $rombel */
    private function kelasId(array $rombel): int
    {
        $tingkat = $rombel['tingkat'];
        $index   = $rombel['index'];

        $key = $tingkat . '|' . $index;
        if (isset($this->kelasCache[$key])) {
            return $this->kelasCache[$key];
        }

        $id = $this->findKelasId($tingkat, $index);

        if ($id === null) {
            $this->write($this->db->table('tb_kelas')->insert([
                'tingkat'     => $tingkat,
                'index_kelas' => $index,
                'id_jurusan'  => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]), "membuat kelas {$tingkat}-{$index}");
            $id = (int) $this->db->insertID();
        }

        return $this->kelasCache[$key] = $id;
    }

    /** Kelas (belum dihapus) untuk tingkat+index tanpa membuat baru. */
    private function findKelasId(string $tingkat, string $index): ?int
    {
        $row = $this->db->table('tb_kelas')
            ->where('tingkat', $tingkat)
            ->where('index_kelas', $index)
            ->where('deleted_at', null)
            ->orderBy('id_kelas')
            ->get()->getRowArray();

        return $row === null ? null : (int) $row['id_kelas'];
    }

    private function skip(PpdbSyncResult $result, string $ref, string $reason): void
    {
        $result->skipped++;
        $this->skipNotes[] = "{$ref} ({$reason})";
    }

    private function finish(string $trigger, PpdbSyncResult $result): PpdbSyncResult
    {
        try {
            (new PpdbSyncLogModel())->record($trigger, $result->status, $result->counts(), $result->message);
            $this->integration->markResult($result->status, $result->message, $result->syncedUntil);
        } catch (Throwable $e) {
            log_message('error', 'PpdbSync: gagal menulis log/status: ' . $e->getMessage());
        }

        return $result;
    }
}
