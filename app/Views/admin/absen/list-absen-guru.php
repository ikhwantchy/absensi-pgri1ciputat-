<?php
use App\Libraries\enums\UserRole;

/* ---- Helpers ---- */
if (!function_exists('kehadiran')) {
   function kehadiran(int $k): array {
      return match($k) {
         1 => ['label' => 'Hadir',       'class' => 'status-success', 'icon' => 'check_circle'],
         2 => ['label' => 'Sakit',       'class' => 'status-warning', 'icon' => 'sick'],
         3 => ['label' => 'Izin',        'class' => 'status-info',    'icon' => 'assignment'],
         4 => ['label' => 'Alfa',        'class' => 'status-danger',  'icon' => 'cancel'],
         default => ['label' => 'Belum absen', 'class' => 'status-muted', 'icon' => 'schedule'],
      };
   }
}

if (!function_exists('avatarBg')) {
   function avatarBg(string $name): string {
      $palette = ['7C3AED','10B981','3B82F6','F59E0B','EF4444','06B6D4','F97316','8B5CF6'];
      return $palette[ord(strtolower($name[0] ?? 'a')) % count($palette)];
   }
}

$total = count($data ?? []);
?>

<section class="card overflow-hidden">
   <div class="card-head">
      <div>
         <h2 class="card-title">Absensi guru</h2>
         <p class="card-sub"><?= $total; ?> guru · <?= date('d M Y', strtotime($tanggal ?? date('Y-m-d'))); ?></p>
      </div>
      <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
         <div class="input-icon min-w-0 flex-1 sm:w-64 sm:flex-none">
            <i class="material-icons">search</i>
            <input type="text" id="searchGuru" class="input" placeholder="Cari nama / NUPTK" oninput="filterTable()">
         </div>
         <button type="button" onclick="getGuru()" class="btn btn-secondary">
            <i class="material-icons">refresh</i>Muat ulang
         </button>
      </div>
   </div>

   <?php if (!empty($data)): ?>
   <div class="table-wrap">
      <table id="tabelGuru" class="table">
         <thead>
            <tr>
               <th class="w-10"></th>
               <th>Nama guru</th>
               <th>NUPTK</th>
               <th>Kehadiran</th>
               <th>Jam masuk</th>
               <th>Jam pulang</th>
               <th>Keterangan</th>
               <?php if (can_edit_attendance()): ?><th class="text-right">Aksi</th><?php endif; ?>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($data as $row):
               $idKehadiran = intval($row['id_kehadiran'] ?? ($lewat ? 5 : 4));
               $k           = kehadiran($idKehadiran);
               $bg          = avatarBg($row['nama_guru']);
               $initials    = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $row['nama_guru']))));
               $initials    = substr($initials, 0, 2);
            ?>
            <tr>
               <td class="w-10">
                  <input type="checkbox" class="cb" aria-label="Pilih <?= esc($row['nama_guru'], 'attr'); ?>">
               </td>
               <td class="whitespace-nowrap">
                  <div class="flex items-center gap-2.5">
                     <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: #<?= $bg; ?>;"><?= $initials; ?></span>
                     <span class="font-medium text-slate-900"><?= esc($row['nama_guru']); ?></span>
                  </div>
               </td>
               <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($row['nuptk']); ?></td>
               <td><span class="status <?= $k['class']; ?>"><i class="material-icons"><?= $k['icon']; ?></i><?= $k['label']; ?></span></td>
               <td class="whitespace-nowrap tabular-nums"><?= $row['jam_masuk']  ?? '—'; ?></td>
               <td class="whitespace-nowrap tabular-nums"><?= $row['jam_keluar'] ?? '—'; ?></td>
               <td class="min-w-[10rem] text-slate-600"><?= esc($row['keterangan'] ?? '—'); ?></td>
               <?php if (can_edit_attendance()): ?>
               <td class="text-right">
                  <?php if (!$lewat): ?>
                     <button type="button" class="btn btn-ghost btn-icon btn-sm"
                             data-bs-toggle="modal"
                             data-bs-target="#ubahModal"
                             onclick="getDataKehadiran(<?= $row['id_presensi'] ?? -1; ?>, <?= $row['id_guru']; ?>)"
                             title="Ubah kehadiran" aria-label="Ubah kehadiran">
                        <i class="material-icons">edit</i>
                     </button>
                  <?php else: ?>
                     <span class="text-slate-400">—</span>
                  <?php endif; ?>
               </td>
               <?php endif; ?>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>

   <div class="border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
      Menampilkan <strong class="text-slate-900" id="rowCount"><?= $total; ?></strong> dari <strong class="text-slate-900"><?= $total; ?></strong> guru
   </div>

   <?php else: ?>
   <p class="empty">Tidak ada data absensi guru. Periksa tanggal.</p>
   <?php endif; ?>
</section>

<script>
function filterTable() {
   var q = document.getElementById('searchGuru').value.toLowerCase();
   var rows = document.querySelectorAll('#tabelGuru tbody tr');
   var visible = 0;
   rows.forEach(function(row) {
      var text = row.textContent.toLowerCase();
      var show = text.includes(q);
      row.style.display = show ? '' : 'none';
      if (show) visible++;
   });
   var el = document.getElementById('rowCount');
   if (el) el.textContent = visible;
}

// Select all checkbox
document.querySelectorAll('#tabelGuru thead .cb')?.forEach(function(cb) {
   cb.addEventListener('change', function() {
      document.querySelectorAll('#tabelGuru tbody .cb').forEach(function(c) {
         c.checked = cb.checked;
      });
   });
});
</script>
