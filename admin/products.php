<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error = '';

/* =========================
   HANDLE TAMBAH PRODUK
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $quantity    = (int) $_POST['quantity'];
    $description = trim($_POST['description']);

    if ($name && $category_id && $quantity >= 0) {

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO products (category_id, name, description)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iss", $category_id, $name, $description);
            $stmt->execute();

            $product_id = $stmt->insert_id;

            $stmtStock = $conn->prepare("
                INSERT INTO product_stock (product_id, quantity)
                VALUES (?, ?)
            ");
            $stmtStock->bind_param("ii", $product_id, $quantity);
            $stmtStock->execute();

            $conn->commit();
            $success = "Produk berhasil ditambahkan";

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal menambahkan produk";
        }
    } else {
        $error = "Semua field wajib diisi";
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$products = $conn->query("
    SELECT p.name, c.name AS category, ps.quantity
    FROM products p
    JOIN categories c ON c.id = p.category_id
    JOIN product_stock ps ON ps.product_id = p.id
    ORDER BY p.created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Manajemen Produk</h3>

    <div class="row g-4">

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header fw-semibold">
        Tambah Produk Baru
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Stok Awal</label>
                <input type="number" name="quantity" class="form-control" min="0" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-md-12">
                <button class="btn btn-success">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">
        Daftar Produk
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows === 0): ?>
                    <tr>
                        <td colspan="4" class="text-center">Belum ada produk</td>
                    </tr>
                <?php else: $no=1; while($p=$products->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>
                            <span class="badge bg-<?= $p['quantity'] > 0 ? 'success' : 'danger' ?>">
                                <?= $p['quantity'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

    </div>
</div>

<?php include 'partials/footer.php'; ?>
