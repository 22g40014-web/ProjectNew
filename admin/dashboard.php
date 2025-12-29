<?php
require_once 'auth.php';
require_once 'config/db.php';
include 'partials/header.php';
include 'partials/sidebar.php';

/* =========================
   PAGINATION SETUP
========================= */
$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$offset = ($page - 1) * $limit;

/* =========================
   FILTER SEARCH
========================= */
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

/* =========================
   TOTAL DATA (BATAS PAGE)
========================= */
$totalQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM products p
    JOIN categories c ON c.id = p.category_id
    $where
");

$totalData  = (int)($totalQuery->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, ceil($totalData / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
if (isset($_GET['toggle_menu'])) {
    $id = (int) $_GET['toggle_menu'];

    $conn->query("
        UPDATE products
        SET show_in_menu = IF(show_in_menu = 1, 0, 1)
        WHERE id = $id
    ");

    header("Location: dashboard.php");
    exit;
}

/* =========================
   QUERY DATA
========================= */
$products = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.stock,
        p.price_buy,
        p.price_sell,
        p.description,
        p.show_in_menu,
        c.name AS category_name,
        pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset
");

/* =========================
   QUERY STRING (FIX BUG PAGE)
========================= */
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
?>

<div class="col-md-10 p-4">

<h3 class="mb-4">Manajemen Produk</h3>

<!-- ================= SEARCH ================= -->
<form method="GET" class="mb-3">
    <div class="row g-3 align-items-end" style="max-width: 700px;">

        <div class="col-md-5">
            <label class="form-label fw-semibold">Nama Produk</label>
            <input type="text" name="search_name" class="form-control"
                   placeholder="Ketik sebagian nama produk..."
                   value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
        </div>

        <div class="col-md-1"></div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Kategori</label>
            <select name="search_category" class="form-select">
                <option value="">-- Semua Kategori --</option>
                <?php
                $catSearch = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 5");
                while ($c = $catSearch->fetch_assoc()):
                ?>
                <option value="<?= $c['id']; ?>"
                    <?= (($_GET['search_category'] ?? '') == $c['id']) ? 'selected' : ''; ?>>
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
    <th>Tampil di Menu</th>

</tr>
</thead>
<tbody>

<?php if ($products->num_rows > 0): ?>
<?php $no = $offset + 1; while ($p = $products->fetch_assoc()): ?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($p['name']); ?></td>
    <td><?= htmlspecialchars($p['category_name']); ?></td>
    <td><?= $p['stock']; ?></td>
    <td>Rp <?= number_format($p['price_buy'], 2); ?></td>
    <td>Rp <?= number_format($p['price_sell'], 2); ?></td>
    <td><?= htmlspecialchars($p['description']); ?></td>
    <td class="text-center">
        <?php if ($p['image']): ?>
            <img src="<?= $p['image']; ?>" width="80" class="rounded">
        <?php else: ?>
            <span class="text-muted">Tidak ada</span>
        <?php endif; ?>
    </td>
    <td class="text-center">
        <a href="dashboard.php?toggle_menu=<?= $p['id']; ?>"
        class="btn btn-sm <?= $p['show_in_menu'] ? 'btn-success' : 'btn-secondary'; ?>"
        onclick="return confirm('Ubah status tampilan menu produk ini?')">
            <?= $p['show_in_menu'] ? 'Tampil' : 'Disembunyikan'; ?>
        </a>
    </td>
<!-- ================= TAMBAHKAN DI FORM ADD / EDIT =============== -->
<!--     <div class="form-check">
        <input class="form-check-input" type="checkbox" name="show_in_menu" value="1" checked>
        <label class="form-check-label">
            Tampilkan di Menu
        </label>
    </div>
        --> 

</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="8" class="text-center text-muted">Data tidak ditemukan</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<!-- ================= PAGINATION ================= -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
<ul class="pagination justify-content-center">

<li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
    <a class="page-link" href="?page=<?= $page - 1; ?>&<?= $baseQuery; ?>">&laquo;</a>
</li>

<?php for ($i = 1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
    <a class="page-link" href="?page=<?= $i; ?>&<?= $baseQuery; ?>">
        <?= $i; ?>
    </a>
</li>
<?php endfor; ?>

<li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
    <a class="page-link" href="?page=<?= $page + 1; ?>&<?= $baseQuery; ?>">&raquo;</a>
</li>

</ul>
</nav>
<?php endif; ?>

</div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
