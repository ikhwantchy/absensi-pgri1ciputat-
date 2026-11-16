<?php if (!$empty) : ?>
   <div class="table-wrap">
      <table class="table">
         <thead>
            <tr>
               <th class="num">No</th>
               <th>NUPTK</th>
               <th>Nama guru</th>
               <th>Jenis kelamin</th>
               <th>No HP</th>
               <th>Alamat</th>
               <th class="text-right">Aksi</th>
            </tr>
         </thead>
         <tbody>
            <?php $i = 1; foreach ($data as $value) : ?>
               <tr>
                  <td class="num"><?= $i; ?></td>
                  <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($value['nuptk']); ?></td>
                  <td class="min-w-[12rem] font-medium text-slate-900"><?= esc($value['nama_guru']); ?></td>
                  <td class="whitespace-nowrap text-slate-600"><?= esc($value['jenis_kelamin']); ?></td>
                  <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($value['no_hp']); ?></td>
                  <td class="max-w-[16rem] truncate text-slate-600" title="<?= htmlspecialchars($value['alamat']) ?>"><?= esc($value['alamat']); ?></td>
                  <td>
                     <div class="flex items-center justify-end gap-1">
                        <a title="Edit" aria-label="Edit" href="<?= base_url('admin/guru/edit/' . $value['id_guru']); ?>" class="btn btn-ghost btn-icon btn-sm">
                           <i class="material-icons">edit</i>
                        </a>
                        <form action="<?= base_url('admin/guru/delete/' . $value['id_guru']); ?>" method="post" class="m-0">
                           <?= csrf_field(); ?>
                           <input type="hidden" name="_method" value="DELETE">
                           <button title="Hapus" aria-label="Hapus" type="submit" data-confirm="Konfirmasi untuk menghapus data guru ini?" class="btn btn-ghost-danger btn-icon btn-sm btn-delete-confirm">
                              <i class="material-icons">delete</i>
                           </button>
                        </form>
                        <a title="Unduh QR code" aria-label="Unduh QR code" href="<?= base_url('admin/qr/guru/' . $value['id_guru'] . '/download'); ?>" class="btn btn-ghost btn-icon btn-sm">
                           <i class="material-icons">qr_code_2</i>
                        </a>
                     </div>
                  </td>
               </tr>
            <?php $i++; endforeach; ?>
         </tbody>
      </table>
   </div>
<?php else : ?>
   <div class="empty">Belum ada data guru.</div>
<?php endif; ?>
