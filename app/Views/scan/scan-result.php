<?php

use App\Libraries\enums\TipeUser;

switch ($type) {
   case TipeUser::Siswa:
      $isTerlambat = ($waktu == 'masuk' && ($presensi['keterangan'] ?? '') === 'Terlambat');
      ?>
      <?php if ($isTerlambat): ?>
         <h3 class="text-warning font-bold" style="color: #d97706; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <i class="material-icons" style="font-size: 28px; color: #d97706;">warning</i> Absen <?= $waktu; ?> Berhasil (Terlambat)
         </h3>
      <?php else: ?>
         <h3 class="text-success">Absen <?= $waktu; ?> berhasil</h3>
      <?php endif; ?>
      <div class="row w-100">
         <div class="col">
            <p>Nama : <b><?= esc($data['nama_siswa']); ?></b></p>
            <p>NIS : <b><?= esc($data['nis']); ?></b></p>
            <p>Kelas : <b><?= $data['kelas']; ?></b></p>
         </div>
         <div class="col">
             <p>Jam masuk : <b class="text-info"><?= $presensi['jam_masuk'] ?? '-'; ?></b></p>
             <p>Jam pulang : <b class="text-info"><?= $presensi['jam_keluar'] ?? '-'; ?></b></p>
         </div>
      </div>
      <?php if ($isTerlambat): ?>
         <div class="mt-4 p-4 rounded-xl flex items-center gap-3" style="background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-weight: 600;">
            <i class="material-icons" style="font-size: 24px; color: #d97706;">alarm</i>
            <span>Peringatan: Anda datang terlambat (melewati batas jam <?= esc(getenv('JAM_MASUK') ?: '07:00') ?>).</span>
         </div>
      <?php endif; ?>
      <?php break;

   case TipeUser::Guru:
      $isTerlambat = ($waktu == 'masuk' && ($presensi['keterangan'] ?? '') === 'Terlambat');
      ?>
      <?php if ($isTerlambat): ?>
         <h3 class="text-warning font-bold" style="color: #d97706; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <i class="material-icons" style="font-size: 28px; color: #d97706;">warning</i> Absen <?= $waktu; ?> Berhasil (Terlambat)
         </h3>
      <?php else: ?>
         <h3 class="text-success">Absen <?= $waktu; ?> berhasil</h3>
      <?php endif; ?>
      <div class="row w-100">
         <div class="col">
            <p>Nama : <b><?= $data['nama_guru']; ?></b></p>
            <p>NUPTK : <b><?= $data['nuptk']; ?></b></p>
            <p>No HP : <b><?= esc($data['no_hp']); ?></b></p>
         </div>
         <div class="col">
             <p>Jam masuk : <b class="text-info"><?= $presensi['jam_masuk'] ?? '-'; ?></b></p>
             <p>Jam pulang : <b class="text-info"><?= $presensi['jam_keluar'] ?? '-'; ?></b></p>
         </div>
      </div>
      <?php if ($isTerlambat): ?>
         <div class="mt-4 p-4 rounded-xl flex items-center gap-3" style="background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-weight: 600;">
            <i class="material-icons" style="font-size: 24px; color: #d97706;">alarm</i>
            <span>Peringatan: Anda datang terlambat (melewati batas jam <?= esc(getenv('JAM_MASUK') ?: '07:00') ?>).</span>
         </div>
      <?php endif; ?>
      <?php break;

   default:
      ?>
      <h3 class="text-danger">Tipe tidak valid</h3>
      <?php
      break;
}

?>
