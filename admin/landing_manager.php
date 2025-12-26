<?php
require_once 'auth.php';
require_once 'config/db.php';

$success = '';
$error = '';

/* =========================
   TAMBAH PROMO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_promo'])) {

        $product_id = (int) $_POST['product_id'];
        $title      = trim($_POST['title']);

        if ($product_id && isset($_FILES['image'])) {

            $dir = '../uploads/landing/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $name = uniqid('promo_') . '.' . $ext;
            $path = $dir . $name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {

                $stmt = $conn->prepare("
                    INSERT INTO landing_products (product_id, image, title)
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iss", $product_id, $name, $title);

                if ($stmt->execute()) {
                    $success = "Produk berhasil ditambahkan ke landing page";
                } else {
                    $error = "Gagal menyimpan data";
                }
            } else {
                $error = "Upload gambar gagal";
            }
        } else {
            $error = "Produk dan gambar wajib diisi";
        }
    }
}

/* =========================
   DELETE PROMO
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("
        SELECT image FROM landing_products WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc();

    if ($img) {
        @unlink('../uploads/landing/' . $img['image']);

        $del = $conn->prepare("
            DELETE FROM landing_products WHERE id = ?
        ");
        $del->bind_param("i", $id);
        $del->execute();

        $success = "Produk promo dihapus";
    }
}

/* =========================
   DATA PRODUK
========================= */
$products = $conn->query("
    SELECT id, name FROM products ORDER BY name ASC
");

/* =========================
   DATA PROMO
========================= */
$promos = $conn->query("
    SELECT lp.id, lp.image, lp.title, p.name
    FROM landing_products lp
    JOIN products p ON p.id = lp.product_id
    ORDER BY lp.created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Manajemen Landing Page</h3>

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
        Tambah Produk ke Landing Page
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="row g-3">

            <div class="col-md-4">
                <select name="product_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <input type="file" name="image" class="form-control" required>
            </div>

            <div class="col-md-4">
                <input type="text" name="title" class="form-control"
                       placeholder="Judul promo (opsional)">
            </div>

            <div class="col-md-12">
                <button class="btn btn-success" name="add_promo">
                    Tambahkan ke Landing Page
                </button>
            </div>

        </form>
    </div>
</div>

<!-- LIST PROMO -->
<div class="card">
    <div class="card-header fw-semibold">
        Produk di Landing Page
    </div>
    <div class="card-body table-responsive">

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Gambar</th>
                    <th>Produk</th>
                    <th>Judul</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($promos->num_rows === 0): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada produk promo</td>
                </tr>
            <?php else: while ($row = $promos->fetch_assoc()): ?>
                <tr>
                    <td width="120">
                        <img src="../uploads/landing/<?= $row['image'] ?>"
                             class="img-fluid rounded">
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Hapus dari landing page?')"
                           class="btn btn-danger btn-sm">
                            Delete
                        </a>
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
