<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

// CRUD siswa_kelas (penempatan siswa ke kelas) + lihat kelas_mapel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $sid = (int)$_POST['siswa_id'];
    $kelas = (int)$_POST['kelas_id'];
    $ta = (int)$_POST['tahun_ajaran_id'];
    $status = $_POST['status'] ?? 'aktif';
    $tgl = $_POST['tanggal_masuk'] ?: null;
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE siswa_kelas SET siswa_id=?, kelas_id=?, tahun_ajaran_id=?, status=?, tanggal_masuk=? WHERE id=?")
                ->execute([$sid, $kelas, $ta, $status, $tgl, $id]);
            flash('success', 'Data kelas siswa diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO siswa_kelas (siswa_id, kelas_id, tahun_ajaran_id, status, tanggal_masuk) VALUES (?,?,?,?,?)")
                ->execute([$sid, $kelas, $ta, $status, $tgl]);
            flash('success', 'Siswa ditempatkan ke kelas.');
        }
        redirect('kelas_mapel_siswa.php');
    } catch (Exception $e) {
        flash('error', 'Gagal: ' . $e->getMessage());
    }
}

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM siswa_kelas WHERE id=?")->execute([$id]);
        flash('success','Data dihapus.');
    } catch (Exception $e) { flash('error','Gagal hapus: '.$e->getMessage()); }
    redirect('kelas_mapel_siswa.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM siswa_kelas WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$siswaList  = $pdo->query("SELECT s.id, u.nama, s.nis FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY u.nama")->fetchAll();
$kelasList  = $pdo->query("SELECT k.id, k.nama_kelas, k.tingkat, j.nama_jurusan FROM kelas k JOIN jurusan j ON j.id=k.jurusan_id ORDER BY k.tingkat, k.nama_kelas")->fetchAll();
$taList     = $pdo->query("SELECT * FROM tahun_ajaran ORDER BY id DESC")->fetchAll();

$list = $pdo->query("
    SELECT sk.*, u.nama AS siswa_nama, s.nis, k.nama_kelas, k.tingkat, j.nama_jurusan, ta.nama AS tahun_ajaran
    FROM siswa_kelas sk
    JOIN siswa s ON s.id=sk.siswa_id
    JOIN users u ON u.id=s.user_id
    JOIN kelas k ON k.id=sk.kelas_id
    JOIN jurusan j ON j.id=k.jurusan_id
    JOIN tahun_ajaran ta ON ta.id=sk.tahun_ajaran_id
    ORDER BY ta.id DESC, u.nama
")->fetchAll();

// kelas_mapel detail (mapel yang diajarkan pada kelas)
$kelasMapel = $pdo->query("
    SELECT km.id, k.nama_kelas, k.tingkat, j.nama_jurusan,
           mp.nama_mapel, mp.kode_mapel, j2.nama_jurusan AS mapel_jurusan,
           u.nama AS guru_nama, ta.nama AS tahun_ajaran
    FROM kelas_mapel km
    JOIN kelas k ON k.id = km.kelas_id
    JOIN jurusan j ON j.id = k.jurusan_id
    JOIN mata_pelajaran mp ON mp.id = km.mapel_id
    JOIN jurusan j2 ON j2.id = mp.jurusan_id
    JOIN tendik t ON t.id = km.tendik_id
    JOIN users u ON u.id = t.user_id
    JOIN tahun_ajaran ta ON ta.id = km.tahun_ajaran_id
    ORDER BY ta.id DESC, k.nama_kelas, mp.nama_mapel
")->fetchAll();

$pageTitle = 'Jurusan & Mapel Siswa';
include __DIR__ . '/../includes/header.php';
?>
<h4>Jurusan & Mata Pelajaran Siswa</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Penempatan Kelas</div>
            <div class="card-body">
                <form method="post" action="kelas_mapel_siswa.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($edit['siswa_id']??0)==$s['id']?'selected':'' ?>><?= sanitize($s['nama']).' ('.$s['nis'].')' ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($edit['kelas_id']??0)==$k['id']?'selected':'' ?>>
                                    <?= sanitize($k['tingkat'].' '.$k['nama_kelas'].' - '.$k['nama_jurusan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select" required>
                            <?php foreach ($taList as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($edit['tahun_ajaran_id']??0)==$t['id']?'selected':'' ?>><?= sanitize($t['nama']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['aktif','pindah','lulus'] as $st): ?>
                                <option <?= ($edit['status']??'aktif')==$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" class="form-control" value="<?= $edit['tanggal_masuk'] ?? '' ?>"></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="kelas_mapel_siswa.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Daftar Penempatan Siswa</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Siswa</th><th>Kelas</th><th>Jurusan</th><th>Tahun</th><th>Status</th><th>Aksi</th><th>Jurusan Kelas</th>
<th>Jurusan Mapel</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['siswa_nama']).' ('.$r['nis'].')' ?></td>
                            <td><?= sanitize($r['tingkat'].' '.$r['nama_kelas']) ?></td>
                            <td><?= sanitize($r['nama_jurusan']) ?></td>
                            <td><?= sanitize($r['mapel_jurusan']) ?></td>
                            <td><?= sanitize($r['tahun_ajaran']) ?></td>
                            <td><?= $r['status'] ?></td>
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
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Mata Pelajaran Siswa (per Kelas)</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Kelas</th><th>Jurusan</th><th>Mapel</th><th>Guru</th><th>Tahun</th></tr></thead>
                    <tbody>
                    <?php foreach ($kelasMapel as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['tingkat'].' '.$r['nama_kelas']) ?></td>
                            <td><?= sanitize($r['nama_jurusan']) ?></td>
                            <td><?= sanitize($r['nama_mapel']) ?></td>
                            <td><?= sanitize($r['guru_nama']) ?></td>
                            <td><?= sanitize($r['tahun_ajaran']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>