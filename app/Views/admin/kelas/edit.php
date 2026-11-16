<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <form action="<?= base_url('admin/kelas/editKelasPost'); ?>" method="post" class="card">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= esc($kelas->id_kelas); ?>">
      <input type="hidden" name="back_url" value="<?= currentFullURL(); ?>">

      <div class="card-body grid gap-3 sm:grid-cols-2">
         <div>
            <label class="label" for="tingkat">Tingkat <span class="text-danger-600">*</span></label>
            <input type="text" id="tingkat" name="tingkat"
               class="input <?= invalidFeedback('tingkat') ? 'is-invalid' : ''; ?>"
               placeholder="Contoh: VII, VIII, IX" value="<?= old('tingkat') ?? $kelas->tingkat ?? '' ?>" required>
            <?php if (invalidFeedback('tingkat')): ?>
               <p class="field-error"><?= invalidFeedback('tingkat'); ?></p>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="index_kelas">Indeks kelas <span class="text-danger-600">*</span></label>
            <input type="text" id="index_kelas" name="index_kelas"
               class="input <?= invalidFeedback('index_kelas') ? 'is-invalid' : ''; ?>"
               placeholder="Contoh: A, B, C, 1, 2" value="<?= old('index_kelas') ?? $kelas->index_kelas ?? '' ?>" required>
            <?php if (invalidFeedback('index_kelas')): ?>
               <p class="field-error"><?= invalidFeedback('index_kelas'); ?></p>
            <?php endif; ?>
         </div>

         <input type="hidden" name="id_jurusan" value="1">

         <div>
            <label class="label" for="id_wali_kelas">Wali kelas</label>
            <select class="input <?= invalidFeedback('id_wali_kelas') ? 'is-invalid' : ''; ?>"
               id="id_wali_kelas" name="id_wali_kelas">
               <option value="">Pilih wali kelas</option>
               <?php foreach ($guru as $value): ?>
                  <option value="<?= $value['id_guru']; ?>" <?= $kelas->id_wali_kelas == $value['id_guru'] ? 'selected' : ''; ?>>
                     <?= $value['nama_guru']; ?>
                  </option>
               <?php endforeach; ?>
            </select>
            <?php if (invalidFeedback('id_wali_kelas')): ?>
               <p class="field-error"><?= invalidFeedback('id_wali_kelas'); ?></p>
            <?php endif; ?>
         </div>
      </div>

      <div class="card-foot">
         <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary">Batal</a>
         <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
   </form>
</div>
<?= $this->endSection() ?>
