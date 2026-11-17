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
    $nip = trim($_POST['nip']);
    $jabatan = trim($_POST['jabatan']);
    $jk = $_POST['jenis_kelamin'] ?? null;
    $alamat = trim($_POST['alamat']);
    try {
        $pdo->beginTransaction();
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE users SET nama=?, no_hp=? WHERE id=(SELECT user_id FROM tendik WHERE id=?)")
                ->execute([$nama, $no_hp, $id]);
            if ($password !== '') {
                $pdo->prepare("UPDATE users SET password=? WHERE id=(SELECT user_id FROM tendik WHERE id=?)")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            }
            $pdo->prepare("UPDATE tendik SET nip=?, jabatan=?, jenis_kelamin=?, alamat=? WHERE id=?")
                ->execute([$nip, $jabatan, $jk, $alamat, $id]);
            flash('success','Data tendik diperbarui.');
        } else {
            $hash = password_hash($password ?: 'tendik123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (nama, no_hp, password, role, status) VALUES (?,?,?,'tendik','aktif')")
                ->execute([$nama, $no_hp, $hash]);
            $uid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO tendik (user_id, nip, jabatan, jenis_kelamin, alamat) VALUES (?,?,?,?,?)")
                ->execute([$uid, $nip, $jabatan, $jk, $alamat]);
            flash('success','Tendik ditambahkan.');
        }
        $pdo->commit();
        redirect('data_tendik.php');
    } catch (Exception $e) { $pdo->rollBack(); flash('error','Gagal: '.$e->getMessage()); }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        $uid = $pdo->query("SELECT user_id FROM tendik WHERE id=$id")->fetchColumn();
        $pdo->prepare("DELETE FROM tendik WHERE id=?")->execute([$id]);
        if ($uid) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        $pdo->commit();
        flash('success','Tendik dihapus.');
    } catch (Exception $e) { $pdo->rollBack(); flash('error','Gagal: '.$e->getMessage()); }
    redirect('data_tendik.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT t.*, u.nama, u.no_hp FROM tendik t JOIN users u ON u.id=t.user_id WHERE t.id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$list = $pdo->query("SELECT t.*, u.nama, u.no_hp FROM tendik t JOIN users u ON u.id=t.user_id ORDER BY u.nama")->fetchAll();
$pageTitle='Data Tendik';
include __DIR__ . '/../includes/header.php';
?>
<h4>Data Tendik</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Tendik</div>
            <div class="card-body">
                <form method="post" action="data_tendik.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Nama</label>
                        <input name="nama" class="form-control" required value="<?= sanitize($edit['nama'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">No HP</label>
                        <input name="no_hp" class="form-control" required value="<?= sanitize($edit['no_hp'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Password <?= $edit?'(kosongkan jika tetap)':'' ?></label>
                        <input type="password" name="password" class="form-control" <?= $edit?'':'required' ?>></div>
                    <div class="mb-2"><label class="form-label">NIP</label>
                        <input name="nip" class="form-control" value="<?= sanitize($edit['nip'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Jabatan</label>
                        <input name="jabatan" class="form-control" value="<?= sanitize($edit['jabatan'] ?? '') ?>"></div>
                    <div class="mb-2"><label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="L" <?= ($edit['jenis_kelamin']??'')==='L'?'selected':'' ?>>L</option>
                            <option value="P" <?= ($edit['jenis_kelamin']??'')==='P'?'selected':'' ?>>P</option>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= sanitize($edit['alamat'] ?? '') ?></textarea></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="data_tendik.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Tendik</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>JK</th><th>No HP</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['nama']) ?></td>
                            <td><?= sanitize($r['nip']) ?></td>
                            <td><?= sanitize($r['jabatan']) ?></td>
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