<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">

   <!-- Filter tanggal -->
   <div class="filter-bar">
      <div class="min-w-[10rem] flex-1 sm:max-w-[13rem] sm:flex-none">
         <label class="label" for="tanggal">Tanggal</label>
         <input class="input" type="date" name="tanggal" id="tanggal"
            value="<?= $date; ?>" onchange="getSiswa(<?= $kelas['id_kelas']; ?>, '<?= $kelas['kelas']; ?>')">
      </div>
   </div>

   <!-- Tabel absensi (dimuat via AJAX) -->
   <div class="card overflow-hidden" id="dataSiswa">
      <div class="empty">
         <div class="spinner mt-0 mb-2"></div>
         Memuat absensi…
      </div>
   </div>

   <!-- Modal ubah kehadiran -->
   <div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="modalUbahKehadiran" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="modalUbahKehadiran">Ubah kehadiran siswa</h5>
               <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                  <i class="material-icons">close</i>
               </button>
            </div>
            <div id="modalFormUbahSiswa"></div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var lastIdKelas = <?= $kelas['id_kelas']; ?>;
    var lastKelas = '<?= $kelas['kelas']; ?>';

   $(document).ready(function() {
       getSiswa(lastIdKelas, lastKelas);
   });

   function getSiswa(idKelas, kelas) {
      var tanggal = $('#tanggal').val();

      jQuery.ajax({
         url: "<?= base_url('/teacher/attendance/get-list'); ?>",
         type: 'post',
         data: {
            'kelas': kelas,
            'id_kelas': idKelas,
            'tanggal': tanggal
         },
         success: function (response, status, xhr) {
            $('#dataSiswa').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#dataSiswa').html('<p class="empty text-danger-700">Gagal memuat data. Coba lagi.</p>');
         }
      });
      lastIdKelas = idKelas;
      lastKelas = kelas;
   }

   function getDataKehadiran(idPresensi, idSiswa) {
      jQuery.ajax({
         url: "<?= base_url('/teacher/attendance/get-edit-modal'); ?>",
         type: 'post',
         data: {
            'id_presensi': idPresensi,
            'id_siswa': idSiswa
         },
         success: function (response, status, xhr) {
            $('#modalFormUbahSiswa').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#modalFormUbahSiswa').html('<p class="empty text-danger-700">Gagal memuat form.</p>');
         }
      });
   }

   function ubahKehadiran() {
      var tanggal = $('#tanggal').val();
      var form = $('#formUbah').serializeArray();

      form.push({
         name: 'tanggal',
         value: tanggal
      });

      jQuery.ajax({
         url: "<?= base_url('/teacher/attendance/update-single'); ?>",
         type: 'post',
         data: form,
         success: function (response, status, xhr) {
            if (response['status']) {
               getSiswa(lastIdKelas, lastKelas);
               $('#ubahModal').modal('hide');
               // Basic toast placeholder, you might want to use a real toast library
               alert('Berhasil ubah kehadiran: ' + response['nama_siswa']);
            } else {
               alert('Gagal ubah kehadiran: ' + response['nama_siswa']);
            }
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            alert('Gagal ubah kehadiran\n' + thrown);
         }
      });
   }
</script>
<?= $this->endSection() ?>
