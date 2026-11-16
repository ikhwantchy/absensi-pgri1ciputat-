<?php if (session()->has('message')) : ?>
	<div class="alert alert-success mb-4" role="alert">
		<i class="material-icons">check_circle</i>
		<div class="min-w-0 flex-1"><?= session('message') ?></div>
		<button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Tutup"><i class="material-icons">close</i></button>
	</div>
<?php endif ?>

<?php if (session()->has('error')) : ?>
	<div class="alert alert-danger mb-4" role="alert">
		<i class="material-icons">error</i>
		<div class="min-w-0 flex-1"><?= session('error') ?></div>
		<button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Tutup"><i class="material-icons">close</i></button>
	</div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
	<div class="alert alert-danger mb-4" role="alert">
		<i class="material-icons">error</i>
		<div class="min-w-0 flex-1">
			<?php foreach (session('errors') as $error) : ?>
				<div><?= $error ?></div>
			<?php endforeach ?>
		</div>
		<button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Tutup"><i class="material-icons">close</i></button>
	</div>
<?php endif ?>
