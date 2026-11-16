<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="robots" content="noindex, nofollow">
   <meta name="theme-color" content="#2e3192">
   <?= csrf_meta(); ?>

   <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo.png'); ?>">

   <?= $this->include('templates/css'); ?>

   <title><?= esc($title ?? 'Absensi') ?> · Absensi SMP PGRI 1 Ciputat</title>
</head>
