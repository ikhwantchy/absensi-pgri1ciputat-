<?php

namespace App\Libraries;

use App\Models\KelasModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;

/**
 * Rekap persentase kehadiran untuk dashboard.
 *
 * Hari efektif = tanggal yang punya minimal satu catatan presensi (hari libur
 * dan akhir pekan otomatis tidak dihitung). Siswa/guru tanpa catatan pada hari
 * efektif dihitung alfa, sama seperti kartu "hari ini" di dashboard.
 * Persentase hadir = hadir / (jumlah orang x hari efektif).
 */
class RekapKehadiran
{
   private const HADIR = '1';
   private const SAKIT = '2';
   private const IZIN  = '3';

   private BaseConnection $db;

   public function __construct(?BaseConnection $db = null)
   {
      $this->db = $db ?? db_connect();
   }

   /** Tanggal awal (inklusif) untuk N hari terakhir termasuk hari ini. */
   public static function mulai(int $hari): string
   {
      return Time::now()->subDays($hari - 1)->toDateString();
   }

   /**
    * Rekap harian siswa (opsional per kelas) atau guru.
    *
    * @return list<array{tanggal: string, hadir: int, sakit: int, izin: int, alfa: int, total: int, persen: float}>
    */
   public function harian(string $tipe, int $hari, $idKelas = null): array
   {
      $mulai  = self::mulai($hari);
      $sampai = Time::now()->toDateString();
      $total  = $this->jumlahOrang($tipe, $idKelas);

      $hitung = [];
      foreach ($this->hitung($tipe, $mulai, $sampai, $idKelas, 'tanggal') as $r) {
         $hitung[$r['tanggal']][(string) $r['id_kehadiran']] = (int) $r['jumlah'];
      }

      $hasil = [];
      foreach ($this->hariEfektif($tipe, $mulai, $sampai) as $tanggal) {
         $hasil[] = $this->baris(['tanggal' => $tanggal], $hitung[$tanggal] ?? [], $total, 1);
      }

      return $hasil;
   }

   /**
    * Ringkasan satu periode: total per status dan persentase hadir.
    *
    * @return array{hari: int, hadir: int, sakit: int, izin: int, alfa: int, total: int, persen: float}
    */
   public function ringkasan(string $tipe, int $hari, $idKelas = null): array
   {
      $harian = $this->harian($tipe, $hari, $idKelas);
      $sum = ['hari' => count($harian), 'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'total' => 0];

      foreach ($harian as $h) {
         foreach (['hadir', 'sakit', 'izin', 'alfa', 'total'] as $k) {
            $sum[$k] += $h[$k];
         }
      }
      $sum['persen'] = $sum['total'] > 0 ? round($sum['hadir'] / $sum['total'] * 100, 1) : 0.0;

      return $sum;
   }

   /**
    * Persentase kehadiran tiap kelas dalam N hari, urut dari yang paling rendah.
    *
    * @return list<array{id_kelas: int, kelas: string, siswa: int, hadir: int, sakit: int, izin: int, alfa: int, total: int, persen: float}>
    */
   public function perKelas(int $hari): array
   {
      $mulai   = self::mulai($hari);
      $sampai  = Time::now()->toDateString();
      $hariEfektif = count($this->hariEfektif('siswa', $mulai, $sampai));

      $siswaPerKelas = [];
      foreach ($this->db->table('tb_siswa')->select('id_kelas, COUNT(*) AS n')->where('is_active', 1)->groupBy('id_kelas')->get()->getResultArray() as $r) {
         $siswaPerKelas[(int) $r['id_kelas']] = (int) $r['n'];
      }

      $hitung = [];
      foreach ($this->hitung('siswa', $mulai, $sampai, null, 'tb_siswa.id_kelas') as $r) {
         $hitung[(int) $r['id_kelas']][(string) $r['id_kehadiran']] = (int) $r['jumlah'];
      }

      $hasil = [];
      foreach ((new KelasModel())->getDataKelas() as $k) {
         $id    = (int) $k['id_kelas'];
         $siswa = $siswaPerKelas[$id] ?? 0;
         if ($siswa === 0) {
            continue;
         }
         $hasil[] = $this->baris(['id_kelas' => $id, 'kelas' => $k['kelas'], 'siswa' => $siswa], $hitung[$id] ?? [], $siswa, $hariEfektif);
      }

      usort($hasil, static fn ($a, $b) => [$a['persen'], $a['kelas']] <=> [$b['persen'], $b['kelas']]);

      return $hasil;
   }

   private function baris(array $awal, array $c, int $orang, int $hari): array
   {
      $hadir = $c[self::HADIR] ?? 0;
      $sakit = $c[self::SAKIT] ?? 0;
      $izin  = $c[self::IZIN] ?? 0;
      $total = $orang * $hari;

      return $awal + [
         'hadir'  => $hadir,
         'sakit'  => $sakit,
         'izin'   => $izin,
         'alfa'   => max(0, $total - $hadir - $sakit - $izin),
         'total'  => $total,
         'persen' => $total > 0 ? round($hadir / $total * 100, 1) : 0.0,
      ];
   }

   /** @return list<string> tanggal yang punya catatan presensi, urut naik */
   private function hariEfektif(string $tipe, string $mulai, string $sampai): array
   {
      $rows = $this->db->table($tipe === 'guru' ? 'tb_presensi_guru' : 'tb_presensi_siswa')
         ->select('tanggal')
         ->distinct()
         ->where('tanggal >=', $mulai)
         ->where('tanggal <=', $sampai)
         ->orderBy('tanggal')
         ->get()->getResultArray();

      return array_column($rows, 'tanggal');
   }

   private function jumlahOrang(string $tipe, $idKelas): int
   {
      if ($tipe === 'guru') {
         return $this->db->table('tb_guru')->countAllResults();
      }

      $q = $this->db->table('tb_siswa')->where('is_active', 1);
      if ($idKelas) {
         $q->where('id_kelas', $idKelas);
      }

      return $q->countAllResults();
   }

   /** Jumlah catatan per (kunci, id_kehadiran) dalam rentang tanggal. */
   private function hitung(string $tipe, string $mulai, string $sampai, $idKelas, string $kunci): array
   {
      if ($tipe === 'guru') {
         $q = $this->db->table('tb_presensi_guru p')
            ->join('tb_guru', 'tb_guru.id_guru = p.id_guru');
      } else {
         $q = $this->db->table('tb_presensi_siswa p')
            ->join('tb_siswa', 'tb_siswa.id_siswa = p.id_siswa')
            ->where('tb_siswa.is_active', 1);
         if ($idKelas) {
            $q->where('tb_siswa.id_kelas', $idKelas);
         }
      }

      $alias = $kunci === 'tanggal' ? 'p.tanggal AS tanggal' : "{$kunci} AS id_kelas";
      $group = $kunci === 'tanggal' ? 'p.tanggal' : $kunci;

      return $q->select("{$alias}, p.id_kehadiran, COUNT(*) AS jumlah", false)
         ->where('p.tanggal >=', $mulai)
         ->where('p.tanggal <=', $sampai)
         ->groupBy([$group, 'p.id_kehadiran'])
         ->get()->getResultArray();
   }
}
