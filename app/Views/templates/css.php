<?php
$assetVersion = '3.0.0';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&display=block" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . $assetVersion); ?>" />

<?= $this->renderSection("styles") ?>
