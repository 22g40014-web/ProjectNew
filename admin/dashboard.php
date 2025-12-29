<?php include 'partials/header.php'; 
include 'partials/sidebar.php'; 
require_once 'auth.php';
require_once 'config/db.php'; 


$where = [];
$conditions = [];

if (!empty($_GET['search_name'])) {
    $name = $conn->real_escape_string($_GET['search_name']);
    $conditions[] = "p.name LIKE '%$name%'";
}

if (!empty($_GET['search_category'])) {
    $cat_id = (int) $_GET['search_category'];
    $conditions[] = "p.category_id = $cat_id";
}

if (!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
} else {
    $where = "";
}


$products = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.stock,
        p.price_buy,
        p.price_sell,
        p.description,
        c.name AS category_name,
        pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    $where
    ORDER BY p.created_at DESC
    LIMIT 10
");
?>

<div class="col-md-10 p-4">

<h3 class="mb-4">Manajemen Produk</h3>

<!-- ================= DASHBOARD ================= -->
<div class="col-md-10 p-4">
    <h3 class="mb-4">Dashboard</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total Produk</h6>
                    <h3>24</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Admin Pending</h6>
                    <h3>3</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH PRODUK -->
<form method="GET" class="mb-3">
    <div class="row g-3 align-items-end" style="max-width: 700px;">

        <!-- CARI NAMA PRODUK -->
        <div class="col-md-5">
            <label class="form-label fw-semibold">Nama Produk</label>
            <input 
                type="text" 
                name="search_name" 
                class="form-control"
                placeholder="Ketik sebagian nama produk..."
                value="<?= isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
        </div>

        <!-- JARAK VISUAL -->
        <div class="col-md-1"></div>

        <!-- DROPDOWN KATEGORI -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Kategori</label>
            <select name="search_category" class="form-select">
                <option value="">-- Semua Kategori --</option>

                <?php
                // ambil 5 kategori
                $catSearch = $conn->query("
                    SELECT id, name 
                    FROM categories 
                    ORDER BY name ASC 
                    LIMIT 5
                ");
                while ($c = $catSearch->fetch_assoc()):
                ?>
                    <option value="<?= $c['id']; ?>"
                        <?= (isset($_GET['search_category']) && $_GET['search_category'] == $c['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- BUTTON -->
        <div class="col-md-2">
            <button class="btn btn-primary w-100">
                Cari
            </button>
        </div>

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
    

        </div>
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
