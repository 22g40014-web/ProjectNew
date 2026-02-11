<?php
require_once 'auth.php';
require_once 'config/db.php';

/* ===============================
   HELPER QUERY STRING (TAMBAHAN)
================================ */
function current_query_string() {
    return !empty($_SERVER['QUERY_STRING'])
        ? '?' . $_SERVER['QUERY_STRING']
        : '';
}

/* ===============================
   PAGINATION
================================ */
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

/* ===============================
   TAMBAH PRODUK (TIDAK DIUBAH)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {

    $category_id = (int)$_POST['category_id'];
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stock       = (int)$_POST['stock'];
    $price_buy   = (float)$_POST['price_buy'];
    $price_sell  = (float)$_POST['price_sell'];
    $is_active   = ($stock > 0) ? 1 : 0;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            INSERT INTO products
            (category_id, name, description, stock, price_buy, price_sell, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issiddi",
            $category_id, $name, $description,
            $stock, $price_buy, $price_sell, $is_active
        );
        $stmt->execute();
        $product_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('product_') . '.' . $ext;
            $path = 'uploads/products/' . $filename;

            if (!is_dir('uploads/products')) {
                mkdir('uploads/products', 0777, true);
            }

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
        header("Location: products.php" . current_query_string());
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

/* ===============================
   UPDATE PRODUK (TIDAK DIUBAH)
================================ */
if (isset($_POST['update_product'])) {

    $id = (int)$_POST['id'];
    $category_id = (int)$_POST['category_id']; // ⬅ TAMBAHAN
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $price_buy = $_POST['price_buy'];
    $price_sell = $_POST['price_sell'];
    $description = $_POST['description'];
    $is_active = ($stock == 0) ? 0 : 1;

    $conn->query("
        UPDATE products SET
            category_id='$category_id',
            name='$name',
            stock='$stock',
            price_buy='$price_buy',
            price_sell='$price_sell',
            description='$description',
            is_active='$is_active'
        WHERE id=$id
    ");

    if (!empty($_FILES['image']['name'])) {

        $q = $conn->query("SELECT image FROM product_images WHERE product_id=$id LIMIT 1");
        $old = $q->fetch_assoc();

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = "product_{$id}_" . time() . "." . $ext;
        $path = "uploads/products/" . $newName;

        move_uploaded_file($_FILES['image']['tmp_name'], $path);

        if ($old && file_exists($old['image'])) {
            unlink($old['image']);
        }

        if ($old) {
            $conn->query("UPDATE product_images SET image='$path' WHERE product_id=$id");
        } else {
            $conn->query("INSERT INTO product_images (product_id, image) VALUES ($id, '$path')");
        }
    }

    $params = [
    'search_name' => $_POST['search_name'] ?? '',
    'search_category' => $_POST['search_category'] ?? '',
    'page' => $_POST['page'] ?? 1
    ];

    header("Location: products.php?". http_build_query($params));
    exit;
}

/* ===============================
   DELETE (TIDAK DIUBAH)
================================ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM product_stock WHERE product_id=$id");
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: products.php" . current_query_string() );
    exit;
}

/* ===============================
   SEARCH
================================ */
$conditions = [];

if (!empty($_GET['search_name'])) {
    $name = $conn->real_escape_string($_GET['search_name']);
    $conditions[] = "p.name LIKE '%$name%'";
}

if (!empty($_GET['search_category'])) {
    $cat_id = (int)$_GET['search_category'];
    $conditions[] = "p.category_id = $cat_id";
}

$where = !empty($conditions)
    ? "WHERE " . implode(" AND ", $conditions)
    : "";

/* ===============================
   TOTAL DATA (BATAS PAGE)
================================ */
$totalQ = $conn->query("
    SELECT COUNT(*) AS total
    FROM products p
    JOIN categories c ON c.id = p.category_id
    $where
");
$totalData  = (int)($totalQ->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, ceil($totalData / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

/* ===============================
   DATA PRODUK
================================ */
$products = $conn->query("
    SELECT p.*, c.name AS category_name, pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset
");

/* ===============================
   QUERY STRING PAGINATION
================================ */
$params = $_GET;
unset($params['page']);
$queryString = http_build_query($params);

/* ===============================
   DATA KATEGORI
================================ */
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
<h3 class="mb-4">Manajemen Produk</h3>

<!-- ================= FORM TAMBAH (TETAP) ================= -->
<div class="card mb-4">
<div class="card-header fw-semibold">Tambah Produk</div>
<div class="card-body">
<form method="POST" enctype="multipart/form-data" class="row g-3">

<div class="col-md-6">
<label>Nama Produk</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-6">
<label>Kategori</label>
<select name="category_id" class="form-select" required>
<option value="">-- Pilih Kategori --</option>

<?php
$categoriesAdd = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($c = $categoriesAdd->fetch_assoc()):
?>
<option value="<?= $c['id']; ?>">
    <?= htmlspecialchars($c['name']); ?>
</option>
<?php endwhile; ?>

</select>
</div>


<div class="col-md-12">
<label>Deskripsi</label>
<textarea name="description" class="form-control" required></textarea>
</div>

<div class="col-md-4">
<label>Stock</label>
<input type="number" name="stock" class="form-control" required>
</div>

<div class="col-md-4">
<label>Harga Beli</label>
<input type="number" step="0.01" name="price_buy" class="form-control" required>
</div>

<div class="col-md-4">
<label>Harga Jual</label>
<input type="number" step="0.01" name="price_sell" class="form-control" required>
</div>

<div class="col-md-6">
<label>Gambar</label>
<input type="file" name="image" class="form-control">
</div>

<button type="submit" name="add_product" class="btn btn-success">
Simpan Produk
</button>

</form>
</div>
</div>

<!-- ================= SEARCH ================= -->
<form method="GET" class="mb-3">
<div class="row g-3 align-items-end" style="max-width:700px">

<div class="col-md-5">
<label>Nama Produk</label>
<input type="text" name="search_name" class="form-control"
value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
</div>

<div class="col-md-1"></div>

<div class="col-md-4">
<label>Kategori</label>
<select name="search_category" class="form-select">
<option value="">-- Semua Kategori --</option>
<?php
$cat5 = $conn->query("SELECT id,name FROM categories ORDER BY name ASC LIMIT 5");
while($c=$cat5->fetch_assoc()):
?>
<option value="<?= $c['id']; ?>"
<?= (($_GET['search_category'] ?? '') == $c['id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($c['name']); ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Cari</button>
</div>

</div>
</form>

<!-- ================= TABLE ================= -->
<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered">
<thead>
<tr>
<th>#</th><th>Produk</th><th>Kategori</th><th>Stock</th>
<th>Harga Beli</th><th>Harga Jual</th><th>Deskripsi</th>
<th>Gambar</th><th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=$offset+1; while($p=$products->fetch_assoc()): ?>
<tr>
<td><?= $no++; ?></td>
<td><?= htmlspecialchars($p['name']); ?></td>
<td><?= htmlspecialchars($p['category_name']); ?></td>
<td><?= $p['stock']; ?></td>
<td><?= number_format($p['price_buy'],2); ?></td>
<td><?= number_format($p['price_sell'],2); ?></td>
<td><?= htmlspecialchars($p['description']); ?></td>

<td class="text-center">
<?php if ($p['image']): ?>
<button class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#img<?= $p['id']; ?>">
  Lihat
</button>

<!-- MODAL GAMBAR -->
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
<button class="btn btn-sm btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#edit<?= $p['id']; ?>">
  Edit
</button>

<!-- HAPUS -->
<a href="?delete=<?= $p['id']; ?>"
   class="btn btn-sm btn-danger"
   onclick="return confirm('Yakin ingin menghapus produk ini?')">
  Hapus
</a>
</td>
</tr>

<!-- ================= MODAL EDIT PRODUK ================= -->
<div class="modal fade" id="edit<?= $p['id']; ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <form method="POST" enctype="multipart/form-data">

        <!-- SIMPAN FILTER PENCARIAN (TAMBAHAN) -->
        <input type="hidden" name="search_name"
              value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">

        <input type="hidden" name="search_category"
              value="<?= htmlspecialchars($_GET['search_category'] ?? '') ?>">

        <input type="hidden" name="page"
              value="<?= htmlspecialchars($_GET['page'] ?? 1) ?>">


        <!-- HEADER -->
        <div class="modal-header">
          <h5 class="modal-title">Edit Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- BODY -->
        <div class="modal-body">

          <input type="hidden" name="id" value="<?= $p['id']; ?>">

          <!-- ROW 1 : KATEGORI & NAMA -->
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                $cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
                while ($cat = $cats->fetch_assoc()):
                ?>
                <option value="<?= $cat['id']; ?>"
                  <?= ($cat['id'] == $p['category_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']); ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nama Produk</label>
              <input type="text"
                     name="name"
                     class="form-control"
                     value="<?= htmlspecialchars($p['name']); ?>"
                     required>
            </div>
          </div>

          <!-- ROW 2 : STOCK & HARGA BELI -->
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Stock</label>
              <input type="number"
                     name="stock"
                     class="form-control"
                     value="<?= $p['stock']; ?>"
                     required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Harga Beli</label>
              <input type="number"
                     step="0.01"
                     name="price_buy"
                     class="form-control"
                     value="<?= $p['price_buy']; ?>"
                     required>
            </div>
          </div>

          <!-- ROW 3 : HARGA JUAL -->
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Harga Jual</label>
              <input type="number"
                     step="0.01"
                     name="price_sell"
                     class="form-control"
                     value="<?= $p['price_sell']; ?>"
                     required>
            </div>
          </div>

          <!-- DESKRIPSI -->
          <div class="row g-3 mt-1">
            <div class="col-md-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description"
                        class="form-control"
                        rows="3"
                        required><?= htmlspecialchars($p['description']); ?></textarea>
            </div>
          </div>

          <!-- GAMBAR SAAT INI -->
          <div class="row g-3 mt-1">
            <div class="col-md-12">
              <label class="form-label">Gambar Saat Ini</label><br>
              <?php if ($p['image']): ?>
                <img src="<?= $p['image']; ?>" width="120" class="rounded border mb-2">
              <?php else: ?>
                <span class="text-muted">Belum ada gambar</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- GANTI GAMBAR -->
          <div class="row g-3 mt-1">
            <div class="col-md-12">
              <label class="form-label">Ganti Gambar</label>
              <input type="file" name="image" class="form-control">
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">
            Batal
          </button>

          <button type="submit"
                  name="update_product"
                  class="btn btn-primary">
            Simpan Perubahan
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
<!-- ================= END MODAL EDIT ================= -->



<?php endwhile; ?>
</tbody>
</table>

<!-- ================= PAGINATION ================= -->
<?php if ($totalPages > 1): ?>
<nav>
<ul class="pagination justify-content-center">

<li class="page-item <?= ($page<=1)?'disabled':'' ?>">
<a class="page-link" href="?page=<?= $page-1 ?>&<?= $queryString ?>">&laquo;</a>
</li>

<?php for($i=1;$i<=$totalPages;$i++): ?>
<li class="page-item <?= ($i==$page)?'active':'' ?>">
<a class="page-link" href="?page=<?= $i ?>&<?= $queryString ?>"><?= $i ?></a>
</li>
<?php endfor; ?>

<li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
<a class="page-link" href="?page=<?= $page+1 ?>&<?= $queryString ?>">&raquo;</a>
</li>

</ul>
</nav>
<?php endif; ?>

</div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
