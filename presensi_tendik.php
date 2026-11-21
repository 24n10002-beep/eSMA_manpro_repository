<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $tendik = (int)$_POST['tendik_id'];
    $km = $_POST['kelas_mapel_id'] ? (int)$_POST['kelas_mapel_id'] : null;
    $tgl = $_POST['tanggal'];
    $jm = $_POST['jam_masuk'] ?: null;
    $jk = $_POST['jam_keluar'] ?: null;
    $status = $_POST['status'];
    $ket = trim($_POST['keterangan'] ?? '');
    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE presensi_tendik SET tendik_id=?, kelas_mapel_id=?, tanggal=?, jam_masuk=?, jam_keluar=?, status=?, keterangan=? WHERE id=?")
                ->execute([$tendik, $km, $tgl, $jm, $jk, $status, $ket, $id]);
            flash('success','Presensi diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO presensi_tendik (tendik_id, kelas_mapel_id, tanggal, jam_masuk, jam_keluar, status, keterangan) VALUES (?,?,?,?,?,?,?)")
                ->execute([$tendik, $km, $tgl, $jm, $jk, $status, $ket]);
            flash('success','Presensi ditambahkan.');
        }
        redirect('presensi.php');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM presensi_tendik WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('presensi.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM presensi_tendik WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$tendikList = $pdo->query("SELECT t.id, u.nama, t.nip FROM tendik t JOIN users u ON u.id=t.user_id ORDER BY u.nama")->fetchAll();
$kmList = $pdo->query("SELECT km.id, k.nama_kelas, k.tingkat, mp.nama_mapel FROM kelas_mapel km JOIN kelas k ON k.id=km.kelas_id JOIN mata_pelajaran mp ON mp.id=km.mapel_id ORDER BY k.nama_kelas")->fetchAll();

$list = $pdo->query("
    SELECT p.*, u.nama AS tendik_nama, t.nip, k.nama_kelas, k.tingkat, mp.nama_mapel
    FROM presensi_tendik p
    JOIN tendik t ON t.id=p.tendik_id
    JOIN users u ON u.id=t.user_id
    LEFT JOIN kelas_mapel km ON km.id=p.kelas_mapel_id
    LEFT JOIN kelas k ON k.id=km.kelas_id
    LEFT JOIN mata_pelajaran mp ON mp.id=km.mapel_id
    ORDER BY p.tanggal DESC, p.id DESC
")->fetchAll();

$pageTitle='Presensi Tendik';
include __DIR__ . '/../includes/header.php';
?>
<h4>Presensi Tendik</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Presensi Tendik</div>
            <div class="card-body">
                <form method="post" action="presensi.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Tendik</label>
                        <select name="tendik_id" class="form-select" required>
                            <?php foreach ($tendikList as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($edit['tendik_id']??0)==$t['id']?'selected':'' ?>><?= sanitize($t['nama']).' ('.$t['nip'].')' ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Kelas-Mapel (opsional)</label>
                        <select name="kelas_mapel_id" class="form-select">
                            <option value="">-- Umum --</option>
                            <?php foreach ($kmList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($edit['kelas_mapel_id']??0)==$k['id']?'selected':'' ?>><?= sanitize($k['tingkat'].' '.$k['nama_kelas'].' '.$k['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required value="<?= $edit['tanggal'] ?? date('Y-m-d') ?>"></div>
                    <div class="row">
                        <div class="col-6 mb-2"><label class="form-label">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control" value="<?= $edit['jam_masuk'] ?? '' ?>"></div>
                        <div class="col-6 mb-2"><label class="form-label">Jam Keluar</label>
                            <input type="time" name="jam_keluar" class="form-control" value="<?= $edit['jam_keluar'] ?? '' ?>"></div>
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
                    <?php if ($edit): ?><a href="presensi.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Presensi Tendik</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Tanggal</th><th>Tendik</th><th>Kelas-Mapel</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= formatTanggal($r['tanggal']) ?></td>
                            <td><?= sanitize($r['tendik_nama']) ?></td>
                            <td><?= $r['nama_kelas'] ? sanitize($r['tingkat'].' '.$r['nama_kelas'].' '.$r['nama_mapel']) : '-' ?></td>
                            <td><?= $r['jam_masuk'] ?? '-' ?></td>
                            <td><?= $r['jam_keluar'] ?? '-' ?></td>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
