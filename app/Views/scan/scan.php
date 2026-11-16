<?= $this->extend('templates/starting_page_layout'); ?>

<?= $this->section('navaction') ?>
<a href="<?= base_url('/admin'); ?>" class="btn btn-secondary btn-sm">
   <i class="material-icons">space_dashboard</i>
   <span class="hidden sm:inline">Dashboard</span>
</a>

<a href="<?= base_url('/logout'); ?>" class="btn btn-ghost-danger btn-sm">
   <i class="material-icons">logout</i>
   <span class="hidden sm:inline">Keluar</span>
</a>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="mx-auto max-w-5xl space-y-4">
   <div class="grid items-start gap-4 lg:grid-cols-3">
      <div class="space-y-4 lg:col-span-2">
         <div class="card">
            <div class="card-head">
               <div>
                  <h2 class="card-title">Tap kartu</h2>
                  <p class="card-sub">Tap pertama tercatat masuk, tap berikutnya pulang.</p>
               </div>
               <span id="statusBadge" class="status status-success">
                  <i class="material-icons">usb</i>
                  <span id="statusText">RFID Reader: Siap (Fokus Aktif)</span>
               </span>
            </div>

            <!-- RFID Section (Default & Only Mode) -->
            <div id="rfidSection" class="card-body">
               <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                  <label for="rfidInput" class="label">Kode kartu</label>
                  <input type="text" id="rfidInput" class="input mx-auto h-12 max-w-xs text-center text-lg font-semibold tracking-widest text-primary-700" placeholder="Tap kartu di sini" autofocus autocomplete="off">
                  <p class="hint">Kotak ini harus tetap aktif saat kartu ditempel.</p>
               </div>
            </div>
         </div>

         <!-- Result Area -->
         <div id="hasilScan" class="scroll-mt-20"></div>
      </div>

      <div class="card">
         <div class="card-head justify-start">
            <h3 class="card-title">Cara pakai</h3>
            <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Reader USB 125 kHz (EM4100/TK4100) mengirim kode kartu lalu menekan Enter otomatis.</span></span>
         </div>
         <ol class="card-body list-decimal space-y-1.5 pl-8 text-sm text-slate-600">
            <li>Pastikan status reader "Siap".</li>
            <li>Tempelkan kartu saat datang untuk absen masuk.</li>
            <li>Tempelkan lagi saat pulang untuk absen pulang.</li>
         </ol>
      </div>
   </div>
</div>

<script src="<?= base_url('assets/js/core/jquery-3.5.1.min.js') ?>"></script>
<script type="text/javascript">
   let audio = new Audio("<?= base_url('assets/audio/beep.mp3'); ?>");

   async function cekData(code) {
      // Show loading state in result area
      $('#hasilScan').html('<div class="card flex justify-center p-6"><div class="spinner mt-0"></div></div>');

      jQuery.ajax({
         url: "<?= base_url('scan/cek'); ?>",
         type: 'post',
         data: { 'unique_code': code, 'waktu': 'auto' },
         success: function (response) {
            audio.play().catch(e => console.log('Audio autoplay blocked by browser'));
            $('#hasilScan').html(response);
            $('html, body').animate({ scrollTop: $("#hasilScan").offset().top - 40 }, 500);
         },
         error: function (xhr, status, thrown) {
            $('#hasilScan').html('<div class="alert alert-danger"><i class="material-icons">error</i><div>Terjadi kesalahan koneksi saat memproses presensi.</div></div>');
         }
      });
   }

   // RFID Input Submission Listener
   $('#rfidInput').on('keypress', function (e) {
      if (e.which == 13) {
         let code = $(this).val().trim();
         if (code.length > 0) {
            cekData(code);
            $(this).val('');
         }
      }
   });

   $(document).ready(function () {
      const rfidInput = $('#rfidInput');
      const statusBadge = $('#statusBadge');
      const statusText = $('#statusText');

      function updateStatus(focused) {
         if (focused) {
            statusBadge.removeClass('status-danger').addClass('status-success');
            statusText.text('RFID Reader: Siap (Fokus Aktif)');
         } else {
            statusBadge.removeClass('status-success').addClass('status-danger');
            statusText.text('RFID Reader: Tidak Fokus (Klik Disini)');
         }
      }

      rfidInput.on('focus', () => updateStatus(true));
      rfidInput.on('blur', () => {
         updateStatus(false);
         setTimeout(() => {
            rfidInput.focus();
         }, 2500);
      });

      $(document).on('click', () => {
         rfidInput.focus();
      });

      rfidInput.focus();
   });
</script>
<?= $this->endSection(); ?>
