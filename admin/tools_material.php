<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error = '';

/* =========================
   CREATE & UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE
    if (isset($_POST['add_item'])) {
        $name     = trim($_POST['name']);
        $quantity = (int) $_POST['quantity'];

        if ($name && $quantity >= 0) {
            $stmt = $conn->prepare("
                INSERT INTO tools_materials (name, quantity)
                VALUES (?, ?)
            ");
            $stmt->bind_param("si", $name, $quantity);

            if ($stmt->execute()) {
                $success = "Data berhasil ditambahkan";
            } else {
                $error = "Gagal menambahkan data";
            }
        } else {
            $error = "Nama dan jumlah wajib diisi";
        }
    }

    // UPDATE
    if (isset($_POST['edit_item'])) {
        $id       = (int) $_POST['id'];
        $name     = trim($_POST['name']);
        $quantity = (int) $_POST['quantity'];

        if ($id && $name && $quantity >= 0) {
            $stmt = $conn->prepare("
                UPDATE tools_materials
                SET name = ?, quantity = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sii", $name, $quantity, $id);

            if ($stmt->execute()) {
                $success = "Data berhasil diperbarui";
            } else {
                $error = "Gagal memperbarui data";
            }
        }
    }
}

/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM tools_materials WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "Data berhasil dihapus";
    } else {
        $error = "Gagal menghapus data";
    }
}

/* =========================
   READ
========================= */
$items = $conn->query("
    SELECT * FROM tools_materials ORDER BY created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Manajemen Produk (Alat dan Bahan)</h3>

    <div class="row g-4">

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- FORM TAMBAH -->
<div class="card mb-4">
    <div class="card-header fw-semibold">
        Tambah Alat / Bahan
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">

            <div class="col-md-5">
                <input type="text" name="name" class="form-control"
                       placeholder="Nama alat / bahan" required>
            </div>

            <div class="col-md-4">
                <input type="number" name="quantity" class="form-control"
                       placeholder="Jumlah" min="0" required>
            </div>

            <div class="col-md-3">
                <button class="btn btn-success w-100" name="add_item">
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>

<!-- TABEL -->
<div class="card">
    <div class="card-header fw-semibold">
        Daftar Alat & Bahan
    </div>
    <div class="card-body table-responsive">

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($items->num_rows === 0): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data</td>
                </tr>
            <?php else: $no=1; while ($row = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <span class="badge bg-<?= $row['quantity'] > 0 ? 'success' : 'danger' ?>">
                            <?= $row['quantity'] ?>
                        </span>
                    </td>
                    <td>
                        <!-- EDIT -->
                        <button
                            class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#edit<?= $row['id'] ?>">
                            Edit
                        </button>

                        <!-- DELETE -->
                        <a href="?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Yakin ingin menghapus data ini?')"
                           class="btn btn-danger btn-sm">
                            Delete
                        </a>
                    </td>
                </tr>

                <!-- MODAL EDIT -->
                <div class="modal fade" id="edit<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Alat / Bahan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="name"
                                           class="form-control"
                                           value="<?= htmlspecialchars($row['name']) ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="quantity"
                                           class="form-control"
                                           min="0"
                                           value="<?= $row['quantity'] ?>"
                                           required>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-success" name="edit_item">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php endwhile; endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
