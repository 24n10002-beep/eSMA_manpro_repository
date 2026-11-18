-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 01:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_elearning_e_sma`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jabatan` varchar(100) DEFAULT 'Administrator'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `user_id`, `jabatan`) VALUES
(2, 3, 'Kepala Sekolah');

-- --------------------------------------------------------

--
-- Table structure for table `izin_siswa`
--

CREATE TABLE `izin_siswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis` enum('izin','sakit','cuti') NOT NULL,
  `alasan` text NOT NULL,
  `file_bukti` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `disetujui_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izin_tendik`
--

CREATE TABLE `izin_tendik` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tendik_id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis` enum('izin','sakit','cuti') NOT NULL,
  `alasan` text NOT NULL,
  `file_bukti` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `disetujui_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_jurusan` varchar(20) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id`, `kode_jurusan`, `nama_jurusan`, `keterangan`) VALUES
(1, '01', 'IPA', 'Ilmu Alam (Matematika, Fisika, Kimia, Biologi)'),
(2, '02', 'IPS', 'Ilmu Sosial (Sejarah, Sosiologi, Geografi dan Bahasa)');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jurusan_id` bigint(20) UNSIGNED NOT NULL,
  `nama_kelas` varchar(30) NOT NULL,
  `tingkat` enum('X','XI','XII') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas_mapel`
--

CREATE TABLE `kelas_mapel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kelas_id` bigint(20) UNSIGNED NOT NULL,
  `mapel_id` bigint(20) UNSIGNED NOT NULL,
  `tendik_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jurusan_id` bigint(20) UNSIGNED NOT NULL,
  `kode_mapel` varchar(20) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id`, `jurusan_id`, `kode_mapel`, `nama_mapel`, `deskripsi`) VALUES
(1, 1, 'FSK', 'Fisika', 'Mata Pelajaran Fisika - Mempelajari hukum alam'),
(2, 2, 'SOL', 'Sosiologi', 'Mata Pelajaran Ilmu Sosiologi - Mempelajari bagaimana manusia berinteraksi dan mengambil keputusan secara sosial dengan satu sama lain, serta gejala yang muncul'),
(3, 1, 'MTK', 'Matematika', 'Mata Pelajaran Ilmu Matematika - Ilmu Penghitungan dan pengukuran'),
(4, 2, 'MTS', 'Matematika untuk IPS', 'Mata Pelajaran Ilmu Matematika - Ilmu Penghitungan dan pengukuran Ilmu Sosial yang meliputi statistik, akuntansi dan sejenisnya');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `link_video` varchar(255) DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED NOT NULL,
  `nilai_tugas` decimal(5,2) DEFAULT 0.00,
  `nilai_uts` decimal(5,2) DEFAULT 0.00,
  `nilai_uas` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `semester` enum('ganjil','genap') NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan`
--

CREATE TABLE `pengumpulan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penilaian_id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `file_jawaban` varchar(255) DEFAULT NULL,
  `jawaban` text DEFAULT NULL,
  `tanggal_kumpul` datetime DEFAULT current_timestamp(),
  `status` enum('terkirim','terlambat','dinilai','revisi') DEFAULT 'terkirim',
  `nilai` decimal(5,2) DEFAULT NULL,
  `catatan_guru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED NOT NULL,
  `tendik_id` bigint(20) UNSIGNED NOT NULL,
  `jenis` enum('tugas','UTS','UAS') NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_soal` varchar(255) DEFAULT NULL,
  `tanggal_mulai` datetime DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `bobot` decimal(5,2) DEFAULT 0.00,
  `status` enum('draft','aktif','ditutup') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_modul` varchar(100) NOT NULL,
  `can_create` tinyint(1) DEFAULT 0,
  `can_read` tinyint(1) DEFAULT 0,
  `can_update` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `nama_modul`, `can_create`, `can_read`, `can_update`, `can_delete`) VALUES
(1, 'admin', 0, 0, 0, 0),
(2, 'tendik', 0, 0, 0, 0),
(3, 'siswa', 0, 0, 0, 0),
(4, 'jurusan', 0, 0, 0, 0),
(5, 'kelas', 0, 0, 0, 0),
(6, 'mata_pelajaran', 0, 0, 0, 0),
(7, 'tahun_ajaran', 0, 0, 0, 0),
(8, 'siswa_kelas', 0, 0, 0, 0),
(9, 'kelas_mapel', 0, 0, 0, 0),
(10, 'materi', 0, 0, 0, 0),
(11, 'penilaian', 0, 0, 0, 0),
(12, 'pengumpulan', 0, 0, 0, 0),
(13, 'nilai', 0, 0, 0, 0),
(14, 'presensi_siswa', 0, 0, 0, 0),
(15, 'izin_siswa', 0, 0, 0, 0),
(16, 'presensi_tendik', 0, 0, 0, 0),
(17, 'izin_tendik', 0, 0, 0, 0),
(18, 'laporan', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `presensi_siswa`
--

CREATE TABLE `presensi_siswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `pertemuan` int(11) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpa') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dicatat_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presensi_tendik`
--

CREATE TABLE `presensi_tendik` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tendik_id` bigint(20) UNSIGNED NOT NULL,
  `kelas_mapel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpa') NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('admin','tendik','siswa') NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `can_create` tinyint(1) DEFAULT 0,
  `can_read` tinyint(1) DEFAULT 0,
  `can_update` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`, `can_create`, `can_read`, `can_update`, `can_delete`) VALUES
(1, 'admin', 1, 1, 1, 1, 1),
(2, 'admin', 15, 1, 1, 1, 1),
(3, 'admin', 17, 1, 1, 1, 1),
(4, 'admin', 4, 1, 1, 1, 1),
(5, 'admin', 5, 1, 1, 1, 1),
(6, 'admin', 9, 1, 1, 1, 1),
(7, 'admin', 18, 1, 1, 1, 1),
(8, 'admin', 6, 1, 1, 1, 1),
(9, 'admin', 10, 1, 1, 1, 1),
(10, 'admin', 13, 1, 1, 1, 1),
(11, 'admin', 12, 1, 1, 1, 1),
(12, 'admin', 11, 1, 1, 1, 1),
(13, 'admin', 14, 1, 1, 1, 1),
(14, 'admin', 16, 1, 1, 1, 1),
(15, 'admin', 3, 1, 1, 1, 1),
(16, 'admin', 8, 1, 1, 1, 1),
(17, 'admin', 7, 1, 1, 1, 1),
(18, 'admin', 2, 1, 1, 1, 1),
(33, 'tendik', 15, 1, 1, 1, 0),
(34, 'tendik', 10, 1, 1, 1, 0),
(35, 'tendik', 13, 1, 1, 1, 0),
(36, 'tendik', 12, 1, 1, 1, 0),
(37, 'tendik', 11, 1, 1, 1, 0),
(38, 'tendik', 14, 1, 1, 1, 0),
(40, 'siswa', 10, 0, 1, 0, 0),
(41, 'siswa', 13, 0, 1, 0, 0),
(42, 'siswa', 11, 0, 1, 0, 0),
(43, 'siswa', 14, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nisn` varchar(30) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nama_ortu` varchar(150) DEFAULT NULL,
  `no_hp_ortu` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `user_id`, `nis`, `nisn`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `nama_ortu`, `no_hp_ortu`) VALUES
(1, 4, '26010001', '0010121234', 'L', 'Semarang', '2010-01-05', 'NAUVAL IV/NO.67', 'Daniel Liem', '0989898989898');

-- --------------------------------------------------------

--
-- Table structure for table `siswa_kelas`
--

CREATE TABLE `siswa_kelas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `kelas_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `status` enum('aktif','pindah','lulus') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(20) NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','selesai') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tendik`
--

CREATE TABLE `tendik` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tendik`
--

INSERT INTO `tendik` (`id`, `user_id`, `nip`, `jabatan`, `jenis_kelamin`, `alamat`) VALUES
(1, 5, '0021234564', 'Pengajar', 'L', 'AYAM X/NO. 17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','tendik','siswa') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `no_hp`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Kepala Sekolah SMA', '01234567891', '$2y$10$BGlWQUf0mXq/1NtIhPrBzOsqjBfDFOGYILSo2fbDMxj0CiTJdyM56', 'admin', 'aktif', '2026-11-17 11:26:10', '2026-11-17 11:26:10'),
(4, 'Nauval Liem', '0898989898989', '$2y$10$H.G70pbBTCnXSHV1x35nSukQEVxnFWc/JuOjO/8F8ytozNuzQ.qzi', 'siswa', 'aktif', '2026-11-17 11:41:24', '2026-11-17 11:42:28'),
(5, 'Daniel Rahmantoro', '054321987654', '$2y$10$izWFl78mXvGcaeCG4r/si.QXNwCAEKz0uJEc2o8McqZtULYWtCz4a', 'tendik', 'aktif', '2026-11-17 11:52:42', '2026-11-17 11:52:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `izin_siswa`
--
ALTER TABLE `izin_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`),
  ADD KEY `disetujui_oleh` (`disetujui_oleh`);

--
-- Indexes for table `izin_tendik`
--
ALTER TABLE `izin_tendik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tendik_id` (`tendik_id`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`),
  ADD KEY `disetujui_oleh` (`disetujui_oleh`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jurusan` (`kode_jurusan`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jurusan_id` (`jurusan_id`);

--
-- Indexes for table `kelas_mapel`
--
ALTER TABLE `kelas_mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelas_id` (`kelas_id`,`mapel_id`,`tahun_ajaran_id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `tendik_id` (`tendik_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indexes for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kode_jurusan` (`kode_mapel`,`jurusan_id`),
  ADD KEY `jurusan_id` (`jurusan_id`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_id` (`siswa_id`,`kelas_mapel_id`,`semester`,`tahun_ajaran_id`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indexes for table `pengumpulan`
--
ALTER TABLE `pengumpulan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penilaian_id` (`penilaian_id`,`siswa_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`),
  ADD KEY `tendik_id` (`tendik_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_modul` (`nama_modul`);

--
-- Indexes for table `presensi_siswa`
--
ALTER TABLE `presensi_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelas_mapel_id` (`kelas_mapel_id`,`siswa_id`,`tanggal`,`pertemuan`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`);

--
-- Indexes for table `presensi_tendik`
--
ALTER TABLE `presensi_tendik`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tendik_id` (`tendik_id`,`kelas_mapel_id`,`tanggal`),
  ADD KEY `kelas_mapel_id` (`kelas_mapel_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role` (`role`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `nisn` (`nisn`);

--
-- Indexes for table `siswa_kelas`
--
ALTER TABLE `siswa_kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_id` (`siswa_id`,`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tendik`
--
ALTER TABLE `tendik`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_hp` (`no_hp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `izin_siswa`
--
ALTER TABLE `izin_siswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `izin_tendik`
--
ALTER TABLE `izin_tendik`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas_mapel`
--
ALTER TABLE `kelas_mapel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumpulan`
--
ALTER TABLE `pengumpulan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `presensi_siswa`
--
ALTER TABLE `presensi_siswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presensi_tendik`
--
ALTER TABLE `presensi_tendik`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa_kelas`
--
ALTER TABLE `siswa_kelas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tendik`
--
ALTER TABLE `tendik`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `izin_siswa`
--
ALTER TABLE `izin_siswa`
  ADD CONSTRAINT `izin_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `izin_siswa_ibfk_2` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`),
  ADD CONSTRAINT `izin_siswa_ibfk_3` FOREIGN KEY (`disetujui_oleh`) REFERENCES `tendik` (`id`);

--
-- Constraints for table `izin_tendik`
--
ALTER TABLE `izin_tendik`
  ADD CONSTRAINT `izin_tendik_ibfk_1` FOREIGN KEY (`tendik_id`) REFERENCES `tendik` (`id`),
  ADD CONSTRAINT `izin_tendik_ibfk_2` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`),
  ADD CONSTRAINT `izin_tendik_ibfk_3` FOREIGN KEY (`disetujui_oleh`) REFERENCES `admin` (`id`);

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`);

--
-- Constraints for table `kelas_mapel`
--
ALTER TABLE `kelas_mapel`
  ADD CONSTRAINT `kelas_mapel_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `kelas_mapel_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`),
  ADD CONSTRAINT `kelas_mapel_ibfk_3` FOREIGN KEY (`tendik_id`) REFERENCES `tendik` (`id`),
  ADD CONSTRAINT `kelas_mapel_ibfk_4` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Constraints for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`);

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`),
  ADD CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Constraints for table `pengumpulan`
--
ALTER TABLE `pengumpulan`
  ADD CONSTRAINT `pengumpulan_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian` (`id`),
  ADD CONSTRAINT `pengumpulan_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`tendik_id`) REFERENCES `tendik` (`id`);

--
-- Constraints for table `presensi_siswa`
--
ALTER TABLE `presensi_siswa`
  ADD CONSTRAINT `presensi_siswa_ibfk_1` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`),
  ADD CONSTRAINT `presensi_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `presensi_siswa_ibfk_3` FOREIGN KEY (`dicatat_oleh`) REFERENCES `tendik` (`id`);

--
-- Constraints for table `presensi_tendik`
--
ALTER TABLE `presensi_tendik`
  ADD CONSTRAINT `presensi_tendik_ibfk_1` FOREIGN KEY (`tendik_id`) REFERENCES `tendik` (`id`),
  ADD CONSTRAINT `presensi_tendik_ibfk_2` FOREIGN KEY (`kelas_mapel_id`) REFERENCES `kelas_mapel` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `siswa_kelas`
--
ALTER TABLE `siswa_kelas`
  ADD CONSTRAINT `siswa_kelas_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `siswa_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `siswa_kelas_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Constraints for table `tendik`
--
ALTER TABLE `tendik`
  ADD CONSTRAINT `tendik_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
