<?php
use App\Libraries\enums\UserRole;

/* ---- Helpers ---- */
if (!function_exists('kehadiran')) {
   function kehadiran(int $k): array {
      return match($k) {
         1 => ['label' => 'Hadir',       'class' => 'badge-success'],
         2 => ['label' => 'Sakit',       'class' => 'badge-warning'],
         3 => ['label' => 'Izin',        'class' => 'badge-info'],
         4 => ['label' => 'Alfa',        'class' => 'badge-danger'],
         default => ['label' => 'Belum', 'class' => 'badge-muted'],
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

<div class="card" style="padding: 28px; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); background: #ffffff;">

   <!-- Table Header -->
   <div class="flex items-center justify-between" style="margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
      <div>
         <div style="font-size: 18px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.3px;">Data Absensi Siswa</div>
         <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">
            Kelas: <strong style="color: var(--primary); font-weight: 600;"><?= esc($kelas); ?></strong>
            &nbsp;·&nbsp; <?= $total; ?> siswa terdaftar
         </div>
      </div>

      <div class="flex items-center gap-3" style="flex-wrap: wrap;">
         <!-- Search -->
         <div style="position: relative; display: flex; align-items: center; min-width: 280px;">
            <i class="material-icons" style="position: absolute; left: 14px; font-size: 20px; color: var(--text-muted); pointer-events: none;">search</i>
            <input type="text" id="searchSiswa" class="form-control" placeholder="Cari nama / NIS…" oninput="filterTable()" style="padding-left: 44px !important; height: 44px; border-radius: 10px; font-size: 14px; border: 1px solid var(--border); width: 100%; box-shadow: none;">
         </div>
         <!-- Refresh -->
         <button onclick="onDateChange()" class="btn btn-outline" style="height: 44px; padding: 0 20px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; border-color: var(--border);">
            <i class="material-icons" style="font-size: 18px; color: var(--text-secondary);">refresh</i> Refresh
         </button>
      </div>
   </div>

   <!-- Table -->
   <?php if (!empty($data)): ?>
   <div class="table-responsive border-0" style="overflow-x: auto; margin: 0 -28px; padding: 0 28px;">
      <table id="tabelSiswa" style="width: 100%; border-collapse: separate; border-spacing: 0;">
         <thead>
            <tr style="background: #f8fafc;">
               <th style="width: 48px; padding: 16px 20px; border-bottom: 2px solid #e2e8f0; border-top-left-radius: 10px; border-bottom-left-radius: 10px; white-space: nowrap;"></th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Nama Siswa</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">NIS</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Kehadiran</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Jam Masuk</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Jam Pulang</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Keterangan</th>
               <?php if (can_edit_attendance()): ?><th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; text-align: center; border-bottom: 2px solid #e2e8f0; border-top-right-radius: 10px; border-bottom-right-radius: 10px; white-space: nowrap;">Aksi</th><?php endif; ?>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($data as $row):
               $idKehadiran = intval($row['id_kehadiran'] ?? ($lewat ? 5 : 4));
               $k           = kehadiran($idKehadiran);
               $bg          = avatarBg($row['nama_siswa']);
               $initials    = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $row['nama_siswa']))));
               $initials    = substr($initials, 0, 2);
            ?>
            <tr style="transition: all 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
               <!-- Checkbox -->
               <td style="width: 48px; padding: 18px 20px; text-align: center; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                  <input type="checkbox" class="cb" style="width: 18px; height: 18px; cursor: pointer;">
               </td>

               <!-- Nama -->
               <td style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                  <div class="flex items-center gap-3">
                     <div style="width: 40px !important; height: 40px !important; border-radius: 50% !important; background: #<?= $bg; ?>; color: #ffffff; font-size: 14px; font-weight: 700; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <?= esc($initials); ?>
                     </div>
                     <span style="font-size: 15px; font-weight: 600; color: #1e293b;"><?= esc($row['nama_siswa']); ?></span>
                  </div>
               </td>

               <!-- NIS -->
               <td style="padding: 18px 20px; color: #64748b; font-size: 14px; font-weight: 500; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">#<?= esc($row['nis']); ?></td>

               <!-- Kehadiran Badge -->
               <td style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                  <span class="badge <?= $k['class']; ?>" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 20px; display: inline-block; white-space: nowrap; letter-spacing: 0.3px;"><?= $k['label']; ?></span>
               </td>

               <!-- Jam -->
               <td style="padding: 18px 20px; font-size: 14px; font-weight: 600; color: #334155; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= $row['jam_masuk']  ?? '—'; ?></td>
               <td style="padding: 18px 20px; font-size: 14px; font-weight: 600; color: #334155; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= $row['jam_keluar'] ?? '—'; ?></td>

               <!-- Keterangan -->
               <td style="padding: 18px 20px; font-size: 14px; color: #64748b; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= esc($row['keterangan'] ?? '—'); ?></td>

               <!-- Aksi -->
               <?php if (can_edit_attendance()): ?>
               <td style="padding: 18px 20px; text-align: center; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                  <?php if (!$lewat): ?>
                     <button class="btn btn-sm btn-outline"
                             data-bs-toggle="modal"
                             data-bs-target="#ubahModal"
                             onclick="getDataKehadiran(<?= $row['id_presensi'] ?? -1; ?>, <?= $row['id_siswa']; ?>)"
                             style="padding: 8px 14px; height: 38px; border-radius: 8px; border-color: #cbd5e1; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i class="material-icons" style="font-size: 16px; color: #64748b;">edit</i>
                     </button>
                  <?php else: ?>
                     <span style="font-size: 14px; color: #94a3b8;">—</span>
                  <?php endif; ?>
               </td>
               <?php endif; ?>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>

   <!-- Footer: count -->
   <div class="flex items-center justify-between" style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
      <div style="font-size: 14px; color: #64748b;">
         Menampilkan <strong style="color: #0f172a; font-weight: 700;" id="rowCount"><?= $total; ?></strong> dari <strong style="color: #0f172a; font-weight: 700;"><?= $total; ?></strong> siswa
      </div>
      <div style="font-size: 14px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 6px 16px; border-radius: 20px;">
         <?= date('d M Y', strtotime($tanggal ?? date('Y-m-d'))); ?>
      </div>
   </div>

   <?php else: ?>
   <!-- Empty -->
   <div style="text-align:center;padding:48px 24px;">
      <i class="material-icons" style="font-size:48px;color:var(--border);display:block;margin-bottom:12px;">search_off</i>
      <div style="font-size:14px;font-weight:500;color:var(--text-secondary);">Tidak ada data untuk kelas ini</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Pastikan kelas dan tanggal sudah benar</div>
   </div>
   <?php endif; ?>

</div>

<script>
function filterTable() {
   var q = document.getElementById('searchSiswa').value.toLowerCase();
   var rows = document.querySelectorAll('#tabelSiswa tbody tr');
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
document.querySelectorAll('#tabelSiswa thead .cb')?.forEach(function(cb) {
   cb.addEventListener('change', function() {
      document.querySelectorAll('#tabelSiswa tbody .cb').forEach(function(c) {
         c.checked = cb.checked;
      });
   });
});
</script>