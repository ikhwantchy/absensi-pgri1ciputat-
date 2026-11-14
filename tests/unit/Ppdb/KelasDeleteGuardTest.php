<?php

namespace Tests\Unit\Ppdb;

use App\Models\SiswaModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Kelas dengan siswa nonaktif (batal dari PPDB) tidak boleh terhapus:
 * hapus kelas meng-cascade siswa dan riwayat presensinya.
 *
 * @internal
 */
final class KelasDeleteGuardTest extends CIUnitTestCase
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

        $this->db->initialize();
        $this->db->connID->createFunction('CONCAT', static fn (...$parts) => implode('', $parts));

        $this->seed(\App\Database\Seeds\GeneralSettingsSeeder::class);

        $this->db->table('tb_jurusan')->insert(['jurusan' => 'IPA']);
        $jurusanId = $this->db->insertID();

        $this->db->table('tb_kelas')->insert([
            'tingkat'     => '10',
            'id_jurusan'  => $jurusanId,
            'index_kelas' => 'A',
        ]);
        $this->kelasId = (int) $this->db->insertID();

        $this->db->table('tb_siswa')->insert([
            'nis'           => '2001',
            'nama_siswa'    => 'Batal Satu',
            'id_kelas'      => $this->kelasId,
            'jenis_kelamin' => 'Laki-laki',
            'no_hp'         => '0800',
            'unique_code'   => 'UC-BATAL',
            'is_active'     => 0,
        ]);
    }

    public function testCountCanIncludeInactive(): void
    {
        $model = new SiswaModel();
        $this->assertSame(0, (int) $model->getSiswaCountByKelas($this->kelasId));
        $this->assertSame(1, (int) (new SiswaModel())->getSiswaCountByKelas($this->kelasId, true));
    }

    public function testKelasWithOnlyInactiveStudentsCannotBeDeleted(): void
    {
        $this->db->table('users')->insert([
            'email'         => 'admin@test.com',
            'username'      => 'admin',
            'is_superadmin' => 1,
            'password_hash' => 'x',
            'active'        => 1,
        ]);
        $adminId = (int) $this->db->insertID();

        \Config\Services::resetSingle('authentication');
        \Config\Services::resetSingle('authorization');
        $this->withSession(['logged_in' => $adminId])
            ->post('admin/kelas/deleteKelasPost', ['id' => $this->kelasId]);

        $this->assertSame(1, $this->db->table('tb_kelas')->where('id_kelas', $this->kelasId)->countAllResults());
        $this->assertSame(1, $this->db->table('tb_siswa')->where('nis', '2001')->countAllResults());
    }
}
