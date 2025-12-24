<?php
require_once 'auth.php';
require_once 'config/db.php';

// PROSES PENJUALAN
if (isset($_POST['sell'])) {
    $product_id = $_POST['product_id'];
    $qty        = (int)$_POST['qty'];

    // ambil data produk
    $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

    if ($product && $qty > 0 && $qty <= $product['stock']) {
        $price_sell = $product['price_sell'];
        $price_buy  = $product['price_buy'];

        $total  = $qty * $price_sell;
        $profit = $qty * ($price_sell - $price_buy);

        // simpan ke sales
        $stmt = $conn->prepare("
            INSERT INTO sales (product_id, qty, price_sell, total, profit)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiddd", $product_id, $qty, $price_sell, $total, $profit);
        $stmt->execute();

        // kurangi stok
        $newStock = $product['stock'] - $qty;
        $conn->query("UPDATE products SET stock = $newStock WHERE id = $product_id");

        $success = "Penjualan berhasil disimpan";
    } else {
        $error = "Stok tidak mencukupi";
    }
}

// ambil produk
$products = $conn->query("SELECT * FROM products WHERE stock > 0");

// rekap
$rekap = $conn->query("
    SELECT 
        COUNT(id) as total_transaksi,
        SUM(total) as omzet,
        SUM(profit) as profit
    FROM sales
")->fetch_assoc();
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="container-fluid p-4">

    <h4 class="mb-3">Penjualan Produk</h4>

    <?php if (isset($success)) : ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- FORM PENJUALAN -->
    <div class="card mb-4">
        <div class="card-header">Input Penjualan</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Produk</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php while ($p = $products->fetch_assoc()) : ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= $p['name'] ?> (stok: <?= $p['stock'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Qty</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button name="sell" class="btn btn-primary w-100">
                            Simpan Penjualan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- REKAP -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Total Transaksi</h6>
                    <h4><?= $rekap['total_transaksi'] ?? 0 ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Omzet</h6>
                    <h4>Rp <?= number_format($rekap['omzet'] ?? 0) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Profit</h6>
                    <h4 class="text-success">
                        Rp <?= number_format($rekap['profit'] ?? 0) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- BUTTON LAPORAN -->
    <div class="mt-4">
        <a href="sales-report.php" class="btn btn-secondary">
            Laporan Penjualan
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            Print
        </button>
    </div>

</div>

<?php include 'partials/footer.php'; ?>
