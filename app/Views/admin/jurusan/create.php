<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <form action="<?= base_url('admin/jurusan/tambahJurusanPost'); ?>" method="post" class="card">
      <?= csrf_field() ?>

      <div class="card-body grid gap-3 sm:grid-cols-2">
         <div>
            <label class="label" for="jurusan">Nama jurusan <span class="text-danger-600">*</span></label>
            <input type="text" id="jurusan" name="jurusan"
               class="input <?= invalidFeedback('jurusan') ? 'is-invalid' : ''; ?>"
               placeholder="Contoh: MIPA, IPS, RPL, TKJ" value="<?= old('jurusan'); ?>" required>
            <?php if (invalidFeedback('jurusan')): ?>
               <p class="field-error"><?= invalidFeedback('jurusan'); ?></p>
            <?php endif; ?>
         </div>
      </div>

      <div class="card-foot">
         <button type="reset" class="btn btn-ghost mr-auto">Reset</button>
         <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary">Batal</a>
         <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
   </form>
</div>
<?= $this->endSection() ?>
