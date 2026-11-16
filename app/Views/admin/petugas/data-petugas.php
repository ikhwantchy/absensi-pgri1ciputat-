<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
   <?= view('admin/_messages'); ?>

   <div class="flex flex-wrap items-center justify-end gap-2">
      <a href="<?= base_url('admin/petugas/bulk'); ?>" class="btn btn-secondary">
         <i class="material-icons">upload_file</i>Impor CSV
      </a>
      <a href="<?= base_url('admin/petugas/register'); ?>" class="btn btn-primary">
         <i class="material-icons">add</i>Tambah petugas
      </a>
   </div>

   <div id="dataPetugas">
      <div class="card py-10">
         <div class="spinner mt-0"></div>
         <p class="mt-3 text-center text-sm text-slate-500">Memuat data petugas…</p>
      </div>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   getDataPetugas();

   function getDataPetugas() {
      document.getElementById('dataPetugas').innerHTML = `
         <div class="card py-10">
            <div class="spinner mt-0"></div>
            <p class="mt-3 text-center text-sm text-slate-500">Memuat data petugas…</p>
         </div>`;

      jQuery.ajax({
         url: "<?= base_url('/admin/petugas'); ?>",
         type: 'post',
         data: {},
         success: function(response, status, xhr) {
            $('#dataPetugas').html(response);
         },
         error: function(xhr, status, thrown) {
            document.getElementById('dataPetugas').innerHTML =
               `<div class="alert alert-danger">
                  <i class="material-icons">error</i>
                  <span>Gagal memuat data. Coba lagi.</span>
                </div>`;
         }
      });
   }
</script>
<?= $this->endSection() ?>
