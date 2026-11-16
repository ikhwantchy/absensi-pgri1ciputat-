<?php
$statusIcon = [
    'success'  => 'check_circle',
    'warning'  => 'sick',
    'info'     => 'assignment',
    'danger'   => 'cancel',
    'disabled' => 'schedule',
];
?>
<div class="modal-body">
    <form id="formUbah" class="grid gap-y-2.5">

        <input type="hidden" name="id_siswa" value="<?= $data['id_siswa'] ?? ''; ?>">
        <input type="hidden" name="id_guru" value="<?= $data['id_guru'] ?? ''; ?>">
        <input type="hidden" name="id_kelas" value="<?= $data['id_kelas'] ?? ''; ?>">

        <div>
            <label class="label" for="kehadiran">Kehadiran</label>
            <div class="grid gap-1.5" id="kehadiran">
                <?php foreach ($listKehadiran as $value2): ?>
                    <?php $kehadiran = kehadiran($value2['id_kehadiran']); ?>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50 has-[:checked]:border-primary-300 has-[:checked]:bg-primary-50" for="k<?= $kehadiran['text']; ?>">
                        <input type="radio" name="id_kehadiran" id="k<?= $kehadiran['text']; ?>"
                            value="<?= $value2['id_kehadiran']; ?>"
                            <?= $value2['id_kehadiran'] == ($presensi['id_kehadiran'] ?? '4') ? 'checked' : ''; ?>>
                        <span class="status <?= $kehadiran['color'] === 'disabled' ? 'status-muted' : 'status-' . $kehadiran['color']; ?> text-sm"><i class="material-icons"><?= $statusIcon[$kehadiran['color']] ?? 'schedule'; ?></i><?= $kehadiran['text']; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label" for="jamMasuk">Jam masuk</label>
                <input class="input" type="time" name="jam_masuk" id="jamMasuk"
                    value="<?= $presensi['jam_masuk'] ?? ''; ?>">
            </div>
            <div>
                <label class="label" for="jamKeluar">Jam keluar</label>
                <input class="input" type="time" name="jam_keluar" id="jamKeluar"
                    value="<?= $presensi['jam_keluar'] ?? ''; ?>">
            </div>
        </div>
        <div>
            <label class="label" for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" class="input" rows="3" placeholder="Opsional"><?= trim($presensi['keterangan'] ?? ''); ?></textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Tutup</button>
    <button type="button" onclick="ubahKehadiran()" class="btn btn-primary" data-dismiss="modal">Simpan</button>
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
