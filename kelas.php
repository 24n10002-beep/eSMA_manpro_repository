<?php
// siswa/kelas.php — CRUD Data Kelas (tingkat + nama_kelas + jurusan)
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// ---------- PROSES SIMPAN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode       = $_POST['mode'] ?? 'add';
    $jurusan_id = (int)$_POST['jurusan_id'];
    $nama_kelas = trim($_POST['nama_kelas']);
    $tingkat    = $_POST['tingkat'];

    try {
        if ($mode === 'edit' && $id) {
            $s = $pdo->prepare("UPDATE kelas SET jurusan_id=?, nama_kelas=?, tingkat=? WHERE id=?");
            $s->execute([$jurusan_id, $nama_kelas, $tingkat, $id]);
            flash('success', 'Data kelas diperbarui.');
        } else {
            $s = $pdo->prepare("INSERT INTO kelas (jurusan_id, nama_kelas, tingkat) VALUES (?,?,?)");
            $s->execute([$jurusan_id, $nama_kelas, $tingkat]);
            flash('success', 'Kelas berhasil ditambahkan.');
        }
        redirect('kelas.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

// ---------- PROSES HAPUS ----------
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM kelas WHERE id=?")->execute([$id]);
        flash('success', 'Kelas dihapus.');
    } catch (Exception $e) {
        flash('error', 'Tidak dapat menghapus: ' . $e->getMessage());
    }
    redirect('kelas.php');
}

// ---------- AMBIL DATA EDIT ----------
$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM kelas WHERE id=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

// ---------- DATA PENDUKUNG ----------
$jurusanList = $pdo->query("SELECT * FROM jurusan ORDER BY nama_jurusan")->fetchAll();

$list = $pdo->query("
    SELECT k.*, j.kode_jurusan, j.nama_jurusan,
           (SELECT COUNT(*) FROM siswa_kelas sk WHERE sk.kelas_id = k.id AND sk.status='aktif') AS jml_siswa,
           (SELECT COUNT(*) FROM kelas_mapel km WHERE km.kelas_id = k.id) AS jml_mapel
    FROM kelas k
    JOIN jurusan j ON j.id = k.jurusan_id
    ORDER BY k.tingkat, j.nama_jurusan, k.nama_kelas
")->fetchAll();

$pageTitle = 'Data Kelas';
include __DIR__ . '/../includes/header.php';
?>
<h4>Data Kelas</h4>
<p class="text-muted">Kelola tingkat (X/XI/XII), nama kelas, dan jurusan. Data ini menjadi acuan penempatan siswa dan penugasan mapel.</p>

<div class="row">
    <!-- FORM -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">
                <?= $edit ? 'Edit' : 'Tambah' ?> Kelas
            </div>
            <div class="card-body">
                <form method="post" action="kelas.php<?= $edit ? '?id='.$edit['id'] : '' ?>">
                    <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'add' ?>">

                    <div class="mb-2">
                        <label class="form-label">Tingkat</label>
                        <select name="tingkat" class="form-select" required>
                            <?php foreach (['X','XI','XII'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($edit['tingkat'] ?? '') === $t ? 'selected' : '' ?>>
                                    <?= $t ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan_id" class="form-select" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>" <?= ($edit['jurusan_id'] ?? 0) == $j['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($j['nama_jurusan']) ?> (<?= sanitize($j['kode_jurusan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Nama Kelas</label>
                        <input name="nama_kelas" class="form-control" required
                               placeholder="contoh: IPA-1, IPS-2, XI MIPA 3"
                               value="<?= sanitize($edit['nama_kelas'] ?? '') ?>">
                        <small class="text-muted">Gunakan format konsisten, mis. <code>IPA-1</code>.</small>
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?>
                        <a href="kelas.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Kelas</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tingkat</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th>Siswa Aktif</th>
                            <th>Mapel</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$list): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data kelas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($list as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><span class="badge bg-primary"><?= sanitize($r['tingkat']) ?></span></td>
                            <td><?= sanitize($r['nama_kelas']) ?></td>
                            <td><?= sanitize($r['nama_jurusan']) ?></td>
                            <td><?= (int)$r['jml_siswa'] ?></td>
                            <td><?= (int)$r['jml_mapel'] ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Hapus kelas ini? Pastikan tidak ada siswa/mapel terkait.')">Hapus</a>
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