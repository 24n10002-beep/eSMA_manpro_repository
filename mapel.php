<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jurusan_id = (int)$_POST['jurusan_id'];
    $kode = trim($_POST['kode_mapel']);
    $nama = trim($_POST['nama_mapel']);
    $desk = trim($_POST['deskripsi']);
    try {
        if (($_POST['mode'] ?? '') === 'edit' && $id) {
            $s = $pdo->prepare("UPDATE mata_pelajaran SET jurusan_id=?, kode_mapel=?, nama_mapel=?, deskripsi=? WHERE id=?");
            $s->execute([$jurusan_id, $kode, $nama, $desk, $id]);
            flash('success', 'Mata pelajaran diperbarui.');
        } else {
            $s = $pdo->prepare("INSERT INTO mata_pelajaran (jurusan_id, kode_mapel, nama_mapel, deskripsi) VALUES (?,?,?,?)");
            $s->execute([$jurusan_id, $kode, $nama, $desk]);
            flash('success', 'Mata pelajaran ditambahkan.');
        }
        redirect('mapel.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM mata_pelajaran WHERE id=?")->execute([$id]);
        flash('success', 'Mata pelajaran dihapus.');
    } catch (Exception $e) {
        flash('error', 'Tidak dapat menghapus: ' . $e->getMessage());
    }
    redirect('mapel.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM mata_pelajaran WHERE id=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

$jurusanList = $pdo->query("SELECT * FROM jurusan ORDER BY nama_jurusan")->fetchAll();

$list = $pdo->query("
    SELECT mp.*, j.kode_jurusan, j.nama_jurusan
    FROM mata_pelajaran mp
    JOIN jurusan j ON j.id = mp.jurusan_id
    ORDER BY j.nama_jurusan, mp.nama_mapel
")->fetchAll();

$pageTitle = 'Data Mata Pelajaran';
include __DIR__ . '/../includes/header.php';
?>
<h4>Data Mata Pelajaran</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><?= $edit ? 'Edit' : 'Tambah' ?> Mapel</div>
            <div class="card-body">
                <form method="post" action="mapel.php<?= $edit ? '?id='.$edit['id'] : '' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <div class="mb-2">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan_id" class="form-select" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>" <?= ($edit['jurusan_id']??0)==$j['id']?'selected':'' ?>>
                                    <?= sanitize($j['nama_jurusan']) ?> (<?= sanitize($j['kode_jurusan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kode Mapel</label>
                        <input name="kode_mapel" class="form-control" required
                               value="<?= sanitize($edit['kode_mapel'] ?? '') ?>">
                        <small class="text-muted">Kode unik per jurusan.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Mapel</label>
                        <input name="nama_mapel" class="form-control" required
                               value="<?= sanitize($edit['nama_mapel'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"><?= sanitize($edit['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?>
                        <a href="mapel.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Mapel per Jurusan</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jurusan</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><span class="badge bg-secondary"><?= sanitize($r['nama_jurusan']) ?></span></td>
                            <td><?= sanitize($r['kode_mapel']) ?></td>
                            <td><?= sanitize($r['nama_mapel']) ?></td>
                            <td><?= sanitize($r['deskripsi']) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Hapus?')">Hapus</a>
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