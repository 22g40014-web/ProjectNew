<?php
require_once 'auth.php';
require_once 'config/db.php';

// =====================
// TAMBAH PRODUK
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {

    $category_id = (int)$_POST['category_id'];
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stock       = (int)$_POST['stock'];
    $price_buy   = (float)$_POST['price_buy'];
    $price_sell  = (float)$_POST['price_sell'];

    $conn->begin_transaction();

    try {
        // INSERT PRODUCT
        $stmt = $conn->prepare("
            INSERT INTO products
            (category_id, name, description, stock, price_buy, price_sell, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param(
            "issidd",
            $category_id,
            $name,
            $description,
            $stock,
            $price_buy,
            $price_sell
        );
        $stmt->execute();
        $product_id = $stmt->insert_id;
        $stmt->close();

        // =====================
        // UPLOAD GAMBAR
        // =====================
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('product_') . '.' . $ext;
            $path = 'uploads/products/' . $filename;

            move_uploaded_file($_FILES['image']['tmp_name'], $path);

            $img = $conn->prepare("
                INSERT INTO product_images (product_id, image)
                VALUES (?, ?)
            ");
            $img->bind_param("is", $product_id, $path);
            $img->execute();
            $img->close();
        }

        $conn->commit();
        header("Location: products.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// =====================
// DATA
// =====================
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$products = $conn->query("
    SELECT 
        p.*, 
        c.name AS category_name,
        pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    ORDER BY p.created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">

<h3 class="mb-4">Manajemen Produk</h3>

<!-- ================= FORM ================= -->
<div class="card mb-4">
    <div class="card-header fw-semibold">Tambah Produk</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while($c=$categories->fetch_assoc()): ?>
                        <option value="<?= $c['id']; ?>">
                            <?= htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Harga Beli</label>
                <input type="number" step="0.01" name="price_buy" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Harga Jual</label>
                <input type="number" step="0.01" name="price_sell" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Gambar Produk</label>
                <input type="file" name="image" class="form-control">
            </div>

            <button type="submit" name="add_product" class="btn btn-success">
                Simpan Produk
            </button>


        </form>
    </div>
</div>

<!-- ================= TABLE ================= -->
<div class="card">
<div class="card-header fw-semibold">Daftar Produk</div>
<div class="card-body table-responsive">

<table class="table table-bordered table-hover align-middle">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Produk</th>
    <th>Kategori</th>
    <th>Stock</th>
    <th>Harga Jual</th>
    <th>Gambar</th>
</tr>
</thead>
<tbody>
<?php $no=1; while($p=$products->fetch_assoc()): ?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($p['name']); ?></td>
    <td><?= htmlspecialchars($p['category_name']); ?></td>
    <td><?= $p['stock']; ?></td>
    <td>Rp <?= number_format($p['price_sell'],2); ?></td>
    <td class="text-center">
        <?php if ($p['image']): ?>
            <button 
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#img<?= $p['id']; ?>">
                Lihat
            </button>

            <!-- MODAL -->
            <div class="modal fade" id="img<?= $p['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($p['name']); ?></h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="<?= $p['image']; ?>" class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <span class="text-muted">Tidak ada</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

</div>

<?php include 'partials/footer.php'; ?>
