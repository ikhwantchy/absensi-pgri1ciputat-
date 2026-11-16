<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php $validation = \Config\Services::validation(); ?>
<div class="max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <form action="<?= base_url('admin/petugas/edit'); ?>" method="post" class="card">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $data['id']; ?>">

      <div class="card-body grid gap-3 sm:grid-cols-2">
         <div>
            <label class="label" for="username">Username</label>
            <input type="text" id="username" class="input <?= $validation->getError('username') ? 'is-invalid' : ''; ?>" name="username" placeholder="Masukkan username" value="<?= old('username') ?? $oldInput['username'] ?? $data['username'] ?>">
            <div class="invalid-feedback"><?= $validation->getError('username'); ?></div>
         </div>

         <div>
            <label class="label" for="email">Email</label>
            <input type="email" id="email" class="input <?= $validation->getError('email') ? 'is-invalid' : ''; ?>" name="email" placeholder="contoh@sekolah.id" value="<?= old('email') ?? $oldInput['email'] ?? $data['email'] ?>">
            <div class="invalid-feedback"><?= $validation->getError('email'); ?></div>
         </div>

         <div class="sm:col-span-2">
            <label class="label" for="password">Password baru <span class="font-normal text-slate-500">(opsional)</span></label>
            <input type="password" id="password" class="input <?= $validation->getError('password') ? 'is-invalid' : ''; ?>" name="password" placeholder="Kosongkan bila tidak diubah">
            <div class="invalid-feedback"><?= $validation->getError('password'); ?></div>
         </div>

         <div>
            <label class="label" for="role">Peran</label>
            <select class="input <?= $validation->getError('role') ? 'is-invalid' : ''; ?>" id="role" name="role">
               <option value="">Pilih peran</option>
               <?php foreach ($roles as $role): ?>
                  <option value="<?= $role->value ?>" <?= (old('role') ?? $oldInput['role'] ?? $data['is_superadmin']) == (string) $role->value ? 'selected' : ''; ?>>
                     <?= $role->label() ?>
                  </option>
               <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= $validation->getError('role'); ?></div>
         </div>

         <div>
            <label class="label" for="id_guru">Guru terkait <span class="font-normal text-slate-500">(opsional)</span></label>
            <select class="input" id="id_guru" name="id_guru">
               <option value="">Tidak dihubungkan</option>
               <?php foreach ($guru as $g): ?>
                  <option value="<?= $g['id_guru']; ?>" <?= (old('id_guru') ?? $data['id_guru']) == $g['id_guru'] ? 'selected' : ''; ?>><?= $g['nama_guru']; ?></option>
               <?php endforeach; ?>
            </select>
         </div>
      </div>

      <div class="card-foot">
         <a href="<?= base_url('admin/petugas'); ?>" class="btn btn-secondary">Batal</a>
         <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
   </form>
</div>
<?= $this->endSection() ?>
