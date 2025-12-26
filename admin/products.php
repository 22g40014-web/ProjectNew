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
    //$stock       = (int)$_POST['stock'];
    $stock = isset($_POST['stock']) && $_POST['stock'] !== ''
    ? (int) $_POST['stock']
    : null;
    $price_buy   = (float)$_POST['price_buy'];
    $price_sell  = (float)$_POST['price_sell'];
    //$is_active = ($stock > 0) ? 1 : 0;
    $stock = (int)$_POST['stock'];
    // =====================
    // LOGIC STATUS AKTIF
    // =====================
    $is_active = ($stock > 0) ? 1 : 0;
    
    $conn->begin_transaction();

    try {

        // INSERT PRODUCT
        $stmt = $conn->prepare("
            INSERT INTO products
            (category_id, name, description, stock, price_buy, price_sell, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issidi",
            $category_id,
            $name,
            $description,
            $stock,
            $price_buy,
            $price_sell,
            $is_active
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




// testttttt//
if (isset($_POST['update_product'])) {

    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $price_buy = $_POST['price_buy'];
    $price_sell = $_POST['price_sell'];
    $description = $_POST['description'];
    $is_active = ($stock == 0) ? 0 : 1;

    //$is_active = $_POST['is_active'];

    // UPDATE DATA PRODUK (TANPA IMAGE)
    $conn->query("
        UPDATE products SET
            name='$name',
            stock='$stock',
            price_buy='$price_buy',
            price_sell='$price_sell',
            description='$description',
            is_active='$is_active'
        
        WHERE id=$id
    ");

    // ================= IMAGE =================

    if (!empty($_FILES['image']['name'])) {

        // Ambil gambar lama
        $q = $conn->query("
            SELECT image FROM product_images 
            WHERE product_id=$id LIMIT 1
        ");
        $old = $q->fetch_assoc();

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = "product_".$id."_".time().".".$ext;
        $uploadDir = "../uploads/";
        $imagePath = $uploadDir.$newName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

        // hapus gambar lama
        if ($old && file_exists($old['image'])) {
            unlink($old['image']);
        }

        // cek sudah ada image atau belum
        if ($old) {
            // UPDATE
            $conn->query("
                UPDATE product_images 
                SET image='$imagePath' 
                WHERE product_id=$id
            ");
        } else {
            // INSERT
            $conn->query("
                INSERT INTO product_images (product_id, image)
                VALUES ($id, '$imagePath')
            ");
        }
    }

    echo "<script>location.href='';</script>";
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // hapus relasi stock dulu jika ada
    $conn->query("DELETE FROM product_stock WHERE product_id=$id");

    // hapus produk
    $conn->query("DELETE FROM products WHERE id=$id");

    echo "<script>location.href='';</script>";
}



// =====================
// DATA
// =====================
$where = "";

if (!empty($_GET['search_category'])) {
    $keyword = $conn->real_escape_string($_GET['search_category']);
    $where = "WHERE c.name LIKE '%$keyword%'";
}


$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$products = $conn->query("
    SELECT 
        p.*, 
        c.name AS category_name,
        pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    $where
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

<!-- SEARCH KATEGORI -->
<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input 
            type="text" 
            name="search_category" 
            class="form-control"
            placeholder="Cari berdasarkan kategori..."
            value="<?= isset($_GET['search_category']) ? htmlspecialchars($_GET['search_category']) : ''; ?>">
        <button class="btn btn-primary" type="submit">
            Cari
        </button>
    </div>
</form>



<!-- ================= TABLE daftar produk ================= -->
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
    <th>Harga Beli</th>
    <th>Harga Jual</th>
    <th>Deskripsi</th>
    <th>Gambar</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; while($p=$products->fetch_assoc()): ?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($p['name']); ?></td>
    <td><?= htmlspecialchars($p['category_name']); ?></td>
    <td><?= $p['stock']; ?></td>
    <td>Rp <?= number_format($p['price_buy'],2); ?></td>
    <td>Rp <?= number_format($p['price_sell'],2); ?></td>
    <td><?= htmlspecialchars($p['description']); ?></td>
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
    <td class="text-center">
        <!-- EDIT -->
        <button 
            class="btn btn-sm btn-warning"
            data-bs-toggle="modal"
            data-bs-target="#edit<?= $p['id']; ?>">
            Edit
        </button>

        <!-- HAPUS -->
        <a 
            href="?delete=<?= $p['id']; ?>" 
            class="btn btn-sm btn-danger"
            onclick="return confirm('Yakin ingin menghapus produk ini?')">
            Hapus
        </a>
    </td>

    <!-- MODAL EDIT -->
    <!-- MODAL EDIT -->
        <div class="modal fade" id="edit<?= $p['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

            <form method="POST" enctype="multipart/form-data">

                <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">

                <input type="hidden" name="id" value="<?= $p['id']; ?>">

                <div class="col-md-6">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control"
                    value="<?= htmlspecialchars($p['name']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control"
                    value="<?= $p['stock']; ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" step="0.01" name="price_buy"
                    value="<?= $p['price_buy']; ?>" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" step="0.01" name="price_sell"
                    value="<?= $p['price_sell']; ?>" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($p['description']); ?></textarea>
                </div>

                <!-- GAMBAR LAMA -->
                <div class="col-md-12">
                    <label class="form-label">Gambar Saat Ini</label><br>
                    <?php if ($p['image']): ?>
                    <img src="<?= $p['image']; ?>" width="120" class="rounded border mb-2">
                    <?php else: ?>
                    <span class="text-muted">Belum ada gambar</span>
                    <?php endif; ?>
                </div>

                <!-- GANTI GAMBAR -->
                <div class="col-md-12">
                    <label class="form-label">Ganti Gambar (opsional)</label>
                    <input type="file" name="image" class="form-control">
                </div>

                </div>

                <div class="modal-footer">
                <button type="submit" name="update_product" class="btn btn-primary">
                    Simpan Perubahan
                </button>
                </div>

            </form>

            </div>
        </div>
        </div>


        

</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

</div>

<?php include 'partials/footer.php'; ?>
