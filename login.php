<?php
// auth/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['kepsek_id'])) redirect(baseUrl('dashboard.php'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT u.*, a.jabatan FROM users u
                           INNER JOIN admin a ON a.user_id = u.id
                           WHERE u.no_hp = ? AND u.role = 'admin' AND u.status = 'aktif'");
    $stmt->execute([$no_hp]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['kepsek_id'] = $user['id'];
        $_SESSION['kepsek_nama'] = $user['nama'];
        $_SESSION['kepsek_role'] = 'admin';
        $_SESSION['kepsek_jabatan'] = $user['jabatan'];
        flash('success', 'Selamat datang, ' . $user['nama']);
        redirect(baseUrl('dashboard.php'));
    } else {
        $error = 'No HP atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Kepala Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h4 class="text-center mb-1"><i class="bi bi-mortarboard-fill text-primary"></i> Kepsek eSMA</h4>
                    <p class="text-center text-muted mb-4">Login Kepala Sekolah</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= sanitize($error) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="text-center mt-3 mb-0 small">
                        Belum punya akun? <a href="<?= baseUrl('auth/register.php') ?>">Daftar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>