<?php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add';
    $kelas = (int)$_POST['kelas_id'];
    $mapel = (int)$_POST['mapel_id'];
    $tendik = (int)$_POST['tendik_id'];
    $ta = (int)$_POST['tahun_ajaran_id'];
    try {
        // VALIDASI: pastikan mapel milik jurusan yang sama dengan kelas
        $cek = $pdo->prepare("
            SELECT COUNT(*) FROM kelas k
            JOIN mata_pelajaran mp ON mp.jurusan_id = k.jurusan_id
            WHERE k.id = ? AND mp.id = ?
        ");
        $cek->execute([$kelas, $mapel]);
        if ((int)$cek->fetchColumn() === 0) {
            throw new Exception('Mata pelajaran tidak sesuai dengan jurusan kelas.');
        }

        if ($mode === 'edit' && $id) {
            $pdo->prepare("UPDATE kelas_mapel SET kelas_id=?, mapel_id=?, tendik_id=?, tahun_ajaran_id=? WHERE id=?")
                ->execute([$kelas, $mapel, $tendik, $ta, $id]);
            flash('success','Data diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO kelas_mapel (kelas_id, mapel_id, tendik_id, tahun_ajaran_id) VALUES (?,?,?,?)")
                ->execute([$kelas, $mapel, $tendik, $ta]);
            flash('success','Data ditambahkan.');
        }
        redirect('kelas_mapel_tendik.php');
    } catch (Exception $e) {
        flash('error','Gagal: '.$e->getMessage());
    }
}


if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM kelas_mapel WHERE id=?")->execute([$id]); flash('success','Dihapus.'); }
    catch (Exception $e) { flash('error','Gagal: '.$e->getMessage()); }
    redirect('kelas_mapel_tendik.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM kelas_mapel WHERE id=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$kelasList = $pdo->query("
    SELECT k.id, k.nama_kelas, k.tingkat, j.nama_jurusan, k.jurusan_id
    FROM kelas k JOIN jurusan j ON j.id = k.jurusan_id
    ORDER BY k.nama_kelas
")->fetchAll();

$mapelList = $pdo->query("
    SELECT mp.*, j.nama_jurusan
    FROM mata_pelajaran mp
    JOIN jurusan j ON j.id = mp.jurusan_id
    ORDER BY j.nama_jurusan, mp.nama_mapel
")->fetchAll();
$tendikList = $pdo->query("SELECT t.id, u.nama, t.nip FROM tendik t JOIN users u ON u.id=t.user_id ORDER BY u.nama")->fetchAll();
$taList = $pdo->query("SELECT * FROM tahun_ajaran ORDER BY id DESC")->fetchAll();

$list = $pdo->query("
    SELECT km.*, k.nama_kelas, k.tingkat, j.nama_jurusan, mp.nama_mapel, u.nama AS tendik_nama, ta.nama AS tahun_ajaran
    FROM kelas_mapel km
    JOIN kelas k ON k.id=km.kelas_id
    JOIN jurusan j ON j.id=k.jurusan_id
    JOIN mata_pelajaran mp ON mp.id=km.mapel_id
    JOIN tendik t ON t.id=km.tendik_id
    JOIN users u ON u.id=t.user_id
    JOIN tahun_ajaran ta ON ta.id=km.tahun_ajaran_id
    ORDER BY ta.id DESC, k.nama_kelas, mp.nama_mapel
")->fetchAll();

$pageTitle='Jurusan & Mapel Tendik';
include __DIR__ . '/../includes/header.php';
?>
<h4>Jurusan & Mata Pelajaran Tendik</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold"><?= $edit?'Edit':'Tambah' ?> Penugasan</div>
            <div class="card-body">
                <form method="post" action="kelas_mapel_tendik.php<?= $edit?'?id='.$edit['id']:'' ?>">
                    <input type="hidden" name="mode" value="<?= $edit?'edit':'add' ?>">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="mb-2"><label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($edit['kelas_id']??0)==$k['id']?'selected':'' ?>>
                                    <?= sanitize($k['tingkat'].' '.$k['nama_kelas'].' - '.$k['nama_jurusan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
    <?php
    $grouped = [];
    foreach ($mapelList as $m) {
        $grouped[$m['nama_jurusan']][] = $m;
    }
    foreach ($grouped as $jurusanNama => $items): ?>
        <optgroup label="<?= sanitize($jurusanNama) ?>">
            <?php foreach ($items as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($edit['mapel_id']??0)==$m['id']?'selected':'' ?>>
                    <?= sanitize($m['nama_mapel']) ?> (<?= sanitize($m['kode_mapel']) ?>)
                </option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select></div>
                    <div class="mb-2"><label class="form-label">Tendik / Guru</label>
                        <select name="tendik_id" class="form-select" required>
                            <?php foreach ($tendikList as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($edit['tendik_id']??0)==$t['id']?'selected':'' ?>><?= sanitize($t['nama']).' ('.$t['nip'].')' ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-2"><label class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select" required>
                            <?php foreach ($taList as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($edit['tahun_ajaran_id']??0)==$t['id']?'selected':'' ?>><?= sanitize($t['nama']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <button class="btn btn-primary">Simpan</button>
                    <?php if ($edit): ?><a href="kelas_mapel_tendik.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Penugasan Tendik</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Kelas</th><th>Jurusan</th><th>Mapel</th><th>Tendik</th><th>Tahun</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= sanitize($r['tingkat'].' '.$r['nama_kelas']) ?></td>
                            <td><?= sanitize($r['nama_jurusan']) ?></td>
                            <td><?= sanitize($r['nama_mapel']) ?></td>
                            <td><?= sanitize($r['tendik_nama']) ?></td>
                            <td><?= sanitize($r['tahun_ajaran']) ?></td>
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