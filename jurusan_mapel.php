<?php
// tendik/jurusan_mapel.php
require_once __DIR__ . '/../includes/auth.php';
requireKepsek();

// Query untuk mengambil semua data kelas_mapel, dikelompokkan berdasarkan jurusan dan kelas
$sql = "
    SELECT 
        j.id AS jurusan_id, 
        j.nama_jurusan, 
        k.tingkat, 
        k.nama_kelas, 
        mp.nama_mapel, 
        mp.kode_mapel,
        u.nama AS nama_guru,
        t.nip,
        ta.nama AS tahun_ajaran
    FROM kelas_mapel km
    JOIN kelas k ON k.id = km.kelas_id
    JOIN jurusan j ON j.id = k.jurusan_id
    JOIN mata_pelajaran mp ON mp.id = km.mapel_id
    JOIN tendik t ON t.id = km.tendik_id
    JOIN users u ON u.id = t.user_id
    JOIN tahun_ajaran ta ON ta.id = km.tahun_ajaran_id
    ORDER BY j.nama_jurusan ASC, k.tingkat ASC, k.nama_kelas ASC, mp.nama_mapel ASC
";
$data = $pdo->query($sql)->fetchAll();

// Kelompokkan data di PHP
$struktur = [];
foreach ($data as $row) {
    $jurusan = $row['nama_jurusan'];
    $kelas = $row['tingkat'] . ' ' . $row['nama_kelas'];
    
    $struktur[$jurusan][$kelas][] = $row;
}

$pageTitle = 'Struktur Jurusan & Mapel';
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Struktur Jurusan, Kelas & Mata Pelajaran</h4>
    <a href="<?= baseUrl('tendik/kelas_mapel_tendik.php') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Tambah Penugasan Baru
    </a>
</div>
<p class="text-muted">Halaman ini menampilkan daftar jurusan, kelas, mata pelajaran, dan tendik/guru yang mengampu.</p>

<?php if (empty($struktur)): ?>
    <div class="alert alert-info">Belum ada data penugasan tendik ke mata pelajaran.</div>
<?php endif; ?>

<div class="row">
    <?php foreach ($struktur as $namaJurusan => $listKelas): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-diagram-3"></i> Jurusan <?= sanitize($namaJurusan) ?>
                </div>
                <div class="card-body bg-light">
                    <?php foreach ($listKelas as $namaKelas => $listMapel): ?>
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-header bg-white fw-bold text-secondary">
                                <i class="bi bi-door-open"></i> Kelas <?= sanitize($namaKelas) ?>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($listMapel as $m): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= sanitize($m['nama_mapel']) ?></strong> 
                                            <span class="badge bg-secondary ms-1"><?= sanitize($m['kode_mapel']) ?></span>
                                        </div>
                                        <div class="text-end small">
                                            <i class="bi bi-person-circle text-primary"></i> <?= sanitize($m['nama_guru']) ?><br>
                                            <span class="text-muted">TA: <?= sanitize($m['tahun_ajaran']) ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>