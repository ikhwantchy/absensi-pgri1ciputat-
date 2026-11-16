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
      <div class="flex min-w-0 flex-1 flex-wrap gap-2">
         <select name="kelas" id="filterKelasSiswa" class="input min-w-[9rem] flex-1 sm:max-w-[12rem]" aria-label="Filter tingkat">
            <option value="">Semua tingkat</option>
            <?php foreach ($tingkat as $value): ?>
               <option value="<?= $value['tingkat']; ?>"><?= $value['tingkat']; ?></option>
            <?php endforeach; ?>
         </select>
         <select name="index" id="filterIndexSiswa" class="input min-w-[9rem] flex-1 sm:max-w-[12rem]" aria-label="Filter indeks kelas">
            <option value="">Semua indeks</option>
            <?php foreach ($index_kelas as $value): ?>
               <option value="<?= $value['index_kelas']; ?>"><?= $value['index_kelas']; ?></option>
            <?php endforeach; ?>
         </select>
      </div>
      <div class="flex w-full gap-2 sm:w-auto">
         <button type="button" class="btn btn-ghost-danger btn-table-delete flex-1 sm:flex-none" onclick="deleteSelectedSiswa('Data yang sudah dihapus tidak bisa dikembalikan');">
            <i class="material-icons">delete</i>Hapus<span class="hidden sm:inline">&nbsp;terpilih</span>
         </button>
         <a class="btn btn-secondary flex-1 sm:flex-none" href="<?= base_url('admin/siswa/bulk'); ?>">
            <i class="material-icons">upload_file</i>Import
         </a>
         <a class="btn btn-primary flex-1 sm:flex-none" href="<?= base_url('admin/siswa/create'); ?>">
            <i class="material-icons">add</i>Tambah
         </a>
      </div>
   </div>

   <section class="card overflow-hidden">
      <div class="card-head">
         <div>
            <h2 class="card-title">Daftar siswa</h2>
            <p class="card-sub">Tahun ajaran <?= $generalSettings->school_year; ?></p>
         </div>
      </div>
      <div id="dataSiswa">
         <div class="flex items-center justify-center gap-2 py-10 text-slate-500"><div class="spinner !m-0"></div>Memuat data…</div>
      </div>
   </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   var kelas = null;
   var jurusan = null;
   var index = null;

   getDataSiswa(kelas, jurusan, index);

   $('#filterKelasSiswa').on('change', function () {
      kelas = $(this).val() || null;
      trig();
   });


   $('#filterIndexSiswa').on('change', function () {
      index = $(this).val() || null;
      trig();
   });

   function trig() {
      $('#dataSiswa').html('<div class="flex items-center justify-center gap-2 py-10 text-slate-500"><div class="spinner !m-0"></div>Memuat data…</div>');
      getDataSiswa(kelas, jurusan, index);
   }

   function getDataSiswa(_kelas = null, _jurusan = null, _index = null) {
      jQuery.ajax({
         url: "<?= base_url('/admin/siswa'); ?>",
         type: 'post',
         data: {
            'kelas': _kelas,
            'jurusan': _jurusan,
            'index': _index
         },
         success: function (response, status, xhr) {
            $('#dataSiswa').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#dataSiswa').html('<div class="empty text-danger-700">Gagal memuat data. Coba lagi.</div>');
         }
      });
   }

   document.addEventListener('DOMContentLoaded', function () {
      // Re-delegate checkAll since dataSiswa content is dynamic
      $(document).on('click', '#checkAll', function (e) {
         $('input:checkbox').not(this).prop('checked', this.checked);
      });
   });
</script>
<?= $this->endSection() ?>
