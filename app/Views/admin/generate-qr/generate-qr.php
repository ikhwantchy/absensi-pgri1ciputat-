<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="space-y-4">
   <?= view('admin/_messages'); ?>

   <div class="grid gap-4 lg:grid-cols-2">
      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title">QR siswa</h2>
               <p class="card-sub"><span class="tabular-nums"><?= count($siswa); ?></span> siswa · <a href="<?= base_url('admin/siswa'); ?>" class="font-semibold no-underline hover:underline">Lihat data</a></p>
            </div>
            <div class="flex flex-wrap gap-2">
               <a href="<?= base_url('admin/qr/siswa/download'); ?>" class="btn btn-secondary btn-sm">
                  <i class="material-icons">download</i>Unduh semua
               </a>
               <button onclick="generateAllQrSiswa()" class="btn btn-primary btn-sm">
                  <i class="material-icons">qr_code_2</i>Buat semua
               </button>
            </div>
         </div>

         <div class="card-body grid gap-y-3">
            <div id="progressSiswa" class="d-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
               <div class="mb-1.5 flex items-center justify-between gap-2">
                  <span id="progressTextSiswa" class="text-xs font-semibold tabular-nums text-slate-700"></span>
                  <i id="progressSelesaiSiswa" class="material-icons d-none text-base text-green-700">check_circle</i>
               </div>
               <div class="progress-siswa h-1.5 overflow-hidden rounded-full bg-primary-100">
                  <div id="progressBarSiswa" class="my-progress-bar h-full rounded-full bg-primary-700 transition-all" style="width: 0%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
            </div>

            <form action="<?= base_url('admin/qr/siswa/download'); ?>" method="get" class="grid gap-y-2.5">
               <div>
                  <label for="kelasSelect" class="label">Per kelas</label>
                  <select name="id_kelas" id="kelasSelect" class="input" required>
                     <option value="">Pilih kelas</option>
                     <?php foreach ($kelas as $value): ?>
                        <option id="idKelas<?= esc($value['id_kelas']); ?>" value="<?= esc($value['id_kelas']); ?>">
                           <?= esc($value['kelas']); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
                  <div class="field-error" id="textErrorKelas"></div>
               </div>

               <div class="flex flex-wrap justify-end gap-2">
                  <button type="submit" class="btn btn-secondary">
                     <i class="material-icons">download</i>Unduh kelas
                  </button>
                  <button type="button" onclick="generateQrSiswaByKelas()" class="btn btn-secondary">
                     <i class="material-icons">qr_code</i>Buat kelas
                  </button>
               </div>

               <div id="progressKelas" class="d-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                  <div class="mb-1.5 flex items-center justify-between gap-2">
                     <span id="progressTextKelas" class="text-xs font-semibold tabular-nums text-slate-700"></span>
                     <i id="progressSelesaiKelas" class="material-icons d-none text-base text-green-700">check_circle</i>
                  </div>
                  <div class="progress-siswa d-none h-1.5 overflow-hidden rounded-full bg-primary-100" id="progressBarBgKelas">
                     <div id="progressBarKelas" class="my-progress-bar d-none h-full rounded-full bg-primary-700 transition-all" style="width: 0%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
               </div>
            </form>
         </div>
      </section>

      <section class="card self-start">
         <div class="card-head">
            <div>
               <h2 class="card-title inline-flex items-center gap-1">
                  QR guru
                  <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Berkas QR disimpan di folder public/uploads/ server.</span></span>
               </h2>
               <p class="card-sub"><span class="tabular-nums"><?= count($guru); ?></span> guru · <a href="<?= base_url('admin/guru'); ?>" class="font-semibold no-underline hover:underline">Lihat data</a></p>
            </div>
            <div class="flex flex-wrap gap-2">
               <a href="<?= base_url('admin/qr/guru/download'); ?>" class="btn btn-secondary btn-sm">
                  <i class="material-icons">download</i>Unduh semua
               </a>
               <button onclick="generateAllQrGuru()" class="btn btn-primary btn-sm">
                  <i class="material-icons">qr_code_2</i>Buat semua
               </button>
            </div>
         </div>

         <div class="card-body grid gap-y-3">
            <p class="text-slate-600">QR per guru bisa diunduh dari halaman data guru.</p>
            <div id="progressGuru" class="d-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
               <div class="mb-1.5 flex items-center justify-between gap-2">
                  <span id="progressTextGuru" class="text-xs font-semibold tabular-nums text-slate-700"></span>
                  <i id="progressSelesaiGuru" class="material-icons d-none text-base text-green-700">check_circle</i>
               </div>
               <div class="progress-guru h-1.5 overflow-hidden rounded-full bg-primary-100">
                  <div id="progressBarGuru" class="my-progress-bar h-full rounded-full bg-primary-700 transition-all" style="width: 0%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
            </div>
         </div>
      </section>
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
