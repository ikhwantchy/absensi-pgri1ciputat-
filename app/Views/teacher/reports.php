<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl space-y-4">
    <section class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title">Laporan kelas <?= $kelas['kelas']; ?></h2>
                <p class="card-sub">Rekap kehadiran bulanan</p>
            </div>
        </div>
        <form action="<?= base_url('teacher/laporan/generate'); ?>" method="post">
            <?= csrf_field() ?>
            <div class="card-body grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="bulan" class="label">Bulan</label>
                    <input type="month" id="bulan" name="bulan" class="input"
                        value="<?= date('Y-m'); ?>" required>
                </div>
                <div>
                    <label for="type" class="label">Format</label>
                    <select class="input" name="type" id="type">
                        <option value="pdf">PDF</option>
                        <option value="doc">Word (.doc)</option>
                    </select>
                </div>
            </div>
            <div class="card-foot">
                <button type="submit" class="btn btn-primary"><i class="material-icons">download</i>Unduh laporan</button>
            </div>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
