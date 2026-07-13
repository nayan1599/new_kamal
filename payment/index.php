<?php


// মূল ডাটা
// $today = date('Y-m-d');

$stmt = $pdo->query("SELECT * FROM kisti_payments ORDER BY payment_date DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
?>

<div class="container-fluid px-3 px-lg-4 py-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">কিস্তি পেমেন্ট ম্যানেজমেন্ট</h1>
            <p class="text-muted">সকল কিস্তি আদায়ের রেকর্ড ও সামারি</p>
        </div>
        <a href="index.php?page=payment/add" class="btn btn-success btn-lg">
            <i class="fas fa-plus me-2"></i> নতুন কিস্তি আদায়
        </a>
    </div>
 
    <!-- মেইন টেবিল -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>সকল কিস্তি পেমেন্ট রেকর্ড</h5>
            
            <div class="d-flex gap-2">
                <input type="text" id="searchInput" class="form-control form-control-sm w-300" 
                       placeholder="নাম, গাড়ি নং, ফোন দিয়ে সার্চ করুন...">
                <button class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> প্রিন্ট
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentTable">
                    <thead class="table-light">
                        <tr>
                            <th>তারিখ</th>                          
                            <th>গাড়ির নং</th>
                            
                            <th>কিস্তি নং</th>
                            <th class="text-end">টাকা</th>
                            <th class="text-end">জরিমানা</th>
                            <th>মেথড</th>
                            <th>স্ট্যাটাস</th>
                            <th>প্রাপক</th>
                            <th class="text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($records as $row): ?>
                        <tr>
                            <td><?= bn_number(date('d/m/Y', strtotime($row['payment_date']))) ?></td>
                          
                            <td><strong><?= htmlspecialchars($row['car_number'] ?: '—') ?></strong></td>
                            
                            <td><?= $row['kisti_number'] ?? '—' ?></td>
                            <td class="text-end fw-bold text-success">৳ <?= bn_number(number_format($row['amount'], 2)) ?></td>
                            <td class="text-end text-danger">
                                <?= $row['fine_amount'] ? '৳ ' . bn_number(number_format($row['fine_amount'], 2)) : '—' ?>
                            </td>
                            <td>
                                <?php $methodMap = [ 'cash' => 'ক্যাশ', 'bank_transfer' => 'ব্যাংক ট্রান্সফার', 'bkash' => 'বিকাশ', 'nagad' => 'নগদ', 'rocket' => 'রকেট', 'cheque' => 'চেক', 'others' => 'অন্যান্য' ]; $method = $row['payment_method'] ?? ''; $methodText = $methodMap[$method] ?? $method; ?>

<span class="badge bg-<?= $method == 'cash' ? 'success' : 'info' ?>"> <?= $methodText ?> </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'paid' ? 'success' : 'warning' ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                            <td class="text-center">
<!-- view  -->

 <a href="index.php?page=payment/view&id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
        View
    </a>

    <a href="index.php?page=payment/edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
        সম্পাদনা
    </a>

<a href="delete_payment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
   onclick="return confirm('আপনি কি এই রেকর্ডটি মুছে ফেলতে চান?')">
    মুছুন
</a>


</td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// লাইভ সার্চ
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase().trim();
    let rows = document.querySelectorAll('#paymentTable tbody tr');

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script>

<style>
.hover-shadow:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    transform: translateY(-2px);
}
.w-300 { width: 300px; }
</style>