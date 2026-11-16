<div class="table-wrap">
   <table class="table">
      <thead>
         <tr>
            <th class="num">No</th>
            <th>Jurusan</th>
            <th class="text-right">Aksi</th>
         </tr>
      </thead>
      <tbody>
         <?php $i = 1; foreach ($data as $value): ?>
            <tr>
               <td class="num"><?= $i; ?></td>
               <td class="font-medium text-slate-900"><?= $value['jurusan']; ?></td>
               <td>
                  <div class="flex items-center justify-end gap-1">
                     <a href="<?= base_url('admin/jurusan/edit/' . $value['id']); ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit jurusan" aria-label="Edit jurusan">
                        <i class="material-icons">edit</i>
                     </a>
                     <button onclick='deleteItem("admin/jurusan/deleteJurusanPost","<?= $value["id"]; ?>","Konfirmasi untuk menghapus data jurusan ini?");' class="btn btn-ghost-danger btn-icon btn-sm" title="Hapus jurusan" aria-label="Hapus jurusan">
                        <i class="material-icons">delete</i>
                     </button>
                  </div>
               </td>
            </tr>
         <?php $i++; endforeach; ?>
         <?php if(empty($data)): ?>
            <tr>
               <td colspan="3" class="empty">Belum ada data jurusan.</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>
</div>
