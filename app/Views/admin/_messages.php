<?php $session = session();

if ($session->getFlashdata('error')) : ?>
    <div class="alert alert-danger mb-4" role="alert">
        <i class="material-icons">error</i>
        <div class="min-w-0 flex-1"><?= $session->getFlashdata('error'); ?></div>
        <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Tutup"><i class="material-icons">close</i></button>
    </div>
<?php elseif ($session->getFlashdata('success')) : ?>
    <div class="alert alert-success mb-4" role="alert">
        <i class="material-icons">check_circle</i>
        <div class="min-w-0 flex-1"><?= $session->getFlashdata('success'); ?></div>
        <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Tutup"><i class="material-icons">close</i></button>
    </div>
<?php endif; ?>
