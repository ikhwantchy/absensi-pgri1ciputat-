<?php

namespace Tests\Unit;

use App\Libraries\RekapKehadiran;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RekapKehadiranTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private array $kelas = [];

    protected function setUp(): void
    {
        parent::setUp();
        Time::setTestNow('2026-11-17 10:00:00');

        // SQLite tidak punya CONCAT() yang dipakai KelasModel (MySQL).
        $this->db->initialize();
        $this->db->connID->createFunction('CONCAT', static fn (...$parts) => implode('', $parts));

        foreach (['Hadir', 'Sakit', 'Izin', 'Tanpa keterangan'] as $i => $k) {
            $this->db->table('tb_kehadiran')->insert(['id_kehadiran' => $i + 1, 'kehadiran' => $k]);
        }
        $this->db->table('tb_jurusan')->insert(['jurusan' => 'Reguler']);
        $jurusan = $this->db->insertID();
        foreach (['A', 'B'] as $idx) {
            $this->db->table('tb_kelas')->insert(['tingkat' => '7', 'id_jurusan' => $jurusan, 'index_kelas' => $idx]);
            $this->kelas[$idx] = (int) $this->db->insertID();
        }

        // 7A: 2 siswa, 7B: 2 siswa + 1 nonaktif (tidak dihitung).
        $this->siswa(1, 'A');
        $this->siswa(2, 'A');
        $this->siswa(3, 'B');
        $this->siswa(4, 'B');
        $this->siswa(5, 'B', 0);

        // Dua hari efektif: 16 dan 17 Nov. 15 Nov (Minggu) tanpa catatan.
        $this->presensi(1, '2026-11-16', 1);
        $this->presensi(2, '2026-11-16', 1);
        $this->presensi(3, '2026-11-16', 1);
        $this->presensi(4, '2026-11-16', 2);
        $this->presensi(5, '2026-11-16', 1); // nonaktif, diabaikan
        $this->presensi(1, '2026-11-17', 1);
        $this->presensi(2, '2026-11-17', 3);
        // siswa 3 dan 4 tanpa catatan pada 17 Nov => alfa
    }

    protected function tearDown(): void
    {
        Time::setTestNow();
        parent::tearDown();
    }

    private function siswa(int $id, string $kelas, int $aktif = 1): void
    {
        $this->db->table('tb_siswa')->insert([
            'id_siswa' => $id, 'nis' => "N{$id}", 'nama_siswa' => "S{$id}", 'id_kelas' => $this->kelas[$kelas],
            'jenis_kelamin' => 'Laki-laki', 'no_hp' => '', 'unique_code' => "U{$id}", 'is_active' => $aktif,
        ]);
    }

    private function presensi(int $siswa, string $tanggal, int $kehadiran): void
    {
        $this->db->table('tb_presensi_siswa')->insert([
            'id_siswa' => $siswa, 'tanggal' => $tanggal, 'id_kehadiran' => $kehadiran, 'keterangan' => '',
        ]);
    }

    public function testHarianHanyaHariEfektifDanAlfaDariSisa(): void
    {
        $harian = (new RekapKehadiran())->harian('siswa', 7);

        $this->assertSame(['2026-11-16', '2026-11-17'], array_column($harian, 'tanggal'));
        $this->assertSame([3, 1, 0, 0, 4, 75.0], [$harian[0]['hadir'], $harian[0]['sakit'], $harian[0]['izin'], $harian[0]['alfa'], $harian[0]['total'], $harian[0]['persen']]);
        $this->assertSame([1, 0, 1, 2, 25.0], [$harian[1]['hadir'], $harian[1]['sakit'], $harian[1]['izin'], $harian[1]['alfa'], $harian[1]['persen']]);
    }

    public function testRingkasanDanFilterKelas(): void
    {
        $rekap = new RekapKehadiran();

        $semua = $rekap->ringkasan('siswa', 7);
        $this->assertSame(2, $semua['hari']);
        $this->assertSame(8, $semua['total']);
        $this->assertSame(50.0, $semua['persen']);

        // 7B tetap dihitung 2 hari efektif walau tidak punya catatan 17 Nov.
        $b = $rekap->ringkasan('siswa', 7, $this->kelas['B']);
        $this->assertSame(2, $b['hari']);
        $this->assertSame([1, 1, 2], [$b['hadir'], $b['sakit'], $b['alfa']]);
        $this->assertSame(25.0, $b['persen']);
    }

    public function testPerKelasUrutDariTerendah(): void
    {
        $rows = (new RekapKehadiran())->perKelas(7);

        $this->assertSame(['7 B', '7 A'], array_column($rows, 'kelas'));
        $this->assertSame([25.0, 75.0], array_column($rows, 'persen'));
        $this->assertSame([2, 2], array_column($rows, 'siswa'));
        $this->assertSame(2, $rows[0]['alfa']);
    }
}
