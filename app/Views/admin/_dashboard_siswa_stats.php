<?php
$cells = [
   ['Hadir', $hadir, 'text-green-700'],
   ['Sakit', $sakit, 'text-amber-700'],
   ['Izin', $izin, 'text-sky-700'],
   ['Alfa', $alfa, 'text-red-700'],
   ['Total', $totalSiswa, 'text-slate-900'],
];
?>
<dl class="grid grid-cols-5 divide-x divide-slate-100 text-center">
   <?php foreach ($cells as [$label, $value, $color]): ?>
      <div class="px-1">
         <dt class="text-xs font-semibold <?= $color; ?>"><?= $label; ?></dt>
         <dd class="mt-0.5 text-xl font-semibold tabular-nums text-slate-900"><?= $value; ?></dd>
      </div>
   <?php endforeach; ?>
</dl>
