<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $sid = (int)$_POST['siswa_id'];
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
            $pdo->prepare("UPDATE izin_siswa SET siswa_id=?, kelas_mapel_id=?, tanggal_mulai=?, tanggal_selesai=?, jenis=?, alasan=?, file_bukti=?, status=?, disetujui_oleh=? WHERE id=?")
                ->execute([$sid, $km, $tm, $ts, $jenis, $alasan, $file, $status, $setuju, $id]);
            flash('success', 'Izin diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO izin_siswa (siswa_id, kelas_mapel_id, tanggal_mulai, tanggal_selesai, jenis, alasan, file_bukti, status, disetujui_oleh) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$sid, $km, $tm, $ts, $jenis, $alasan, $file, $status, $setuju]);
            flash('success', 'Izin ditambahkan.');
        }
        redirect('cuti.php');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
}

if ($action === 'approve' && $id) {
    try {
        // FK disetujui_oleh mengarah ke tendik -> kita isi null karena kepsek admin, atau isi tendik yang mengajar jika ada
        $pdo->prepare("UPDATE izin_siswa SET status='disetujui', disetujui_oleh=NULL WHERE id=?")->execute([$id]);
        flash('success','Izin disetujui.');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}
if ($action === 'reject' && $id) {
    try {
        $pdo->prepare("UPDATE izin_siswa SET status='ditolak', disetujui_oleh=NULL WHERE id=?")->execute([$id]);
        flash('success','Izin ditolak.');
    } catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}
if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM izin_siswa WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('cuti.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM izin_siswa WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$siswaList = $pdo->query("SELECT s.id, u.nama, s.nis FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY u.nama")->fetchAll();
$kmList = $pdo->query("SELECT km.id, k.nama_kelas, k.tingkat, mp.nama_mapel FROM kelas_mapel km JOIN kelas k ON k.id=km.kelas_id JOIN mata_pelajaran mp ON mp.id=km.mapel_id ORDER BY k.nama_kelas")->fetchAll();

$list = $pdo->query("
    SELECT i.*, u.nama AS siswa_nama, s.nis
    FROM izin_siswa i
    JOIN siswa s ON s.id=i.siswa_id
    JOIN users u ON u.id=s.user_id
    ORDER BY i.id DESC
")->fetchAll();

$pageTitle = 'Cuti/Izin Siswa';
include __DIR__ . '/../includes/header.php';
?>
<h4>Cuti / Izin Siswa</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Izin Siswa</div>
            <div class="card-body">
                <form method="post" action="cuti.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($edit['siswa_id']??0)==$s['id']?'selected':'' ?>><?= sanitize($s['nama']).' ('.$s['nis'].')' ?></option>
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
            <div class="card-header bg-white fw-bold">Daftar Izin/Cuti Siswa</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Siswa</th><th>Mulai</th><th>Selesai</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['siswa_nama']) ?></td>
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