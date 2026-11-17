<?php
// dashboard.php
require_once __DIR__ . '/includes/auth.php';
requireKepsek();

$stats = [];
$stats['siswa']     = (int)$pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$stats['tendik']    = (int)$pdo->query("SELECT COUNT(*) FROM tendik")->fetchColumn();
$stats['jurusan']   = (int)$pdo->query("SELECT COUNT(*) FROM jurusan")->fetchColumn();
$stats['mapel']     = (int)$pdo->query("SELECT COUNT(*) FROM mata_pelajaran")->fetchColumn();
$stats['kelas']     = (int)$pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$stats['penilaian'] = (int)$pdo->query("SELECT COUNT(*) FROM penilaian")->fetchColumn();

$izinSiswaPending = (int)$pdo->query("SELECT COUNT(*) FROM izin_siswa WHERE status='menunggu'")->fetchColumn();
$izinTendikPending = (int)$pdo->query("SELECT COUNT(*) FROM izin_tendik WHERE status='menunggu'")->fetchColumn();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Dashboard Kepala Sekolah</h4>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Siswa', $stats['siswa'], 'bi-people', 'primary'],
        ['Tendik', $stats['tendik'], 'bi-person-badge', 'success'],
        ['Jurusan', $stats['jurusan'], 'bi-diagram-3', 'warning'],
        ['Mata Pelajaran', $stats['mapel'], 'bi-book', 'info'],
        ['Kelas', $stats['kelas'], 'bi-door-open', 'secondary'],
        ['Penilaian', $stats['penilaian'], 'bi-clipboard-check', 'dark'],
    ];
    foreach ($cards as [$label, $val, $icon, $color]): ?>
        <div class="col-md-4 col-lg-2">
            <div class="card shadow-sm border-<?= $color ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small"><?= $label ?></div>
                            <div class="fs-3 fw-bold"><?= $val ?></div>
                        </div>
                        <i class="bi <?= $icon ?> fs-1 text-<?= $color ?> opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Permohonan Izin Siswa Menunggu</div>
            <div class="card-body">
                <h2 class="text-warning"><?= $izinSiswaPending ?></h2>
                <a href="<?= baseUrl('siswa/cuti.php') ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Permohonan Izin Tendik Menunggu</div>
            <div class="card-body">
                <h2 class="text-warning"><?= $izinTendikPending ?></h2>
                <a href="<?= baseUrl('tendik/cuti.php') ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>