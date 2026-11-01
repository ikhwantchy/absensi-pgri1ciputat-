<?php

namespace App\Controllers\Api;

use App\Models\SiswaModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

/**
 * POST /api/siswa/sync — terima siswa baru dari PPDB dan simpan berdasarkan NIS (upsert).
 * Body JSON: {"students": [{"nis", "nama_siswa", "id_kelas", "jenis_kelamin", "no_hp"}]}
 * Kolom sama dengan template impor CSV siswa. rfid_code dan unique_code yang sudah ada tidak diubah.
 */
class SiswaSync extends Controller
{
   use ResponseTrait;

   public function sync()
   {
      $students = $this->request->getJSON(true)['students'] ?? null;
      if (!is_array($students)) {
         return $this->respond(['message' => 'Format tidak valid: butuh "students".'], 422);
      }

      $kelasIds = array_map('intval', array_column(
         db_connect()->table('tb_kelas')->select('id_kelas')->where('deleted_at', null)->get()->getResultArray(),
         'id_kelas'
      ));
      $siswaModel = new SiswaModel();
      $created = $updated = 0;
      $errors = [];

      foreach ($students as $i => $row) {
         $data = [
            'nis' => preg_replace('/\D/', '', (string) ($row['nis'] ?? '')),
            'nama_siswa' => trim((string) ($row['nama_siswa'] ?? '')),
            'id_kelas' => (int) ($row['id_kelas'] ?? 0),
            'jenis_kelamin' => (string) ($row['jenis_kelamin'] ?? ''),
            'no_hp' => preg_replace('/[^0-9+]/', '', (string) ($row['no_hp'] ?? '')),
         ];

         $problem = match (true) {
            $data['nis'] === '' || strlen($data['nis']) > 16 => 'NIS tidak valid',
            $data['nama_siswa'] === '' => 'Nama kosong',
            !in_array($data['id_kelas'], $kelasIds, true) => 'id_kelas tidak ada di absensi',
            !in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true) => 'jenis_kelamin harus Laki-laki/Perempuan',
            default => null,
         };
         if ($problem) {
            $errors[] = ['index' => $i, 'nis' => $data['nis'], 'message' => $problem];
            continue;
         }

         $existing = $siswaModel->where('nis', $data['nis'])->first();
         if ($existing) {
            $siswaModel->update($existing['id_siswa'], $data);
            $updated++;
         } else {
            $siswaModel->insert($data + ['unique_code' => generateToken()]);
            $created++;
         }
      }

      return $this->respond(['created' => $created, 'updated' => $updated, 'errors' => $errors], $errors && !$created && !$updated ? 422 : 200);
   }
}
