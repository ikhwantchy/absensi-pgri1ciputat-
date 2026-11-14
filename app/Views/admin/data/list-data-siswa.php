<div class="table-responsive border-0 rounded-none" style="overflow-x: auto; margin: 0 -24px; padding: 0 24px;">
   <?php if (!$empty): ?>
      <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
         <thead>
            <tr style="background: #f8fafc;">
               <th style="width: 40px; padding: 16px 20px; border-bottom: 2px solid #e2e8f0; border-top-left-radius: 10px; border-bottom-left-radius: 10px; white-space: nowrap;">
                  <div class="flex items-center justify-center">
                     <input type="checkbox" class="cb" id="checkAll">
                  </div>
               </th>
               <th style="width: 60px; padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">No</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">NIS</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Nama Siswa</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Jenis Kelamin</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Kelas</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">No HP</th>
               <th style="padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; text-align: center; border-top-right-radius: 10px; border-bottom-right-radius: 10px; white-space: nowrap;">Aksi</th>
            </tr>
         </thead>
         <tbody>
            <?php $i = 1; foreach ($data as $value): ?>
               <tr style="transition: all 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                  <td style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; text-align: center; white-space: nowrap;">
                     <div class="flex items-center justify-center">
                        <input type="checkbox" name="checkbox-table" class="cb checkbox-table" value="<?= $value['id_siswa']; ?>">
                     </div>
                  </td>
                  <td style="padding: 18px 20px; font-size: 14px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= $i; ?></td>
                  <td style="padding: 18px 20px; font-size: 14px; font-weight: 700; color: var(--primary); border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= esc($value['nis']); ?></td>
                  <td style="padding: 18px 20px; font-size: 15px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                     <?= esc($value['nama_siswa']); ?>
                     <?php if (!empty($value['ppdb_ref'])): ?>
                        <span style="margin-left: 8px; font-size: 11px; font-weight: 700; color: #1d4ed8; background: #dbeafe; padding: 3px 8px; border-radius: 6px;">PPDB</span>
                     <?php endif; ?>
                     <?php if (isset($value['is_active']) && (int) $value['is_active'] === 0): ?>
                        <span style="margin-left: 8px; font-size: 11px; font-weight: 700; color: #b91c1c; background: #fee2e2; padding: 3px 8px; border-radius: 6px;">Nonaktif</span>
                     <?php endif; ?>
                  </td>
                  <td style="padding: 18px 20px; font-size: 14px; color: #475569; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= $value['jenis_kelamin']; ?></td>
                  <td style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                     <span style="font-weight: 700; color: #334155; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; font-size: 13px; display: inline-block; white-space: nowrap;"><?= $value['kelas']; ?></span>
                  </td>
                  <td style="padding: 18px 20px; font-size: 14px; color: #475569; border-bottom: 1px solid #f1f5f9; white-space: nowrap;"><?= esc($value['no_hp'] ?: '-'); ?></td>
                  <td style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                     <div class="flex items-center justify-center gap-2">
                        <a title="Edit" href="<?= base_url('admin/siswa/edit/' . $value['id_siswa']); ?>"
                           class="btn btn-sm btn-outline" style="height: 36px; width: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #3b82f6; border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.08);">
                           <i class="material-icons text-[16px]">edit</i>
                        </a>
                        <form action="<?= base_url('admin/siswa/delete/' . $value['id_siswa']); ?>" method="post" class="m-0">
                           <?= csrf_field(); ?>
                           <input type="hidden" name="_method" value="DELETE">
                           <button title="Hapus" type="submit" data-confirm="Konfirmasi untuk menghapus data siswa ini?"
                              class="btn btn-sm btn-outline btn-delete-confirm" style="height: 36px; width: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #ef4444; border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.08);">
                              <i class="material-icons text-[16px]">delete_outline</i>
                           </button>
                        </form>
                        <a title="Download QR Code" href="<?= base_url('admin/qr/siswa/' . $value['id_siswa'] . '/download'); ?>"
                           class="btn btn-sm btn-outline" style="height: 36px; width: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #10b981; border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.08);">
                           <i class="material-icons text-[16px]">qr_code_2</i>
                        </a>
                     </div>
                  </td>
               </tr>
               <?php $i++; endforeach; ?>
         </tbody>
      </table>
   <?php else: ?>
      <div class="flex flex-col items-center justify-center py-12">
         <i class="material-icons text-6xl text-gray-300 mb-4">search_off</i>
         <h4 class="text-lg font-semibold text-gray-600">Data siswa tidak ditemukan</h4>
         <p class="text-sm text-gray-400 mt-1">Coba sesuaikan filter pencarian di atas.</p>
      </div>
   <?php endif; ?>
</div>