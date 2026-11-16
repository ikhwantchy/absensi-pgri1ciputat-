<?php
$context = $ctx ?? 'dashboard';

if (is_wali_kelas()) {
   $menuGroups = [
      '' => [
         ['title' => 'Dashboard',           'url' => 'teacher/dashboard',  'icon' => 'space_dashboard', 'context' => 'dashboard',     'visible' => true],
         ['title' => 'Kehadiran Kelas',     'url' => 'teacher/attendance', 'icon' => 'fact_check',      'context' => 'attendance',    'visible' => true],
         ['title' => 'Laporan Kelas',       'url' => 'teacher/laporan',    'icon' => 'print',           'context' => 'laporan-kelas', 'visible' => true],
         ['title' => 'QR Code Siswa',       'url' => 'teacher/qr',         'icon' => 'qr_code_2',       'context' => 'qr',            'visible' => true],
      ],
   ];
} else {
   $menuGroups = [
      '' => [
         ['title' => 'Dashboard',        'url' => 'admin/dashboard',        'icon' => 'space_dashboard', 'context' => 'dashboard',        'visible' => true],
      ],
      'Absensi' => [
         ['title' => 'Absensi Siswa',    'url' => 'admin/absen-siswa',      'icon' => 'fact_check',      'context' => 'absen-siswa',      'visible' => true],
         ['title' => 'Absensi Guru',     'url' => 'admin/absen-guru',       'icon' => 'how_to_reg',      'context' => 'absen-guru',       'visible' => true],
         ['title' => 'Laporan',          'url' => 'admin/laporan',          'icon' => 'print',           'context' => 'laporan',          'visible' => can_view_report()],
         ['title' => 'QR Code',          'url' => 'admin/generate',         'icon' => 'qr_code_2',       'context' => 'qr',               'visible' => can_generate_qr()],
      ],
      'Data' => [
         ['title' => 'Siswa',            'url' => 'admin/siswa',            'icon' => 'groups',          'context' => 'siswa',            'visible' => is_superadmin()],
         ['title' => 'Guru',             'url' => 'admin/guru',             'icon' => 'badge',           'context' => 'guru',             'visible' => is_superadmin()],
         ['title' => 'Kelas',            'url' => 'admin/kelas',            'icon' => 'meeting_room',    'context' => 'kelas',            'visible' => is_superadmin()],
         ['title' => 'Petugas',          'url' => 'admin/petugas',          'icon' => 'manage_accounts', 'context' => 'petugas',          'visible' => is_superadmin()],
      ],
      'Sistem' => [
         ['title' => 'Integrasi PPDB',   'url' => 'admin/ppdb-integration', 'icon' => 'sync_alt',        'context' => 'ppdb-integration', 'visible' => is_superadmin()],
         ['title' => 'WhatsApp',         'url' => 'admin/whatsapp',         'icon' => 'chat',            'context' => 'whatsapp',         'visible' => is_superadmin()],
         ['title' => 'Pengaturan',       'url' => 'admin/general-settings', 'icon' => 'settings',        'context' => 'general_settings', 'visible' => is_superadmin() || is_kepsek()],
         ['title' => 'Backup & Restore', 'url' => 'admin/backup',           'icon' => 'backup',          'context' => 'backup',           'visible' => is_superadmin()],
      ],
   ];
}
?>

<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden" onclick="toggleSidebar(false)"></div>

<aside id="mainSidebar" class="fixed inset-y-0 left-0 z-40 flex w-60 shrink-0 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform md:sticky md:top-0 md:h-screen md:translate-x-0">
   <a href="<?= base_url(is_wali_kelas() ? 'teacher/dashboard' : 'admin/dashboard'); ?>" class="flex items-center gap-2 px-4 py-4 no-underline">
      <img src="<?= getLogo(); ?>" alt="Logo" class="h-9 w-9 object-contain">
      <div class="text-sm font-bold leading-tight text-primary-700">Absensi<br><span class="font-semibold text-slate-700">SMP PGRI 1 Ciputat</span></div>
   </a>

   <div class="px-3 pb-2">
      <div class="input-icon">
         <i class="material-icons">search</i>
         <input type="search" id="sidebarSearch" class="input h-9" placeholder="Cari menu" aria-label="Cari menu">
      </div>
   </div>

   <nav id="sidebarNav" class="flex-1 space-y-0.5 overflow-y-auto px-2 pb-4 text-sm">
      <?php foreach ($menuGroups as $group => $items):
         $items = array_filter($items, fn ($i) => $i['visible']);
         if (! $items) continue;
      ?>
         <div class="sidebar-group space-y-0.5">
            <?php if ($group !== ''): ?>
               <div class="px-3 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400"><?= $group; ?></div>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
               <a class="nav-item <?= $context == $item['context'] ? 'active' : '' ?>" href="<?= base_url($item['url']); ?>">
                  <i class="material-icons"><?= $item['icon']; ?></i>
                  <span><?= $item['title']; ?></span>
               </a>
            <?php endforeach; ?>
         </div>
      <?php endforeach; ?>
   </nav>

   <div class="flex items-center gap-2.5 border-t border-slate-100 px-4 py-3">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700"><?= esc(strtoupper(substr(user()->username, 0, 1))); ?></span>
      <div class="min-w-0 leading-5">
         <div class="truncate text-sm font-medium text-slate-800"><?= esc(user()->username); ?></div>
         <div class="truncate text-xs text-slate-500"><?= esc(user_role()->label()); ?></div>
      </div>
   </div>
</aside>

<script>
   function toggleSidebar(open) {
      document.getElementById('mainSidebar').classList.toggle('-translate-x-full', !open);
      document.getElementById('sidebarOverlay').classList.toggle('hidden', !open);
   }

   document.getElementById('sidebarSearch')?.addEventListener('input', function() {
      var q = this.value.toLowerCase();
      document.querySelectorAll('#sidebarNav .sidebar-group').forEach(function(group) {
         var any = false;
         group.querySelectorAll('.nav-item').forEach(function(link) {
            var match = link.textContent.toLowerCase().includes(q);
            link.style.display = match ? '' : 'none';
            any = any || match;
         });
         group.style.display = any ? '' : 'none';
      });
   });
</script>
