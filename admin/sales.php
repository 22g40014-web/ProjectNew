<?php
require_once 'config/db.php';
require_once 'partials/header.php';
include 'partials/sidebar.php';

/* ================= DATA PRODUK ================= */
$products = $conn->query("
    SELECT id, name, stock, price_sell
    FROM products
    WHERE is_active = 1
    ORDER BY name ASC
");

/* ================= DATA PENJUALAN ================= */
$sales = $conn->query("
    SELECT s.*, p.name
    FROM sales s
    JOIN products p ON p.id = s.product_id
    ORDER BY s.created_at DESC
");
?>

<div class="col-md-10 p-4">
<h3 class="mb-4">Penjualan</h3>

<!-- ================= FORM TAMBAH ================= -->
<form method="post" class="card p-3 mb-4">
<div class="row g-2">

<div class="col-md-4">
<label class="form-label">Produk</label>
<select name="product_id" id="productSearch" class="form-select" required>
<option value="">Cari & Pilih Produk</option>
<?php while($p = $products->fetch_assoc()): ?>
<?php if($p['stock'] <= 0) continue; ?>
<option value="<?= $p['id'] ?>" data-price="<?= $p['price_sell'] ?>">
<?= htmlspecialchars($p['name']) ?> (Stok: <?= $p['stock'] ?>)
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-2">
<label>Qty</label>
<input type="number" name="qty" class="form-control" required>
</div>

<div class="col-md-3">
<label>Harga Jual</label>
<input type="number" name="price_sell" class="form-control" required>
</div>

<div class="col-md-3">
<label>Beban Penjualan</label>
<input type="number" name="final_cost" class="form-control" value="0">
</div>

<div class="col-md-3 d-flex align-items-end">
<button name="add_sale" class="btn btn-success w-100">Simpan</button>
</div>

</div>
</form>

<!-- ================= TABLE ================= -->
<table class="table table-bordered table-striped">
<thead class="table-dark text-center">
<tr>
<th>No</th>
<th>Produk</th>
<th>Qty</th>
<th>Harga Jual</th>
<th>Harga Beli</th>
<th>Beban</th>
<th>Profit</th>
<th width="120">Aksi</th>
</tr>
</thead>

<tbody>
<?php $no=1; while($s = $sales->fetch_assoc()): ?>
<tr class="align-middle text-center">
<td><?= $no++ ?></td>
<td class="text-start"><?= htmlspecialchars($s['name']) ?></td>
<td><?= $s['qty'] ?></td>
<td>Rp <?= number_format($s['price_sell'],0,',','.') ?></td>
<td>Rp <?= number_format($s['price_buy'],0,',','.') ?></td>
<td>Rp <?= number_format($s['final_cost'],0,',','.') ?></td>
<td class="<?= $s['profit']<0?'text-danger':'text-success' ?> fw-bold">
Rp <?= number_format($s['profit'],0,',','.') ?>
</td>
<td>
<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#edit<?= $s['id'] ?>">✏️</button>

<a href="?delete=<?= $s['id'] ?>"
onclick="return confirm('Hapus data?')"
class="btn btn-danger btn-sm">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- ================= MODAL EDIT (DIPINDAHKAN KE LUAR TABLE) ================= -->
<?php
$sales->data_seek(0);
while($s = $sales->fetch_assoc()):
?>
<div class="modal fade" id="edit<?= $s['id'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<form method="post" class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Edit Penjualan</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="sale_id" value="<?= $s['id'] ?>">

<div class="mb-3">
<label>Qty</label>
<input type="number" name="qty" value="<?= $s['qty'] ?>" class="form-control" required>
</div>

<div class="mb-3">
<label>Harga Jual</label>
<input type="number" name="price_sell" value="<?= $s['price_sell'] ?>" class="form-control" required>
</div>

<div class="mb-3">
<label>Beban Penjualan</label>
<input type="number" name="final_cost" value="<?= $s['final_cost'] ?>" class="form-control">
</div>
</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
<button name="update_sale" class="btn btn-primary">Simpan</button>
</div>

</form>
</div>
</div>
<?php endwhile; ?>

<!-- ================= ASSET WAJIB ================= -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
.modal-content,
.modal input,
.modal select {
background:#fff !important;
color:#000 !important;
}
</style>

<script>
$(function(){
$('#productSearch').select2({
placeholder:'Cari & Pilih Produk',
width:'100%'
});

$('#productSearch').on('change',function(){
let price=$(this).find(':selected').data('price')||0;
$('input[name="price_sell"]').val(price);
});
});
</script>

<?php require_once 'partials/footer.php'; ?>
