<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="Kiosk absensi RFID SMP PGRI 1 Ciputat">
   <meta name="theme-color" content="#2e3192">
   <title><?= $title ?? 'Kiosk Absensi' ?></title>
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo.png'); ?>">

   <?= $this->include('templates/css'); ?>

   <style>
      /* Kelas status yang diganti oleh JS di bawah. */
      .mode-btn.masuk-active,
      .mode-btn.pulang-active {
         background-color: #2e3192;
         color: #fff;
      }

      .status-badge.connected {
         color: #15803d;
      }

      .status-badge.disconnected {
         color: #b91c1c;
      }

      .status-badge .ping-glow {
         width: 8px;
         height: 8px;
         border-radius: 9999px;
         background: currentColor;
         animation: kiosk-pulse 1.6s ease-in-out infinite;
      }

      @keyframes kiosk-pulse {
         0%, 100% { opacity: 1; }
         50% { opacity: 0.3; }
      }

      .kiosk-result-card {
         animation: kiosk-in 0.25s ease-out;
      }

      @keyframes kiosk-in {
         from { opacity: 0; transform: translateY(6px); }
         to { opacity: 1; transform: none; }
      }
   </style>
</head>
<body>

   <div class="flex min-h-screen flex-col lg:h-screen">

      <!-- Header Section -->
      <header class="border-b border-slate-200 bg-white">
         <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-6">
            <div class="flex min-w-0 items-center gap-3">
               <img src="<?= getLogo(); ?>" alt="Logo sekolah" class="h-10 w-10 shrink-0 object-contain">
               <div class="min-w-0 leading-tight">
                  <p class="text-base font-semibold text-slate-900">Absensi</p>
                  <p class="truncate text-sm text-slate-500">SMP PGRI 1 Ciputat</p>
               </div>
            </div>
            <div class="text-right">
               <div class="text-2xl font-semibold leading-none text-slate-900 tabular-nums sm:text-4xl" id="liveTime">00:00:00</div>
               <div class="mt-1 text-xs text-slate-500 sm:text-sm" id="liveDate">Senin, 1 Januari 2026</div>
            </div>
         </div>
      </header>

      <!-- Main Kiosk Grid -->
      <main class="mx-auto grid w-full max-w-7xl flex-1 gap-4 p-4 lg:min-h-0 lg:grid-cols-5 lg:p-6">

         <!-- Control & Input Panel -->
         <section class="card flex flex-col gap-6 p-5 lg:col-span-2 lg:p-6">

            <!-- Mode Selection Toggles -->
            <div class="tabs shadow-none ring-1 ring-slate-200">
               <button id="btnMasuk" class="mode-btn inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap py-2.5 text-sm font-semibold sm:text-base <?= $waktu == 'Masuk' ? 'masuk-active' : 'inactive' ?>" onclick="switchMode('Masuk')">
                  <i class="material-icons">login</i> Absen masuk
               </button>
               <button id="btnPulang" class="mode-btn inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap py-2.5 text-sm font-semibold sm:text-base <?= $waktu == 'Pulang' ? 'pulang-active' : 'inactive' ?>" onclick="switchMode('Pulang')">
                  <i class="material-icons">logout</i> Absen pulang
               </button>
            </div>

            <div>
               <h1 class="text-2xl font-semibold text-slate-900">Selamat datang</h1>
               <p class="mt-1 text-base text-slate-500">Tempelkan kartu RFID pada reader untuk mencatat kehadiran.</p>
            </div>

            <!-- RFID Input & Focus status -->
            <div class="lg:my-auto">
               <div id="statusBadge" class="status-badge connected mb-3 inline-flex items-center gap-2 text-sm font-semibold">
                  <span class="ping-glow"></span>
                  <i class="material-icons" id="statusIcon">usb</i>
                  <span id="statusText">RFID Reader: Siap (Fokus Aktif)</span>
               </div>

               <input type="text" id="rfidInput" class="input h-14 text-center text-xl font-semibold tracking-widest text-primary-700" placeholder="Tap kartu di sini" aria-label="Kode kartu RFID" autofocus autocomplete="off">

               <p class="hint">Kotak ini aktif kembali otomatis bila kehilangan fokus.</p>
            </div>

         </section>

         <!-- Result Display Panel -->
         <section class="card flex min-h-[22rem] flex-col items-center justify-center p-5 lg:col-span-3 lg:p-8" id="resultPanel">

            <!-- Default View -->
            <div class="flex max-w-sm flex-col items-center text-center" id="emptyState">
               <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                  <i class="material-icons text-4xl!">contactless</i>
               </div>
               <h2 class="text-xl font-semibold text-slate-900">Menunggu kartu</h2>
               <p class="mt-1 text-base text-slate-500">Hasil pencatatan kehadiran tampil di sini.</p>
            </div>

            <!-- AJAX Result Container -->
            <div id="hasilScan" style="width: 100%; display: none;"></div>

         </section>

      </main>

      <!-- Footer Section -->
      <footer class="border-t border-slate-200 bg-white">
         <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-2 text-xs text-slate-500 lg:px-6">
            <span>&copy; 2026 SMP PGRI 1 Ciputat</span>
            <span class="status status-success"><i class="material-icons">wifi</i>Kiosk online</span>
         </div>
      </footer>

   </div>

   <!-- JQuery -->
   <script src="<?= base_url('assets/js/core/jquery-3.5.1.min.js') ?>"></script>
   
   <script>
      let currentMode = '<?= $waktu; ?>';
      let clearTimer = null;
      let audio = new Audio("<?= base_url('assets/audio/beep.mp3'); ?>");

      // Live Clock
      function updateClock() {
         const now = new Date();
         
         // Formatting Time
         let hours = String(now.getHours()).padStart(2, '0');
         let minutes = String(now.getMinutes()).padStart(2, '0');
         let seconds = String(now.getSeconds()).padStart(2, '0');
         document.getElementById('liveTime').textContent = `${hours}:${minutes}:${seconds}`;

         // Formatting Date
         const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
         const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
         
         let dayName = days[now.getDay()];
         let date = now.getDate();
         let monthName = months[now.getMonth()];
         let year = now.getFullYear();
         
         document.getElementById('liveDate').textContent = `${dayName}, ${date} ${monthName} ${year}`;
      }

      setInterval(updateClock, 1000);
      updateClock();

      // Switch Kiosk mode (Masuk / Pulang)
      function switchMode(mode) {
         currentMode = mode;
         const btnMasuk = $('#btnMasuk');
         const btnPulang = $('#btnPulang');

         if (mode === 'Masuk') {
            btnMasuk.removeClass('inactive').addClass('masuk-active');
            btnPulang.removeClass('pulang-active').addClass('inactive');
         } else {
            btnPulang.removeClass('inactive').addClass('pulang-active');
            btnMasuk.removeClass('masuk-active').addClass('inactive');
         }
         
         $('#rfidInput').focus();
         resetKioskView();
      }

      // Reset panel view to waiting state
      function resetKioskView() {
         $('#hasilScan').hide().html('');
         $('#emptyState').fadeIn();
         $('#rfidInput').val('');
      }

      // Check Scanned ID Code via AJAX
      async function sendScanData(code) {
         if (clearTimer) clearTimeout(clearTimer);
         
         // Visual loading state
         $('#emptyState').hide();
         $('#hasilScan').html('<div class="flex flex-col items-center gap-3 text-center"><div class="spinner mt-0"></div><h3 class="text-base font-medium text-slate-600">Memproses presensi...</h3></div>').show();

         jQuery.ajax({
            url: "<?= base_url('scanner/cek'); ?>",
            type: 'post',
            data: { 'unique_code': code, 'waktu': currentMode.toLowerCase() },
            success: function (response) {
               audio.play().catch(e => console.log('Audio autoplay blocked'));
               
               // Wrap custom response inside clean kiosk card styling
               let parsedHtml = formatKioskResult(response);
               $('#hasilScan').html(parsedHtml);
               
               // Auto reset view after 5 seconds
               clearTimer = setTimeout(resetKioskView, 5000);
            },
            error: function (xhr, status, thrown) {
               $('#hasilScan').html('<div class="kiosk-result-card w-full"><div class="kiosk-result-header flex flex-col items-center text-center text-red-700"><i class="material-icons text-4xl!">cancel</i><h3 class="mt-2 text-2xl font-semibold">Terjadi kesalahan</h3><p class="mt-1 text-base text-slate-500">Koneksi gagal atau kartu tidak valid.</p></div></div>');
               clearTimer = setTimeout(resetKioskView, 5000);
            }
         });
      }

      // Format response to kiosk visual standard
      function formatKioskResult(htmlSnippet) {
         let tempDiv = $('<div>').html(htmlSnippet);
         let title = tempDiv.find('h3').first().text();
         let isSuccess = tempDiv.find('h3').hasClass('text-success') || tempDiv.find('h3').first().text().includes('Berhasil');
         let isWarning = tempDiv.find('h3').hasClass('text-warning') || tempDiv.find('h3').first().text().includes('Terlambat');
         let isError = tempDiv.find('h3').hasClass('text-danger');
         
         let titleClass = 'text-green-700';
         let iconName = 'check_circle';
         
         if (isWarning) {
            titleClass = 'text-amber-700';
            iconName = 'schedule';
         } else if (isError || !isSuccess) {
            titleClass = 'text-red-700';
            iconName = 'cancel';
         }

         let rows = tempDiv.find('p');
         let detailsHtml = '';
         let timeBoxes = '';

         rows.each(function() {
            let label = $(this).text().split(':')[0].trim();
            let value = $(this).find('b').text().trim();
            
            if (label.includes('Jam masuk') || label.includes('Jam pulang')) {
               timeBoxes += `<div class="time-box rounded-lg bg-slate-50 px-3 py-2.5 text-center"><span class="block text-sm text-slate-500">${label}</span><b class="text-2xl font-semibold text-slate-900 tabular-nums">${value || '—'}</b></div>`;
            } else {
               detailsHtml += `<p class="flex gap-3 text-base"><span class="w-16 shrink-0 text-slate-500">${label}</span><b class="min-w-0 font-semibold text-slate-900">${value}</b></p>`;
            }
         });

         let warningNotice = '';
         let warningDiv = tempDiv.find('.mt-4');
         if (warningDiv.length > 0) {
            warningNotice = `<div class="late-badge alert alert-warning mt-4 text-base font-medium"><i class="material-icons">alarm</i><span>${warningDiv.text().trim()}</span></div>`;
         }

         return `
            <div class="kiosk-result-card w-full">
               <div class="kiosk-result-header ${titleClass} mb-5 flex flex-col items-center text-center">
                  <i class="material-icons text-4xl!">${iconName}</i>
                  <h3 class="mt-2 text-2xl font-semibold">${title || 'Pencatatan Kehadiran'}</h3>
               </div>

               <div class="profile-info-grid flex items-center gap-4 rounded-xl border border-slate-200 p-4 sm:gap-5 sm:p-5">
                  <div class="photo-placeholder flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700 sm:h-24 sm:w-24">
                     <i class="material-icons text-5xl!">person</i>
                  </div>
                  <div class="info-details grid min-w-0 gap-1.5">
                     ${detailsHtml}
                  </div>
               </div>

               ${timeBoxes ? `<div class="time-row mt-4 grid grid-cols-2 gap-3">${timeBoxes}</div>` : ''}
               ${warningNotice}
            </div>
         `;
      }

      // Input Submission Listener
      $('#rfidInput').on('keypress', function (e) {
         if (e.which == 13) {
            let code = $(this).val().trim();
            if (code.length > 0) {
               sendScanData(code);
               $(this).val('');
            }
         }
      });

      // RFID Connection / Auto-Focus Management
      $(document).ready(function () {
         const rfidInput = $('#rfidInput');
         const statusBadge = $('#statusBadge');
         const statusText = $('#statusText');

         function updateStatus(focused) {
            if (focused) {
               statusBadge.removeClass('disconnected').addClass('connected');
               statusText.text('RFID Reader: Siap (Fokus Aktif)');
            } else {
               statusBadge.removeClass('connected').addClass('disconnected');
               statusText.text('RFID Reader: Tidak Fokus (Klik Disini)');
            }
         }

         rfidInput.on('focus', () => updateStatus(true));
         rfidInput.on('blur', () => {
            updateStatus(false);
            // Re-gain focus instantly to ensure RFID reader always writes to this input field
            setTimeout(() => {
               rfidInput.focus();
            }, 100);
         });

         // Make clicking anywhere on the page refocus the input
         $(document).on('click', () => {
            rfidInput.focus();
         });

         // Initial focus
         rfidInput.focus();
         
         // Auto-mode switching based on clock hours:
         // Before 11:30 AM default to Masuk, after 11:30 AM default to Pulang harian
         const hours = new Date().getHours();
         const minutes = new Date().getMinutes();
         if (hours > 11 || (hours === 11 && minutes >= 30)) {
            switchMode('Pulang');
         } else {
            switchMode('Masuk');
         }
      });
   </script>
</body>
</html>
