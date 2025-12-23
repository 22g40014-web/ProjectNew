<?php
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (username, password_hash, status)
            VALUES (?, ?, 'pending')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $message = "Registrasi berhasil. Menunggu persetujuan admin.";
    } else {
        $message = "Gagal daftar: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Admin | Project Bonsai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS (SAMA dengan login) -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="text-center mb-4">
            <h3 class="brand">Project <span>Bonsai</span></h3>
            <p class="login-subtitle">Register Admin</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info text-center">
                <?= htmlspecialchars($message) ?>
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

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-login">
                    Register
                </button>

                <a href="index.php" class="btn btn-outline-secondary">
                    Kembali ke Login
                </a>
            </div>
        </form>

        <div class="text-center mt-4 footer-text">
            © <?= date('Y') ?> Project Bonsai
        </div>

    </div>
</div>

</body>
</html>
