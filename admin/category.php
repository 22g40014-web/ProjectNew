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
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);

        if ($name) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);

            if ($stmt->execute()) {
                $success = "Kategori berhasil ditambahkan";
            } else {
                $error = "Gagal menambahkan kategori";
            }
        } else {
            $error = "Nama kategori wajib diisi";
        }
    }

    // UPDATE
    if (isset($_POST['edit_category'])) {
        $id   = (int) $_POST['id'];
        $name = trim($_POST['name']);

        if ($id && $name) {
            $stmt = $conn->prepare("
                UPDATE categories SET name = ? WHERE id = ?
            ");
            $stmt->bind_param("si", $name, $id);

            if ($stmt->execute()) {
                $success = "Kategori berhasil diperbarui";
            } else {
                $error = "Gagal memperbarui kategori";
            }
        }
    }
}

/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Cek apakah kategori dipakai produk
    $check = $conn->prepare("
        SELECT COUNT(*) AS total FROM products WHERE category_id = ?
    ");
    $check->bind_param("i", $id);
    $check->execute();
    $used = $check->get_result()->fetch_assoc()['total'];

    if ($used > 0) {
        $error = "Kategori tidak bisa dihapus karena sedang digunakan produk";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $success = "Kategori berhasil dihapus";
        } else {
            $error = "Gagal menghapus kategori";
        }
    }
}

/* =========================
   READ
========================= */
$categories = $conn->query("
    SELECT * FROM categories ORDER BY name ASC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Manajemen Kategori Produk</h3>

    <div class="row g-4">

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- FORM TAMBAH -->
<div class="card mb-4">
    <div class="card-header fw-semibold">Tambah Kategori</div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Nama kategori" required>
            </div>
            <div class="col-md-6">
                <button class="btn btn-success" name="add_category">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL -->
<div class="card">
    <div class="card-header fw-semibold">Daftar Kategori</div>
    <div class="card-body table-responsive">

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($categories->num_rows === 0): ?>
                <tr>
                    <td colspan="3" class="text-center">Belum ada kategori</td>
                </tr>
            <?php else: $no=1; while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td>
                        <!-- EDIT -->
                        <button
                            class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#edit<?= $cat['id'] ?>">
                            Edit
                        </button>

                        <!-- DELETE -->
                        <a
                            href="?delete=<?= $cat['id'] ?>"
                            onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                            class="btn btn-danger btn-sm">
                            Delete
                        </a>
                    </td>
                </tr>

                <!-- MODAL EDIT -->
                <div class="modal fade" id="edit<?= $cat['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Kategori</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <input type="text" name="name"
                                    class="form-control"
                                    value="<?= htmlspecialchars($cat['name']) ?>"
                                    required>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-success" name="edit_category">
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
