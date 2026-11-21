<?php
// siswa/tahun_ajaran.php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $nama = trim($_POST['nama']);
    $tgl_mulai = $_POST['tanggal_mulai'] ?: null;
    $tgl_selesai = $_POST['tanggal_selesai'] ?: null;
    $status = $_POST['status'] ?? 'aktif';

    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE tahun_ajaran SET nama=?, tanggal_mulai=?, tanggal_selesai=?, status=? WHERE id=?")
                ->execute([$nama, $tgl_mulai, $tgl_selesai, $status, $id]);
            flash('success', 'Tahun ajaran diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO tahun_ajaran (nama, tanggal_mulai, tanggal_selesai, status) VALUES (?,?,?,?)")
                ->execute([$nama, $tgl_mulai, $tgl_selesai, $status]);
            flash('success', 'Tahun ajaran ditambahkan.');
        }
        redirect('tahun_ajaran.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM tahun_ajaran WHERE id=?")->execute([$id]);
        flash('success', 'Tahun ajaran dihapus.');
    } catch (Exception $e) {
        flash('error', 'Tidak dapat menghapus: ' . $e->getMessage());
    }
    redirect('tahun_ajaran.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM tahun_ajaran WHERE id=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

$list = $pdo->query("SELECT * FROM tahun_ajaran ORDER BY id DESC")->fetchAll();
$pageTitle = 'Tahun Ajaran';
include __DIR__ . '/../includes/header.php';
?>
<h4>Tahun Ajaran</h4>
<p class="text-muted">Kelola data tahun ajaran untuk penempatan siswa dan penugasan mata pelajaran.</p>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Tahun Ajaran</div>
            <div class="card-body">
                <form method="post" action="tahun_ajaran.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <div class="mb-2">
                        <label class="form-label">Nama Tahun Ajaran</label>
                        <input name="nama" class="form-control" required placeholder="Contoh: 2026/2027" value="<?= sanitize($edit['nama'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= $edit['tanggal_mulai'] ?? '' ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="<?= $edit['tanggal_selesai'] ?? '' ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif" <?= ($edit['status']??'aktif')==='aktif'?'selected':'' ?>>Aktif</option>
                            <option value="selesai" <?= ($edit['status']??'')==='selesai'?'selected':'' ?>>Selesai</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="tahun_ajaran.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Tahun Ajaran</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Nama</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['nama']) ?></td>
                            <td><?= formatTanggal($r['tanggal_mulai']) ?></td>
                            <td><?= formatTanggal($r['tanggal_selesai']) ?></td>
                            <td>
                                <span class="badge bg-<?= $r['status']==='aktif'?'success':'secondary' ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
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