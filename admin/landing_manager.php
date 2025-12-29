<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error   = '';

/* =========================
   TAMBAH PROMO (MANUAL)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promo'])) {

    $product_name   = trim($_POST['product_name']);
    $title          = trim($_POST['title']);
    $price_normal   = (float) $_POST['price_normal'];
    $discount_type  = $_POST['discount_type'];
    $discount_value = (float) $_POST['discount_value'];

    if ($discount_type === 'percent') {
        $price_after = $price_normal - ($price_normal * $discount_value / 100);
    } else {
        $price_after = $price_normal - $discount_value;
    }

    if ($price_after < 0) $price_after = 0;

    if (!empty($_FILES['image']['name'])) {

        $dir = '../uploads/landing/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img  = uniqid('promo_') . '.' . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $dir . $img);

        $stmt = $conn->prepare("
            INSERT INTO promos
            (product_name, title, price_normal, discount_type,
             discount_value, price_after, image)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssdsdds",
            $product_name,
            $title,
            $price_normal,
            $discount_type,
            $discount_value,
            $price_after,
            $img
        );

        $stmt->execute();
        $success = "Promo berhasil ditambahkan";
    } else {
        $error = "Gambar promo wajib diisi";
    }
}

/* =========================
   UPDATE PROMO
========================= */
if (isset($_POST['update_promo'])) {

    $id             = (int) $_POST['id'];
    $product_name   = $_POST['product_name'];
    $title          = $_POST['title'];
    $price_normal   = (float) $_POST['price_normal'];
    $discount_type  = $_POST['discount_type'];
    $discount_value = (float) $_POST['discount_value'];

    if ($discount_type === 'percent') {
        $price_after = $price_normal - ($price_normal * $discount_value / 100);
    } else {
        $price_after = $price_normal - $discount_value;
    }

    if ($price_after < 0) $price_after = 0;

    $conn->query("
        UPDATE promos SET
            product_name='$product_name',
            title='$title',
            price_normal='$price_normal',
            discount_type='$discount_type',
            discount_value='$discount_value',
            price_after='$price_after'
        WHERE id=$id
    ");

    if (!empty($_FILES['image']['name'])) {

        $old = $conn->query("SELECT image FROM promos WHERE id=$id")->fetch_assoc();
        if ($old && file_exists('../uploads/landing/'.$old['image'])) {
            unlink('../uploads/landing/'.$old['image']);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img = uniqid('promo_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/landing/'.$img);

        $conn->query("UPDATE promos SET image='$img' WHERE id=$id");
    }

    $success = "Promo berhasil diperbarui";
}

/* =========================
   DELETE PROMO
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $img = $conn->query("SELECT image FROM promos WHERE id=$id")->fetch_assoc();
    if ($img) @unlink('../uploads/landing/'.$img['image']);

    $conn->query("DELETE FROM promos WHERE id=$id");
    $success = "Promo berhasil dihapus";
}

/* =========================
   DATA PROMO
========================= */
$promos = $conn->query("
    SELECT * FROM promos ORDER BY created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
<h3 class="mb-4">Manajemen Promo Landing Page</h3>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- ================= TAMBAH PROMO ================= -->
<div class="card mb-4">
<div class="card-header fw-semibold">Tambah Promo</div>
<div class="card-body">
<form method="POST" enctype="multipart/form-data" class="row g-3">

<div class="col-md-4">
<input type="text" name="product_name" class="form-control"
placeholder="Nama Produk" required>
</div>

<div class="col-md-4">
<input type="text" name="title" class="form-control"
placeholder="Judul Promo" required>
</div>

<div class="col-md-4">
<input type="number" step="0.01" name="price_normal"
class="form-control" placeholder="Harga Normal" required>
</div>

<div class="col-md-4">
<select name="discount_type" class="form-select" required>
<option value="">-- Tipe Diskon --</option>
<option value="percent">Persen (%)</option>
<option value="nominal">Nominal (Rp)</option>
</select>
</div>

<div class="col-md-4">
<input type="number" step="0.01" name="discount_value"
class="form-control" placeholder="Nilai Diskon" required>
</div>

<div class="col-md-4">
<input type="file" name="image" class="form-control" required>
</div>

<div class="col-md-12">
<button class="btn btn-success" name="add_promo">
Simpan Promo
</button>
</div>

</form>
</div>
</div>

<!-- ================= TABLE PROMO ================= -->
<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered align-middle">
<thead>
<tr>
<th>Produk</th>
<th>Harga Normal</th>
<th>Diskon</th>
<th>Harga Promo</th>
<th>Gambar</th>
<th width="150">Aksi</th>
</tr>
</thead>
<tbody>

<?php while($pr=$promos->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($pr['product_name']); ?></td>
<td><?= number_format($pr['price_normal'],2); ?></td>
<td>
<?= $pr['discount_type']=='percent'
    ? $pr['discount_value'].' %'
    : 'Rp '.number_format($pr['discount_value'],2); ?>
</td>
<td><strong><?= number_format($pr['price_after'],2); ?></strong></td>

<td width="120">
<img src="../uploads/landing/<?= $pr['image']; ?>" class="img-fluid rounded">
</td>

<td>
<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#edit<?= $pr['id']; ?>">Edit</button>

<a href="?delete=<?= $pr['id']; ?>"
onclick="return confirm('Hapus promo ini?')"
class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="edit<?= $pr['id']; ?>" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<form method="POST" enctype="multipart/form-data">

<div class="modal-header">
<h5 class="modal-title">Edit Promo</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body row g-3">

<input type="hidden" name="id" value="<?= $pr['id']; ?>">

<div class="col-md-6">
<label>Nama Produk</label>
<input type="text" name="product_name" class="form-control"
value="<?= htmlspecialchars($pr['product_name']); ?>" required>
</div>

<div class="col-md-6">
<label>Judul Promo</label>
<input type="text" name="title" class="form-control"
value="<?= htmlspecialchars($pr['title']); ?>" required>
</div>

<div class="col-md-6">
<label>Harga Normal</label>
<input type="number" step="0.01" name="price_normal"
value="<?= $pr['price_normal']; ?>" class="form-control">
</div>

<div class="col-md-6">
<label>Tipe Diskon</label>
<select name="discount_type" class="form-select">
<option value="percent" <?= $pr['discount_type']=='percent'?'selected':'' ?>>Persen</option>
<option value="nominal" <?= $pr['discount_type']=='nominal'?'selected':'' ?>>Nominal</option>
</select>
</div>

<div class="col-md-6">
<label>Nilai Diskon</label>
<input type="number" step="0.01" name="discount_value"
value="<?= $pr['discount_value']; ?>" class="form-control">
</div>

<div class="col-md-12">
<label>Gambar Saat Ini</label><br>
<img src="../uploads/landing/<?= $pr['image']; ?>"
width="150" class="rounded border mb-2">
</div>

<div class="col-md-12">
<label>Ganti Gambar</label>
<input type="file" name="image" class="form-control">
</div>

</div>

<div class="modal-footer">
<button type="submit" name="update_promo"
class="btn btn-primary">Simpan Perubahan</button>
</div>

</form>

</div>
</div>
</div>
<!-- ================= END MODAL ================= -->

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
