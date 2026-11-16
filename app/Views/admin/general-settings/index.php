<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="mx-auto max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <section class="card">
      <div class="card-head">
         <h2 class="card-title">Profil sekolah</h2>
      </div>
      <form action="<?= base_url('admin/general-settings/update'); ?>" method="post" enctype="multipart/form-data">
         <?= csrf_field() ?>

         <div class="card-body grid gap-y-2.5">
            <div>
               <label for="school_name" class="label">Nama sekolah</label>
               <input type="text" id="school_name" class="input <?= invalidFeedback('school_name') ? 'is-invalid' : ''; ?>" name="school_name" placeholder="Contoh: SMP 1 PGRI" value="<?= esc($generalSettings->school_name); ?>" required>
               <div class="invalid-feedback"><?= invalidFeedback('school_name'); ?></div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
               <div>
                  <label for="school_year" class="label">Tahun ajaran</label>
                  <input type="text" id="school_year" class="input <?= invalidFeedback('school_year') ? 'is-invalid' : ''; ?>" name="school_year" placeholder="Contoh: 2024/2025" value="<?= esc($generalSettings->school_year); ?>" required>
                  <div class="invalid-feedback"><?= invalidFeedback('school_year'); ?></div>
               </div>

               <div>
                  <label for="copyright" class="label">Teks hak cipta footer</label>
                  <input type="text" id="copyright" class="input <?= invalidFeedback('copyright') ? 'is-invalid' : ''; ?>" name="copyright" placeholder="Contoh: © 2024 All rights reserved" value="<?= esc($generalSettings->copyright); ?>" required>
                  <div class="invalid-feedback"><?= invalidFeedback('copyright'); ?></div>
               </div>
            </div>

            <div>
               <label for="logo-upload" class="label">Logo</label>
               <div class="flex items-center gap-3">
                  <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1">
                     <img id="logo" src="<?= getLogo(); ?>" alt="logo" class="max-h-full max-w-full object-contain">
                  </div>
                  <div class="min-w-0">
                     <button type="button" onclick="$('#logo-upload').trigger('click');" class="btn btn-secondary btn-sm">
                        <i class="material-icons">upload</i>Pilih file
                     </button>
                     <input type="file" id="logo-upload" name="logo" accept="image/jpg,image/jpeg,image/png,image/gif,image/svg+xml" style="display:none;" onchange="$('#upload-file-info1').html($(this).val().replace(/.*[\/\\]/, '')); document.getElementById('logo').src = window.URL.createObjectURL(this.files[0])">
                     <p class="hint">PNG, JPG, SVG. Maks. 2 MB.</p>
                     <div id="upload-file-info1" class="truncate text-xs font-medium text-primary-700"></div>
                  </div>
               </div>
            </div>
         </div>

         <div class="card-foot">
            <button type="submit" class="btn btn-primary">Simpan</button>
         </div>
      </form>
   </section>
</div>

<?= $this->endSection() ?>
