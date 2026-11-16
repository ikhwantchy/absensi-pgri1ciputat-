<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="space-y-4">
   <?= view('admin/_messages'); ?>

   <div class="grid gap-4 lg:grid-cols-2">
      <section class="card flex flex-col">
         <div class="card-head">
            <div>
               <h2 class="card-title">Database</h2>
               <p class="card-sub">Cadangan seluruh tabel dalam berkas .sql</p>
            </div>
            <a href="<?= base_url('admin/backup/db/backup'); ?>" class="btn btn-primary btn-sm">
               <i class="material-icons">download</i>Unduh .sql
            </a>
         </div>
         <form action="<?= base_url('admin/backup/db/restore'); ?>" method="post" enctype="multipart/form-data" class="flex flex-1 flex-col">
            <div class="card-body grid flex-1 content-start gap-y-2.5">
               <div>
                  <label for="file_backup_db" class="label">Pulihkan dari berkas .sql</label>
                  <input type="file" id="file_backup_db" name="file_backup_db" class="input" accept=".sql" required>
               </div>
               <div class="alert alert-danger">
                  <i class="material-icons">warning</i>
                  <span>Data di server saat ini akan tertimpa permanen.</span>
               </div>
            </div>
            <div class="card-foot">
               <button type="submit" class="btn btn-danger" onclick="return confirm('APAKAH ANDA YAKIN INGIN MERESTORE DATABASE?\n\nPerhatian: Seluruh data saat ini akan DITIMPA dan HILANG digantikan oleh berkas cadangan ini!')">
                  <i class="material-icons">upload</i>Pulihkan database
               </button>
            </div>
         </form>
      </section>

      <section class="card flex flex-col">
         <div class="card-head">
            <div>
               <h2 class="card-title">Foto QR</h2>
               <p class="card-sub">Arsip QR siswa dan guru dalam berkas .zip</p>
            </div>
            <a href="<?= base_url('admin/backup/photos/backup'); ?>" class="btn btn-primary btn-sm">
               <i class="material-icons">download</i>Unduh .zip
            </a>
         </div>
         <form action="<?= base_url('admin/backup/photos/restore'); ?>" method="post" enctype="multipart/form-data" class="flex flex-1 flex-col">
            <div class="card-body grid flex-1 content-start gap-y-2.5">
               <div>
                  <label for="file_backup_photos" class="label">Pulihkan dari berkas .zip</label>
                  <input type="file" id="file_backup_photos" name="file_backup_photos" class="input" accept=".zip" required>
               </div>
               <div class="alert alert-warning">
                  <i class="material-icons">info</i>
                  <span>Foto dengan nama berkas yang sama akan ditimpa.</span>
               </div>
            </div>
            <div class="card-foot">
               <button type="submit" class="btn btn-secondary" onclick="return confirm('Apakah Anda yakin ingin memulihkan (restore) arsip foto dari berkas ZIP ini?')">
                  <i class="material-icons">upload</i>Pulihkan foto
               </button>
            </div>
         </form>
      </section>
   </div>
</div>

<?= $this->endSection() ?>
