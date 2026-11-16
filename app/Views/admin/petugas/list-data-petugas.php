<?php
   $total = count($data ?? []);
   if (!function_exists('avatarBg')) {
      function avatarBg(string $name): string {
         $palette = ['7C3AED','10B981','3B82F6','F59E0B','EF4444','06B6D4','F97316','8B5CF6'];
         return $palette[ord(strtolower($name[0] ?? 'a')) % count($palette)];
      }
   }
?>

<section class="card overflow-hidden">
   <div class="card-head">
      <div>
         <h2 class="card-title">Daftar petugas</h2>
         <p class="card-sub">Total <span class="tabular-nums"><?= $total; ?></span> akun</p>
      </div>
      <div class="input-icon w-full sm:w-64">
         <i class="material-icons">search</i>
         <input type="text" id="searchPetugas" class="input" placeholder="Cari username atau email" aria-label="Cari petugas" oninput="filterTable()">
      </div>
   </div>

   <?php if (!empty($data)): ?>
   <div class="table-wrap">
      <table id="tabelPetugas" class="table">
         <thead>
            <tr>
               <th>User</th>
               <th>Email</th>
               <th>Peran</th>
               <th>Guru terkait</th>
               <th>Status</th>
               <th class="text-right">Aksi</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($data as $value):
               $initials = strtoupper(substr($value['username'], 0, 2));
               $bg = avatarBg($value['username']);
               $isActive = ($value['active'] ?? 0) == 1;
            ?>
               <tr>
                  <td class="whitespace-nowrap">
                     <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: #<?= $bg; ?>;"><?= $initials; ?></span>
                        <span class="font-medium text-slate-900"><?= esc($value['username']); ?></span>
                     </div>
                  </td>

                  <td class="whitespace-nowrap text-slate-600"><?= esc($value['email']); ?></td>

                  <td class="whitespace-nowrap text-slate-700"><?= getUserRole($value['is_superadmin']); ?></td>

                  <td class="whitespace-nowrap text-slate-600"><?= esc($value['nama_guru'] ?? '—'); ?></td>

                  <td>
                     <?php if ($isActive): ?>
                        <span class="status status-success"><i class="material-icons">check_circle</i>Aktif</span>
                     <?php else: ?>
                        <span class="status status-danger"><i class="material-icons">cancel</i>Nonaktif</span>
                     <?php endif; ?>
                  </td>

                  <td>
                     <?php if ($value['username'] == 'superadmin'): ?>
                        <div class="flex justify-end">
                           <button disabled class="btn btn-ghost btn-icon btn-sm" title="Superadmin tidak dapat diubah" aria-label="Superadmin tidak dapat diubah">
                              <i class="material-icons">lock</i>
                           </button>
                        </div>
                     <?php else: ?>
                        <div class="flex items-center justify-end gap-1">
                           <a href="<?= base_url('admin/petugas/activate/' . $value['id']); ?>"
                              title="<?= $isActive ? 'Non-aktifkan' : 'Aktifkan'; ?>"
                              aria-label="<?= $isActive ? 'Non-aktifkan' : 'Aktifkan'; ?>"
                              class="btn btn-ghost btn-icon btn-sm <?= $isActive ? 'text-amber-700' : 'text-green-700'; ?>">
                              <i class="material-icons"><?= $isActive ? 'block' : 'check_circle'; ?></i>
                           </a>

                           <a href="<?= base_url('admin/petugas/edit/' . $value['id']); ?>"
                              title="Edit data"
                              aria-label="Edit data"
                              class="btn btn-ghost btn-icon btn-sm">
                              <i class="material-icons">edit</i>
                           </a>

                           <form action="<?= base_url('admin/petugas/delete/' . $value['id']); ?>" method="post" class="inline-flex">
                              <?= csrf_field(); ?>
                              <input type="hidden" name="_method" value="DELETE">
                              <button type="submit" data-confirm="Yakin ingin menghapus akun ini?"
                                 title="Hapus akun"
                                 aria-label="Hapus akun"
                                 class="btn btn-ghost-danger btn-icon btn-sm btn-delete-confirm">
                                 <i class="material-icons">delete</i>
                              </button>
                           </form>
                        </div>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>

   <div class="border-t border-slate-100 px-4 py-3 text-sm text-slate-500">
      Menampilkan <span class="font-semibold tabular-nums text-slate-900" id="rowCount"><?= $total; ?></span> akun
   </div>

   <?php else: ?>
   <p class="empty">Belum ada data petugas.</p>
   <?php endif; ?>
</section>

<script>
function filterTable() {
   var q = document.getElementById('searchPetugas').value.toLowerCase();
   var rows = document.querySelectorAll('#tabelPetugas tbody tr');
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
</script>
