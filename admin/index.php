<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT id, password_hash, status 
         FROM admins 
         WHERE username = ? 
         LIMIT 1"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password_hash'])) {

            if ($admin['status'] !== 'approved') {
                $error = "Akun Anda belum disetujui admin";
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_status'] = $admin['status'];
                header("Location: dashboard.php");
                exit;
            }

        } else {
            $error = "Username atau password salah";
        }
    } else {
        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Project Bonsai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="text-center mb-4">
            <h3 class="brand">Project <span>Bonsai</span></h3>
            <p class="login-subtitle">Admin Dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Username"
                    required
                >
            </div>

            <div class="mb-4">
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required
                >
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-login">
                    Login
                </button>
            </div>
        </form>

        <!-- LINK REGISTER -->
        <div class="text-center mt-3">
            <small class="text-muted">
                Belum punya akun?
                <a href="register.php" class="register-link">Daftar di sini</a>
            </small>
        </div>

        <div class="text-center mt-4 footer-text">
            © <?= date('Y') ?> Project Bonsai
        </div>

    </div>
</div>

</body>
</html>
