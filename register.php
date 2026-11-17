<?php
// auth/register.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php'; 

if (isset($_SESSION['kepsek_id'])) redirect(baseUrl('dashboard.php'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $jabatan = trim($_POST['jabatan'] ?? 'Kepala Sekolah');

    if ($nama === '' || $no_hp === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek apakah sudah ada admin (kepsek) terdaftar
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin");
        $adminCount = (int)$stmt->fetchColumn();
        if ($adminCount > 0) {
            $error = 'Akun kepala sekolah sudah terdaftar. Silakan login.';
        } else {
            $cek = $pdo->prepare("SELECT id FROM users WHERE no_hp = ?");
            $cek->execute([$no_hp]);
            if ($cek->fetch()) {
                $error = 'No HP sudah digunakan.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $pdo->prepare("INSERT INTO users (nama, no_hp, password, role, status) VALUES (?, ?, ?, 'admin', 'aktif')");
                    $ins->execute([$nama, $no_hp, $hash]);
                    $userId = $pdo->lastInsertId();

                    $insAdmin = $pdo->prepare("INSERT INTO admin (user_id, jabatan) VALUES (?, ?)");
                    $insAdmin->execute([$userId, $jabatan]);

                    $pdo->commit();
                    flash('success', 'Pendaftaran berhasil. Silakan login.');
                    redirect(baseUrl('auth/login.php'));
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Gagal mendaftar: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Kepala Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h4 class="text-center mb-1"><i class="bi bi-mortarboard-fill text-primary"></i> Kepsek eSMA</h4>
                    <p class="text-center text-muted mb-4">Pendaftaran Akun Kepala Sekolah</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= sanitize($error) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No HP (untuk login)</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="Kepala Sekolah">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Daftar</button>
                    </form>
                    <p class="text-center mt-3 mb-0 small">
                        Sudah punya akun? <a href="<?= baseUrl('auth/login.php') ?>">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>