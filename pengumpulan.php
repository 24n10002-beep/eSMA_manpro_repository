<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

// Kepsek dapat melihat & memberi nilai, serta CRUD pengumpulan
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $penilaian_id = (int)$_POST['penilaian_id'];
    $siswa_id = (int)$_POST['siswa_id'];
    $jawaban = trim($_POST['jawaban'] ?? '');
    $status = $_POST['status'] ?? 'terkirim';
    $nilai = $_POST['nilai'] === '' ? null : (float)$_POST['nilai'];
    $catatan = trim($_POST['catatan_guru'] ?? '');
    $file = trim($_POST['file_jawaban'] ?? '');
    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE pengumpulan SET penilaian_id=?, siswa_id=?, jawaban=?, file_jawaban=?, status=?, nilai=?, catatan_guru=? WHERE id=?")
                ->execute([$penilaian_id, $siswa_id, $jawaban, $file, $status, $nilai, $catatan, $id]);
            flash('success', 'Pengumpulan diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO pengumpulan (penilaian_id, siswa_id, jawaban, file_jawaban, status, nilai, catatan_guru) VALUES (?,?,?,?,?,?,?)")
                ->execute([$penilaian_id, $siswa_id, $jawaban, $file, $status, $nilai, $catatan]);
            flash('success', 'Pengumpulan ditambahkan.');
        }
        redirect('pengumpulan.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM pengumpulan WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('pengumpulan.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM pengumpulan WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$penilaianList = $pdo->query("
    SELECT p.id, p.jenis, p.judul, mp.nama_mapel, k.nama_kelas, k.tingkat,
           j.nama_jurusan
    FROM penilaian p
    JOIN kelas_mapel km ON km.id = p.kelas_mapel_id
    JOIN kelas k ON k.id = km.kelas_id
    JOIN jurusan j ON j.id = k.jurusan_id
    JOIN mata_pelajaran mp ON mp.id = km.mapel_id
    WHERE p.jenis IN ('UTS','UAS')
    ORDER BY p.id DESC
")->fetchAll();
$siswaList = $pdo->query("SELECT s.id, u.nama, s.nis FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY u.nama")->fetchAll();

$list = $pdo->query("
    SELECT pg.*, p.jenis, p.judul AS judul_penilaian, u.nama AS siswa_nama, s.nis, mp.nama_mapel
    FROM pengumpulan pg
    JOIN penilaian p ON p.id=pg.penilaian_id
    JOIN kelas_mapel km ON km.id=p.kelas_mapel_id
    JOIN mata_pelajaran mp ON mp.id=km.mapel_id
    JOIN siswa s ON s.id=pg.siswa_id
    JOIN users u ON u.id=s.user_id
    ORDER BY pg.id DESC
")->fetchAll();

$pageTitle = 'Pengumpulan UTS/UAS';
include __DIR__ . '/../includes/header.php';
?>
<h4>Pengumpulan Tugas UTS & UAS Siswa</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Pengumpulan</div>
            <div class="card-body">
                <form method="post" action="pengumpulan.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Penilaian (UTS/UAS)</label>
                        <select name="penilaian_id" class="form-select" required>
                            <?php foreach ($penilaianList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($edit['penilaian_id']??0)==$p['id']?'selected':'' ?>>
                                    [<?= $p['jenis'] ?>] <?= sanitize($p['judul']) ?> - <?= sanitize($p['tingkat'].' '.$p['nama_kelas'].' '.$p['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($edit['siswa_id']??0)==$s['id']?'selected':'' ?>>
                                    <?= sanitize($s['nama']).' ('.$s['nis'].')' ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Jawaban</label>
                        <textarea name="jawaban" class="form-control" rows="2"><?= sanitize($edit['jawaban'] ?? '') ?></textarea></div>
                    <div class="mb-2"><label class="form-label">File Jawaban (path)</label>
                        <input name="file_jawaban" class="form-control" value="<?= sanitize($edit['file_jawaban'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['terkirim','terlambat','dinilai','revisi'] as $st): ?>
                                <option <?= ($edit['status']??'terkirim')==$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Nilai</label>
                        <input type="number" step="0.01" name="nilai" class="form-control" value="<?= $edit['nilai'] ?? '' ?>"></div>
                    <div class="mb-2"><label class="form-label">Catatan Guru</label>
                        <textarea name="catatan_guru" class="form-control" rows="2"><?= sanitize($edit['catatan_guru'] ?? '') ?></textarea></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="pengumpulan.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Pengumpulan UTS/UAS</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Siswa</th><th>Jenis</th><th>Judul</th><th>Mapel</th><th>Status</th><th>Nilai</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['siswa_nama']).' ('.$r['nis'].')' ?></td>
                            <td><span class="badge bg-info"><?= $r['jenis'] ?></span></td>
                            <td><?= sanitize($r['judul_penilaian']) ?></td>
                            <td><?= sanitize($r['nama_mapel']) ?></td>
                            <td><?= $r['status'] ?></td>
                            <td><?= $r['nilai'] ?? '-' ?></td>
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