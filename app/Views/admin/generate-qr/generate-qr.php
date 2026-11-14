<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<style>
  .progress-siswa, .progress-guru {
    height: 6px;
    border-radius: 4px;
    background-color: var(--surface);
    overflow: hidden;
    margin-top: 8px;
  }
  .progress-siswa, .progress-guru { background-color: rgba(124, 58, 237, 0.1); }

  .my-progress-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
  }
  .progress-siswa .my-progress-bar, .progress-guru .my-progress-bar { background-color: var(--primary); }
</style>

<!-- PAGE HEADER (Clean, avoiding MD conflict) -->
<div class="admin-page-header">
   <div>
      <h1 class="page-title">Generate QR Code</h1>
      <div class="breadcrumb">
         <a href="<?= base_url('admin/dashboard'); ?>">Dashboard</a>
         <span class="breadcrumb-sep">›</span>
         <span class="breadcrumb-active">Generate QR Code</span>
      </div>
   </div>
</div>

<!-- PREMIUM HERO BANNER -->
<div class="premium-hero-card">
   <div>
      <h2 class="premium-hero-title">Generator & Ekspor QR Code</h2>
      <p class="premium-hero-subtitle">Hasilkan dan unduh kode matriks QR presensi berkeamanan tinggi secara masal untuk dicetak pada kartu identitas siswa maupun guru pendidik.</p>
   </div>
   <div class="premium-hero-badge">
      <i class="material-icons" style="font-size: 18px; color: #34D399;">qr_code_scanner</i>
      <span>Matrix Code Engine</span>
   </div>
</div>

<?= view('admin/_messages'); ?>

<div class="flex flex-col md:flex-row gap-8" style="margin-top: 12px;">
   <!-- DATA SISWA CARD -->
   <div class="flex-1">
      <div class="premium-filter-card h-full flex flex-col" style="margin: 0; border-top-color: var(--primary) !important;">
         <div class="flex items-center gap-4 mb-6">
            <div style="width: 52px; height: 52px; border-radius: var(--r-md); background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
               <i class="material-icons" style="font-size: 28px;">school</i>
            </div>
            <div>
               <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;">QR Code Siswa</h2>
               <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0 0;">
                  Total Siswa: <strong style="color: var(--text-primary);"><?= count($siswa); ?></strong> akun • 
                  <a href="<?= base_url('admin/siswa'); ?>" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Lihat Data</a>
               </p>
            </div>
         </div>

         <!-- All Siswa Buttons -->
         <div style="display: flex; gap: 12px; margin-bottom: 24px;">
            <button onclick="generateAllQrSiswa()" class="btn" style="flex: 1; background: linear-gradient(135deg, var(--primary), #6D28D9); color: #FFFFFF; font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;">
               <i class="material-icons" style="font-size: 20px;">qr_code_2</i> Generate Semua
            </button>
            <a href="<?= base_url('admin/qr/siswa/download'); ?>" class="btn btn-outline" style="flex: 1; font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); border-color: var(--border); display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--text-secondary);">
               <i class="material-icons" style="font-size: 20px;">cloud_download</i> Download Semua
            </a>
         </div>

         <!-- Progress Siswa All -->
         <div id="progressSiswa" class="d-none" style="margin-bottom: 28px; background: var(--surface); padding: 12px 16px; border-radius: var(--r-md); border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
               <span id="progressTextSiswa" style="font-size: 13px; font-weight: 600; color: var(--text-primary);"></span>
               <i id="progressSelesaiSiswa" class="material-icons d-none" style="font-size: 18px; color: var(--primary);">check_circle</i>
            </div>
            <div class="progress-siswa" style="margin: 0; background-color: #EDE9FE;">
               <div id="progressBarSiswa" class="my-progress-bar" style="width: 0%; background-color: var(--primary);" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
         </div>

         <hr style="border: 0; border-top: 1px dashed #E5E7EB; margin: 0 0 28px 0;">

         <!-- Per Kelas Section -->
         <div style="margin-bottom: 16px;">
            <h2 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin: 0 0 4px 0;">Generate Per Kelas Spesifik</h2>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Pilih tingkatan kelas untuk menargetkan ekspor QR Code.</p>
         </div>
         <form action="<?= base_url('admin/qr/siswa/download'); ?>" method="get" style="margin-top: auto;">
            <div style="margin-bottom: 20px;">
               <select name="id_kelas" id="kelasSelect" class="form-control" required style="font-size: 14px; font-weight: 600; padding: 12px 16px;">
                  <option value="">-- Pilih Kelas Siswa --</option>
                  <?php foreach ($kelas as $value): ?>
                     <option id="idKelas<?= esc($value['id_kelas']); ?>" value="<?= esc($value['id_kelas']); ?>">
                        <?= esc($value['kelas']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <div class="text-danger mt-2" id="textErrorKelas" style="font-size: 12px; font-weight: 600;"></div>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 20px;">
               <button type="button" onclick="generateQrSiswaByKelas()" class="btn" style="flex: 1; background: var(--primary-soft); color: var(--primary); border: 1px solid rgba(124, 58, 237, 0.3); font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); display: flex; align-items: center; justify-content: center; gap: 8px;">
                  <i class="material-icons" style="font-size: 20px;">qr_code</i> Generate Kelas
               </button>
               <button type="submit" class="btn btn-outline" style="flex: 1; font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); border-color: var(--border); display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--text-secondary);">
                  <i class="material-icons" style="font-size: 20px;">cloud_download</i> Download Kelas
               </button>
            </div>

            <!-- Progress Kelas -->
            <div id="progressKelas" class="d-none" style="background: var(--surface); padding: 12px 16px; border-radius: var(--r-md); border: 1px solid var(--border);">
               <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                  <span id="progressTextKelas" style="font-size: 13px; font-weight: 600; color: var(--text-primary);"></span>
                  <i id="progressSelesaiKelas" class="material-icons d-none" style="font-size: 18px; color: var(--primary);">check_circle</i>
               </div>
               <div class="progress-siswa d-none" id="progressBarBgKelas" style="margin: 0; background-color: #EDE9FE;">
                  <div id="progressBarKelas" class="my-progress-bar d-none" style="width: 0%; background-color: var(--primary);" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
            </div>
         </form>
      </div>
   </div>

   <!-- DATA GURU CARD -->
   <div class="flex-1">
      <div class="premium-filter-card h-full flex flex-col" style="margin: 0; border-top-color: var(--primary) !important;">
         <div class="flex items-center gap-4 mb-6">
            <div style="width: 52px; height: 52px; border-radius: var(--r-md); background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
               <i class="material-icons" style="font-size: 28px;">badge</i>
            </div>
            <div>
               <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;">QR Code Guru</h2>
               <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0 0;">
                  Total Guru: <strong style="color: var(--text-primary);"><?= count($guru); ?></strong> akun • 
                  <a href="<?= base_url('admin/guru'); ?>" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Lihat Data</a>
               </p>
            </div>
         </div>

         <!-- All Guru Buttons -->
         <div style="display: flex; gap: 12px; margin-bottom: 24px;">
            <button onclick="generateAllQrGuru()" class="btn" style="flex: 1; background: linear-gradient(135deg, var(--primary), #6D28D9); color: #FFFFFF; font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;">
               <i class="material-icons" style="font-size: 20px;">qr_code_2</i> Generate Semua
            </button>
            <a href="<?= base_url('admin/qr/guru/download'); ?>" class="btn btn-outline" style="flex: 1; font-weight: 600; padding: 12px 16px; border-radius: var(--r-full); border-color: var(--border); display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--text-secondary);">
               <i class="material-icons" style="font-size: 20px;">cloud_download</i> Download Semua
            </a>
         </div>

         <!-- Progress Guru All -->
         <div id="progressGuru" class="d-none" style="margin-bottom: 28px; background: var(--surface); padding: 12px 16px; border-radius: var(--r-md); border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
               <span id="progressTextGuru" style="font-size: 13px; font-weight: 600; color: var(--text-primary);"></span>
               <i id="progressSelesaiGuru" class="material-icons d-none" style="font-size: 18px; color: var(--primary);">check_circle</i>
            </div>
            <div class="progress-guru" style="margin: 0; background-color: #EDE9FE;">
               <div id="progressBarGuru" class="my-progress-bar" style="width: 0%; background-color: var(--primary);" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
         </div>

         <div style="margin-top: auto; padding: 16px; background: rgba(124, 58, 237, 0.06); border: 1px solid rgba(124, 58, 237, 0.15); border-radius: var(--r-lg); display: flex; gap: 14px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <i class="material-icons" style="font-size: 22px; color: var(--primary); margin-top: 2px;">tips_and_updates</i>
            <div style="font-size: 13px; color: #4c1d95; line-height: 1.6;">
               <strong style="color: var(--primary);">Informasi Penyimpanan:</strong> Seluruh berkas gambar matriks QR Code yang dihasilkan akan otomatis terenkripsi dan disimpan di dalam folder <code>public/uploads/</code> server. Anda juga dapat mengunduh QR Code spesifik secara individual melalui halaman daftar guru.
            </div>
         </div>
      </div>
   </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const dataGuru = [
    <?php foreach ($guru as $value) {
      echo "{
              'nama' : `$value[nama_guru]`,
              'unique_code' : `$value[unique_code]`,
              'nomor' : `$value[nuptk]`
            },";
    }
    ; ?>
  ];

  const dataSiswa = [
    <?php foreach ($siswa as $value) {
      echo "{
              'nama' : " . json_encode($value['nama_siswa'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ",
              'unique_code' : `$value[unique_code]`,
              'id_kelas' : `$value[id_kelas]`,
              'nomor' : " . json_encode($value['nis'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "
            },";
    }
    ; ?>
  ];

  var dataSiswaPerKelas = [];

  function generateAllQrSiswa() {
    var i = 1;
    $('#progressSiswa').removeClass('d-none');
    $('#progressBarSiswa')
      .attr('aria-valuenow', '0')
      .attr('aria-valuemin', '0')
      .attr('aria-valuemax', dataSiswa.length)
      .attr('style', 'width: 0%;');

    dataSiswa.forEach(element => {
      jQuery.ajax({
        url: "<?= base_url('admin/generate/siswa'); ?>",
        type: 'post',
        data: {
          nama: element['nama'],
          unique_code: element['unique_code'],
          id_kelas: element['id_kelas'],
          nomor: element['nomor']
        },
        success: function (response) {
          if (!response) return;
          if (i != dataSiswa.length) {
            $('#progressTextSiswa').html('Progres: ' + i + '/' + dataSiswa.length);
          } else {
            $('#progressTextSiswa').html('Progres: ' + i + '/' + dataSiswa.length + ' selesai');
            $('#progressSelesaiSiswa').removeClass('d-none');
          }

          $('#progressBarSiswa')
            .attr('aria-valuenow', i)
            .attr('style', 'width: ' + (i / dataSiswa.length) * 100 + '%;');
          i++;
        }
      });
    });
  }

  function generateQrSiswaByKelas() {
    var i = 1;

    idKelas = $('#kelasSelect').val();

    if (idKelas == '') {
      $('#progressKelas').addClass('d-none');
      $('#textErrorKelas').html('Pilih kelas terlebih dahulu');
      return;
    }

    kelas = $('#idKelas' + idKelas).html();

    jQuery.ajax({
      url: "<?= base_url('admin/generate/siswa-by-kelas'); ?>",
      type: 'post',
      data: {
        idKelas: idKelas
      },
      success: function (response) {
        dataSiswaPerKelas = response;

        if (dataSiswaPerKelas.length < 1) {
          $('#progressKelas').addClass('d-none');
          $('#textErrorKelas').html('Data siswa kelas ' + kelas + ' tidak ditemukan');
          return;
        }

        $('#textErrorKelas').html('')

        $('#progressKelas').removeClass('d-none');
        $('#progressBarBgKelas')
          .removeClass('d-none');
        $('#progressBarKelas')
          .removeClass('d-none')
          .attr('aria-valuenow', '0')
          .attr('aria-valuemin', '0')
          .attr('aria-valuemax', dataSiswaPerKelas.length)
          .attr('style', 'width: 0%;');

        dataSiswaPerKelas.forEach(element => {
          jQuery.ajax({
            url: "<?= base_url('admin/generate/siswa'); ?>",
            type: 'post',
            data: {
              nama: element['nama_siswa'],
              unique_code: element['unique_code'],
              id_kelas: element['id_kelas'],
              nomor: element['nis']
            },
            success: function (response) {
              if (!response) return;
              if (i != dataSiswaPerKelas.length) {
                $('#progressTextKelas').html('Progres: ' + i + '/' + dataSiswaPerKelas.length);
              } else {
                $('#progressTextKelas').html('Progres: ' + i + '/' + dataSiswaPerKelas.length + ' selesai');
                $('#progressSelesaiKelas').removeClass('d-none');
              }

              $('#progressBarKelas')
                .attr('aria-valuenow', i)
                .attr('style', 'width: ' + (i / dataSiswaPerKelas.length) * 100 + '%;');
              i++;
            },
            error: function (xhr, status, thrown) {
              console.error(xhr + status + thrown);
            }
          });
        });
      }
    });
  }

  function generateAllQrGuru() {
    var i = 1;
    $('#progressGuru').removeClass('d-none');
    $('#progressBarGuru')
      .attr('aria-valuenow', '0')
      .attr('aria-valuemin', '0')
      .attr('aria-valuemax', dataGuru.length)
      .attr('style', 'width: 0%;');

    dataGuru.forEach(element => {
      jQuery.ajax({
        url: "<?= base_url('admin/generate/guru'); ?>",
        type: 'post',
        data: {
          nama: element['nama'],
          unique_code: element['unique_code'],
          nomor: element['nomor']
        },
        success: function (response) {
          if (!response) return;
          if (i != dataGuru.length) {
            $('#progressTextGuru').html('Progres: ' + i + '/' + dataGuru.length);
          } else {
            $('#progressTextGuru').html('Progres: ' + i + '/' + dataGuru.length + ' selesai');
            $('#progressSelesaiGuru').removeClass('d-none');
          }

          $('#progressBarGuru')
            .attr('aria-valuenow', i)
            .attr('style', 'width: ' + (i / dataGuru.length) * 100 + '%;');
          i++;
        }
      });
    });
  }
</script>
<?= $this->endSection() ?>
