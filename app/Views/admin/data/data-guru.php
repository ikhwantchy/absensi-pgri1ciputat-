<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
   <?php if (session()->getFlashdata('msg')): ?>
      <div class="alert <?= session()->getFlashdata('error') == true ? 'alert-danger' : 'alert-success' ?>" role="alert">
         <i class="material-icons"><?= session()->getFlashdata('error') == true ? 'error' : 'check_circle' ?></i>
         <div class="min-w-0 flex-1"><?= session()->getFlashdata('msg') ?></div>
         <button type="button" class="close" aria-label="Tutup" onclick="this.parentElement.style.display='none'"><i class="material-icons">close</i></button>
      </div>
   <?php endif; ?>

   <div class="flex flex-wrap items-center justify-between gap-2">
      <button type="button" class="btn btn-secondary px-3 sm:px-4" title="Muat ulang" aria-label="Muat ulang" onclick="trigRefresh();">
         <i class="material-icons">refresh</i><span class="hidden sm:inline">Muat ulang</span>
      </button>
      <div class="flex flex-wrap gap-2">
         <a class="btn btn-secondary" href="<?= base_url('admin/guru/bulk'); ?>">
            <i class="material-icons">upload_file</i>Import
         </a>
         <a class="btn btn-primary" href="<?= base_url('admin/guru/create'); ?>">
            <i class="material-icons">add</i>Tambah
         </a>
      </div>
   </div>

   <section class="card overflow-hidden">
      <div class="card-head">
         <div>
            <h2 class="card-title">Daftar guru</h2>
            <p class="card-sub">Tahun ajaran <?= esc($generalSettings->school_year); ?></p>
         </div>
      </div>
      <div id="dataGuru">
         <div class="flex items-center justify-center gap-2 py-10 text-slate-500"><div class="spinner !m-0"></div>Memuat data…</div>
      </div>
   </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   $(document).ready(function() {
      getDataGuru();
   });

   function trigRefresh() {
      $('#dataGuru').html('<div class="flex items-center justify-center gap-2 py-10 text-slate-500"><div class="spinner !m-0"></div>Memuat data…</div>');
      getDataGuru();
   }

   function getDataGuru() {
      jQuery.ajax({
         url: "<?= base_url('/admin/guru'); ?>",
         type: 'post',
         data: {},
         success: function (response, status, xhr) {
            $('#dataGuru').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#dataGuru').html('<div class="empty text-danger-700">Gagal memuat data. Coba lagi.</div>');
         }
      });
   }
</script>
<?= $this->endSection() ?>
