<?php if (!$empty): ?>
   <div class="table-wrap">
      <table class="table">
         <thead>
            <tr>
               <th class="w-10"><input type="checkbox" class="cb align-middle" id="checkAll" aria-label="Pilih semua"></th>
               <th class="num">No</th>
               <th>NIS</th>
               <th>Nama siswa</th>
               <th>Jenis kelamin</th>
               <th>Kelas</th>
               <th>No HP</th>
               <th class="text-right">Aksi</th>
            </tr>
         </thead>
         <tbody>
            <?php $i = 1; foreach ($data as $value): ?>
               <tr>
                  <td><input type="checkbox" name="checkbox-table" class="cb checkbox-table align-middle" value="<?= $value['id_siswa']; ?>" aria-label="Pilih <?= esc($value['nama_siswa'], 'attr'); ?>"></td>
                  <td class="num"><?= $i; ?></td>
                  <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($value['nis']); ?></td>
                  <td class="min-w-[12rem]">
                     <div class="font-medium text-slate-900"><?= esc($value['nama_siswa']); ?></div>
                     <?php
                     $isNonaktif = isset($value['is_active']) && (int) $value['is_active'] === 0;
                     $rfidKosong = array_key_exists('rfid_code', $value) && trim((string) $value['rfid_code']) === '';
                     ?>
                     <?php if (!empty($value['ppdb_ref']) || $isNonaktif || $rfidKosong): ?>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5">
                           <?php if (!empty($value['ppdb_ref'])): ?>
                              <span class="status status-info"><i class="material-icons">sync</i>PPDB</span>
                           <?php endif; ?>
                           <?php if ($isNonaktif): ?>
                              <span class="status status-danger"><i class="material-icons">block</i>Nonaktif</span>
                           <?php endif; ?>
                           <?php if ($rfidKosong): ?>
                              <span class="status status-muted"><i class="material-icons">contactless_off</i>RFID kosong</span>
                           <?php endif; ?>
                        </div>
                     <?php endif; ?>
                  </td>
                  <td class="whitespace-nowrap text-slate-600"><?= $value['jenis_kelamin']; ?></td>
                  <td class="whitespace-nowrap"><?= $value['kelas']; ?></td>
                  <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($value['no_hp'] ?: '-'); ?></td>
                  <td>
                     <div class="flex items-center justify-end gap-1">
                        <a title="Edit" aria-label="Edit" href="<?= base_url('admin/siswa/edit/' . $value['id_siswa']); ?>" class="btn btn-ghost btn-icon btn-sm">
                           <i class="material-icons">edit</i>
                        </a>
                        <form action="<?= base_url('admin/siswa/delete/' . $value['id_siswa']); ?>" method="post" class="m-0">
                           <?= csrf_field(); ?>
                           <input type="hidden" name="_method" value="DELETE">
                           <button title="Hapus" aria-label="Hapus" type="submit" data-confirm="Konfirmasi untuk menghapus data siswa ini?" class="btn btn-ghost-danger btn-icon btn-sm btn-delete-confirm">
                              <i class="material-icons">delete</i>
                           </button>
                        </form>
                        <a title="Unduh QR code" aria-label="Unduh QR code" href="<?= base_url('admin/qr/siswa/' . $value['id_siswa'] . '/download'); ?>" class="btn btn-ghost btn-icon btn-sm">
                           <i class="material-icons">qr_code_2</i>
                        </a>
                     </div>
                  </td>
               </tr>
               <?php $i++; endforeach; ?>
         </tbody>
      </table>
   </div>
<?php else: ?>
   <div class="empty">Data siswa tidak ditemukan. Coba ubah filter.</div>
<?php endif; ?>
