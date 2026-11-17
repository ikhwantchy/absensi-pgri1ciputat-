<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php
$tiles = [
   ['Siswa', count($siswa), 'groups', 'admin/siswa'],
   ['Guru', count($guru), 'badge', 'admin/guru'],
   ['Kelas', count($kelas), 'meeting_room', 'admin/kelas'],
   ['Petugas', count($petugas), 'manage_accounts', 'admin/petugas'],
];
$legend = [
   ['hadir', 'Hadir', 'bg-green-600'],
   ['sakit', 'Sakit', 'bg-amber-500'],
   ['izin', 'Izin', 'bg-sky-500'],
   ['alfa', 'Alfa', 'bg-red-500'],
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

   <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
      <h2 class="inline-flex items-center gap-1 text-sm font-semibold text-slate-900">
         Rekap kehadiran
         <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Dihitung dari hari efektif, yaitu hari yang punya catatan presensi. Akhir pekan dan libur tidak dihitung. Siswa tanpa catatan pada hari efektif dihitung alfa.</span></span>
      </h2>
      <nav class="tabs" aria-label="Periode rekap">
         <button type="button" data-range="7" class="range-tab active">7 hari</button>
         <button type="button" data-range="30" class="range-tab">30 hari</button>
      </nav>
   </div>

   <div class="grid gap-4 lg:grid-cols-3">
      <section class="card lg:col-span-2">
         <div class="card-head">
            <div>
               <h2 class="card-title" id="titleSiswaChart">Tren kehadiran siswa</h2>
               <p class="card-sub"><span id="siswaScope">Semua kelas</span> · persentase hadir per hari</p>
            </div>
            <a class="text-xs font-semibold no-underline hover:underline" href="<?= base_url('admin/absen-siswa'); ?>">Lihat rekap</a>
         </div>
         <div class="card-body">
            <div class="relative h-64 w-full">
               <canvas id="kehadiranSiswa"></canvas>
               <p id="siswaEmpty" class="empty absolute inset-0 hidden items-center justify-center">Belum ada data presensi pada periode ini.</p>
            </div>
         </div>
      </section>

      <section class="card">
         <div class="card-head">
            <div>
               <h2 class="card-title">Rata-rata kehadiran siswa</h2>
               <p class="card-sub"><span id="donutHari">0</span> hari efektif</p>
            </div>
         </div>
         <div class="card-body">
            <div class="relative mx-auto h-44 w-44">
               <canvas id="donutSiswa"></canvas>
               <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                  <span id="donutPersen" class="text-2xl font-semibold tabular-nums text-slate-900">0%</span>
                  <span class="text-xs text-slate-500">rata-rata hadir</span>
               </div>
            </div>
            <ul class="mt-4 grid gap-y-1.5 text-sm">
               <?php foreach ($legend as [$key, $label, $dot]): ?>
                  <li class="flex items-center gap-2">
                     <span class="h-2.5 w-2.5 rounded-full <?= $dot; ?>"></span>
                     <span class="text-slate-600"><?= $label; ?></span>
                     <span class="ml-auto tabular-nums text-slate-500" data-legend-count="<?= $key; ?>">0</span>
                     <span class="w-14 text-right font-semibold tabular-nums text-slate-900" data-legend-persen="<?= $key; ?>">0%</span>
                  </li>
               <?php endforeach; ?>
            </ul>
         </div>
      </section>
   </div>

   <div class="grid gap-4 lg:grid-cols-3">
      <section class="card lg:col-span-2">
         <div class="card-head">
            <div>
               <h2 class="card-title">Tren kehadiran guru</h2>
               <p class="card-sub">Rata-rata <span id="guruPersen" class="font-semibold text-slate-700">0%</span> · persentase hadir per hari</p>
            </div>
            <a class="text-xs font-semibold no-underline hover:underline" href="<?= base_url('admin/absen-guru'); ?>">Lihat rekap</a>
         </div>
         <div class="card-body">
            <div class="relative h-56 w-full">
               <canvas id="kehadiranGuru"></canvas>
               <p id="guruEmpty" class="empty absolute inset-0 hidden items-center justify-center">Belum ada data presensi pada periode ini.</p>
            </div>
         </div>
      </section>

      <section class="card order-first lg:order-none">
         <div class="card-head">
            <div>
               <h2 class="card-title inline-flex items-center gap-1">
                  Kehadiran per kelas
                  <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Urut dari persentase hadir paling rendah. Kelas paling atas adalah kelas yang siswanya paling sering tidak masuk.</span></span>
               </h2>
               <p class="card-sub">Terendah di atas</p>
            </div>
         </div>
         <ul id="kelasList" class="divide-y divide-slate-100"></ul>
      </section>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/plugins/chartjs/chart.umd.min.js') ?>"></script>
<script>
    const rekap = <?= json_encode($rekap) ?>;
    let siswaRekap = rekap.siswa;
    let range = '7';

    const chartFont = "'Plus Jakarta Sans', sans-serif";
    const primary = '#2e3192';
    const statusColors = { hadir: '#16a34a', sakit: '#f59e0b', izin: '#0ea5e9', alfa: '#ef4444' };
    const fmtPersen = (v) => (Math.round(v * 10) / 10).toLocaleString('id-ID') + '%';
    const fmtTanggal = (iso, opts) => new Date(iso + 'T00:00:00').toLocaleDateString('id-ID', opts);

    Chart.defaults.font.family = chartFont;
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';

    function inRange(rows) {
        return range === '7' ? rows.filter(r => r.tanggal >= rekap.mulai7) : rows;
    }

    function areaGradient(context) {
        const { ctx, chartArea } = context.chart;
        if (!chartArea) return 'rgba(46, 49, 146, 0.12)';
        const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, 'rgba(46, 49, 146, 0.28)');
        g.addColorStop(1, 'rgba(46, 49, 146, 0.02)');
        return g;
    }

    // Garis vertikal di titik yang sedang di-hover.
    const hoverLine = {
        id: 'hoverLine',
        afterDatasetsDraw(chart) {
            const active = chart.tooltip?.getActiveElements?.() || [];
            if (!active.length) return;
            const { ctx, chartArea } = chart;
            const x = active[0].element.x;
            ctx.save();
            ctx.strokeStyle = 'rgba(46, 49, 146, 0.25)';
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(x, chartArea.top);
            ctx.lineTo(x, chartArea.bottom);
            ctx.stroke();
            ctx.restore();
        }
    };

    function areaChart(canvasId) {
        return new Chart(document.getElementById(canvasId), {
            type: 'line',
            data: { labels: [], datasets: [{
                data: [],
                rows: [],
                borderColor: primary,
                borderWidth: 2,
                backgroundColor: areaGradient,
                fill: true,
                tension: 0,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: primary,
                pointHoverBorderWidth: 2
            }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        titleColor: '#64748b',
                        titleFont: { size: 11, weight: '500' },
                        bodyColor: '#0f172a',
                        bodyFont: { size: 12 },
                        footerColor: '#64748b',
                        footerFont: { size: 11, weight: '400' },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: (items) => fmtTanggal(items[0].chart.data.datasets[0].rows[items[0].dataIndex].tanggal, { weekday: 'long', day: 'numeric', month: 'long' }),
                            label: (item) => fmtPersen(item.parsed.y) + ' hadir',
                            footer: (items) => {
                                const r = items[0].chart.data.datasets[0].rows[items[0].dataIndex];
                                return `Hadir ${r.hadir} · Sakit ${r.sakit} · Izin ${r.izin} · Alfa ${r.alfa}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 25, callback: (v) => v + '%' },
                        grid: { color: 'rgba(15, 23, 42, 0.05)' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
                    }
                }
            },
            plugins: [hoverLine]
        });
    }

    function fillArea(chart, rows, emptyId) {
        const ds = chart.data.datasets[0];
        ds.rows = rows;
        ds.data = rows.map(r => r.persen);
        chart.data.labels = rows.map(r => fmtTanggal(r.tanggal, { day: 'numeric', month: 'short' }));
        chart.update();
        const empty = document.getElementById(emptyId);
        empty.classList.toggle('hidden', rows.length > 0);
        empty.classList.toggle('flex', rows.length === 0);
    }

    const siswaChart = areaChart('kehadiranSiswa');
    const guruChart = areaChart('kehadiranGuru');

    const donut = new Chart(document.getElementById('donutSiswa'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Sakit', 'Izin', 'Alfa'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [statusColors.hadir, statusColors.sakit, statusColors.izin, statusColors.alfa],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '76%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (i) => `${i.label}: ${i.parsed}` } }
            }
        }
    });

    function fillDonut(r) {
        const keys = ['hadir', 'sakit', 'izin', 'alfa'];
        donut.data.datasets[0].data = r.total > 0 ? keys.map(k => r[k]) : [0, 0, 0, 1];
        donut.data.datasets[0].backgroundColor = r.total > 0 ? keys.map(k => statusColors[k]) : ['#e2e8f0'];
        donut.update();
        document.getElementById('donutPersen').textContent = fmtPersen(r.persen);
        document.getElementById('donutHari').textContent = r.hari;
        keys.forEach(k => {
            document.querySelector(`[data-legend-count="${k}"]`).textContent = r[k];
            document.querySelector(`[data-legend-persen="${k}"]`).textContent = fmtPersen(r.total > 0 ? r[k] / r.total * 100 : 0);
        });
    }

    function fillKelas(rows) {
        const list = document.getElementById('kelasList');
        if (!rows.length) {
            list.innerHTML = '<li class="empty">Belum ada kelas dengan siswa aktif.</li>';
            return;
        }
        list.innerHTML = rows.map((r, i) => {
            const bar = r.persen >= 90 ? 'bg-green-600' : (r.persen >= 75 ? 'bg-amber-500' : 'bg-red-500');
            const text = r.persen >= 90 ? 'text-green-700' : (r.persen >= 75 ? 'text-amber-700' : 'text-red-700');
            const flag = i === 0 && r.total > 0 && rows.length > 1
                ? '<span class="status status-danger"><i class="material-icons">trending_down</i>Terendah</span>' : '';
            return `<li class="px-4 py-2.5">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-900">${r.kelas}</span>
                    <span class="text-xs text-slate-500">${r.siswa} siswa</span>
                    ${flag}
                    <span class="ml-auto font-semibold tabular-nums ${text}">${fmtPersen(r.persen)}</span>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full ${bar}" style="width:${Math.min(100, r.persen)}%"></div></div>
                <div class="mt-1 text-xs text-slate-500">Alfa ${r.alfa} · Sakit ${r.sakit} · Izin ${r.izin}</div>
            </li>`;
        }).join('');
    }

    function render() {
        fillArea(siswaChart, inRange(siswaRekap.harian), 'siswaEmpty');
        fillArea(guruChart, inRange(rekap.guru.harian), 'guruEmpty');
        fillDonut(siswaRekap.ringkasan[range]);
        fillKelas(rekap.perKelas[range]);
        document.getElementById('guruPersen').textContent = fmtPersen(rekap.guru.ringkasan[range].persen);
    }

    document.querySelectorAll('.range-tab').forEach(btn => btn.addEventListener('click', function () {
        range = this.dataset.range;
        document.querySelectorAll('.range-tab').forEach(b => b.classList.toggle('active', b === this));
        render();
    }));

    render();

    $(document).ready(function () {
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
                        siswaRekap = obj.rekap;
                        $('#siswaScope').text(idKelas ? 'Kelas ' + $('#filterKelas option:selected').data('kelas') : 'Semua kelas');
                        render();
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
