<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success' ?>">
      <i class="material-icons"><?= session()->getFlashdata('error') == true ? 'error' : 'check_circle' ?></i>
      <span><?= session()->getFlashdata('msg') ?></span>
      <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
        <i class="material-icons">close</i>
      </button>
    </div>
  <?php endif; ?>

  <section class="card overflow-hidden">
    <div class="card-head">
      <div>
        <h2 class="card-title">QR siswa <?= $kelas['kelas']; ?></h2>
        <p class="card-sub"><span class="tabular-nums"><?= count($siswa); ?></span> siswa</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="<?= base_url('teacher/qr/download'); ?>" class="btn btn-secondary btn-sm">
          <i class="material-icons">download</i>Unduh semua (.zip)
        </a>
        <button onclick="generateAllQrSiswa()" class="btn btn-primary btn-sm">
          <i class="material-icons">qr_code_2</i>Buat semua
        </button>
      </div>
    </div>

    <div id="progressSiswa" class="d-none border-b border-slate-100 px-4 py-3">
      <div class="mb-1.5 flex items-center justify-between gap-2">
        <span id="progressTextSiswa" class="text-xs font-semibold tabular-nums text-slate-700"></span>
        <i id="progressSelesaiSiswa" class="material-icons d-none text-base text-green-700">check</i>
      </div>
      <div class="progress-siswa h-1.5 overflow-hidden rounded-full bg-primary-100">
        <div id="progressBarSiswa" class="my-progress-bar h-full rounded-full bg-primary-700 transition-all" style="width: 0%;" role="progressbar" aria-valuenow="" aria-valuemin="" aria-valuemax=""></div>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th class="num">No</th>
            <th>NIS</th>
            <th>Nama siswa</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1;
          foreach ($siswa as $s): ?>
            <tr>
              <td class="num"><?= $i++; ?></td>
              <td class="tabular-nums"><?= esc($s['nis']); ?></td>
              <td><?= esc($s['nama_siswa']); ?></td>
              <td class="text-right">
                <a href="<?= base_url('admin/qr/siswa/' . $s['id_siswa'] . '/download'); ?>" class="btn btn-ghost btn-sm">
                  <i class="material-icons">download</i>Unduh QR
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
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
</script>
<?= $this->endSection() ?>