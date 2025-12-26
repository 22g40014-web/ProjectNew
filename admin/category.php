<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error = '';

function slugify($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/* =========================
   CREATE & UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $slug = slugify($name);

        if ($name) {
            $check = $conn->prepare("
                SELECT id FROM categories WHERE name = ? OR slug = ?
            ");
            $check->bind_param("ss", $name, $slug);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                header("Location: category.php?error=exists");
                exit;
            }

            $stmt = $conn->prepare("
                INSERT INTO categories (name, slug) VALUES (?, ?)
            ");
            $stmt->bind_param("ss", $name, $slug);

            if ($stmt->execute()) {
                header("Location: category.php?success=added");
                exit;
            }

            header("Location: category.php?error=add");
            exit;
        }
    }

    // UPDATE
    if (isset($_POST['edit_category'])) {
        $id   = (int) $_POST['id'];
        $name = trim($_POST['name']);
        $slug = slugify($name);

        $stmt = $conn->prepare("
            UPDATE categories SET name = ?, slug = ? WHERE id = ?
        ");
        $stmt->bind_param("ssi", $name, $slug, $id);

        if ($stmt->execute()) {
            header("Location: category.php?success=updated");
            exit;
        }

        header("Location: category.php?error=edit");
        exit;
    }
}



/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $check = $conn->prepare("
        SELECT COUNT(*) AS total FROM products WHERE category_id = ?
    ");
    $check->bind_param("i", $id);
    $check->execute();
    $used = $check->get_result()->fetch_assoc()['total'];

    if ($used > 0) {
        header("Location: category.php?error=used");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: category.php?success=deleted");
    exit;
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
                <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#edit<?= $cat['id'] ?>">
                    Edit
                </button>

                <a href="?delete=<?= $cat['id'] ?>"
                onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                class="btn btn-danger btn-sm">
                    Delete
                </a>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
        </table>

        <?php
$categories->data_seek(0);
while ($cat = $categories->fetch_assoc()):
?>
<div class="modal fade" id="edit<?= $cat['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <input type="text"
                       name="name"
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
<?php endwhile; ?>


        </table>

    </div>
</div>
    </div>
</div>

<?php
$message = '';
$type    = 'success';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added')   $message = 'Kategori berhasil ditambahkan';
    if ($_GET['success'] === 'updated') $message = 'Kategori berhasil diperbarui';
    if ($_GET['success'] === 'deleted') $message = 'Kategori berhasil dihapus';
}

if (isset($_GET['error'])) {
    $type = 'danger';
    if ($_GET['error'] === 'exists') $message = 'Kategori sudah ada';
    if ($_GET['error'] === 'used')   $message = 'Kategori sedang digunakan produk';
    if ($_GET['error'] === 'add')    $message = 'Gagal menambahkan kategori';
    if ($_GET['error'] === 'edit')   $message = 'Gagal memperbarui kategori';
}

?>

<?php if ($message): ?>
<div class="modal fade show" style="display:block;" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-<?= $type ?> text-white">
                <h5 class="modal-title">
                    <?= $type === 'success' ? 'Berhasil' : 'Gagal' ?>
                </h5>
            </div>
            <div class="modal-body text-center">
                <p><?= htmlspecialchars($message) ?></p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="category.php" class="btn btn-secondary">OK</a>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>