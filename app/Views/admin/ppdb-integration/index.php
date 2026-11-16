<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php
$statusView = static function ($status): string {
   $status = (string) $status;
   [$cls, $icon, $label] = match ($status) {
      'ok' => ['status-success', 'check_circle', 'Berhasil'],
      'failed' => ['status-danger', 'cancel', 'Gagal'],
      '' => ['status-muted', 'schedule', '–'],
      default => ['status-warning', 'error', $status],
   };

   return '<span class="status ' . $cls . '"><i class="material-icons">' . $icon . '</i>' . esc($label) . '</span>';
};
?>
<div class="mx-auto max-w-5xl space-y-4">
   <?= view('admin/_messages'); ?>

   <?php if ($tokenBad || $secretBad): ?>
      <div class="alert alert-danger">
         <i class="material-icons">error</i>
         <span><?= $tokenBad ? 'Token tersimpan tidak bisa dibaca. ' : '' ?><?= $secretBad ? 'Secret webhook tersimpan tidak bisa dibaca. ' : '' ?>Isi ulang lalu simpan.</span>
      </div>
   <?php endif; ?>

   <div class="grid gap-4 lg:grid-cols-5">
      <section class="card lg:col-span-3">
         <div class="card-head">
            <h2 class="card-title">Koneksi</h2>
         </div>
         <form action="<?= base_url('admin/ppdb-integration/save'); ?>" method="post" autocomplete="off" class="card-body grid gap-y-2.5">
            <div>
               <label class="label" for="base_url">URL PPDB</label>
               <input type="url" id="base_url" name="base_url" class="input" placeholder="https://ppdb.example.sch.id" value="<?= esc($baseUrl, 'attr'); ?>" required>
            </div>
            <div>
               <label class="label inline-flex items-center gap-1" for="token">
                  Token API
                  <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Token dibuat di PPDB. Kosongkan bila tidak diubah. Token terhapus otomatis bila URL PPDB diganti.</span></span>
               </label>
               <input type="password" id="token" name="token" class="input" placeholder="<?= $hasToken ? 'Tersimpan · kosongkan bila tidak diubah' : 'Belum diisi'; ?>" autocomplete="new-password">
            </div>
            <div>
               <label class="label inline-flex items-center gap-1" for="webhook_secret">
                  Secret webhook
                  <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Dipakai memverifikasi tanda tangan HMAC dari PPDB. Harus sama dengan secret di PPDB. Kosongkan bila tidak diubah.</span></span>
               </label>
               <input type="password" id="webhook_secret" name="webhook_secret" class="input" placeholder="<?= $hasSecret ? 'Tersimpan · kosongkan bila tidak diubah' : 'Belum diisi'; ?>" autocomplete="new-password">
            </div>
            <div class="flex justify-end pt-1">
               <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
         </form>
      </section>

      <section class="card lg:col-span-2">
         <div class="card-head">
            <h2 class="card-title">Status</h2>
         </div>
         <div class="card-body space-y-2.5">
            <dl class="grid grid-cols-[7rem_1fr] gap-x-3 gap-y-2.5">
               <dt class="text-slate-500">Token API</dt>
               <dd><?= $hasToken ? '<span class="status status-success"><i class="material-icons">check_circle</i>Tersimpan</span>' : '<span class="status status-muted"><i class="material-icons">remove_circle_outline</i>Belum diisi</span>'; ?></dd>
               <dt class="text-slate-500">Secret webhook</dt>
               <dd><?= $hasSecret ? '<span class="status status-success"><i class="material-icons">check_circle</i>Tersimpan</span>' : '<span class="status status-muted"><i class="material-icons">remove_circle_outline</i>Belum diisi</span>'; ?></dd>
               <dt class="text-slate-500">Sinkron terakhir</dt>
               <dd>
                  <?php if ($lastAt): ?>
                     <div class="flex flex-wrap items-center gap-x-2"><?= $statusView($lastStatus); ?><span class="tabular-nums text-slate-600"><?= esc($lastAt); ?> WIB</span></div>
                     <?php if ((string) $lastMsg !== ''): ?><div class="text-xs text-slate-500"><?= esc((string) $lastMsg); ?></div><?php endif; ?>
                  <?php else: ?>
                     <span class="text-slate-500">Belum pernah sinkron.</span>
                  <?php endif; ?>
               </dd>
               <?php if (! empty($syncedUntil)): ?>
                  <dt class="inline-flex items-center gap-1 text-slate-500">Titik sinkron <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Sinkron berikutnya hanya mengambil data PPDB yang berubah setelah waktu ini.</span></span></dt>
                  <dd class="tabular-nums text-slate-600"><?= esc($syncedUntil); ?> UTC</dd>
               <?php endif; ?>
            </dl>
            <div>
               <label class="label inline-flex items-center gap-1" for="webhook_url">
                  URL webhook
                  <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Tempel URL ini di pengaturan webhook PPDB.</span></span>
               </label>
               <input type="text" id="webhook_url" class="input" value="<?= esc($webhookUrl, 'attr'); ?>" readonly onclick="this.select()">
            </div>
         </div>
         <div class="card-foot">
            <form action="<?= base_url('admin/ppdb-integration/test'); ?>" method="post">
               <button type="submit" class="btn btn-secondary">Tes koneksi</button>
            </form>
            <form action="<?= base_url('admin/ppdb-integration/sync'); ?>" method="post">
               <button type="submit" class="btn btn-primary"><i class="material-icons">sync</i>Sinkron sekarang</button>
            </form>
         </div>
      </section>
   </div>

   <section class="card overflow-hidden">
      <div class="card-head">
         <h2 class="card-title">Riwayat sinkron</h2>
      </div>
      <div class="table-wrap">
         <table class="table">
            <thead>
               <tr>
                  <th>Waktu</th>
                  <th>Pemicu</th>
                  <th>Status</th>
                  <th class="text-right">Baru</th>
                  <th class="text-right">Diubah</th>
                  <th class="text-right">Nonaktif</th>
                  <th class="text-right">Lewat</th>
                  <th>Pesan</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($logs as $log): ?>
                  <tr>
                     <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($log['created_at']); ?></td>
                     <td><?= esc($log['trigger']); ?></td>
                     <td><?= $statusView($log['status']); ?></td>
                     <td class="text-right tabular-nums"><?= (int) $log['created']; ?></td>
                     <td class="text-right tabular-nums"><?= (int) $log['updated']; ?></td>
                     <td class="text-right tabular-nums"><?= (int) $log['deactivated']; ?></td>
                     <td class="text-right tabular-nums"><?= (int) $log['skipped']; ?></td>
                     <td class="min-w-[16rem] text-slate-600"><?= esc((string) $log['message']); ?></td>
                  </tr>
               <?php endforeach; ?>
               <?php if (empty($logs)): ?>
                  <tr><td colspan="8" class="empty">Belum ada riwayat sinkron.</td></tr>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </section>
</div>
<?= $this->endSection() ?>
