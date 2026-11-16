<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="mx-auto max-w-3xl space-y-4">
   <?php if (session()->getFlashdata('msg')): ?>
      <div class="alert <?= session()->getFlashdata('error') == true ? 'alert-danger' : 'alert-success' ?>" role="alert">
         <i class="material-icons"><?= session()->getFlashdata('error') == true ? 'error' : 'check_circle' ?></i>
         <div class="min-w-0 flex-1"><?= session()->getFlashdata('msg') ?></div>
         <button type="button" class="close" aria-label="Tutup" onclick="this.parentElement.style.display='none'"><i class="material-icons">close</i></button>
      </div>
   <?php endif; ?>

   <form action="<?= base_url('admin/guru/create'); ?>" method="post" class="card">
      <?= csrf_field() ?>
      <?php $validation = \Config\Services::validation(); ?>

      <div class="card-head">
         <h2 class="card-title">Data guru</h2>
         <a href="<?= base_url('admin/guru') ?>" class="btn btn-ghost btn-sm"><i class="material-icons">arrow_back</i>Kembali</a>
      </div>

      <div class="card-body grid gap-3 sm:grid-cols-2">
         <div>
            <label class="label" for="nuptk">NUPTK <span class="text-danger-700">*</span></label>
            <input type="text" id="nuptk" name="nuptk"
               class="input <?= $validation->getError('nuptk') ? 'is-invalid' : ''; ?>"
               placeholder="Contoh: 12345678" value="<?= old('nuptk') ?? $oldInput['nuptk'] ?? '' ?>">
            <?php if ($validation->getError('nuptk')): ?>
               <p class="field-error"><?= $validation->getError('nuptk'); ?></p>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="nama">Nama lengkap <span class="text-danger-700">*</span></label>
            <input type="text" id="nama" name="nama"
               class="input <?= $validation->getError('nama') ? 'is-invalid' : ''; ?>"
               placeholder="Nama lengkap guru" value="<?= old('nama') ?? $oldInput['nama'] ?? '' ?>" required>
            <?php if ($validation->getError('nama')): ?>
               <p class="field-error"><?= $validation->getError('nama'); ?></p>
            <?php endif; ?>
         </div>

         <div>
            <span class="label">Jenis kelamin <span class="text-danger-700">*</span></span>
            <?php
            if (old('jk') || (isset($oldInput) && is_array($oldInput))) {
               $l = (old('jk') ?? ($oldInput['jk'] ?? '')) == '1' ? 'checked' : '';
               $p = (old('jk') ?? ($oldInput['jk'] ?? '')) == '2' ? 'checked' : '';
            }
            ?>
            <div class="flex h-10 items-center gap-5">
               <label class="inline-flex items-center gap-2">
                  <input type="radio" name="jk" value="1" <?= $l ?? ''; ?>>
                  <span>Laki-laki</span>
               </label>
               <label class="inline-flex items-center gap-2">
                  <input type="radio" name="jk" value="2" <?= $p ?? ''; ?>>
                  <span>Perempuan</span>
               </label>
            </div>
            <?php if ($validation->getError('jk')): ?>
               <p class="field-error"><?= $validation->getError('jk'); ?></p>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="hp">No HP <span class="text-danger-700">*</span></label>
            <input type="number" id="hp" name="no_hp"
               class="input <?= $validation->getError('no_hp') ? 'is-invalid' : ''; ?>"
               placeholder="Contoh: 08123456789" value="<?= old('no_hp') ?? $oldInput['no_hp'] ?? '' ?>" required>
            <?php if ($validation->getError('no_hp')): ?>
               <p class="field-error"><?= $validation->getError('no_hp'); ?></p>
            <?php endif; ?>
         </div>

         <div class="sm:col-span-2">
            <label class="label" for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" class="input"
               placeholder="Alamat lengkap" value="<?= old('alamat') ?? $oldInput['alamat'] ?? '' ?>">
         </div>

         <div class="sm:col-span-2">
            <label class="label inline-flex items-center gap-1" for="rfid">
               Kode RFID
               <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Klik kolom ini lalu tempelkan kartu ke pembaca RFID. Boleh dikosongkan.</span></span>
            </label>
            <input type="text" id="rfid" name="rfid"
               class="input <?= $validation->getError('rfid') ? 'is-invalid' : ''; ?>"
               placeholder="Tempelkan kartu RFID" value="<?= old('rfid') ?? $oldInput['rfid'] ?? '' ?>">
            <?php if ($validation->getError('rfid')): ?>
               <p class="field-error"><?= $validation->getError('rfid'); ?></p>
            <?php endif; ?>
         </div>
      </div>

      <div class="card-foot">
         <button type="reset" class="btn btn-secondary">Reset</button>
         <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
   </form>
</div>
<?= $this->endSection() ?>
