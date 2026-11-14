<?php

namespace Tests\Unit\Ppdb;

use App\Models\PpdbIntegrationModel;
use App\Models\SiswaModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class SiswaActiveTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    protected int $kelasId;

    protected function setUp(): void
    {
        parent::setUp();

        // SQLite tidak punya CONCAT() yang dipakai query SiswaModel (MySQL).
        $this->db->initialize();
        $this->db->connID->createFunction('CONCAT', static fn (...$parts) => implode('', $parts));

        $this->db->table('tb_jurusan')->insert(['jurusan' => 'IPA']);
        $jurusanId = $this->db->insertID();

        $this->db->table('tb_kelas')->insert([
            'tingkat'     => '10',
            'id_jurusan'  => $jurusanId,
            'index_kelas' => 'A',
        ]);
        $this->kelasId = $this->db->insertID();

        $this->insertSiswa('1001', 'Aktif Satu', 'UC-AKTIF', null, 1);
        $this->insertSiswa('1002', 'Nonaktif Dua', 'UC-NONAKTIF', 'RFID-NON', 0);
    }

    private function insertSiswa(string $nis, string $nama, string $code, ?string $rfid, int $active): void
    {
        $this->db->table('tb_siswa')->insert([
            'nis'           => $nis,
            'nama_siswa'    => $nama,
            'id_kelas'      => $this->kelasId,
            'jenis_kelamin' => 'Laki-laki',
            'no_hp'         => '0800',
            'unique_code'   => $code,
            'rfid_code'     => $rfid,
            'is_active'     => $active,
        ]);
    }

    public function testInactiveStudentHiddenFromClassLists(): void
    {
        $model = new SiswaModel();

        $all = array_column($model->getAllSiswaWithKelas(), 'nama_siswa');
        $this->assertSame(['Aktif Satu'], $all);

        $byKelas = array_column($model->getSiswaByKelas($this->kelasId), 'nama_siswa');
        $this->assertSame(['Aktif Satu'], $byKelas);

        $this->assertSame(1, (int) $model->getSiswaCountByKelas($this->kelasId));
        $this->assertSame(1, (int) (new SiswaModel())->getSiswaCountByKelas());
    }

    public function testDataSiswaListCanIncludeInactive(): void
    {
        $names = array_column((new SiswaModel())->getAllSiswaWithKelas(null, null, null, true), 'nama_siswa');
        $this->assertSame(['Aktif Satu', 'Nonaktif Dua'], $names);
    }

    public function testScanRejectsInactiveStudent(): void
    {
        $result = $this->post('scanner/cek', ['unique_code' => 'RFID-NON', 'waktu' => 'masuk']);

        $result->assertSee('Siswa nonaktif');
        $this->assertSame(0, $this->db->table('tb_presensi_siswa')->countAllResults());
    }

    public function testConfigEncryptsTokenAtRest(): void
    {
        $model = new PpdbIntegrationModel();
        $model->saveConfig('https://x', 'tok', 'sec');

        $raw = $this->db->table('tb_ppdb_integration')->get()->getRowArray();
        $this->assertNotSame('tok', $raw['token']);
        $this->assertNotSame('sec', $raw['webhook_secret']);

        $cfg = $model->config();
        $this->assertSame('tok', $cfg['token']);
        $this->assertSame('sec', $cfg['webhook_secret']);
        $this->assertSame('https://x', $cfg['base_url']);

        $model->saveConfig('https://y', null, null);
        $cfg = $model->config();
        $this->assertSame('tok', $cfg['token']);
        $this->assertSame('https://y', $cfg['base_url']);
    }
}
