<?php


// মূল ডাটা
$today = date('Y-m-d');

$stmt = $pdo->query("SELECT * FROM kisti_payments WHERE payment_date = '$today' ORDER BY payment_date DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// সামারি ক্যালকুলেশন
$totalRecords = count($records);
$totalAmount = 0;
$totalFine = 0;
$todayAmount = 0;
$carSummary = [];

$today = date('Y-m-d');

foreach ($records as $row) {
    $totalAmount += $row['amount'] ?? 0;
    $totalFine += $row['fine_amount'] ?? 0;
    
    if (date('Y-m-d', strtotime($row['payment_date'])) === $today) {
        $todayAmount += $row['amount'] ?? 0;
    }
    
    $car = $row['car_number'] ?: 'অন্যান্য';
    if (!isset($carSummary[$car])) {
        $carSummary[$car] = ['count' => 0, 'total' => 0];
    }
    $carSummary[$car]['count']++;
    $carSummary[$car]['total'] += $row['amount'] ?? 0;
}
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

    <!-- Summary Cards -->
    <div class="row g-3 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="opacity-75">মোট কিস্তি</h6>
                            <h2 class="mb-0"><?= $totalRecords ?></h2>
                        </div>
                        <i class="fas fa-receipt fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="opacity-75">মোট আদায়</h6>
                            <h2 class="mb-0"><?= number_format($totalAmount, 2) ?> ৳</h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="opacity-75">আজকের আদায়</h6>
                            <h2 class="mb-0"><?= number_format($todayAmount, 2) ?> ৳</h2>
                        </div>
                        <i class="fas fa-calendar-day fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="opacity-75">মোট জরিমানা</h6>
                            <h2 class="mb-0"><?= number_format($totalFine, 2) ?> ৳</h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- গাড়ি অনুযায়ী সামারি -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-car me-2"></i>গাড়ি অনুযায়ী সামারি</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach($carSummary as $car => $data): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= htmlspecialchars($car) ?></h6>
                            <small class="text-muted"><?= $data['count'] ?> টি পেমেন্ট</small>
                        </div>
                        <div class="text-end">
                            <h5 class="text-success mb-0"><?= number_format($data['total'], 2) ?> ৳</h5>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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
                            <th>গ্রাহক</th>
                            <th>গাড়ির নং</th>
                            <th>মোবাইল</th>
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
                            <td><?= date('d/m/Y', strtotime($row['payment_date'])) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><strong><?= htmlspecialchars($row['car_number'] ?: '—') ?></strong></td>
                            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                            <td><?= $row['kisti_number'] ?? '—' ?></td>
                            <td class="text-end fw-bold text-success">৳ <?= number_format($row['amount'], 2) ?></td>
                            <td class="text-end text-danger">
                                <?= $row['fine_amount'] ? '৳ ' . number_format($row['fine_amount'], 2) : '—' ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['payment_method'] == 'cash' ? 'success' : 'info' ?>">
                                    <?= strtoupper($row['payment_method']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'paid' ? 'success' : 'warning' ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                            <td class="text-center">
                                <a href="index.php?page=payment/edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                    edit
                                </a>
                                <a href="delete_payment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('এই রেকর্ড ডিলিট করতে চান?')">
                                     delete
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