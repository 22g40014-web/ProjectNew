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

$sales = $conn->query("
    SELECT s.*, p.name 
    FROM sales s
    JOIN products p ON p.id = s.product_id
    ORDER BY s.created_at DESC
");


include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">

<h3 class="mb-4">Penjualan</h3>

<?php if (isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- FORM TAMBAH -->
<form method="post" class="card mb-4 p-3">
<div class="row g-2">
    <div class="col-md-4">
    <label class="form-label">Produk</label>
<select name="product_id" class="form-select" required>
    <option value="">-- Pilih Produk --</option>

    <?php while ($p = $products->fetch_assoc()): ?>
        <?php
            $stock = (int)($p['stock'] ?? 0);

            if ($stock <= 0) {
                $label = "HABIS";
            } elseif ($stock <= 3) {
                $label = "Stok: $stock (Hampir Habis)";
            } else {
                $label = "Stok: $stock";
            }
        ?>
        <option 
            value="<?= $p['id'] ?>"
            data-price="<?= $p['price_sell'] ?>"
            <?= $stock <= 0 ? 'disabled' : '' ?>
        >
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
<thead>
<tr>
    <th>#</th>
    <th>Produk</th>
    <th>Qty</th>
    <th>Harga Jual</th>
    <th>Total</th>
    <th>Profit</th>
    <th>Beban</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; while ($s = $sales->fetch_assoc()): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $s['name'] ?></td>
<td><?= $s['qty'] ?></td>
<td>Rp <?= number_format($s['price_sell']) ?></td>
<td>Rp <?= number_format($s['total']) ?></td>
<td>Rp <?= number_format($s['profit']) ?></td>
<td>Rp <?= number_format($s['final_cost']) ?></td>
<td>
<button 
    class="btn btn-sm btn-warning"
    data-bs-toggle="modal"
    data-bs-target="#edit<?= $s['id'] ?>">Edit</button>
<a 
    href="?delete=<?= $s['id'] ?>" 
    onclick="return confirm('Hapus penjualan?')"
    class="btn btn-sm btn-danger">Delete</a>
</td>
</tr>

<!-- MODAL EDIT -->
<div class="modal fade" id="edit<?= $s['id'] ?>">
<div class="modal-dialog">
<form method="post" class="modal-content">
<div class="modal-header">
<h5>Edit Penjualan</h5>
</div>
<div class="modal-body">
<input type="hidden" name="sale_id" value="<?= $s['id'] ?>">
<div class="mb-2">
<label>Qty</label>
<input type="number" name="qty" value="<?= $s['qty'] ?>" class="form-control">
</div>
<div class="mb-2">
<label>Harga Jual</label>
<input type="number" name="price_sell" value="<?= $s['price_sell'] ?>" class="form-control">
</div>
<div class="mb-2">
    <label>Beban Penjualan</label>
    <input type="number" name="final_cost" 
           value="<?= $s['final_cost'] ?>" 
           class="form-control">
</div>
</div>
<div class="modal-footer">
<button name="update_sale" class="btn btn-primary">Update</button>
</div>
</form>
</div>
</div>

<?php endwhile; ?>
</tbody>
</table>
</div>

<?php require_once 'partials/footer.php'; ?>