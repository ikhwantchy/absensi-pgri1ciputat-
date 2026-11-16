<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
   <?= view('admin/_messages'); ?>

   <div class="flex flex-wrap items-center justify-end gap-2">
      <a class="btn btn-secondary" href="<?= base_url('admin/kelas/bulk'); ?>">
         <i class="material-icons">upload_file</i>Impor CSV
      </a>
      <a class="btn btn-primary" href="<?= base_url('admin/kelas/tambah'); ?>">
         <i class="material-icons">add</i>Tambah kelas
      </a>
   </div>

   <section class="card overflow-hidden">
      <div class="card-head">
         <div>
            <h2 class="card-title">Daftar kelas</h2>
            <p class="card-sub">Angkatan <?= $generalSettings->school_year; ?></p>
         </div>
         <button type="button" class="btn btn-ghost btn-icon btn-sm" onclick="fetchKelasJurusanData('kelas', '#dataKelas')" title="Muat ulang" aria-label="Muat ulang">
            <i class="material-icons">refresh</i>
         </button>
      </div>
      <div id="dataKelas" class="min-h-40">
         <div class="spinner"></div>
         <p class="py-3 text-center text-sm text-slate-500">Memuat data kelas…</p>
      </div>
   </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    fetchKelasJurusanData('kelas', '#dataKelas');
  });
</script>
<?= $this->endSection() ?>
