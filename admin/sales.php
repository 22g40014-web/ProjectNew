<?php
require_once 'config/db.php';
require_once 'partials/header.php';


// =======================
// TAMBAH PENJUALAN
// =======================
if (isset($_POST['add_sale'])) {
    $product_id = (int)$_POST['product_id'];
    $qty        = (int)$_POST['qty'];
    $price_sell = (float)$_POST['price_sell'];
    $final_cost = (float)$_POST['final_cost'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            SELECT stock, price_buy 
            FROM products 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) throw new Exception("Produk tidak ditemukan");
        if ($product['stock'] < $qty) throw new Exception("Stok tidak mencukupi");

        $price_buy = $product['price_buy'];
        $total     = $qty * $price_sell;
        $profit    = (($price_sell - $price_buy) * $qty) - $final_cost;

        $stmt = $conn->prepare("
            INSERT INTO sales 
            (product_id, qty, price_buy, price_sell, final_cost, total, profit) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iidddii",
            $product_id,
            $qty,
            $price_buy,
            $price_sell,
            $final_cost,
            $total,
            $profit
        );
        $stmt->execute();

        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $qty, $product_id);
        $stmt->execute();

        $conn->commit();
        $success = "Penjualan berhasil disimpan";

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// =======================
// UPDATE PENJUALAN
// =======================
if (isset($_POST['update_sale'])) {
    $sale_id    = (int)$_POST['sale_id'];
    $new_qty    = (int)$_POST['qty'];
    $price_sell = (float)$_POST['price_sell'];
    $final_cost = (float)$_POST['final_cost'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            SELECT s.*, p.price_buy 
            FROM sales s
            JOIN products p ON p.id = s.product_id
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        if (!$old) throw new Exception("Data penjualan tidak ditemukan");

        // rollback stok lama
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock + ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $old['qty'], $old['product_id']);
        $stmt->execute();

        // cek stok baru
        $stmt = $conn->prepare("
            SELECT stock FROM products WHERE id = ?
        ");
        $stmt->bind_param("i", $old['product_id']);
        $stmt->execute();
        $stock = $stmt->get_result()->fetch_assoc()['stock'];

        if ($stock < $new_qty) throw new Exception("Stok tidak mencukupi");

        // apply stok baru
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $new_qty, $old['product_id']);
        $stmt->execute();

        $total  = $new_qty * $price_sell;
        $profit = (($price_sell - $old['price_buy']) * $new_qty) - $final_cost;

        $stmt = $conn->prepare("
            UPDATE sales SET
                qty = ?,
                price_sell = ?,
                final_cost = ?,
                total = ?,
                profit = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "idddii",
            $new_qty,
            $price_sell,
            $final_cost,
            $total,
            $profit,
            $sale_id
        );
        $stmt->execute();

        $conn->commit();
        $success = "Penjualan berhasil diperbarui";

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}


// =======================
// DELETE PENJUALAN
// =======================
if (isset($_GET['delete'])) {
    $sale_id = (int)$_GET['delete'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $sale = $stmt->get_result()->fetch_assoc();

        if (!$sale) throw new Exception("Data tidak ditemukan");

        // rollback stok
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock + ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $sale['qty'], $sale['product_id']);
        $stmt->execute();

        // delete sales
        $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();

        $conn->commit();
        $success = "Penjualan berhasil dihapus";

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// =======================
// DATA
// =======================
$products = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.stock,
        p.price_sell
    FROM products p
    WHERE p.is_active = 1
    ORDER BY p.name ASC
");

// =======================
// DATA PENJUALAN
// =======================
$sales = $conn->query("
    SELECT 
        s.id,
        s.qty,
        s.price_buy,
        s.price_sell,
        s.final_cost,
        s.total,
        s.profit,
        p.name
    FROM sales s
    JOIN products p ON p.id = s.product_id
    ORDER BY s.created_at DESC
");



include 'partials/sidebar.php';
?>
<!DOCTYPE html>
<html>

<head>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
.modal input.form-control {
    background-color: #fff !important;
    color: #000 !important;
    width: 100% !important;
}
</style>
</head>



<div class="col-md-10 p-4">

<h3 class="mb-4">Penjualan</h3>

<?php if (isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- FORM TAMBAH -->
<!-- FORM TAMBAH -->
<form method="post" class="card mb-4 p-3">
<div class="row g-2">

    <div class="col-md-4">
        <label class="form-label">Produk</label>

        <select name="product_id"
                id="productSearch"
                class="form-select"
                required>
            <option value="">Pilih Produk</option>

            <?php while ($p = $products->fetch_assoc()): ?>
                <?php
                    $stock = (int)($p['stock'] ?? 0);
                    if ($stock <= 0) continue;

                    $label = $stock <= 3
                        ? "Stok: $stock (Hampir Habis)"
                        : "Stok: $stock";
                ?>
                <option value="<?= $p['id'] ?>"
                        data-price="<?= $p['price_sell'] ?>">
                    <?= htmlspecialchars($p['name']) ?> — <?= $label ?>
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
        <label>Beban Penjualan (Final)</label>
        <input type="number" name="final_cost" class="form-control" value="0">
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button name="add_sale" class="btn btn-success w-100">Simpan</button>
    </div>

</div>
</form>




<!-- TABLE -->
<table class="table table-bordered">
<thead class="table-dark">
<tr class="text-center">
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
<!-- TABLE -->


<tbody>
<?php $no = 1; while ($s = $sales->fetch_assoc()): ?>
<tr class="text-center align-middle">
    <td><?= $no++ ?></td>
    <td class="text-start"><?= htmlspecialchars($s['name']) ?></td>
    <td><?= $s['qty'] ?></td>
    <td>Rp <?= number_format($s['price_sell'],0,',','.') ?></td>
    <td>Rp <?= number_format($s['price_buy'],0,',','.') ?></td>
    <td>Rp <?= number_format($s['final_cost'],0,',','.') ?></td>

    <td class="<?= $s['profit'] < 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
        Rp <?= number_format($s['profit'],0,',','.') ?>
    </td>

    <td>
        <div class="d-flex gap-1 justify-content-center">
            <button 
                class="btn btn-sm btn-warning"
                data-bs-toggle="modal"
                data-bs-target="#edit<?= $s['id'] ?>">
                ✏️
            </button>

            <a 
                href="?delete=<?= $s['id'] ?>"
                onclick="return confirm('Hapus penjualan ini?')"
                class="btn btn-sm btn-danger">
                🗑
            </a>
        </div>
    </td>
</tr>


<!-- MODAL EDIT -->
<!-- MODAL EDIT -->
<div class="modal fade" id="edit<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <form method="post" class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Penjualan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="sale_id" value="<?= $s['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Qty</label>
          <input type="number"
                 name="qty"
                 value="<?= $s['qty'] ?>"
                 class="form-control"
                 min="1"
                 required>
        </div>

        <div class="mb-3">
          <label class="form-label">Harga Jual</label>
          <input type="number"
                 name="price_sell"
                 value="<?= $s['price_sell'] ?>"
                 class="form-control"
                 required>
        </div>

        <div class="mb-3">
          <label class="form-label">Beban Penjualan</label>
          <input type="number"
                 name="final_cost"
                 value="<?= $s['final_cost'] ?>"
                 class="form-control">
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
          Batal
        </button>
        <button name="update_sale"
                class="btn btn-primary">
          Simpan
        </button>
      </div>

    </form>
  </div>
</div>



<?php endwhile; ?>
</tbody>


<!-- JQUERY (WAJIB) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {

    $('#productSearch').select2({
        placeholder: 'Cari & Pilih Produk',
        width: '100%',
        minimumResultsForSearch: 0   // PAKSA SEARCH MUNCUL
    });

    // Auto isi harga
    $('#productSearch').on('change', function () {
        const price = $(this).find(':selected').data('price') || 0;
        $('input[name="price_sell"]').val(price);
    });

});
</script>

</tbody>
</table>
</div>
</html>
<?php require_once 'partials/footer.php'; ?>


