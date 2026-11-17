<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $tendik = (int)$_POST['tendik_id'];
    $km = $_POST['kelas_mapel_id'] ? (int)$_POST['kelas_mapel_id'] : null;
    $tm = $_POST['tanggal_mulai'];
    $ts = $_POST['tanggal_selesai'];
    $jenis = $_POST['jenis'];
    $alasan = trim($_POST['alasan']);
    $file = trim($_POST['file_bukti'] ?? '');
    $status = $_POST['status'] ?? 'menunggu';
    $setuju = $_SESSION['kepsek_id']; // id admin
    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE izin_tendik SET tendik_id=?, kelas_mapel_id=?, tanggal_mulai=?, tanggal_selesai=?, jenis=?, alasan=?, file_bukti=?, status=?, disetujui_oleh=? WHERE id=?")
                ->execute([$tendik, $km, $tm, $ts, $jenis, $alasan, $file, $status, $setuju, $id]);
            flash('success','Izin diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO izin_tendik (tendik_id, kelas_mapel_id, tanggal_mulai, tanggal_selesai, jenis, alasan, file_bukti, status, disetujui_oleh) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$tendik, $km, $tm, $ts, $jenis, $alasan, $file, $status, $setuju]);
            flash('success','Izin ditambahkan.');
        }
        redirect('cuti.php');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
}

if ($action === 'approve' && $id) {
    try {
        // disetujui_oleh FK ke admin.id — kita ambil id admin yang sedang login
        $adm = $pdo->prepare("SELECT id FROM admin WHERE user_id=?");
        $adm->execute([$_SESSION['kepsek_id']]);
        $adminId = $adm->fetchColumn() ?: null;
        $pdo->prepare("UPDATE izin_tendik SET status='disetujui', disetujui_oleh=? WHERE id=?")->execute([$adminId, $id]);
        flash('success','Izin disetujui.');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}
if ($action === 'reject' && $id) {
    try {
        $pdo->prepare("UPDATE izin_tendik SET status='ditolak' WHERE id=?")->execute([$id]);
        flash('success','Izin ditolak.');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}
if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM izin_tendik WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM izin_tendik WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$tendikList = $pdo->query("SELECT t.id, u.nama, t.nip FROM tendik t JOIN users u ON u.id=t.user_id ORDER BY u.nama")->fetchAll();
$kmList = $pdo->query("SELECT km.id, k.nama_kelas, k.tingkat, mp.nama_mapel FROM kelas_mapel km JOIN kelas k ON k.id=km.kelas_id JOIN mata_pelajaran mp ON mp.id=km.mapel_id ORDER BY k.nama_kelas")->fetchAll();

$list = $pdo->query("
    SELECT i.*, u.nama AS tendik_nama, t.nip
    FROM izin_tendik i
    JOIN tendik t ON t.id=i.tendik_id
    JOIN users u ON u.id=t.user_id
    ORDER BY i.id DESC
")->fetchAll();

$pageTitle='Cuti/Izin Tendik';
include __DIR__ . '/../includes/header.php';
?>
<h4>Cuti / Izin Tendik</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Izin Tendik</div>
            <div class="card-body">
                <form method="post" action="cuti.php<?= $edit?'?id='.$edit['id']:'' ?>">
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
                            <option value="">-- Semua --</option>
                            <?php foreach ($kmList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($edit['kelas_mapel_id']??0)==$k['id']?'selected':'' ?>><?= sanitize($k['tingkat'].' '.$k['nama_kelas'].' '.$k['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="row">
                        <div class="col-6 mb-2"><label class="form-label">Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required value="<?= $edit['tanggal_mulai'] ?? '' ?>"></div>
                        <div class="col-6 mb-2"><label class="form-label">Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required value="<?= $edit['tanggal_selesai'] ?? '' ?>"></div>
                    </div>
                    <div class="mb-2"><label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <?php foreach (['izin','sakit','cuti'] as $j): ?>
                                <option <?= ($edit['jenis']??'izin')==$j?'selected':'' ?>><?= $j ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Alasan</label>
                        <textarea name="alasan" class="form-control" rows="2" required><?= sanitize($edit['alasan'] ?? '') ?></textarea></div>
                    <div class="mb-2"><label class="form-label">File Bukti (path)</label>
                        <input name="file_bukti" class="form-control" value="<?= sanitize($edit['file_bukti'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['menunggu','disetujui','ditolak'] as $st): ?>
                                <option <?= ($edit['status']??'menunggu')==$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="cuti.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Izin/Cuti Tendik</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Tendik</th><th>Mulai</th><th>Selesai</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['tendik_nama']) ?></td>
                            <td><?= formatTanggal($r['tanggal_mulai']) ?></td>
                            <td><?= formatTanggal($r['tanggal_selesai']) ?></td>
                            <td><?= $r['jenis'] ?></td>
                            <td><span class="badge bg-<?= $r['status']==='disetujui'?'success':($r['status']==='ditolak'?'danger':'warning') ?>"><?= $r['status'] ?></span></td>
                            <td>
                                <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?action=approve&id=<?= $r['id'] ?>" class="btn btn-sm btn-success">Setujui</a>
                                <a href="?action=reject&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger">Tolak</a>
                                <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</a>
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