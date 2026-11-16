<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php
$tiles = [
   ['Siswa', count($siswa), 'groups', 'admin/siswa'],
   ['Guru', count($guru), 'badge', 'admin/guru'],
   ['Kelas', count($kelas), 'meeting_room', 'admin/kelas'],
   ['Petugas', count($petugas), 'manage_accounts', 'admin/petugas'],
];
?>
<div class="space-y-4">
   <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
      <?php foreach ($tiles as [$label, $count, $icon, $url]): ?>
         <a href="<?= base_url($url); ?>" class="card flex items-center gap-3 p-4 text-slate-800 no-underline transition hover:ring-1 hover:ring-primary-200">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700"><i class="material-icons"><?= $icon; ?></i></span>
            <span class="min-w-0">
               <span class="block text-xs font-medium text-slate-500"><?= $label; ?></span>
               <span class="block text-xl font-semibold leading-7 tabular-nums text-slate-900"><?= $count; ?></span>
            </span>
         </a>
      <?php endforeach; ?>
   </div>

   <div class="grid gap-4 lg:grid-cols-2">
      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title" id="titleSiswaStats">Absensi siswa hari ini</h2>
               <p class="card-sub"><?= $dateNow; ?></p>
            </div>
            <div class="flex w-full items-center gap-2 sm:w-56">
               <div id="filterLoader" style="display: none;">
                  <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary-200 border-t-primary-700"></div>
               </div>
               <select name="id_kelas" id="filterKelas" class="input" aria-label="Filter kelas">
                  <option value="">Semua kelas (<?= count($siswa) ?>)</option>
                  <?php foreach ($kelas as $k): ?>
                     <option value="<?= $k['id_kelas'] ?>" data-kelas="<?= $k['kelas'] ?>"><?= $k['kelas'] ?> (<?= $k['jumlah_siswa'] ?? 0 ?>)</option>
                  <?php endforeach; ?>
               </select>
            </div>
         </div>
         <div class="card-body" id="siswaStatsContainer">
            <?= view('admin/_dashboard_siswa_stats', [
               'hadir' => $jumlahKehadiranSiswa['hadir'],
               'sakit' => $jumlahKehadiranSiswa['sakit'],
               'izin' => $jumlahKehadiranSiswa['izin'],
               'alfa' => $jumlahKehadiranSiswa['alfa'],
               'totalSiswa' => $totalSiswa
            ]) ?>
         </div>
      </section>

      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title">Absensi guru hari ini</h2>
               <p class="card-sub"><?= $dateNow; ?></p>
            </div>
         </div>
         <div class="card-body">
            <?= view('admin/_dashboard_siswa_stats', [
               'hadir' => $jumlahKehadiranGuru['hadir'],
               'sakit' => $jumlahKehadiranGuru['sakit'],
               'izin' => $jumlahKehadiranGuru['izin'],
               'alfa' => $jumlahKehadiranGuru['alfa'],
               'totalSiswa' => $totalGuru
            ]) ?>
         </div>
      </section>
   </div>

   <div class="grid gap-4 lg:grid-cols-2">
      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title" id="titleSiswaChart">Tren kehadiran siswa</h2>
               <p class="card-sub">7 hari terakhir</p>
            </div>
            <a class="text-xs font-semibold no-underline hover:underline" href="<?= base_url('admin/absen-siswa'); ?>">Lihat rekap</a>
         </div>
         <div class="card-body">
            <div class="relative h-64 w-full">
               <canvas id="kehadiranSiswa"></canvas>
            </div>
         </div>
      </section>

      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title">Tren kehadiran guru</h2>
               <p class="card-sub">7 hari terakhir</p>
            </div>
            <a class="text-xs font-semibold no-underline hover:underline" href="<?= base_url('admin/absen-guru'); ?>">Lihat rekap</a>
         </div>
         <div class="card-body">
            <div class="relative h-64 w-full">
               <canvas id="kehadiranGuru"></canvas>
            </div>
         </div>
      </section>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/plugins/chartjs/chart.umd.min.js') ?>"></script>
<script>
    let kehadiranSiswaChart;
    let kehadiranGuruChart;

    const chartLabels = <?= json_encode($dateRange) ?>;
    const chartFont = "'Plus Jakarta Sans', sans-serif";

    const chartColors = {
        hadir: '#16a34a',
        sakit: '#f59e0b',
        izin: '#0ea5e9',
        alfa: '#ef4444'
    };

    function dataset(label, data, color) {
        return {
            label: label,
            data: data,
            backgroundColor: color,
            borderRadius: 3,
            barPercentage: 0.6,
            categoryPercentage: 0.8
        };
    }

    function createChartConfig(data) {
        return {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    dataset('Hadir', data.hadir, chartColors.hadir),
                    dataset('Sakit', data.sakit, chartColors.sakit),
                    dataset('Izin', data.izin, chartColors.izin),
                    dataset('Alfa', data.alfa, chartColors.alfa)
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
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 16,
                            font: { family: chartFont, size: 12 }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#0f172a',
                        titleFont: { family: chartFont, size: 12, weight: '600' },
                        bodyFont: { family: chartFont, size: 12 },
                        padding: 8,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' orang';
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
                            },
                            font: { family: chartFont, size: 11 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: {
                        stacked: false,
                        grid: { display: false },
                        ticks: { font: { family: chartFont, size: 11 } }
                    }
                }
            }
        };
    }

    function updateSiswaChart(newData) {
        if (kehadiranSiswaChart) {
            kehadiranSiswaChart.data.datasets[0].data = newData.hadir;
            kehadiranSiswaChart.data.datasets[1].data = newData.sakit;
            kehadiranSiswaChart.data.datasets[2].data = newData.izin;
            kehadiranSiswaChart.data.datasets[3].data = newData.alfa;
            kehadiranSiswaChart.update('active');
        }
    }

    function initDashboardPageCharts() {
        const siswaCtx = document.getElementById('kehadiranSiswa');
        if (siswaCtx) {
            const dataSiswa = {
                hadir: <?= json_encode($grafikKehadiranSiswa['hadir']) ?>,
                sakit: <?= json_encode($grafikKehadiranSiswa['sakit']) ?>,
                izin: <?= json_encode($grafikKehadiranSiswa['izin']) ?>,
                alfa: <?= json_encode($grafikKehadiranSiswa['alfa']) ?>
            };
            kehadiranSiswaChart = new Chart(siswaCtx, createChartConfig(dataSiswa));
        }

        const guruCtx = document.getElementById('kehadiranGuru');
        if (guruCtx) {
            const dataGuru = {
                hadir: <?= json_encode($grafikKehadiranGuru['hadir']) ?>,
                sakit: <?= json_encode($grafikKehadiranGuru['sakit']) ?>,
                izin: <?= json_encode($grafikKehadiranGuru['izin']) ?>,
                alfa: <?= json_encode($grafikKehadiranGuru['alfa']) ?>
            };
            kehadiranGuruChart = new Chart(guruCtx, createChartConfig(dataGuru));
        }
    }

    $(document).ready(function () {
        initDashboardPageCharts();

        $('#filterKelas').on('change', function () {
            const idKelas = $(this).val();
            const loader = $('#filterLoader');

            loader.show();

            $.ajax({
                url: '<?= base_url('admin/dashboard/filter-data') ?>',
                type: 'POST',
                data: setAjaxData({ id_kelas: idKelas }),
                success: function (response) {
                    const obj = JSON.parse(response);
                    if (obj.result == 1) {
                        $('#siswaStatsContainer').html(obj.htmlContent);
                        updateSiswaChart(obj.chartData);
                    }
                },
                error: function (xhr, status, thrown) {
                    console.error(thrown);
                },
                complete: function () {
                    loader.hide();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
