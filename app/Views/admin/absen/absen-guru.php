<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="space-y-4">
   <!-- Filter: tanggal -->
   <div class="filter-bar">
      <div class="min-w-[10rem] flex-1 sm:max-w-[13rem] sm:flex-none">
         <label class="label" for="tanggal">Tanggal</label>
         <input type="date" id="tanggal" name="tanggal" class="input" value="<?= date('Y-m-d'); ?>" onchange="getGuru()">
      </div>
   </div>

   <!-- Tabel absensi (dimuat via AJAX) -->
   <div id="dataGuru">
      <div class="card">
         <div class="empty">
            <div class="spinner mt-0 mb-2"></div>
            Memuat absensi guru…
         </div>
      </div>
   </div>
</div>

<!-- MODAL Ubah Kehadiran -->
<div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalLabel">Ubah kehadiran guru</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
               <i class="material-icons">close</i>
            </button>
         </div>
         <div id="modalFormUbahGuru"></div>
      </div>
   </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   getGuru();

   function getGuru() {
      var tanggal = $('#tanggal').val();

      document.getElementById('dataGuru').innerHTML = `
         <div class="card">
            <div class="empty">
               <div class="spinner mt-0 mb-2"></div>
               Memuat absensi guru…
            </div>
         </div>`;

      jQuery.ajax({
         url: "<?= base_url('/admin/absen-guru'); ?>",
         type: 'post',
         data: {
            'tanggal': tanggal
         },
         success: function(response, status, xhr) {
            $('#dataGuru').html(response);
         },
         error: function(xhr, status, thrown) {
            document.getElementById('dataGuru').innerHTML =
               `<div class="card">
                  <p class="empty text-danger-700">Gagal memuat data. Coba lagi.</p>
                </div>`;
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
         url: "<?= base_url('/admin/absen-guru/edit'); ?>",
         type: 'post',
         data: form,
         success: function(response, status, xhr) {
            if (response['status']) {
               // Success silent or small notification
               jQuery('#ubahModal').modal('hide');
            } else {
               alert('Gagal ubah kehadiran : ' + response['nama_guru']);
            }
            getGuru();
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            alert('Gagal ubah kehadiran\n' + thrown);
         }
      });
   }

   function getDataKehadiran(idPresensi, idGuru) {
      jQuery.ajax({
         url: "<?= base_url('/admin/absen-guru/kehadiran'); ?>",
         type: 'post',
         data: {
            'id_presensi': idPresensi,
            'id_guru': idGuru
         },
         success: function(response, status, xhr) {
            $('#modalFormUbahGuru').html(response);
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            $('#modalFormUbahGuru').html(thrown);
         }
      });
   }
</script>
<?= $this->endSection() ?>
