<?php
$statusIcon = [
    'success'  => 'check_circle',
    'warning'  => 'sick',
    'info'     => 'assignment',
    'danger'   => 'cancel',
    'disabled' => 'schedule',
];
?>
<div>
    <div class="card-head">
        <div>
            <h2 class="card-title">Kelas <?= $kelas; ?></h2>
            <p class="card-sub"><?= count($data ?? []); ?> siswa</p>
        </div>
        <a href="#" class="btn btn-secondary" onclick="getSiswa(lastIdKelas, lastKelas)" data-toggle="tab">
            <i class="material-icons">refresh</i>Muat ulang
        </a>
    </div>

    <div id="dataSiswa" class="table-wrap">
        <?php if (!empty($data)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th class="num">No.</th>
                        <th>NIS</th>
                        <th>Nama siswa</th>
                        <th>Kehadiran</th>
                        <th>Jam masuk</th>
                        <th>Jam pulang</th>
                        <th>Keterangan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($data as $value): ?>
                        <?php
                        $idKehadiran = intval($value['id_kehadiran'] ?? ($lewat ? 5 : 4));
                        $kehadiran = kehadiran($idKehadiran);
                        ?>
                        <tr>
                            <td class="num"><?= $no; ?></td>
                            <td class="whitespace-nowrap tabular-nums text-slate-600"><?= esc($value['nis']); ?></td>
                            <td class="whitespace-nowrap font-medium text-slate-900"><?= esc($value['nama_siswa']); ?></td>
                            <td>
                                <span class="status <?= $kehadiran['color'] === 'disabled' ? 'status-muted' : 'status-' . $kehadiran['color']; ?>"><i class="material-icons"><?= $statusIcon[$kehadiran['color']] ?? 'schedule'; ?></i><?= $kehadiran['text']; ?></span>
                            </td>
                            <td class="whitespace-nowrap tabular-nums"><?= $value['jam_masuk'] ?? '-'; ?></td>
                            <td class="whitespace-nowrap tabular-nums"><?= $value['jam_keluar'] ?? '-'; ?></td>
                            <td class="min-w-[10rem] text-slate-600"><?= $value['keterangan'] ?? '-'; ?></td>
                            <td class="text-right">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#ubahModal" onclick="getDataKehadiran(<?= $value['id_presensi'] ?? '-1'; ?>, <?= $value['id_siswa']; ?>)" class="btn btn-secondary btn-sm"
                                    id="<?= esc($value['nis']); ?>">
                                    <i class="material-icons">edit</i>
                                    Ubah
                                </button>
                            </td>
                        </tr>
                        <?php $no++;
                    endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">Data tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</div>

<?php
function kehadiran($kehadiran): array
{
    $text = '';
    $color = '';
    switch ($kehadiran) {
        case 1:
            $color = 'success';
            $text = 'Hadir';
            break;
        case 2:
            $color = 'warning';
            $text = 'Sakit';
            break;
        case 3:
            $color = 'info';
            $text = 'Izin';
            break;
        case 4:
            $color = 'danger';
            $text = 'Tanpa keterangan';
            break;
        case 5:
        default:
            $color = 'disabled';
            $text = 'Belum tersedia';
            break;
    }

    return ['color' => $color, 'text' => $text];
}
?>
