<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
    <?php if (isset($no_class)): ?>
        <section class="card">
            <div class="card-body flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="material-icons">meeting_room</i></span>
                <div>
                    <p class="font-semibold text-slate-900">Anda belum ditugaskan sebagai wali kelas.</p>
                    <p class="text-slate-600">Hubungi administrator untuk penugasan kelas.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700"><i class="material-icons">meeting_room</i></span>
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">Kelas Anda</span>
                    <span class="block truncate text-xl font-semibold leading-7 text-slate-900"><?= $kelas['tingkat'] . ' ' . $kelas['index_kelas']; ?></span>
                </span>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700"><i class="material-icons">groups</i></span>
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">Siswa</span>
                    <span class="block text-xl font-semibold leading-7 tabular-nums text-slate-900"><?= $summary['total_siswa']; ?></span>
                </span>
            </div>
            <a href="<?= base_url('teacher/qr'); ?>" class="card flex items-center gap-3 p-4 text-slate-800 no-underline transition hover:ring-1 hover:ring-primary-200">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700"><i class="material-icons">qr_code_2</i></span>
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">QR siswa</span>
                    <span class="block text-sm font-semibold leading-7 text-slate-900">Unduh</span>
                </span>
            </a>
            <a href="<?= base_url('teacher/laporan'); ?>" class="card flex items-center gap-3 p-4 text-slate-800 no-underline transition hover:ring-1 hover:ring-primary-200">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700"><i class="material-icons">assessment</i></span>
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">Laporan</span>
                    <span class="block text-sm font-semibold leading-7 text-slate-900">Unduh</span>
                </span>
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            <section class="card lg:col-span-2 self-start">
                <div class="card-head">
                    <div>
                        <h2 class="card-title">Absensi kelas hari ini</h2>
                        <p class="card-sub"><?= date('d F Y'); ?></p>
                    </div>
                </div>
                <div class="card-body">
                    <?= view('admin/_dashboard_siswa_stats', [
                        'hadir' => $summary['hadir_hari_ini'],
                        'sakit' => $summary['sakit_hari_ini'],
                        'izin' => $summary['izin_hari_ini'],
                        'alfa' => $summary['alfa_hari_ini'],
                        'totalSiswa' => $summary['total_siswa']
                    ]) ?>
                </div>
            </section>

            <section class="card lg:col-span-3">
                <div class="card-head">
                    <div>
                        <h2 class="card-title">Tren kehadiran kelas</h2>
                        <p class="card-sub">7 hari terakhir</p>
                    </div>
                    <a class="text-xs font-semibold no-underline hover:underline" href="<?= base_url('teacher/laporan'); ?>">Unduh laporan</a>
                </div>
                <div class="card-body">
                    <div class="relative h-64 w-full">
                        <canvas id="kehadiranSiswaKelas"></canvas>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!isset($no_class)): ?>
    <!-- Chart.js -->
    <script src="<?= base_url('assets/js/plugins/chartjs/chart.umd.min.js') ?>"></script>
    <script>
        const chartLabels = <?= json_encode($dateRange) ?>;

        const chartColors = {
            hadir: { border: '#16a34a', bg: '#16a34a' },
            sakit: { border: '#f59e0b', bg: '#f59e0b' },
            izin: { border: '#0ea5e9', bg: '#0ea5e9' },
            alfa: { border: '#ef4444', bg: '#ef4444' }
        };

        function initTeacherCharts() {
            const ctx = document.getElementById('kehadiranSiswaKelas');
            if (ctx) {
                const data = {
                    hadir: <?= json_encode($grafikKehadiran['hadir']) ?>,
                    sakit: <?= json_encode($grafikKehadiran['sakit']) ?>,
                    izin: <?= json_encode($grafikKehadiran['izin']) ?>,
                    alfa: <?= json_encode($grafikKehadiran['alfa']) ?>
                };

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Hadir',
                                data: data.hadir,
                                borderColor: chartColors.hadir.border,
                                backgroundColor: chartColors.hadir.bg
                            },
                            {
                                label: 'Sakit',
                                data: data.sakit,
                                borderColor: chartColors.sakit.border,
                                backgroundColor: chartColors.sakit.bg
                            },
                            {
                                label: 'Izin',
                                data: data.izin,
                                borderColor: chartColors.izin.border,
                                backgroundColor: chartColors.izin.bg
                            },
                            {
                                label: 'Alfa',
                                data: data.alfa,
                                borderColor: chartColors.alfa.border,
                                backgroundColor: chartColors.alfa.bg
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: '#0f172a',
                                titleFont: { size: 14 },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' siswa';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function (value) {
                                        if (Number.isInteger(value)) return value;
                                    }
                                },
                                grid: { color: 'rgba(0, 0, 0, 0.05)' }
                            },
                            x: {
                                stacked: false,
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        }

        $(document).ready(function () {
            initTeacherCharts();
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>