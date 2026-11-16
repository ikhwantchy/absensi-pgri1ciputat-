<div class="card overflow-hidden">
   <div class="card-head justify-start">
      <i class="material-icons text-red-700">cancel</i>
      <h3 class="text-danger text-sm font-semibold text-red-700"><?= $msg; ?></h3>
   </div>

<?php

use App\Libraries\enums\TipeUser;

$rowClass = 'flex gap-2';
$labelClass = 'w-24 shrink-0 text-slate-500';

if (empty($type)) {
   echo '</div>';
   return;
} else {
   switch ($type) {
      case TipeUser::Siswa: ?>
         <div class="card-body grid gap-x-6 gap-y-1 sm:grid-cols-2">
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
         <?php break;

      case TipeUser::Guru: ?>
         <div class="card-body grid gap-x-6 gap-y-1 sm:grid-cols-2">
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
         <?php break;

      default: ?>
         <div class="card-body">
            <span class="status status-danger">Tipe tidak valid</span>
         </div>
         <?php break;
   }
}

?>
</div>
