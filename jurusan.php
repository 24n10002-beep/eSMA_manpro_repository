<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_jurusan']);
    $nama = trim($_POST['nama_jurusan']);
    $ket  = trim($_POST['keterangan']);
    try {
        if (($_POST['mode'] ?? '') === 'edit' && $id) {
            $s = $pdo->prepare("UPDATE jurusan SET kode_jurusan=?, nama_jurusan=?, keterangan=? WHERE id=?");
            $s->execute([$kode, $nama, $ket, $id]);
            flash('success', 'Jurusan berhasil diperbarui.');
        } else {
            $s = $pdo->prepare("INSERT INTO jurusan (kode_jurusan, nama_jurusan, keterangan) VALUES (?,?,?)");
            $s->execute([$kode, $nama, $ket]);
            flash('success', 'Jurusan berhasil ditambahkan.');
        }
        redirect('jurusan.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM jurusan WHERE id=?")->execute([$id]);
        flash('success', 'Jurusan dihapus.');
    } catch (Exception $e) {
        flash('error', 'Tidak dapat menghapus: ' . $e->getMessage());
    }
    redirect('jurusan.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM jurusan WHERE id=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

$list = $pdo->query("SELECT * FROM jurusan ORDER BY nama_jurusan")->fetchAll();
$pageTitle = 'Data Jurusan';
include __DIR__ . '/../includes/header.php';
?>
<h4>Data Jurusan</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><?= $edit ? 'Edit' : 'Tambah' ?> Jurusan</div>
            <div class="card-body">
                <form method="post" action="jurusan.php<?= $edit ? '?id='.$edit['id'] : '' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <div class="mb-2">
                        <label class="form-label">Kode Jurusan</label>
                        <input name="kode_jurusan" class="form-control" required value="<?= sanitize($edit['kode_jurusan'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Jurusan</label>
                        <input name="nama_jurusan" class="form-control" required value="<?= sanitize($edit['nama_jurusan'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"><?= sanitize($edit['keterangan'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="jurusan.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Jurusan</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Kode</th><th>Nama</th><th>Keterangan</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['kode_jurusan']) ?></td>
                            <td><?= sanitize($r['nama_jurusan']) ?></td>
                            <td><?= sanitize($r['keterangan']) ?></td>
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