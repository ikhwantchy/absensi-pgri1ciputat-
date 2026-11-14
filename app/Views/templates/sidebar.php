<?php
$context = $ctx ?? 'dashboard';

if (is_wali_kelas()) {
   $menuItems = [
      ['title' => 'Dashboard',         'url' => 'teacher/dashboard',  'icon' => 'dashboard',    'context' => 'dashboard',   'visible' => true],
      ['title' => 'Laporan Kelas',      'url' => 'teacher/laporan',    'icon' => 'print',        'context' => 'laporan-kelas','visible' => true],
      ['title' => 'QR Code Siswa',      'url' => 'teacher/qr',         'icon' => 'qr_code',      'context' => 'qr',          'visible' => true],
      ['title' => 'Manajemen Kehadiran','url' => 'teacher/attendance',  'icon' => 'event_note',   'context' => 'attendance',  'visible' => true],
   ];
} else {
   $menuItems = [
      ['title' => 'Dashboard',         'url' => 'admin/dashboard',         'icon' => 'dashboard',    'context' => 'dashboard',       'visible' => true],
      ['title' => 'Absensi Siswa',     'url' => 'admin/absen-siswa',       'icon' => 'fact_check',   'context' => 'absen-siswa',     'visible' => true],
      ['title' => 'Absensi Guru',      'url' => 'admin/absen-guru',        'icon' => 'how_to_reg',   'context' => 'absen-guru',      'visible' => true],
      ['title' => 'Data Siswa',        'url' => 'admin/siswa',             'icon' => 'school',       'context' => 'siswa',           'visible' => is_superadmin()],
      ['title' => 'Data Guru',         'url' => 'admin/guru',              'icon' => 'person',       'context' => 'guru',            'visible' => is_superadmin()],
      ['title' => 'Data Kelas',        'url' => 'admin/kelas',             'icon' => 'class',        'context' => 'kelas',           'visible' => is_superadmin()],
      ['title' => 'Generate QR Code',  'url' => 'admin/generate',          'icon' => 'qr_code_2',    'context' => 'qr',              'visible' => can_generate_qr()],
      ['title' => 'Generate Laporan',  'url' => 'admin/laporan',           'icon' => 'bar_chart',    'context' => 'laporan',         'visible' => can_view_report()],
      ['title' => 'Data Petugas',      'url' => 'admin/petugas',           'icon' => 'manage_accounts','context' => 'petugas',        'visible' => is_superadmin()],
      ['title' => 'Pengaturan',        'url' => 'admin/general-settings',  'icon' => 'settings',     'context' => 'general_settings','visible' => is_superadmin() || is_kepsek()],
      ['title' => 'Backup & Restore',  'url' => 'admin/backup',            'icon' => 'backup',       'context' => 'backup',          'visible' => is_superadmin()],
      ['title' => 'WhatsApp Gateway',  'url' => 'admin/whatsapp',          'icon' => 'settings_input_antenna', 'context' => 'whatsapp', 'visible' => is_superadmin()],
      ['title' => 'Integrasi PPDB',    'url' => 'admin/ppdb-integration',  'icon' => 'sync_alt',     'context' => 'ppdb-integration', 'visible' => is_superadmin()],
   ];
}

$userName  = user()->username;
$userRole  = user_role()->label();
$userInitial = strtoupper(substr($userName, 0, 1));
?>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('mainSidebar').classList.remove('show'); this.classList.remove('show');"></div>
<div class="sidebar" id="mainSidebar">

   <!-- Logo -->
   <div class="sidebar-logo">
      <div class="sidebar-logo-icon">
         <i class="material-icons">verified</i>
      </div>
      <div class="sidebar-logo-text">
         <div class="sidebar-logo-name">SMP PGRI 1 CIPUTAT</div>
         <div class="sidebar-logo-sub">Management System</div>
      </div>
   </div>

   <!-- Search -->
   <div class="sidebar-search">
      <div class="sidebar-search-inner">
         <i class="material-icons">search</i>
         <input type="text" placeholder="Search..." id="sidebarSearch">
      </div>
   </div>

   <!-- Nav -->
   <nav class="sidebar-nav" id="sidebarNav">
      <div class="sidebar-section-label">General</div>

      <?php foreach ($menuItems as $item):
         if (!$item['visible']) continue;
         $isActive = $context == $item['context'];
      ?>
         <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= base_url($item['url']); ?>">
            <i class="material-icons"><?= $item['icon']; ?></i>
            <span><?= $item['title']; ?></span>
         </a>
      <?php endforeach; ?>
   </nav>

   <!-- User Footer -->
   <div class="sidebar-footer">
      <div class="sidebar-avatar"><?= $userInitial; ?></div>
      <div style="flex:1; overflow:hidden;">
         <div class="sidebar-user-name"><?= $userName; ?></div>
         <div class="sidebar-user-role"><?= $userRole; ?></div>
      </div>
      <a href="<?= base_url('/logout'); ?>" title="Logout" style="color: rgba(255,255,255,0.4); transition: color .15s; display:flex;">
         <i class="material-icons" style="font-size:18px;">logout</i>
      </a>
   </div>
</div>

<script>
// Live sidebar search filter
document.getElementById('sidebarSearch')?.addEventListener('input', function() {
   var q = this.value.toLowerCase();
   document.querySelectorAll('#sidebarNav .nav-link').forEach(function(link) {
      var text = link.textContent.toLowerCase();
      link.style.display = text.includes(q) ? '' : 'none';
   });
});
</script>