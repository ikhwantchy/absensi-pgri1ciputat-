<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid px-0">
      
      <div class="max-w-3xl mx-auto">
         <!-- Card -->
         <div class="card mb-0">
            <div class="card-header border-b border-gray-100 flex items-center justify-between">
               <div>
                  <h4 class="text-lg font-bold text-gray-900">Form Edit Siswa</h4>
                  <p class="text-sm text-gray-500 mt-1">Perbarui data siswa pada form di bawah ini.</p>
               </div>
               <a href="<?= base_url('admin/siswa') ?>" class="btn btn-outline hover:bg-gray-50">
                  <i class="material-icons text-[18px]">arrow_back</i> Kembali
               </a>
            </div>
            
            <div class="card-body">
               <?php if (session()->getFlashdata('msg')): ?>
                  <div class="mb-6 p-4 rounded-lg flex items-center justify-between <?= session()->getFlashdata('error') == true ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
                     <div class="flex items-center gap-3">
                        <i class="material-icons"><?= session()->getFlashdata('error') == true ? 'error_outline' : 'check_circle' ?></i>
                        <span class="font-medium"><?= session()->getFlashdata('msg') ?></span>
                     </div>
                     <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="this.parentElement.style.display='none'">
                        <i class="material-icons">close</i>
                     </button>
                  </div>
               <?php endif; ?>

               <form action="<?= base_url('admin/siswa/edit'); ?>" method="post" class="space-y-6">
                  <?= csrf_field() ?>
                  <?php $validation = \Config\Services::validation(); ?>
                  <?php if (!empty($data['ppdb_ref'])): ?>
                     <div class="p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-sm">Data ini dikelola PPDB; ubah di PPDB.</div>
                  <?php endif; ?>

                  <input type="hidden" name="id" value="<?= $data['id_siswa']; ?>">

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIS <span class="text-red-500">*</span></label>
                        <input type="text" id="nis" name="nis"
                           class="form-control <?= $validation->getError('nis') ? 'border-red-500 focus:ring-red-500' : ''; ?>"
                           placeholder="Contoh: 2023001" value="<?= esc(old('nis') ?? $oldInput['nis'] ?? $data['nis']) ?>">
                        <?php if ($validation->getError('nis')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('nis'); ?></p>
                        <?php endif; ?>
                     </div>

                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="nama" name="nama"
                           class="form-control <?= $validation->getError('nama') ? 'border-red-500 focus:ring-red-500' : ''; ?>"
                           placeholder="Masukkan nama lengkap" value="<?= esc(old('nama') ?? $oldInput['nama'] ?? $data['nama_siswa']) ?>" required>
                        <?php if ($validation->getError('nama')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('nama'); ?></p>
                        <?php endif; ?>
                     </div>

                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                        <select class="form-select <?= $validation->getError('id_kelas') ? 'border-red-500 focus:ring-red-500' : ''; ?>"
                           id="kelas" name="id_kelas">
                           <option value="">-- Pilih kelas --</option>
                           <?php foreach ($kelas as $value): ?>
                              <option value="<?= $value['id_kelas']; ?>" <?= (old('id_kelas') == $value['id_kelas'] || (isset($oldInput) && is_array($oldInput) && isset($oldInput['id_kelas']) && $oldInput['id_kelas'] == $value['id_kelas']) || ($value['id_kelas'] == $data['id_kelas'])) ? 'selected' : ''; ?>>
                                 <?= $value['kelas']; ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                        <?php if ($validation->getError('id_kelas')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('id_kelas'); ?></p>
                        <?php endif; ?>
                     </div>

                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <?php
                        $jenisKelamin = (old('jk') ?? (isset($oldInput) && is_array($oldInput) ? ($oldInput['jk'] ?? null) : null) ?? $data['jenis_kelamin']);
                        $l = $jenisKelamin == 'Laki-laki' || $jenisKelamin == '1' ? 'checked' : '';
                        $p = $jenisKelamin == 'Perempuan' || $jenisKelamin == '2' ? 'checked' : '';
                        ?>
                        <div class="flex gap-4 p-2">
                           <label class="flex items-center gap-2 cursor-pointer">
                              <input type="radio" name="jk" value="1" class="w-4 h-4 text-primary focus:ring-primary" <?= $l; ?>>
                              <span class="text-sm font-medium text-gray-700">Laki-laki</span>
                           </label>
                           <label class="flex items-center gap-2 cursor-pointer">
                              <input type="radio" name="jk" value="2" class="w-4 h-4 text-primary focus:ring-primary" <?= $p; ?>>
                              <span class="text-sm font-medium text-gray-700">Perempuan</span>
                           </label>
                        </div>
                        <?php if ($validation->getError('jk')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('jk'); ?></p>
                        <?php endif; ?>
                     </div>

                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">No HP</label>
                        <input type="number" id="hp" name="no_hp"
                           class="form-control <?= $validation->getError('no_hp') ? 'border-red-500 focus:ring-red-500' : ''; ?>"
                           placeholder="Contoh: 08123456789" value="<?= esc(old('no_hp') ?? $oldInput['no_hp'] ?? $data['no_hp']) ?>">
                        <?php if ($validation->getError('no_hp')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('no_hp'); ?></p>
                        <?php endif; ?>
                     </div>

                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">RFID Code</label>
                        <input type="text" id="rfid" name="rfid"
                           class="form-control <?= $validation->getError('rfid') ? 'border-red-500 focus:ring-red-500' : ''; ?>"
                           placeholder="Tap RFID Card here" value="<?= old('rfid') ?? $oldInput['rfid'] ?? $data['rfid_code'] ?? '' ?>">
                        <?php if ($validation->getError('rfid')): ?>
                           <p class="mt-1 text-sm text-red-500"><?= $validation->getError('rfid'); ?></p>
                        <?php endif; ?>
                     </div>
                  </div>

                  <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                     <button type="submit" class="btn btn-primary">
                        <i class="material-icons">save</i> Update Data Siswa
                     </button>
                  </div>
               </form>

            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>