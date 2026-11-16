<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="space-y-4">
   <?= view('admin/_messages'); ?>

   <div class="grid gap-4 lg:grid-cols-2">
      <section class="card flex flex-col">
         <div class="card-head">
            <div>
               <h2 class="card-title">Laporan absen siswa</h2>
               <p class="card-sub">Rekap kehadiran bulanan per kelas</p>
            </div>
         </div>
         <form action="<?= base_url('admin/laporan/siswa'); ?>" method="post" class="flex flex-1 flex-col">
            <div class="card-body grid flex-1 content-start gap-3 sm:grid-cols-2">
               <div>
                  <label for="tanggalSiswa" class="label">Bulan</label>
                  <input type="month" name="tanggalSiswa" id="tanggalSiswa" class="input" value="<?= date('Y-m'); ?>">
               </div>
               <div>
                  <label for="kelas" class="label">Kelas</label>
                  <select name="kelas" id="kelas" class="input">
                     <option value="">Pilih kelas</option>
                     <?php foreach ($kelas as $key => $value): ?>
                        <?php
                        $idKelas = $value['id_kelas'];
                        $namaKelas = $value['kelas'];
                        $totalSiswa = count($siswaPerKelas[$key]);
                        ?>
                        <option value="<?= $idKelas; ?>">
                           <?= "$namaKelas - ($totalSiswa Siswa)"; ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>
            </div>
            <div class="card-foot">
               <button type="submit" name="type" value="doc" class="btn btn-secondary">
                  <i class="material-icons">description</i>Unduh Word
               </button>
               <button type="submit" name="type" value="pdf" class="btn btn-primary">
                  <i class="material-icons">picture_as_pdf</i>Unduh PDF
               </button>
            </div>
         </form>
      </section>

      <section class="card flex flex-col">
         <div class="card-head">
            <div>
               <h2 class="card-title">Laporan absen guru</h2>
               <p class="card-sub"><span class="tabular-nums"><?= count($guru); ?></span> guru terdaftar</p>
            </div>
         </div>
         <form action="<?= base_url('admin/laporan/guru'); ?>" method="post" class="flex flex-1 flex-col">
            <div class="card-body grid flex-1 content-start gap-3 sm:grid-cols-2">
               <div>
                  <label for="tanggalGuru" class="label">Bulan</label>
                  <input type="month" name="tanggalGuru" id="tanggalGuru" class="input" value="<?= date('Y-m'); ?>">
               </div>
            </div>
            <div class="card-foot">
               <button type="submit" name="type" value="doc" class="btn btn-secondary">
                  <i class="material-icons">description</i>Unduh Word
               </button>
               <button type="submit" name="type" value="pdf" class="btn btn-primary">
                  <i class="material-icons">picture_as_pdf</i>Unduh PDF
               </button>
            </div>
         </form>
      </section>
   </div>
</div>

<?= $this->endSection() ?>
