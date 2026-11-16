<div class="table-wrap">
   <table class="table">
      <thead>
         <tr>
            <th class="num">No</th>
            <th>Tingkat</th>
            <th>Indeks</th>
            <th>Wali kelas</th>
            <th class="text-right">Aksi</th>
         </tr>
      </thead>
      <tbody>
         <?php $i = 1; foreach ($data as $value): ?>
            <tr>
               <td class="num"><?= $i; ?></td>
               <td class="font-medium text-slate-900"><?= $value['tingkat']; ?></td>
               <td class="font-medium text-slate-900"><?= $value['index_kelas']; ?></td>
               <td><?= $value['nama_wali_kelas'] ? '<span class="text-slate-700">' . $value['nama_wali_kelas'] . '</span>' : '<span class="text-slate-400">Belum diset</span>'; ?></td>
               <td>
                  <div class="flex items-center justify-end gap-1">
                     <a href="<?= base_url('admin/kelas/edit/' . $value['id_kelas']); ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit kelas" aria-label="Edit kelas">
                        <i class="material-icons">edit</i>
                     </a>
                     <button onclick='deleteItem("admin/kelas/deleteKelasPost","<?= $value["id_kelas"]; ?>","Konfirmasi untuk menghapus kelas ini?");' class="btn btn-ghost-danger btn-icon btn-sm" title="Hapus kelas" aria-label="Hapus kelas">
                        <i class="material-icons">delete</i>
                     </button>
                  </div>
               </td>
            </tr>
         <?php $i++; endforeach; ?>
         <?php if(empty($data)): ?>
            <tr>
               <td colspan="5" class="empty">Belum ada data kelas.</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>
</div>
