<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
require_once 'config/db.php';

// Approve / Reject
if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];
    $conn->query("UPDATE admins SET status='approved' WHERE id=$id");
}

if (isset($_GET['reject'])) {
    $id = (int) $_GET['reject'];
    $conn->query("UPDATE admins SET status='rejected' WHERE id=$id");
}

// Ambil admin pending
$result = $conn->query("SELECT id, username, created_at FROM admins WHERE status='pending'");
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Approval Admin</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php if ($result->num_rows === 0): ?>
                <p class="text-muted">Tidak ada admin pending.</p>
            <?php else: ?>

            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                Approve
                            </a>
                            <a href="?reject=<?= $row['id'] ?>" class="btn btn-danger btn-sm">
                                Reject
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
