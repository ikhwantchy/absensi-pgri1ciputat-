<?php

namespace Tests\Unit\Ppdb;

use App\Libraries\Ppdb\PpdbSync;
use App\Models\PpdbIntegrationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Ppdb\FakePpdbApiClient;

/**
 * @internal
 */
final class PpdbSyncTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private const SIS      = '/api/v1/siswa';
    private const ACK      = '/api/v1/sync-ack';
    private const OLD_SYNC = '2026-11-14 04:00:00';
    private const SERVER   = '2026-11-14T05:12:00Z';

    protected function setUp(): void
    {
        parent::setUp();

        // Jurusan id 1 ada di produksi ('Umum'); kelas baru hasil sinkron memakainya.
        $this->db->table('tb_jurusan')->insert(['id' => 1, 'jurusan' => 'Umum']);

        $model = new PpdbIntegrationModel();
        $model->saveConfig('https://ppdb.test', 'tok', 'sec');
        $model->markResult('ok', 'awal', self::OLD_SYNC);
    }

    protected function tearDown(): void
    {
        @unlink(WRITEPATH . 'ppdb-sync.lock');
        parent::tearDown();
    }

    // ------------------------------------------------------------------ helpers

    private function item(string $ref, array $override = []): array
    {
        return array_merge([
            'ppdb_ref'      => $ref,
            'nis'           => '2027' . substr($ref, -3),
            'nisn'          => '0123456789',
            'nama'          => 'Siswa ' . $ref,
            'jenis_kelamin' => 'Laki-laki',
            'no_hp'         => '6281234567890',
            'tahun_masuk'   => 2027,
            'rombel'        => ['id' => 3, 'name' => 'VII-A', 'tingkat' => 'VII', 'index' => 'A'],
            'aktif'         => true,
            'updated_at'    => '2026-11-14T05:10:00Z',
        ], $override);
    }

    private function page(array $items, ?string $next = null, string $serverTime = self::SERVER): array
    {
        return ['data' => $items, 'next_cursor' => $next, 'server_time' => $serverTime];
    }

    private function fake(array ...$pages): FakePpdbApiClient
    {
        $fake = new FakePpdbApiClient();

        foreach ($pages as $page) {
            $fake->queue(self::SIS, $page);
        }

        return $fake;
    }

    private function kelas(string $tingkat, string $index, ?int $id = null): int
    {
        $data = ['tingkat' => $tingkat, 'index_kelas' => $index, 'id_jurusan' => 1];
        if ($id !== null) {
            $data['id_kelas'] = $id;
        }
        $this->db->table('tb_kelas')->insert($data);

        return $id ?? (int) $this->db->insertID();
    }

    private function siswa(string $ref): array
    {
        return $this->db->table('tb_siswa')->where('ppdb_ref', $ref)->get()->getRowArray();
    }

    private function rows(string $table): int
    {
        return $this->db->table($table)->countAllResults();
    }

    private function syncedUntil(): ?string
    {
        return (new PpdbIntegrationModel())->config()['synced_until'];
    }

    // ------------------------------------------------------------- siswa baru

    public function testCreatesStudentWithNewKelasAndToken(): void
    {
        $result = (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001')]))))->run('manual');

        $this->assertSame('ok', $result->status);
        $this->assertSame(1, $result->created);

        $kelas = $this->db->table('tb_kelas')->get()->getResultArray();
        $this->assertCount(1, $kelas);
        $this->assertSame('VII', $kelas[0]['tingkat']);
        $this->assertSame('A', $kelas[0]['index_kelas']);
        $this->assertSame(1, (int) $kelas[0]['id_jurusan']);

        $s = $this->siswa('P1C-2027-E-001');
        $this->assertSame((int) $kelas[0]['id_kelas'], (int) $s['id_kelas']);
        $this->assertSame('2027001', $s['nis']);
        $this->assertSame('Siswa P1C-2027-E-001', $s['nama_siswa']);
        $this->assertSame('Laki-laki', $s['jenis_kelamin']);
        $this->assertSame('6281234567890', $s['no_hp']);
        $this->assertGreaterThanOrEqual(32, strlen($s['unique_code']));
        $this->assertSame(3, (int) $s['ppdb_rombel_id']);
        $this->assertSame(1, (int) $s['is_active']);
        $this->assertNotNull($s['ppdb_synced_at']);
        $this->assertNull($s['rfid_code']);
    }

    public function testMatchesExistingKelas(): void
    {
        $this->kelas('VII', 'A', 5);

        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001')]))))->run('manual');

        $this->assertSame(5, (int) $this->siswa('P1C-2027-E-001')['id_kelas']);
        $this->assertSame(1, $this->rows('tb_kelas'));
    }

    public function testIgnoresSoftDeletedKelas(): void
    {
        $this->db->table('tb_kelas')->insert([
            'id_kelas' => 5, 'tingkat' => 'VII', 'index_kelas' => 'A', 'id_jurusan' => 1,
            'deleted_at' => '2026-01-01 00:00:00',
        ]);

        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001')]))))->run('manual');

        $this->assertNotSame(5, (int) $this->siswa('P1C-2027-E-001')['id_kelas']);
        $this->assertSame(2, $this->rows('tb_kelas'));
    }

    public function testSkipsNewItemWithoutRombelOrInactive(): void
    {
        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['rombel' => null]),
            $this->item('P1C-2027-E-002', ['aktif' => false]),
        ]))))->run('manual');

        $this->assertSame('ok', $result->status);
        $this->assertSame(2, $result->skipped);
        $this->assertSame(0, $result->created);
        $this->assertSame(0, $this->rows('tb_siswa'));
    }

    public function testSkipsNewItemWithoutNis(): void
    {
        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['nis' => null]),
        ]))))->run('manual');

        $this->assertSame(1, $result->skipped);
        $this->assertSame(0, $this->rows('tb_siswa'));
        $this->assertStringContainsString('NIS kosong', $result->message);
    }

    public function testNullNoHpStoredAsEmptyString(): void
    {
        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001', ['no_hp' => null])]))))->run('manual');

        $this->assertSame('', $this->siswa('P1C-2027-E-001')['no_hp']);
    }

    // ------------------------------------------------------ siswa yang sudah ada

    private function seedExisting(string $ref, int $kelasId, int $rombelId = 3, string $rfid = '0012338461'): void
    {
        $this->db->table('tb_siswa')->insert([
            'nis'            => '2027001',
            'nama_siswa'     => 'Nama Lama',
            'id_kelas'       => $kelasId,
            'jenis_kelamin'  => 'Laki-laki',
            'no_hp'          => '0800',
            'unique_code'    => 'UC-LAMA-TETAP',
            'rfid_code'      => $rfid,
            'ppdb_ref'       => $ref,
            'ppdb_rombel_id' => $rombelId,
            'is_active'      => 1,
        ]);
    }

    public function testNeverTouchesRfidOrUniqueCode(): void
    {
        $kelas = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $kelas);

        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['nama' => 'Nama Baru', 'jenis_kelamin' => 'Perempuan', 'no_hp' => '0811']),
        ]))))->run('manual');

        $this->assertSame(1, $result->updated);
        $s = $this->siswa('P1C-2027-E-001');
        $this->assertSame('Nama Baru', $s['nama_siswa']);
        $this->assertSame('Perempuan', $s['jenis_kelamin']);
        $this->assertSame('0811', $s['no_hp']);
        $this->assertSame('0012338461', $s['rfid_code']);
        $this->assertSame('UC-LAMA-TETAP', $s['unique_code']);
        $this->assertNotNull($s['ppdb_synced_at']);
    }

    public function testKeepsManualClassWhenRombelUnchanged(): void
    {
        $this->kelas('VII', 'A');
        $viiiA = $this->kelas('VIII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiiA, 3);

        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001')]))))->run('manual');

        $this->assertSame($viiiA, (int) $this->siswa('P1C-2027-E-001')['id_kelas']);
    }

    public function testMovesClassWhenRombelChanged(): void
    {
        $viiA = $this->kelas('VII', 'A');
        $viiB = $this->kelas('VII', 'B');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);

        (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['rombel' => ['id' => 4, 'name' => 'VII-B', 'tingkat' => 'VII', 'index' => 'B']]),
        ]))))->run('manual');

        $s = $this->siswa('P1C-2027-E-001');
        $this->assertSame($viiB, (int) $s['id_kelas']);
        $this->assertSame(4, (int) $s['ppdb_rombel_id']);
    }

    public function testNullRombelKeepsClass(): void
    {
        $viiA = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);

        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['rombel' => null, 'nama' => 'Nama Baru']),
        ]))))->run('manual');

        $this->assertSame('ok', $result->status);
        $s = $this->siswa('P1C-2027-E-001');
        $this->assertSame($viiA, (int) $s['id_kelas']);
        $this->assertSame(3, (int) $s['ppdb_rombel_id']);
        $this->assertSame('Nama Baru', $s['nama_siswa']);
        $this->assertSame(1, $this->rows('tb_kelas'));
    }

    public function testInactiveDeactivatesWithoutDelete(): void
    {
        $viiA = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);

        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', ['aktif' => false]),
        ]))))->run('manual');

        $this->assertSame(1, $result->deactivated);
        $this->assertSame(1, $this->rows('tb_siswa'));
        $this->assertSame(0, (int) $this->siswa('P1C-2027-E-001')['is_active']);
    }

    public function testReactivatesStudent(): void
    {
        $viiA = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);
        $this->db->table('tb_siswa')->where('ppdb_ref', 'P1C-2027-E-001')->update(['is_active' => 0]);

        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001')]))))->run('manual');

        $this->assertSame(1, (int) $this->siswa('P1C-2027-E-001')['is_active']);
    }

    public function testExistingNullNisKeepsOldNis(): void
    {
        $viiA = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);

        (new PpdbSync($this->fake($this->page([$this->item('P1C-2027-E-001', ['nis' => null])]))))->run('manual');

        $this->assertSame('2027001', $this->siswa('P1C-2027-E-001')['nis']);
    }

    // --------------------------------------------------------- paginasi dan ack

    public function testFollowsCursorAndAcksServerTime(): void
    {
        $fake = $this->fake(
            $this->page([$this->item('P1C-2027-E-001')], 'CUR-2'),
            $this->page([$this->item('P1C-2027-E-002')], null, '2026-11-14T05:12:07Z'),
        );

        $result = (new PpdbSync($fake))->run('cron');

        $this->assertSame('ok', $result->status);
        $this->assertSame(2, $result->created);
        $this->assertCount(2, $fake->gets);
        $this->assertSame('2026-11-14T04:00:00Z', $fake->gets[0]['query']['updated_since']);
        $this->assertArrayNotHasKey('cursor', $fake->gets[0]['query']);
        $this->assertSame('CUR-2', $fake->gets[1]['query']['cursor']);

        // server_time halaman pertama jadi titik mulai berikutnya (kontrak absensi-v1).
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
        $this->assertSame([['path' => self::ACK, 'body' => ['synced_until' => self::SERVER]]], $fake->posts);

        $log = $this->db->table('tb_ppdb_sync_log')->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertSame('cron', $log['trigger']);
        $this->assertSame('ok', $log['status']);
        $this->assertSame(2, (int) $log['created']);

        $cfg = (new PpdbIntegrationModel())->config();
        $this->assertSame('ok', $cfg['last_status']);
    }

    public function testFirstSyncOmitsUpdatedSince(): void
    {
        $this->db->table('tb_ppdb_integration')->update(['synced_until' => null]);
        $fake = $this->fake($this->page([]));

        (new PpdbSync($fake))->run('manual');

        $this->assertArrayNotHasKey('updated_since', $fake->gets[0]['query']);
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
    }

    public function testServerTimeWithOffsetConvertedToUtc(): void
    {
        $fake = $this->fake($this->page([], null, '2026-11-14T12:12:00+07:00'));

        (new PpdbSync($fake))->run('manual');

        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
        $this->assertSame('2026-11-14T05:12:00Z', $fake->posts[0]['body']['synced_until']);
    }

    public function testIdempotentSecondRun(): void
    {
        $page = $this->page([$this->item('P1C-2027-E-001'), $this->item('P1C-2027-E-002')]);

        $first  = (new PpdbSync($this->fake($page)))->run('manual');
        $second = (new PpdbSync($this->fake($page)))->run('manual');

        $this->assertSame(2, $first->created);
        $this->assertSame(0, $second->created);
        $this->assertSame(2, $second->updated);
        $this->assertSame(2, $this->rows('tb_siswa'));
        $this->assertSame(1, $this->rows('tb_kelas'));
    }

    public function testFailureMidwayKeepsSyncedUntil(): void
    {
        $pages = [
            $this->page([$this->item('P1C-2027-E-001')], 'CUR-2'),
            $this->page([$this->item('P1C-2027-E-002')]),
        ];

        $failing = $this->fake(...$pages)->failOnGet(2);
        $result  = (new PpdbSync($failing))->run('webhook');

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('HTTP 500', $result->message);
        $this->assertSame(self::OLD_SYNC, $this->syncedUntil());
        $this->assertSame([], $failing->posts);

        $log = $this->db->table('tb_ppdb_sync_log')->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertSame('failed', $log['status']);
        $this->assertSame('webhook', $log['trigger']);
        $this->assertSame('failed', (new PpdbIntegrationModel())->config()['last_status']);

        // Pengulangan dari titik yang sama tidak membuat dobel.
        $retry  = $this->fake(...$pages);
        $result = (new PpdbSync($retry))->run('webhook');

        $this->assertSame('ok', $result->status);
        $this->assertSame('2026-11-14T04:00:00Z', $retry->gets[0]['query']['updated_since']);
        $this->assertSame(2, $this->rows('tb_siswa'));
        $this->assertSame(1, $this->db->table('tb_siswa')->where('ppdb_ref', 'P1C-2027-E-001')->countAllResults());
        $this->assertSame(1, $this->rows('tb_kelas'));
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
    }

    public function testDbErrorInsidePageRollsBackThatPage(): void
    {
        // Kegagalan DB sungguhan: trigger SQLite menolak insert siswa kedua.
        $this->db->query("CREATE TRIGGER tolak_002 BEFORE INSERT ON tb_siswa
            WHEN NEW.ppdb_ref = 'P1C-2027-E-002'
            BEGIN SELECT RAISE(ABORT, 'db rusak'); END");

        $fake = $this->fake($this->page([
            $this->item('P1C-2027-E-001'),
            $this->item('P1C-2027-E-002'),
        ]));

        try {
            $result = (new PpdbSync($fake))->run('manual');
        } finally {
            // Bersihkan trigger agar tidak memengaruhi tes lain.
            $this->db->query('DROP TRIGGER IF EXISTS tolak_002');
        }

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('Gagal insert siswa P1C-2027-E-002', $result->message);
        $this->assertSame(0, $result->created);
        $this->assertSame(0, $this->rows('tb_siswa'));
        $this->assertSame(0, $this->rows('tb_kelas'));
        $this->assertSame(self::OLD_SYNC, $this->syncedUntil());
        $this->assertSame([], $fake->posts);
    }

    public static function malformedRombels(): array
    {
        return [
            'tingkat kosong' => [['id' => 9, 'name' => '?', 'tingkat' => '', 'index' => 'B']],
            'index kosong'   => [['id' => 9, 'name' => '?', 'tingkat' => 'VII', 'index' => '']],
            'tanpa id'       => [['name' => 'VII-B', 'tingkat' => 'VII', 'index' => 'B']],
            'id kosong'      => [['id' => null, 'name' => 'VII-B', 'tingkat' => 'VII', 'index' => 'B']],
        ];
    }

    /**
     * @dataProvider malformedRombels
     */
    public function testMalformedRombelOnNewItemIsSkipped(array $rombel): void
    {
        $fake = $this->fake($this->page([
            $this->item('P1C-2027-E-001'),
            $this->item('P1C-2027-E-002', ['rombel' => $rombel]),
        ]));

        $result = (new PpdbSync($fake))->run('manual');

        $this->assertSame('ok', $result->status);
        $this->assertSame(1, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('rombel tidak lengkap', $result->message);
        $this->assertSame(1, $this->rows('tb_siswa'));
        $this->assertSame(1, $this->rows('tb_kelas'));
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
        $this->assertCount(1, $fake->posts);
    }

    /**
     * @dataProvider malformedRombels
     */
    public function testMalformedRombelOnExistingKeepsClassAndUpdatesFields(array $rombel): void
    {
        $viiA = $this->kelas('VII', 'A');
        $this->seedExisting('P1C-2027-E-001', $viiA, 3);

        $result = (new PpdbSync($this->fake($this->page([
            $this->item('P1C-2027-E-001', [
                'rombel' => $rombel, 'nama' => 'Nama Baru', 'nis' => '2027999',
                'jenis_kelamin' => 'Perempuan', 'no_hp' => '0811', 'aktif' => false,
            ]),
        ]))))->run('manual');

        $this->assertSame('ok', $result->status);
        $this->assertSame(1, $result->deactivated);
        $s = $this->siswa('P1C-2027-E-001');
        $this->assertSame($viiA, (int) $s['id_kelas']);
        $this->assertSame(3, (int) $s['ppdb_rombel_id']);
        $this->assertSame('Nama Baru', $s['nama_siswa']);
        $this->assertSame('2027999', $s['nis']);
        $this->assertSame('Perempuan', $s['jenis_kelamin']);
        $this->assertSame('0811', $s['no_hp']);
        $this->assertSame(0, (int) $s['is_active']);
        $this->assertSame(1, $this->rows('tb_kelas'));
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
    }

    public function testAckFailureDoesNotFailSync(): void
    {
        $fake   = $this->fake($this->page([$this->item('P1C-2027-E-001')]))->failPost();
        $result = (new PpdbSync($fake))->run('manual');

        $this->assertSame('ok', $result->status);
        $this->assertSame('2026-11-14 05:12:00', $this->syncedUntil());
        $this->assertStringContainsString('ack', strtolower($result->message));
        $this->assertCount(1, $fake->posts);
    }

    public function testInvalidResponseFails(): void
    {
        $fake   = $this->fake(['data' => 'x']);
        $result = (new PpdbSync($fake))->run('manual');

        $this->assertSame('failed', $result->status);
        $this->assertSame(self::OLD_SYNC, $this->syncedUntil());
    }

    // ------------------------------------------------------ lock dan konfigurasi

    public function testLockSkipsConcurrentRun(): void
    {
        $handle = fopen(WRITEPATH . 'ppdb-sync.lock', 'c');
        $this->assertTrue(flock($handle, LOCK_EX | LOCK_NB));

        try {
            $fake   = $this->fake($this->page([$this->item('P1C-2027-E-001')]));
            $result = (new PpdbSync($fake))->run('webhook');
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        $this->assertSame('skipped', $result->status);
        $this->assertSame([], $fake->gets);
        $this->assertSame(0, $this->rows('tb_siswa'));
        $log = $this->db->table('tb_ppdb_sync_log')->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertSame('skipped', $log['status']);

        // Lock dilepas setelah run selesai: run berikutnya jalan normal.
        $next = (new PpdbSync($this->fake($this->page([]))))->run('manual');
        $this->assertSame('ok', $next->status);
    }

    public function testMissingConfigFailsGracefully(): void
    {
        $this->db->table('tb_ppdb_integration')->update(['base_url' => '', 'token' => null]);

        $result = (new PpdbSync())->run('cron');

        $this->assertSame('failed', $result->status);
        $this->assertNotSame('', $result->message);
        $this->assertSame(self::OLD_SYNC, $this->syncedUntil());
        $log = $this->db->table('tb_ppdb_sync_log')->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertSame('failed', $log['status']);
    }

    public function testUnreadableTokenFailsGracefully(): void
    {
        $this->db->table('tb_ppdb_integration')->update(['token' => base64_encode('bukan-ciphertext')]);

        $result = (new PpdbSync())->run('manual');

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('isi ulang token', $result->message);
    }
}
