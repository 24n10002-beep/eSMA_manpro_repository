<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $nama = trim($_POST['nama']);
    $no_hp = trim($_POST['no_hp']);
    $password = $_POST['password'] ?? '';
    $nis = trim($_POST['nis']);
    $nisn = trim($_POST['nisn']);
    $jk = $_POST['jenis_kelamin'] ?? null;
    $tempat = trim($_POST['tempat_lahir']);
    $tgl = $_POST['tanggal_lahir'] ?: null;
    $alamat = trim($_POST['alamat']);
    $ortu = trim($_POST['nama_ortu']);
    $hp_ortu = trim($_POST['no_hp_ortu']);

    try {
        $pdo->beginTransaction();
        if ($mode === 'edit' && $id) {
            $s = $pdo->prepare("UPDATE users SET nama=?, no_hp=? WHERE id=(SELECT user_id FROM siswa WHERE id=?)");
            $s->execute([$nama, $no_hp, $id]);
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password=? WHERE id=(SELECT user_id FROM siswa WHERE id=?)")->execute([$hash, $id]);
            }
            $s = $pdo->prepare("UPDATE siswa SET nis=?, nisn=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, alamat=?, nama_ortu=?, no_hp_ortu=? WHERE id=?");
            $s->execute([$nis, $nisn, $jk, $tempat, $tgl, $alamat, $ortu, $hp_ortu, $id]);
            flash('success', 'Data siswa diperbarui.');
        } else {
            $hash = password_hash($password ?: 'siswa123', PASSWORD_DEFAULT);
            $s = $pdo->prepare("INSERT INTO users (nama, no_hp, password, role, status) VALUES (?,?,?,'siswa','aktif')");
            $s->execute([$nama, $no_hp, $hash]);
            $uid = $pdo->lastInsertId();
            $s = $pdo->prepare("INSERT INTO siswa (user_id, nis, nisn, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, nama_ortu, no_hp_ortu) VALUES (?,?,?,?,?,?,?,?,?)");
            $s->execute([$uid, $nis, $nisn, $jk, $tempat, $tgl, $alamat, $ortu, $hp_ortu]);
            flash('success', 'Siswa berhasil ditambahkan.');
        }
        $pdo->commit();
        redirect('data_siswa.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        $uid = $pdo->query("SELECT user_id FROM siswa WHERE id=$id")->fetchColumn();
        $pdo->prepare("DELETE FROM siswa WHERE id=?")->execute([$id]);
        if ($uid) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        $pdo->commit();
        flash('success', 'Siswa dihapus.');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Tidak dapat menghapus: ' . $e->getMessage());
    }
    redirect('data_siswa.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT s.*, u.nama, u.no_hp FROM siswa s JOIN users u ON u.id=s.user_id WHERE s.id=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

$list = $pdo->query("SELECT s.*, u.nama, u.no_hp FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY u.nama")->fetchAll();
$pageTitle = 'Data Siswa';
include __DIR__ . '/../includes/header.php';
?>
<h4>Data Siswa</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Siswa</div>
            <div class="card-body">
                <form method="post" action="data_siswa.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <div class="mb-2"><label class="form-label">Nama</label>
                        <input name="nama" class="form-control" required value="<?= sanitize($edit['nama'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">No HP</label>
                        <input name="no_hp" class="form-control" required value="<?= sanitize($edit['no_hp'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Password <?= $edit?'(kosongkan jika tidak diubah)':'' ?></label>
                        <input type="password" name="password" class="form-control" <?= $edit?'':'required' ?>></div>
                    <div class="mb-2"><label class="form-label">NIS</label>
                        <input name="nis" class="form-control" required value="<?= sanitize($edit['nis'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">NISN</label>
                        <input name="nisn" class="form-control" value="<?= sanitize($edit['nisn'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="L" <?= ($edit['jenis_kelamin']??'')==='L'?'selected':'' ?>>L</option>
                            <option value="P" <?= ($edit['jenis_kelamin']??'')==='P'?'selected':'' ?>>P</option>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tempat Lahir</label>
                        <input name="tempat_lahir" class="form-control" value="<?= sanitize($edit['tempat_lahir'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= $edit['tanggal_lahir'] ?? '' ?>"></div>
                    <div class="mb-2"><label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= sanitize($edit['alamat'] ?? '') ?></textarea></div>
                    <div class="mb-2"><label class="form-label">Nama Ortu</label>
                        <input name="nama_ortu" class="form-control" value="<?= sanitize($edit['nama_ortu'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">No HP Ortu</label>
                        <input name="no_hp_ortu" class="form-control" value="<?= sanitize($edit['no_hp_ortu'] ?? '') ?>"></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="data_siswa.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Siswa</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Nama</th><th>NIS</th><th>JK</th><th>No HP</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['nama']) ?></td>
                            <td><?= sanitize($r['nis']) ?></td>
                            <td><?= $r['jenis_kelamin'] ?></td>
                            <td><?= sanitize($r['no_hp']) ?></td>
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