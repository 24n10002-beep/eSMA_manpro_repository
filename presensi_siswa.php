<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $km = (int)$_POST['kelas_mapel_id'];
    $sid = (int)$_POST['siswa_id'];
    $tgl = $_POST['tanggal'];
    $pertemuan = $_POST['pertemuan'] !== '' ? (int)$_POST['pertemuan'] : null;
    $jm = $_POST['jam_mulai'] ?: null;
    $js = $_POST['jam_selesai'] ?: null;
    $status = $_POST['status'];
    $ket = trim($_POST['keterangan'] ?? '');
    $oleh = $_SESSION['kepsek_id'];
    // Catatan: dicatat_oleh FK ke tendik. Jika kepsek mencatat, kita isi tendik yang mengajar mapel tsb.
    $dosen = (int)($_POST['dicatat_oleh'] ?? 0);
    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE presensi_siswa SET kelas_mapel_id=?, siswa_id=?, tanggal=?, pertemuan=?, jam_mulai=?, jam_selesai=?, status=?, keterangan=?, dicatat_oleh=? WHERE id=?")
                ->execute([$km, $sid, $tgl, $pertemuan, $jm, $js, $status, $ket, $dosen ?: null, $id]);
            flash('success','Presensi diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO presensi_siswa (kelas_mapel_id, siswa_id, tanggal, pertemuan, jam_mulai, jam_selesai, status, keterangan, dicatat_oleh) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$km, $sid, $tgl, $pertemuan, $jm, $js, $status, $ket, $dosen ?: null]);
            flash('success','Presensi ditambahkan.');
        }
        redirect('presensi_siswa.php');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM presensi_siswa WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('presensi_siswa.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM presensi_siswa WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$kmList = $pdo->query("
    SELECT km.id, k.nama_kelas, k.tingkat, mp.nama_mapel, t.id AS tendik_id, u.nama AS guru
    FROM kelas_mapel km
    JOIN kelas k ON k.id=km.kelas_id
    JOIN mata_pelajaran mp ON mp.id=km.mapel_id
    JOIN tendik t ON t.id=km.tendik_id
    JOIN users u ON u.id=t.user_id
    ORDER BY k.nama_kelas, mp.nama_mapel
")->fetchAll();
$siswaList = $pdo->query("SELECT s.id, u.nama, s.nis FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY u.nama")->fetchAll();

$list = $pdo->query("
    SELECT p.*, u.nama AS siswa_nama, s.nis, k.nama_kelas, k.tingkat, mp.nama_mapel, ug.nama AS guru_nama
    FROM presensi_siswa p
    JOIN siswa s ON s.id=p.siswa_id
    JOIN users u ON u.id=s.user_id
    JOIN kelas_mapel km ON km.id=p.kelas_mapel_id
    JOIN kelas k ON k.id=km.kelas_id
    JOIN mata_pelajaran mp ON mp.id=km.mapel_id
    LEFT JOIN tendik t ON t.id=p.dicatat_oleh
    LEFT JOIN users ug ON ug.id=t.user_id
    ORDER BY p.tanggal DESC, p.id DESC
")->fetchAll();

$pageTitle='Presensi Siswa';
include __DIR__ . '/../includes/header.php';
?>
<h4>Presensi Siswa</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Presensi</div>
            <div class="card-body">
                <form method="post" action="presensi_siswa.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Kelas-Mapel</label>
                        <select name="kelas_mapel_id" class="form-select" required onchange="setTendik(this)">
                            <?php foreach ($kmList as $k): ?>
                                <option value="<?= $k['id'] ?>" data-tendik="<?= $k['tendik_id'] ?>" <?= ($edit['kelas_mapel_id']??0)==$k['id']?'selected':'' ?>>
                                    <?= sanitize($k['tingkat'].' '.$k['nama_kelas'].' - '.$k['nama_mapel'].' ('.$k['guru'].')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <input type="hidden" name="dicatat_oleh" id="dicatat_oleh" value="<?= $edit['dicatat_oleh'] ?? '' ?>">
                    <div class="mb-2"><label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($edit['siswa_id']??0)==$s['id']?'selected':'' ?>><?= sanitize($s['nama']).' ('.$s['nis'].')' ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required value="<?= $edit['tanggal'] ?? date('Y-m-d') ?>"></div>
                    <div class="mb-2"><label class="form-label">Pertemuan</label>
                        <input type="number" name="pertemuan" class="form-control" value="<?= $edit['pertemuan'] ?? '' ?>"></div>
                    <div class="row">
                        <div class="col-6 mb-2"><label class="form-label">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" value="<?= $edit['jam_mulai'] ?? '' ?>"></div>
                        <div class="col-6 mb-2"><label class="form-label">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control" value="<?= $edit['jam_selesai'] ?? '' ?>"></div>
                    </div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['hadir','izin','sakit','alpa'] as $st): ?>
                                <option <?= ($edit['status']??'hadir')==$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"><?= sanitize($edit['keterangan'] ?? '') ?></textarea></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="presensi_siswa.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Presensi Siswa</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Mapel</th><th>Pertemuan</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= formatTanggal($r['tanggal']) ?></td>
                            <td><?= sanitize($r['siswa_nama']) ?></td>
                            <td><?= sanitize($r['tingkat'].' '.$r['nama_kelas']) ?></td>
                            <td><?= sanitize($r['nama_mapel']) ?></td>
                            <td><?= $r['pertemuan'] ?? '-' ?></td>
                            <td><span class="badge bg-<?= $r['status']==='hadir'?'success':($r['status']==='alpa'?'danger':'warning') ?>"><?= $r['status'] ?></span></td>
                            <td>
                                <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function setTendik(sel) {
    const t = sel.options[sel.selectedIndex].dataset.tendik;
    document.getElementById('dicatat_oleh').value = t || '';
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.querySelector('select[name=kelas_mapel_id]');
    if (sel) setTendik(sel);
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>