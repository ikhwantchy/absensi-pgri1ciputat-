<?php

use App\Libraries\enums\TipeUser;

/*
 * Struktur dibaca ulang oleh scan/kiosk.php (formatKioskResult):
 * - h3 pertama = judul, kelas text-success / text-warning / text-danger menentukan status;
 * - setiap <p> = "Label : <b>nilai</b>";
 * - elemen .mt-4 = pesan terlambat.
 */
$rowClass = 'flex gap-2';
$labelClass = 'w-24 shrink-0 text-slate-500';

switch ($type) {
   case TipeUser::Siswa:
      $isTerlambat = ($waktu == 'masuk' && ($presensi['keterangan'] ?? '') === 'Terlambat');
      ?>
      <div class="card overflow-hidden">
         <div class="card-head justify-start">
            <?php if ($isTerlambat): ?>
               <i class="material-icons text-amber-700">schedule</i>
               <h3 class="text-warning text-sm font-semibold text-amber-700">Absen <?= $waktu; ?> berhasil (terlambat)</h3>
            <?php else: ?>
               <i class="material-icons text-green-700">check_circle</i>
               <h3 class="text-success text-sm font-semibold text-green-700">Absen <?= $waktu; ?> berhasil</h3>
            <?php endif; ?>
         </div>
         <div class="card-body">
            <div class="flex gap-4">
               <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700" aria-hidden="true">
                  <i class="material-icons text-3xl!">person</i>
               </div>
               <div class="grid min-w-0 flex-1 gap-x-6 gap-y-1 sm:grid-cols-2">
                  <div class="min-w-0 space-y-1">
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Nama<span class="sr-only"> :</span></span> <b class="min-w-0 font-semibold text-slate-900"><?= esc($data['nama_siswa']); ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">NIS<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= esc($data['nis']); ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Kelas<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900"><?= $data['kelas']; ?></b></p>
                  </div>
                  <div class="space-y-1">
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Jam masuk<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= $presensi['jam_masuk'] ?? '-'; ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Jam pulang<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= $presensi['jam_keluar'] ?? '-'; ?></b></p>
                  </div>
               </div>
            </div>
            <?php if ($isTerlambat): ?>
               <div class="mt-4 alert alert-warning font-medium">Peringatan: Anda datang terlambat (melewati batas jam <?= esc(getenv('JAM_MASUK') ?: '07:00') ?>).</div>
            <?php endif; ?>
         </div>
      </div>
      <?php break;

   case TipeUser::Guru:
      $isTerlambat = ($waktu == 'masuk' && ($presensi['keterangan'] ?? '') === 'Terlambat');
      ?>
      <div class="card overflow-hidden">
         <div class="card-head justify-start">
            <?php if ($isTerlambat): ?>
               <i class="material-icons text-amber-700">schedule</i>
               <h3 class="text-warning text-sm font-semibold text-amber-700">Absen <?= $waktu; ?> berhasil (terlambat)</h3>
            <?php else: ?>
               <i class="material-icons text-green-700">check_circle</i>
               <h3 class="text-success text-sm font-semibold text-green-700">Absen <?= $waktu; ?> berhasil</h3>
            <?php endif; ?>
         </div>
         <div class="card-body">
            <div class="flex gap-4">
               <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700" aria-hidden="true">
                  <i class="material-icons text-3xl!">person</i>
               </div>
               <div class="grid min-w-0 flex-1 gap-x-6 gap-y-1 sm:grid-cols-2">
                  <div class="min-w-0 space-y-1">
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Nama<span class="sr-only"> :</span></span> <b class="min-w-0 font-semibold text-slate-900"><?= $data['nama_guru']; ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">NUPTK<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= $data['nuptk']; ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">No HP<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= esc($data['no_hp']); ?></b></p>
                  </div>
                  <div class="space-y-1">
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Jam masuk<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= $presensi['jam_masuk'] ?? '-'; ?></b></p>
                     <p class="<?= $rowClass ?>"><span class="<?= $labelClass ?>">Jam pulang<span class="sr-only"> :</span></span> <b class="font-medium text-slate-900 tabular-nums"><?= $presensi['jam_keluar'] ?? '-'; ?></b></p>
                  </div>
               </div>
            </div>
            <?php if ($isTerlambat): ?>
               <div class="mt-4 alert alert-warning font-medium">Peringatan: Anda datang terlambat (melewati batas jam <?= esc(getenv('JAM_MASUK') ?: '07:00') ?>).</div>
            <?php endif; ?>
         </div>
      </div>
      <?php break;

   default:
      ?>
      <div class="alert alert-danger">
         <i class="material-icons">error</i>
         <h3 class="text-danger font-semibold">Tipe tidak valid</h3>
      </div>
      <?php
      break;
}

?>
