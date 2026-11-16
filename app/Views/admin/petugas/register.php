<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl space-y-4">
   <?= view('Myth\Auth\Views\_message_block') ?>

   <form action="<?= base_url('admin/petugas/register') ?>" method="post" class="card">
      <?= csrf_field() ?>

      <div class="card-body grid gap-3 sm:grid-cols-2">
         <div>
            <label class="label" for="username"><?= lang('Auth.username') ?></label>
            <input type="text" id="username" class="input <?php if (session('errors.username')): ?>is-invalid<?php endif ?>" name="username" placeholder="Masukkan username" value="<?= old('username') ?>">
            <?php if (session('errors.username')): ?>
               <div class="invalid-feedback"><?= session('errors.username') ?></div>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="email"><?= lang('Auth.email') ?></label>
            <input type="email" id="email" class="input <?php if (session('errors.email')): ?>is-invalid<?php endif ?>" name="email" placeholder="contoh@sekolah.id" value="<?= old('email') ?>">
            <?php if (session('errors.email')): ?>
               <div class="invalid-feedback"><?= session('errors.email') ?></div>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="password"><?= lang('Auth.password') ?></label>
            <input type="password" id="password" name="password" class="input <?php if (session('errors.password')): ?>is-invalid<?php endif ?>" placeholder="Masukkan password" autocomplete="off">
            <?php if (session('errors.password')): ?>
               <div class="invalid-feedback"><?= session('errors.password') ?></div>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="pass_confirm"><?= lang('Auth.repeatPassword') ?></label>
            <input type="password" id="pass_confirm" name="pass_confirm" class="input <?php if (session('errors.pass_confirm')): ?>is-invalid<?php endif ?>" placeholder="Ulangi password" autocomplete="off">
            <?php if (session('errors.pass_confirm')): ?>
               <div class="invalid-feedback"><?= session('errors.pass_confirm') ?></div>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="role">Peran</label>
            <select class="input <?php if (session('errors.role')): ?>is-invalid<?php endif ?>" id="role" name="role">
               <option value="">Pilih peran</option>
               <?php foreach ($roles as $role): ?>
                  <option value="<?= $role->value ?>" <?= old('role') == (string) $role->value ? 'selected' : ''; ?>>
                     <?= $role->label() ?>
                  </option>
               <?php endforeach; ?>
            </select>
            <?php if (session('errors.role')): ?>
               <div class="invalid-feedback"><?= session('errors.role') ?></div>
            <?php endif; ?>
         </div>

         <div>
            <label class="label" for="id_guru">Guru terkait <span class="font-normal text-slate-500">(opsional)</span></label>
            <select class="input" id="id_guru" name="id_guru">
               <option value="">Tidak dihubungkan</option>
               <?php foreach ($guru as $g): ?>
                  <option value="<?= $g['id_guru']; ?>" <?= old('id_guru') == $g['id_guru'] ? 'selected' : ''; ?>><?= $g['nama_guru']; ?></option>
               <?php endforeach; ?>
            </select>
         </div>
      </div>

      <div class="card-foot">
         <a href="<?= base_url('admin/petugas'); ?>" class="btn btn-secondary">Batal</a>
         <button type="submit" class="btn btn-primary"><?= lang('Auth.register') ?></button>
      </div>
   </form>
</div>
<?= $this->endSection() ?>
