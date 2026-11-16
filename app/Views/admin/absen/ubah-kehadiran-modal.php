<div class="modal-body">
   <form id="formUbah" class="grid gap-y-2.5">
      <input type="hidden" name="id_siswa" value="<?= $data['id_siswa'] ?? ''; ?>">
      <input type="hidden" name="id_guru" value="<?= $data['id_guru'] ?? ''; ?>">
      <input type="hidden" name="id_kelas" value="<?= $data['id_kelas'] ?? ''; ?>">

      <div>
         <div class="label">Status kehadiran</div>
         <div class="grid gap-1.5" id="kehadiran">
            <?php foreach ($listKehadiran as $value2) : ?>
               <?php
                  $idKeh = $value2['id_kehadiran'];
                  $isChecked = $idKeh == ($presensi['id_kehadiran'] ?? '4');

                  $colorClass = 'status-muted'; $icon = 'schedule';
                  if($idKeh == 1) { $colorClass = 'status-success'; $icon = 'check_circle'; }
                  if($idKeh == 2) { $colorClass = 'status-warning'; $icon = 'sick'; }
                  if($idKeh == 3) { $colorClass = 'status-info'; $icon = 'assignment'; }
                  if($idKeh == 4) { $colorClass = 'status-danger'; $icon = 'cancel'; }
               ?>
               <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50 has-[:checked]:border-primary-300 has-[:checked]:bg-primary-50">
                  <input type="radio" name="id_kehadiran" value="<?= $idKeh; ?>" <?= $isChecked ? 'checked' : ''; ?>>
                  <span class="status <?= $colorClass; ?> text-sm"><i class="material-icons"><?= $icon; ?></i><?= $value2['kehadiran']; ?></span>
               </label>
            <?php endforeach; ?>
         </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
         <div>
            <label class="label" for="jamMasuk">Jam masuk</label>
            <input class="input" type="time" name="jam_masuk" id="jamMasuk" value="<?= $presensi['jam_masuk'] ?? ''; ?>">
         </div>
         <div>
            <label class="label" for="jamKeluar">Jam keluar</label>
            <input class="input" type="time" name="jam_keluar" id="jamKeluar" value="<?= $presensi['jam_keluar'] ?? ''; ?>">
         </div>
      </div>

      <div>
         <label class="label" for="keterangan">Keterangan</label>
         <textarea id="keterangan" name="keterangan" class="input" rows="3" placeholder="Opsional"><?= trim($presensi['keterangan'] ?? ''); ?></textarea>
      </div>
   </form>
</div>
<div class="modal-footer">
   <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
   <button type="button" onclick="ubahKehadiran()" class="btn btn-primary" data-dismiss="modal">Simpan</button>
</div>
