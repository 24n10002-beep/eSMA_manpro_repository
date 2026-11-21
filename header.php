<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Kepala Sekolah - eSMA' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= baseUrl('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= baseUrl('dashboard.php') ?>">
            <i class="bi bi-mortarboard-fill"></i> Kepsek eSMA
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage=='dashboard.php'?'active':'' ?>" href="<?= baseUrl('dashboard.php') ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-people"></i> Manajemen Siswa
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/jurusan.php') ?>">Data Jurusan</a></li>
                        
                        <!-- TAMBAHAN MENU KELAS -->
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/kelas.php') ?>">Data Kelas</a></li>
                        
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/mapel.php') ?>">Data Mata Pelajaran</a></li>
                        
                        <!-- TAMBAHAN MENU TAHUN AJARAN -->
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/tahun_ajaran.php') ?>">Tahun Ajaran</a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/data_siswa.php') ?>">Data Siswa</a></li>
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/kelas_mapel_siswa.php') ?>">Jurusan & Mapel Siswa</a></li>
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/pengumpulan.php') ?>">Pengumpulan UTS/UAS</a></li>
                        <li><a class="dropdown-item" href="<?= baseUrl('siswa/presensi_siswa.php') ?>">Presensi Siswa</a></li>
<li><a class="dropdown-item" href="<?= baseUrl('siswa/cuti_siswa.php') ?>">Cuti/Izin Siswa</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-person-badge"></i> Manajemen Tendik
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="<?= baseUrl('tendik/data_tendik.php') ?>">Data Tendik</a></li>
        <li><a class="dropdown-item" href="<?= baseUrl('tendik/kelas_mapel_tendik.php') ?>">Penugasan Tendik (Input)</a></li>
        
        <!-- MENU BARU DI SINI -->
        <li><a class="dropdown-item" href="<?= baseUrl('tendik/jurusan_mapel.php') ?>">Struktur Jurusan & Mapel</a></li>
        
        <li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="<?= baseUrl('tendik/presensi_tendik.php') ?>">Presensi Tendik</a></li>
<li><a class="dropdown-item" href="<?= baseUrl('tendik/cuti_tendik.php') ?>">Cuti/Izin Tendik</a></li>
    </ul>
</li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        <i class="bi bi-person-circle"></i> <?= sanitize($_SESSION['kepsek_nama'] ?? '') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= baseUrl('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid py-4">
<?php if ($m = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= sanitize($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($m = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= sanitize($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>