<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="Absensi SMP PGRI 1 Ciputat">
   <meta name="theme-color" content="#2e3192">
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo.png'); ?>">

   <?= $this->include("templates/css") ?>

   <title><?= $title ?? 'Absensi' ?> | SMP PGRI 1 CIPUTAT</title>
</head>

<body>
   <div class="flex min-h-screen flex-col">
      <header class="sticky top-0 z-20 border-b border-slate-200 bg-white">
         <div class="mx-auto flex h-14 w-full max-w-6xl items-center justify-between gap-3 px-4">
            <div class="flex min-w-0 items-center gap-2.5">
               <img src="<?= getLogo(); ?>" alt="Logo sekolah" class="h-8 w-8 shrink-0 object-contain">
               <div class="min-w-0 leading-tight">
                  <p class="text-sm font-semibold text-slate-900">Absensi</p>
                  <p class="truncate text-xs text-slate-500">SMP PGRI 1 Ciputat</p>
               </div>
            </div>

            <div class="flex items-center gap-2">
               <?= $this->renderSection("navaction") ?>
            </div>
         </div>
      </header>

      <main class="flex-1 px-4 py-6">
         <?= $this->renderSection("content") ?>
      </main>
   </div>

   <?= $this->include("templates/js") ?>

   <script>
      var BaseConfig = {
         baseURL: '<?= base_url(); ?>',
         csrfTokenName: '<?= csrf_token() ?>',
         textOk: "Ok",
         textCancel: "Batalkan"
      };
   </script>
</body>

</html>
