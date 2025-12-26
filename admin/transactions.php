<?php
require_once 'auth.php';
require_once 'config/db.php';

/* =========================
   READ TRANSACTIONS
========================= */
$transactions = $conn->query("
    SELECT 
        t.id,
        t.type,
        t.quantity,
        t.note,
        t.created_at,
        p.name AS product_name
    FROM transactions t
    LEFT JOIN products p ON p.id = t.product_id
    ORDER BY t.created_at DESC
");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3 class="mb-4">Riwayat Transaksi</h3>

    <div class="card">
        <div class="card-header fw-semibold">
            Log Perubahan Stok
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($transactions->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            Belum ada transaksi
                        </td>
                    </tr>
                <?php else: $no=1; while ($t = $transactions->fetch_assoc()): ?>

                    <?php
                        // badge color
                        $badge = 'secondary';
                        if ($t['type'] === 'IN')   $badge = 'primary';
                        if ($t['type'] === 'SALE') $badge = 'success';
                        if ($t['type'] === 'OUT')  $badge = 'danger';
                        if ($t['type'] === 'USE')  $badge = 'warning';

                        // label
                        $label = [
                            'IN'   => 'Stok Masuk',
                            'SALE' => 'Penjualan',
                            'OUT'  => 'Pengurangan',
                            'USE'  => 'Pemakaian'
                        ];
                    ?>

                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($t['created_at'])) ?></td>
                        <td><?= htmlspecialchars($t['product_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $badge ?>">
                                <?= $label[$t['type']] ?? $t['type'] ?>
                            </span>
                        </td>
                        <td><?= $t['quantity'] ?></td>
                        <td><?= htmlspecialchars($t['note'] ?? '-') ?></td>
                    </tr>

                <?php endwhile; endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
