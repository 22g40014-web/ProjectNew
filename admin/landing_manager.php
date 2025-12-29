<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error   = '';

/* =========================
   SIMPAN / UPDATE PROMO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name   = trim($_POST['product_name']);
    $title          = trim($_POST['title']);
    $price_normal   = (float) $_POST['price_normal'];
    $discount_type  = $_POST['discount_type'];
    $discount_value = (float) $_POST['discount_value'];
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    // hitung harga setelah diskon
    if ($discount_type === 'percent') {
        $price_after = $price_normal - ($price_normal * $discount_value / 100);
    } else {
        $price_after = $price_normal - $discount_value;
    }

    /* =====================
       UPLOAD GAMBAR
    ====================== */
    $image_name = $_POST['old_image'] ?? '';

    if (!empty($_FILES['image']['name'])) {

        $dir = '../uploads/promos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('promo_') . '.' . $ext;
        $target     = $dir . $image_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $error = "Upload gambar gagal";
        }

        // hapus gambar lama
        if (!empty($_POST['old_image'])) {
            @unlink($dir . $_POST['old_image']);
        }
    }

    /* =====================
       INSERT / UPDATE
    ====================== */
    if (empty($error)) {

        if (isset($_POST['id'])) {

            // UPDATE
            $stmt = $conn->prepare("
                UPDATE promos SET
                product_name=?, title=?, price_normal=?, discount_type=?,
                discount_value=?, price_after=?, image=?, is_active=?
                WHERE id=?
            ");
            $stmt->bind_param(
                "ssdsddsii",
                $product_name, $title, $price_normal, $discount_type,
                $discount_value, $price_after, $image_name, $is_active, $_POST['id']
            );

            $success = "Promo berhasil diperbarui";

        } else {

            // INSERT
            $stmt = $conn->prepare("
                INSERT INTO promos
                (product_name, title, price_normal, discount_type, discount_value, price_after, image, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssdsddsi",
                $product_name, $title, $price_normal, $discount_type,
                $discount_value, $price_after, $image_name, $is_active
            );

            $success = "Promo berhasil ditambahkan";
        }

        $stmt->execute();
    }
}

/* =========================
   DELETE PROMO
========================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $img = $conn->query("SELECT image FROM promos WHERE id=$id")->fetch_assoc();
    if ($img) {
        @unlink('../uploads/promos/' . $img['image']);
        $conn->query("DELETE FROM promos WHERE id=$id");
        $success = "Promo berhasil dihapus";
    }
}

/* =========================
   EDIT DATA
========================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id   = (int) $_GET['edit'];
    $edit = $conn->query("SELECT * FROM promos WHERE id=$id")->fetch_assoc();
}

/* =========================
   DATA PROMO
========================= */
$promos = $conn->query("SELECT * FROM promos ORDER BY created_at DESC");

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

<!-- FORM -->
<div class="card mb-4">
<div class="card-header"><?= $edit ? 'Edit Promo' : 'Tambah Promo' ?></div>
<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<?php if ($edit): ?>
<input type="hidden" name="id" value="<?= $edit['id'] ?>">
<input type="hidden" name="old_image" value="<?= $edit['image'] ?>">
<?php endif; ?>

<div class="row g-3">
<div class="col-md-6">
<input type="text" name="product_name" class="form-control" placeholder="Nama Produk"
       value="<?= $edit['product_name'] ?? '' ?>" required>
</div>

<div class="col-md-6">
<input type="text" name="title" class="form-control" placeholder="Judul Promo"
       value="<?= $edit['title'] ?? '' ?>" required>
</div>

<div class="col-md-4">
<input type="number" name="price_normal" class="form-control" placeholder="Harga Normal"
       value="<?= $edit['price_normal'] ?? '' ?>" required>
</div>

<div class="col-md-4">
<select name="discount_type" class="form-select">
<option value="percent" <?= ($edit['discount_type'] ?? '')=='percent'?'selected':'' ?>>Persen</option>
<option value="nominal" <?= ($edit['discount_type'] ?? '')=='nominal'?'selected':'' ?>>Nominal</option>
</select>
</div>

<div class="col-md-4">
<input type="number" name="discount_value" class="form-control" placeholder="Diskon"
       value="<?= $edit['discount_value'] ?? '' ?>" required>
</div>

<div class="col-md-6">
<input type="file" name="image" class="form-control">
</div>

<div class="col-md-6">
<label>
<input type="checkbox" name="is_active" <?= ($edit['is_active'] ?? 1) ? 'checked':'' ?>>
 Aktif
</label>
</div>

<div class="col-md-12">
<button class="btn btn-primary">
<?= $edit ? 'Update Promo' : 'Simpan Promo' ?>
</button>
</div>
</div>
</form>

</div>
</div>

<!-- LIST -->
<div class="card">
<div class="card-header">Daftar Promo</div>
<div class="card-body table-responsive">

<table class="table table-bordered">
<thead>
<tr>
<th>Gambar</th>
<th>Produk</th>
<th>Diskon</th>
<th>Harga</th>
<th>Status</th>
<th width="150">Aksi</th>
</tr>
</thead>
<tbody>

<?php while ($p = $promos->fetch_assoc()): ?>
<tr>
<td width="120">
<img src="../uploads/promos/<?= $p['image'] ?>" class="img-fluid rounded">
</td>
<td><?= $p['product_name'] ?></td>
<td><?= $p['discount_type']=='percent' ? $p['discount_value'].'%' : 'Rp'.$p['discount_value'] ?></td>
<td>
<s>Rp<?= number_format($p['price_normal']) ?></s><br>
<strong>Rp<?= number_format($p['price_after']) ?></strong>
</td>
<td><?= $p['is_active'] ? 'Aktif':'Nonaktif' ?></td>
<td>
<a href="?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
   onclick="return confirm('Hapus promo ini?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
