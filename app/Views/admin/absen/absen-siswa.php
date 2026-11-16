<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<?php
/* Map avatar background colour by initial */
if (!function_exists('avatarColor')) {
    function avatarColor($name) {
        $colors = ['#7C3AED','#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#F97316'];
        return $colors[ord(strtolower($name[0] ?? 'a')) % count($colors)];
    }
}
?>

<div class="space-y-4">
   <!-- Filter: tanggal + kelas -->
   <div class="filter-bar">
      <div class="min-w-[10rem] flex-1 sm:max-w-[13rem] sm:flex-none">
         <label class="label" for="tanggal">Tanggal</label>
         <input type="date" id="tanggal" name="tanggal" class="input" value="<?= date('Y-m-d'); ?>" onchange="onDateChange()">
      </div>

      <div class="w-full">
         <div class="label">Kelas</div>
         <div class="grid gap-y-2" id="klassPills">
            <?php
            $groupedKelas = [];
            foreach ($kelas as $value) {
                $tingkat = $value['tingkat'];
                $groupedKelas[$tingkat][] = $value;
            }
            $tingkatOrder = ['VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            foreach (array_keys($groupedKelas) as $k) {
                if (!in_array($k, $tingkatOrder)) {
                    $tingkatOrder[] = $k;
                }
            }

            foreach ($tingkatOrder as $tingkat):
                if (!empty($groupedKelas[$tingkat])):
            ?>
               <div class="flex flex-wrap items-center gap-1.5">
                  <span class="w-full text-xs font-semibold text-slate-500 sm:w-24">Tingkat <?= esc($tingkat) ?></span>
                  <?php foreach ($groupedKelas[$tingkat] as $value):
                     $idKelas   = $value['id_kelas'];
                     $namaKelas = $value['kelas'];
                  ?>
                     <button type="button" id="kelas-<?= $idKelas; ?>"
                             onclick="getSiswa(<?= $idKelas; ?>, '<?= esc($namaKelas); ?>')"
                             class="kelas-pill rounded-md border border-slate-300 bg-white px-3 py-1 text-sm font-medium text-slate-700 hover:bg-slate-50 [&.active]:border-primary-700 [&.active]:bg-primary-700 [&.active]:text-white">
                        <?= esc($namaKelas); ?>
                     </button>
                  <?php endforeach; ?>
               </div>
            <?php
                endif;
            endforeach;
            ?>
         </div>
      </div>
   </div>

   <!-- Tabel absensi (dimuat via AJAX) -->
   <div id="dataSiswa">
      <div class="card">
         <p class="empty">Pilih kelas untuk menampilkan absensi siswa.</p>
      </div>
   </div>
</div>

<!-- MODAL Ubah Kehadiran -->
<div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalLabel">Ubah kehadiran siswa</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
               <i class="material-icons">close</i>
            </button>
         </div>
         <div id="modalFormUbahSiswa"></div>
      </div>
   </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var lastIdKelas = null, lastKelas = null;

function onDateChange() {
   if (lastIdKelas != null) getSiswa(lastIdKelas, lastKelas);
}

function getSiswa(idKelas, kelas) {
   var tanggal = document.getElementById('tanggal').value;
   updatePill(idKelas);

   document.getElementById('dataSiswa').innerHTML = `
      <div class="card">
         <div class="empty">
            <div class="spinner mt-0 mb-2"></div>
            Memuat data kelas <strong class="text-slate-900">${kelas}</strong>…
         </div>
      </div>`;

   jQuery.ajax({
      url: "<?= base_url('/admin/absen-siswa'); ?>",
      type: 'post',
      data: { kelas, id_kelas: idKelas, tanggal },
      success: function(response) {
         document.getElementById('dataSiswa').innerHTML = response;
         jQuery('html,body').animate({ scrollTop: jQuery('#dataSiswa').offset().top - 80 }, 400);
      },
      error: function(xhr, status, thrown) {
         document.getElementById('dataSiswa').innerHTML =
            `<div class="card">
               <p class="empty text-danger-700">Gagal memuat data. Coba lagi.</p>
             </div>`;
      }
   });

   lastIdKelas = idKelas;
   lastKelas   = kelas;
}

function updatePill(activeId) {
   document.querySelectorAll('.kelas-pill').forEach(function(btn) {
      btn.classList.remove('active');
   });
   var el = document.getElementById('kelas-' + activeId);
   if (el) el.classList.add('active');
}

function getDataKehadiran(idPresensi, idSiswa) {
   jQuery.ajax({
      url: "<?= base_url('/admin/absen-siswa/kehadiran'); ?>",
      type: 'post',
      data: { id_presensi: idPresensi, id_siswa: idSiswa },
      success: function(response) { jQuery('#modalFormUbahSiswa').html(response); },
      error: function(xhr, status, thrown) { jQuery('#modalFormUbahSiswa').html(thrown); }
   });
}

function ubahKehadiran() {
   var tanggal = document.getElementById('tanggal').value;
   var form = jQuery('#formUbah').serializeArray();
   form.push({ name: 'tanggal', value: tanggal });

   jQuery.ajax({
      url: "<?= base_url('/admin/absen-siswa/edit'); ?>",
      type: 'post',
      data: form,
      success: function(response) {
         if (response['status']) {
            getSiswa(lastIdKelas, lastKelas);
            jQuery('#ubahModal').modal('hide');
         } else {
            alert('Gagal ubah kehadiran: ' + response['nama_siswa']);
         }
      },
      error: function(xhr, status, thrown) { alert('Gagal ubah kehadiran\n' + thrown); }
   });
}
</script>
<?= $this->endSection() ?>
