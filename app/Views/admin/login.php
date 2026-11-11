<?= $this->extend('templates/starting_page_layout'); ?>

<?= $this->section('content'); ?>
<div class="flex items-center justify-center" style="min-height: calc(100vh - 144px); padding: 40px 20px; display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 960px; margin: 0 auto;">
        
        <!-- Login Wrapper Card -->
        <div class="card login-card-container" style="border-radius: 24px; background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%); border: 1px solid #e2e8f0; box-shadow: 0 20px 50px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: row; min-height: 550px; margin-bottom: 0 !important;">
            
            <!-- Left Side: School Photo (Hidden on Mobile) -->
            <div class="login-school-banner" style="flex: 1.2; position: relative; background: url('<?= base_url('assets/img/school_building.jpg?v=' . time()); ?>') center center / cover no-repeat;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(124, 58, 237, 0.2), rgba(15, 23, 42, 0.85)); display: flex; flex-direction: column; justify-content: flex-end; padding: 40px; color: #ffffff;">
                    <!-- Logo / Icon -->
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; margin-bottom: 20px; border: 1px solid rgba(255, 255, 255, 0.25);">
                        <i class="material-icons" style="font-size: 24px; color: #ffffff;">school</i>
                    </div>
                    <h3 style="font-size: 24px; font-weight: 800; margin: 0 0 8px 0; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3); letter-spacing: -0.5px;">SMP PGRI 1 CIPUTAT</h3>
                    <p style="font-size: 13px; color: rgba(255, 255, 255, 0.85); margin: 0; line-height: 1.5; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        Sistem Informasi Manajemen & Kehadiran Siswa Berbasis Kode QR, RFID, dan WhatsApp Gateway.
                    </p>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="login-form-side" style="flex: 1; padding: 48px 40px; display: flex; flex-direction: column; justify-content: center;">
                <div style="margin-bottom: 28px;">
                    <div class="login-icon-mobile" style="width: 48px; height: 48px; border-radius: 14px; background: #f5f3ff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                        <i class="material-icons text-primary" style="font-size: 24px;">admin_panel_settings</i>
                    </div>
                    <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.5px;">Selamat Datang Kembali</h2>
                    <p style="font-size: 14px; color: #64748b; margin: 0;">Silakan login untuk mengelola sistem absensi</p>
                </div>

                <?= view('\App\Views\admin\_message_block') ?>
                
                <form action="<?= url_to('login') ?>" method="post" style="display: flex; flex-direction: column; gap: 20px;">
                    <?= csrf_field() ?>
                    
                    <!-- Login Field -->
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                            <?= $config->validFields === ['email'] ? lang('Auth.email') : lang('Auth.emailOrUsername') ?>
                        </label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <i class="material-icons text-primary" style="position: absolute; left: 16px; font-size: 20px; pointer-events: none;">person</i>
                            <input 
                                type="<?= $config->validFields === ['email'] ? 'email' : 'text' ?>" 
                                name="login" 
                                class="form-control" 
                                style="width: 100%; height: 46px; padding-left: 48px !important; padding-right: 16px !important; font-size: 14px; border-radius: 10px; background: #ffffff; border: 1px solid <?php echo session('errors.login') ? '#ef4444' : '#cbd5e1'; ?>; color: #0f172a; box-shadow: none; transition: all 0.2s;" 
                                placeholder="Masukkan <?= $config->validFields === ['email'] ? 'email' : 'username' ?>"
                                autofocus
                            >
                        </div>
                        <?php if (session('errors.login')): ?>
                            <p style="margin-top: 6px; font-size: 12px; color: #ef4444; font-weight: 500;"><?= session('errors.login') ?></p>
                        <?php endif ?>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <label style="font-size: 13px; font-weight: 600; color: #475569; margin: 0;">Password</label>
                            <?php if ($config->activeResetter): ?>
                                <a href="<?= url_to('forgot') ?>" style="font-size: 12px; font-weight: 600; color: var(--primary); text-decoration: none;">Lupa password?</a>
                            <?php endif; ?>
                        </div>
                        <div style="position: relative; display: flex; align-items: center; width: 100%;">
                            <i class="material-icons text-primary" style="position: absolute; left: 16px; font-size: 20px; pointer-events: none;">lock</i>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control" 
                                style="width: 100%; height: 46px; padding-left: 48px !important; padding-right: 48px !important; font-size: 14px; border-radius: 10px; background: #ffffff; border: 1px solid <?php echo session('errors.password') ? '#ef4444' : '#cbd5e1'; ?>; color: #0f172a; box-shadow: none; transition: all 0.2s;" 
                                placeholder="••••••••"
                            >
                            <button type="button" id="togglePassword" style="position: absolute; right: 16px; background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; color: #94a3b8; outline: none; transition: color 0.2s;">
                                <i class="material-icons" id="passwordIcon" style="font-size: 20px;">visibility_off</i>
                            </button>
                        </div>
                        <?php if (session('errors.password')): ?>
                            <p style="margin-top: 6px; font-size: 12px; color: #ef4444; font-weight: 500;"><?= session('errors.password') ?></p>
                        <?php endif ?>
                    </div>

                    <!-- Remember Me -->
                    <?php if ($config->allowRemembering): ?>
                        <div style="display: flex; align-items: center; margin-top: 2px;">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember"
                                style="width: 16px; height: 16px; cursor: pointer; border-radius: 4px; border: 1.5px solid #cbd5e1; accent-color: var(--primary);"
                                <?php if (old('remember')): ?> checked <?php endif ?>
                            >
                            <label for="remember" style="margin-left: 8px; font-size: 13px; color: #64748b; font-weight: 500; cursor: pointer; margin-bottom: 0;">
                                <?= lang('Auth.rememberMe') ?>
                            </label>
                        </div>
                    <?php endif; ?>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; border-radius: 10px; font-weight: 700; font-size: 15px; margin-top: 8px; background: var(--primary); border: none; box-shadow: 0 8px 20px rgba(124,58,237,0.2); display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: all 0.2s;">
                        <span><?= lang('Auth.loginAction') ?></span>
                        <i class="material-icons" style="font-size: 18px;">arrow_forward</i>
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<style>
/* Responsive Styling */
@media (max-width: 768px) {
    .login-card-container {
        flex-direction: column !important;
        max-width: 420px !important;
        margin: 0 auto !important;
    }
    .login-school-banner {
        display: none !important;
    }
    .login-form-side {
        padding: 36px 28px !important;
    }
}
@media (min-width: 769px) {
    .login-icon-mobile {
        display: none !important;
    }
}
</style>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementsByName('password')[0];
    const passwordIcon = document.getElementById('passwordIcon');
    if (passwordInput && passwordIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.textContent = 'visibility';
            this.style.color = 'var(--primary)';
        } else {
            passwordInput.type = 'password';
            passwordIcon.textContent = 'visibility_off';
            this.style.color = '#94a3b8';
        }
    }
});
</script>
<?= $this->endSection(); ?>