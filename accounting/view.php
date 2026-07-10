<?php 

if (!isset($_GET['transactions_id']) || empty($_GET['transactions_id'])) {
    die("<h3 class='text-center mt-5 text-danger'>❌ ট্রানজেকশন আইডি পাওয়া যায়নি!</h3>");
}

$transactions_id = trim($_GET['transactions_id']);

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? LIMIT 1");
$stmt->execute([$transactions_id]);
$transactions = $stmt->fetch();

if (!$transactions) {
    die("<h3 class='text-center mt-5 text-danger'>❌ কোন ডাটা পাওয়া যায়নি!</h3>");
}
?>

<div class="container mt-4">

    <div class="card shadow-lg border-0">
        
        <!-- Header -->
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📄 ট্রানজেকশন বিস্তারিত</h5>
            <a href="index.php?page=transactions/list" class="btn btn-sm btn-light">⬅ ফিরে যান</a>
        </div>

        <!-- Body -->
        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="text-muted">📅 তারিখ</label>
                    <h6><?= htmlspecialchars($transactions['created_at']) ?></h6>
                </div>

              

                <div class="col-md-4">
                    <label class="text-muted">💰 টাকা</label>
                    <h5 class="text-primary">৳ <?= number_format($transactions['taka_in'], 2) ?></h5>
                </div>

                <div class="col-md-4">
                    <label class="text-muted">⚠️ জরিমানা</label>
                    <h5 class="text-warning">৳ <?= number_format($transactions['taka_out'], 2) ?></h5>
                </div>

                <div class="col-md-4">
                    <label class="text-muted">🧾 মোট প্রাপ্ত</label>
                    <h5 class="text-success">৳ <?= number_format($transactions['total_received'], 2) ?></h5>
                </div>

                <div class="col-md-6">
                    <label class="text-muted">💳 পেমেন্ট মাধ্যম</label>
                    <h6>
                        <span class="badge bg-info">
                            <?= strtoupper($transactions['payment_method']) ?>
                        </span>
                    </h6>
                </div>

                <div class="col-md-6">
                    <label class="text-muted">🔢 ট্রানজেকশন আইডি</label>
                    <h6><?= htmlspecialchars($transactions['transaction_id'] ?? 'N/A') ?></h6>
                </div>

                <div class="col-12">
                    <label class="text-muted">📝 নোট</label>
                    <p class="border p-2 rounded bg-light">
                        <?= htmlspecialchars($transactions['note'] ?? 'কোন নোট নেই') ?>
                    </p>
                </div>

            </div>

        </div>

        <!-- Footer -->
        <div class="card-footer text-end">
            <button onclick="window.print()" class="btn btn-dark btn-sm">🖨️ প্রিন্ট</button>
        </div>

    </div>

</div>

<style>
@media print {
    .btn, .card-header, .card-footer {
        display: none !important;
    }
}
</style>