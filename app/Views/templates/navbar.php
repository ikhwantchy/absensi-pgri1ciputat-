<header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
   <div class="flex min-w-0 items-center gap-3">
      <button type="button" class="btn btn-ghost btn-icon md:hidden" onclick="toggleSidebar(true)" aria-label="Buka menu">
         <i class="material-icons">menu</i>
      </button>
      <h1 class="truncate text-base font-semibold text-slate-900"><?= esc($title ?? ''); ?></h1>
   </div>

   <div class="flex items-center gap-2 sm:gap-3">
      <a href="<?= base_url('scan'); ?>" class="btn btn-secondary btn-sm" title="Buka halaman scan kartu">
         <i class="material-icons">contactless</i>
         <span class="hidden sm:inline">Buka Scanner</span>
      </a>
      <span class="hidden text-sm text-slate-600 lg:inline"><?= esc(user()->username); ?></span>
      <a href="<?= base_url('/logout'); ?>" class="text-sm font-medium text-danger-600 no-underline hover:underline">Keluar</a>
   </div>
</header>
