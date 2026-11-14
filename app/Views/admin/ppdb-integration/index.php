<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="admin-page-header">
   <div>
      <h1 class="page-title">Integrasi PPDB</h1>
      <div class="breadcrumb">
         <a href="<?= base_url('admin/dashboard'); ?>">Dashboard</a>
         <span class="breadcrumb-sep">›</span>
         <span class="breadcrumb-active">Integrasi PPDB</span>
      </div>
   </div>
</div>

<?= view('admin/_messages'); ?>

<?php if ($tokenBad || $secretBad): ?>
   <div class="pb-2 px-3">
      <div class="alert alert-danger">
         <?= $tokenBad ? 'Token tersimpan tidak bisa dibaca. ' : '' ?><?= $secretBad ? 'Secret webhook tersimpan tidak bisa dibaca. ' : '' ?>Isi ulang lalu simpan.
      </div>
   </div>
<?php endif; ?>

<div class="premium-filter-card" style="max-width: 850px; margin: 12px auto 24px auto;">
   <form action="<?= base_url('admin/ppdb-integration/save'); ?>" method="post" autocomplete="off">
      <div class="mb-4">
         <label class="block text-sm font-semibold mb-2" for="base_url">URL PPDB</label>
         <input type="url" id="base_url" name="base_url" class="form-control" placeholder="https://ppdb.example.sch.id" value="<?= esc($baseUrl, 'attr'); ?>" required>
      </div>
      <div class="mb-4">
         <label class="block text-sm font-semibold mb-2" for="token">Token API <?= $hasToken ? '<span style="color:#15803d;font-weight:600;">(sudah diisi)</span>' : ''; ?></label>
         <input type="password" id="token" name="token" class="form-control" placeholder="Kosongkan bila tidak diubah" autocomplete="new-password">
      </div>
      <div class="mb-4">
         <label class="block text-sm font-semibold mb-2" for="webhook_secret">Secret webhook <?= $hasSecret ? '<span style="color:#15803d;font-weight:600;">(sudah diisi)</span>' : ''; ?></label>
         <input type="password" id="webhook_secret" name="webhook_secret" class="form-control" placeholder="Kosongkan bila tidak diubah" autocomplete="new-password">
      </div>
      <div class="mb-4">
         <label class="block text-sm font-semibold mb-2" for="webhook_url">URL webhook</label>
         <input type="text" id="webhook_url" class="form-control" value="<?= esc($webhookUrl, 'attr'); ?>" readonly onclick="this.select()">
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
   </form>

   <div class="flex gap-3 mt-4" style="padding-top: 16px; border-top: 1px solid var(--border);">
      <form action="<?= base_url('admin/ppdb-integration/test'); ?>" method="post">
         <button type="submit" class="btn btn-outline">Tes koneksi</button>
      </form>
      <form action="<?= base_url('admin/ppdb-integration/sync'); ?>" method="post">
         <button type="submit" class="btn btn-primary">Sinkron sekarang</button>
      </form>
   </div>
</div>

<div class="premium-filter-card" style="max-width: 850px; margin: 0 auto 32px auto;">
   <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 8px 0;">Status terakhir</h2>
   <p style="margin: 0 0 16px 0;">
      <?php if ($lastAt): ?>
         <?= esc($lastAt); ?> UTC &middot; <strong><?= esc((string) $lastStatus); ?></strong> &middot; <?= esc((string) $lastMsg); ?>
      <?php else: ?>
         Belum pernah sinkron.
      <?php endif; ?>
   </p>

   <div class="table-responsive">
      <table class="table" style="width: 100%;">
         <thead>
            <tr>
               <th>Waktu</th><th>Pemicu</th><th>Status</th><th>Baru</th><th>Diubah</th><th>Nonaktif</th><th>Lewat</th><th>Pesan</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($logs as $log): ?>
               <tr>
                  <td><?= esc($log['created_at']); ?></td>
                  <td><?= esc($log['trigger']); ?></td>
                  <td><?= esc($log['status']); ?></td>
                  <td><?= (int) $log['created']; ?></td>
                  <td><?= (int) $log['updated']; ?></td>
                  <td><?= (int) $log['deactivated']; ?></td>
                  <td><?= (int) $log['skipped']; ?></td>
                  <td><?= esc((string) $log['message']); ?></td>
               </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
               <tr><td colspan="8">Belum ada log.</td></tr>
            <?php endif; ?>
         </tbody>
      </table>
   </div>
</div>

<?= $this->endSection() ?>
