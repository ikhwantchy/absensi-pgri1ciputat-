<?= $this->extend('templates/starting_page_layout'); ?>

<?= $this->section('content'); ?>
<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="mb-5 flex flex-col items-center text-center">
            <img src="<?= getLogo(); ?>" alt="Logo sekolah" class="mb-3 h-12 w-12 object-contain">
            <h1 class="text-lg font-semibold text-slate-900">Masuk ke Absensi</h1>
            <p class="text-sm text-slate-500">SMP PGRI 1 Ciputat</p>
        </div>

        <div class="card card-body">
            <?= view('\App\Views\admin\_message_block') ?>

            <form action="<?= url_to('login') ?>" method="post" class="grid gap-y-3">
                <?= csrf_field() ?>

                <!-- Login Field -->
                <div>
                    <label class="label" for="login">
                        <?= $config->validFields === ['email'] ? lang('Auth.email') : lang('Auth.emailOrUsername') ?>
                    </label>
                    <div class="input-icon">
                        <i class="material-icons">person</i>
                        <input
                            type="<?= $config->validFields === ['email'] ? 'email' : 'text' ?>"
                            name="login"
                            id="login"
                            class="input<?= session('errors.login') ? ' is-invalid' : '' ?>"
                            placeholder="Masukkan <?= $config->validFields === ['email'] ? 'email' : 'username' ?>"
                            autofocus
                        >
                    </div>
                    <?php if (session('errors.login')): ?>
                        <p class="invalid-feedback"><?= session('errors.login') ?></p>
                    <?php endif ?>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="label mb-0" for="password">Password</label>
                        <?php if ($config->activeResetter): ?>
                            <a href="<?= url_to('forgot') ?>" class="text-xs font-medium no-underline hover:underline">Lupa password?</a>
                        <?php endif; ?>
                    </div>
                    <div class="input-icon">
                        <i class="material-icons">lock</i>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="input pr-10<?= session('errors.password') ? ' is-invalid' : '' ?>"
                            placeholder="••••••••"
                        >
                        <button type="button" id="togglePassword" class="absolute right-1 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="material-icons" id="passwordIcon">visibility_off</i>
                        </button>
                    </div>
                    <?php if (session('errors.password')): ?>
                        <p class="invalid-feedback"><?= session('errors.password') ?></p>
                    <?php endif ?>
                </div>

                <!-- Remember Me -->
                <?php if ($config->allowRemembering): ?>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            <?php if (old('remember')): ?> checked <?php endif ?>
                        >
                        <label for="remember" class="text-sm text-slate-600">
                            <?= lang('Auth.rememberMe') ?>
                        </label>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary mt-1 w-full">
                    <?= lang('Auth.loginAction') ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementsByName('password')[0];
    const passwordIcon = document.getElementById('passwordIcon');
    if (passwordInput && passwordIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.textContent = 'visibility';
            this.style.color = 'var(--color-primary-700)';
        } else {
            passwordInput.type = 'password';
            passwordIcon.textContent = 'visibility_off';
            this.style.color = '#94a3b8';
        }
    }
});
</script>
<?= $this->endSection(); ?>
