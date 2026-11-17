<?php

namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tap kartu otomatis: tap pertama masuk, tap berikutnya pulang.
 *
 * @internal
 */
final class ScanOtomatisTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private int $siswaId;

    /** Tanggal hari ini: controller memakai Time::today(), yang tidak ikut Time::setTestNow(). */
    private string $d;

    protected function setUp(): void
    {
        parent::setUp();
        $this->d = date('Y-m-d');

        // SQLite tidak punya CONCAT() yang dipakai query SiswaModel (MySQL).
        $this->db->initialize();
        $this->db->connID->createFunction('CONCAT', static fn (...$parts) => implode('', $parts));

        $this->seed(\App\Database\Seeds\GeneralSettingsSeeder::class);
        foreach (['Hadir', 'Sakit', 'Izin', 'Tanpa keterangan'] as $i => $kehadiran) {
            $this->db->table('tb_kehadiran')->insert(['id_kehadiran' => $i + 1, 'kehadiran' => $kehadiran]);
        }

        $this->db->table('tb_jurusan')->insert(['jurusan' => 'Reguler']);
        $this->db->table('tb_kelas')->insert([
            'tingkat'     => '7',
            'id_jurusan'  => $this->db->insertID(),
            'index_kelas' => 'A',
        ]);
        $kelasId = $this->db->insertID();

        $this->db->table('tb_siswa')->insert([
            'nis'           => '2501',
            'nama_siswa'    => 'Siswa Tap',
            'id_kelas'      => $kelasId,
            'jenis_kelamin' => 'Laki-laki',
            'no_hp'         => '',
            'unique_code'   => 'UC-TAP',
            'rfid_code'     => 'RF-TAP',
        ]);
        $this->siswaId = (int) $this->db->insertID();

        putenv('JEDA_PULANG_MENIT');
    }

    protected function tearDown(): void
    {
        Time::setTestNow();
        putenv('JEDA_PULANG_MENIT');
        parent::tearDown();
    }

    private function tap(string $at)
    {
        Time::setTestNow($at);

        return $this->post('scanner/cek', ['unique_code' => 'RF-TAP', 'waktu' => 'auto']);
    }

    private function presensi(): array
    {
        return $this->db->table('tb_presensi_siswa')->where('id_siswa', $this->siswaId)->get()->getResultArray();
    }

    public function testTapPertamaMasukTapKeduaPulang(): void
    {
        $this->tap($this->d . ' 06:45:00')->assertSee('Absen masuk berhasil');
        $rows = $this->presensi();
        $this->assertCount(1, $rows);
        $this->assertSame('06:45:00', $rows[0]['jam_masuk']);
        $this->assertEmpty($rows[0]['jam_keluar']);

        $this->tap($this->d . ' 13:30:00')->assertSee('Absen pulang berhasil');
        $rows = $this->presensi();
        $this->assertCount(1, $rows);
        $this->assertSame('13:30:00', $rows[0]['jam_keluar']);

        $this->tap($this->d . ' 13:35:00')->assertSee('Anda sudah absen pulang hari ini');
        $this->assertSame('13:30:00', $this->presensi()[0]['jam_keluar']);
    }

    public function testTapUlangSebelumJedaTidakDicatatPulang(): void
    {
        $this->tap($this->d . ' 06:45:00');

        $this->tap($this->d . ' 06:46:00')->assertSee('Absen pulang bisa mulai pukul 07:15');
        $this->assertEmpty($this->presensi()[0]['jam_keluar']);

        $this->tap($this->d . ' 07:15:00')->assertSee('Absen pulang berhasil');
    }

    public function testJedaBisaDiaturLewatEnv(): void
    {
        putenv('JEDA_PULANG_MENIT=0');

        $this->tap($this->d . ' 06:45:00');
        $this->tap($this->d . ' 06:45:30')->assertSee('Absen pulang berhasil');
    }

    public function testTanpaParameterWaktuDianggapOtomatis(): void
    {
        Time::setTestNow($this->d . ' 06:45:00');
        $this->post('scanner/cek', ['unique_code' => 'RF-TAP'])->assertSee('Absen masuk berhasil');
        $this->assertCount(1, $this->presensi());
    }
}
