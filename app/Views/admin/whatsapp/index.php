<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="space-y-4">
   <div class="grid gap-4 lg:grid-cols-3">
      <div class="grid content-start gap-4 lg:col-span-2">
         <section class="card group" id="statusCard">
            <div class="card-head">
               <h2 class="card-title">Status koneksi</h2>
               <span id="stateBadge" class="status status-muted"><i class="material-icons">schedule</i>Memeriksa…</span>
            </div>

            <div class="card-body hidden items-center gap-3 text-slate-600 group-has-[#stateBadge.status-muted]:flex">
               <div class="h-5 w-5 shrink-0 animate-spin rounded-full border-2 border-primary-200 border-t-primary-700"></div>
               <span>Memeriksa status gateway…</span>
            </div>

            <div id="stateConnecting" class="card-body flex items-center gap-3" style="display:none;">
               <div class="h-5 w-5 shrink-0 animate-spin rounded-full border-2 border-primary-200 border-t-primary-700"></div>
               <div>
                  <p class="font-medium text-slate-800">Menghubungkan ke server WhatsApp…</p>
                  <p class="text-xs text-slate-500">Biasanya beberapa detik.</p>
               </div>
            </div>

            <div id="stateQRReady" class="card-body flex flex-col gap-4 sm:flex-row sm:items-start" style="display:none;">
               <div class="mx-auto flex h-52 w-52 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white p-2 sm:mx-0">
                  <img id="qrImage" src="" alt="QR WhatsApp" class="h-full w-full object-contain">
               </div>
               <div class="min-w-0 flex-1 space-y-2.5">
                  <h3 class="font-semibold text-slate-900">Pindai QR dengan WhatsApp</h3>
                  <ol class="list-decimal space-y-1 pl-5 text-slate-600">
                     <li>Buka WhatsApp di ponsel.</li>
                     <li>Buka <strong>Perangkat tertaut</strong> dari menu atau Pengaturan.</li>
                     <li>Ketuk <strong>Tautkan perangkat</strong>, arahkan kamera ke QR ini.</li>
                  </ol>
                  <p class="hint inline-flex items-center gap-1"><i class="material-icons text-sm">refresh</i>QR diperbarui otomatis tiap 20 detik.</p>
               </div>
            </div>

            <div id="stateConnected" class="card-body" style="display:none;">
               <div class="flex flex-wrap items-center justify-between gap-3">
                  <div class="flex min-w-0 items-center gap-3">
                     <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-700"><i class="material-icons">smartphone</i></span>
                     <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900" id="sessionName">Nama Perangkat</p>
                        <p class="text-xs tabular-nums text-slate-500" id="sessionNumber">Nomor HP</p>
                     </div>
                  </div>
                  <button onclick="logoutSession()" class="btn btn-secondary btn-sm text-danger-700">
                     <i class="material-icons">logout</i>Putuskan
                  </button>
               </div>
            </div>

            <div id="stateOffline" class="card-body space-y-2.5" style="display:none;">
               <div class="flex items-start gap-3">
                  <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-50 text-danger-700"><i class="material-icons">wifi_off</i></span>
                  <div class="min-w-0">
                     <p class="font-semibold text-slate-900">Gateway tidak aktif</p>
                     <p class="text-xs text-slate-500" id="offlineErrorMsg">Gagal berkomunikasi dengan proses WhatsApp Gateway pada port 3000. Pastikan server Node.js sudah dinyalakan.</p>
                  </div>
               </div>
               <div class="overflow-x-auto rounded-lg bg-slate-50 px-3 py-2 font-mono text-xs leading-5 text-slate-700">
                  <div class="text-slate-500"># Jalankan di terminal server:</div>
                  <div>cd whatsapp-gateway</div>
                  <div>npm start</div>
               </div>
            </div>
         </section>

         <section class="card" id="messageTesterCard" style="display:none;">
            <div class="card-head">
               <h2 class="card-title">Tes kirim pesan</h2>
            </div>
            <form id="testMessageForm" onsubmit="sendTestMessage(event)">
               <div class="card-body grid gap-3 sm:grid-cols-2">
                  <div>
                     <label for="testNumber" class="label flex items-center gap-1">
                        Nomor penerima
                        <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Awalan 0 atau 62 sama-sama didukung.</span></span>
                     </label>
                     <input type="text" id="testNumber" class="input" placeholder="081234567890" required>
                  </div>
                  <div>
                     <label for="testMessage" class="label">Pesan</label>
                     <textarea id="testMessage" class="input" rows="3" placeholder="Tulis pesan tes" required>Ini adalah pesan uji coba dari Dashboard Sistem Absensi Sekolah SMP PGRI 1 CIPUTAT.</textarea>
                  </div>
               </div>
               <div class="card-foot">
                  <button type="submit" id="btnSendTest" class="btn btn-primary">
                     <i class="material-icons">send</i>Kirim pesan tes
                  </button>
               </div>
            </form>
         </section>
      </div>

      <section class="card self-start">
         <div class="card-head">
            <h2 class="card-title">Catatan</h2>
         </div>
         <ul class="card-body space-y-2.5 text-slate-600">
            <li class="flex items-start gap-2">
               <i class="material-icons mt-0.5 text-base text-slate-400">notifications</i>
               <span>Notifikasi absen dikirim ke orang tua saat kartu dipindai.</span>
            </li>
            <li class="flex items-start gap-2">
               <i class="material-icons mt-0.5 text-base text-slate-400">sync</i>
               <span>Bila koneksi putus, gateway mencoba menyambung ulang otomatis.</span>
            </li>
            <li class="flex items-start gap-2">
               <i class="material-icons mt-0.5 text-base text-slate-400">lock</i>
               <span class="min-w-0">Sesi tersimpan di <code class="break-all text-xs">whatsapp-gateway/auth_info_baileys</code> dan dihapus saat diputus.</span>
            </li>
         </ul>
      </section>
   </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   let pollInterval = null;
   let isProcessing = false;

   $(document).ready(function() {
      // Mulai polling status
      checkStatus();
      pollInterval = setInterval(checkStatus, 3000);
   });

   function updateBadge(state) {
      const badge = $('#stateBadge');
      badge.removeClass('status-muted status-success status-warning status-info status-danger');

      switch(state) {
         case 'connected':
            badge.addClass('status-success');
            badge.html('<i class="material-icons">check_circle</i>Tersambung');
            break;
         case 'qr_ready':
            badge.addClass('status-warning');
            badge.html('<i class="material-icons">qr_code_scanner</i>Perlu pindai QR');
            break;
         case 'connecting':
            badge.addClass('status-info');
            badge.html('<i class="material-icons">sync</i>Menghubungkan');
            break;
         default:
            badge.addClass('status-danger');
            badge.html('<i class="material-icons">cancel</i>Terputus');
      }
   }

   function checkStatus() {
      if (isProcessing) return;

      $.ajax({
         url: '<?= base_url("admin/whatsapp/status"); ?>',
         type: 'GET',
         dataType: 'json',
         success: function(res) {
            if (res.status === false) {
               // Gateway offline
               showState('offline');
               $('#offlineErrorMsg').text(res.message);
            } else {
               updateBadge(res.state);
               
               if (res.state === 'connected') {
                  showState('connected');
                  $('#sessionName').text(res.user.name);
                  $('#sessionNumber').text('+' + res.user.id.split('@')[0]);
               } else if (res.state === 'qr_ready') {
                  showState('qr_ready');
                  if (res.qr) {
                     $('#qrImage').attr('src', res.qr);
                  }
               } else if (res.state === 'connecting') {
                  showState('connecting');
               } else {
                  showState('offline');
               }
            }
         },
         error: function() {
            showState('offline');
            $('#offlineErrorMsg').text('Gagal menghubungi backend admin server absensi sekolah.');
         }
      });
   }

   function showState(state) {
      $('#stateConnecting').hide();
      $('#stateQRReady').hide();
      $('#stateConnected').hide();
      $('#stateOffline').hide();
      
      updateBadge(state);

      if (state === 'connecting') {
         $('#stateConnecting').show();
         $('#messageTesterCard').hide();
      } else if (state === 'qr_ready') {
         $('#stateQRReady').show();
         $('#messageTesterCard').hide();
      } else if (state === 'connected') {
         $('#stateConnected').show();
         $('#messageTesterCard').show();
      } else if (state === 'offline') {
         $('#stateOffline').show();
         $('#messageTesterCard').hide();
      }
   }

   function logoutSession() {
      swal({
         title: "Putuskan WhatsApp?",
         text: "Apakah Anda yakin ingin memutuskan perangkat dan keluar dari sesi aktif saat ini?",
         icon: "warning",
         buttons: {
            cancel: "Batalkan",
            confirm: {
               text: "Ya, Putuskan Sesi",
               value: true,
               visible: true,
               className: "btn-danger"
            }
         },
         dangerMode: true,
      }).then((willLogout) => {
         if (willLogout) {
            isProcessing = true;
            showState('connecting');
            
            $.ajax({
               url: '<?= base_url("admin/whatsapp/logout"); ?>',
               type: 'POST',
               dataType: 'json',
               data: {
                  '<?= csrf_token() ?>': '<?= csrf_hash(); ?>'
               },
               success: function(res) {
                  isProcessing = false;
                  if (res.status === true) {
                     swal("Sesi Terputus", "Kredensial sesi lama berhasil dibersihkan dari penyimpanan.", "success");
                  } else {
                     swal("Gagal Logout", res.message || "Gagal memutuskan sesi perangkat.", "error");
                  }
                  checkStatus();
               },
               error: function() {
                  isProcessing = false;
                  swal("Gagal Logout", "Koneksi ke server terputus.", "error");
                  checkStatus();
               }
            });
         }
      });
   }

   function sendTestMessage(e) {
      e.preventDefault();
      
      const number = $('#testNumber').val().trim();
      const message = $('#testMessage').val().trim();
      const btn = $('#btnSendTest');

      if (!number || !message) return;

      btn.prop('disabled', true).html('<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>Mengirim…');

      $.ajax({
         url: '<?= base_url("admin/whatsapp/test-send"); ?>',
         type: 'POST',
         dataType: 'json',
         data: {
            'number': number,
            'message': message,
            '<?= csrf_token() ?>': '<?= csrf_hash(); ?>'
         },
         success: function(res) {
            btn.prop('disabled', false).html('<i class="material-icons">send</i>Kirim pesan tes');
            if (res.status === true) {
               swal("Pesan Terkirim", "Pesan uji coba telah sukses diteruskan ke perangkat penerima.", "success");
               $('#testNumber').val('');
            } else {
               swal("Gagal Mengirim", res.message || "Gagal mengirimkan pesan uji coba.", "error");
            }
         },
         error: function() {
            btn.prop('disabled', false).html('<i class="material-icons">send</i>Kirim pesan tes');
            swal("Gagal Mengirim", "Koneksi ke backend admin server terputus.", "error");
         }
      });
   }
</script>
<?= $this->endSection() ?>
