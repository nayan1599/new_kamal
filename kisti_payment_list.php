<?php
 
// মূল ডাটা
$stmt = $pdo->query("SELECT * FROM kisti_payments ORDER BY payment_date DESC, created_at DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// সামারি ক্যালকুলেশন
$totalRecords = count($records);
$totalAmount = 0;
$totalFine = 0;
$carSummary = [];

foreach ($records as $row) {
    $totalAmount += $row['amount'];
    $totalFine += $row['fine_amount'] ?? 0;
    
    $car = $row['car_number'] ?: 'অন্যান্য';
    if (!isset($carSummary[$car])) {
        $carSummary[$car] = ['count' => 0, 'total' => 0];
    }
    $carSummary[$car]['count']++;
    $carSummary[$car]['total'] += $row['amount'];
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কিস্তি পেমেন্ট লিস্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .summary-card {
            border-radius: 12px;
            transition: transform 0.3s;
        }
        .summary-card:hover { transform: translateY(-5px); }
        .table th { background-color: #0d6efd; color: white; }
        .search-box { max-width: 450px; }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- সামারি কার্ড -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card summary-card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-receipt"></i> মোট কিস্তি</h5>
                        <h2><?= $totalRecords ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-money-bill-wave"></i> মোট টাকা</h5>
                        <h2><?= number_format($totalAmount, 2) ?> ৳</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-warning text-dark">
                    <div class="card-body">
                        <h5><i class="fas fa-car"></i> গাড়ির সংখ্যা</h5>
                        <h2><?= count($carSummary) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-info text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-plus-circle"></i> মোট জরিমানা</h5>
                        <h2><?= number_format($totalFine, 2) ?> ৳</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- গাড়ি অনুযায়ী সামারি -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5><i class="fas fa-car me-2"></i>গাড়ি অনুযায়ী সামারি</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($carSummary as $car => $data): ?>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 bg-white">
                            <strong><?= htmlspecialchars($car) ?></strong><br>
                            <small class="text-muted"><?= $data['count'] ?> টি কিস্তি</small><br>
                            <strong class="text-success"><?= number_format($data['total'], 2) ?> ৳</strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- মেইন লিস্ট -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-list me-2"></i>সকল কিস্তি পেমেন্ট</h4>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> নতুন এন্ট্রি
                </a>
            </div>
            
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group search-box">
                            <input type="text" id="searchInput" class="form-control" placeholder="গ্রাহকের নাম, গাড়ি নাম্বার বা ফোন দিয়ে খুঁজুন...">
                            <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print"></i> প্রিন্ট
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="paymentTable">
                        <thead>
                            <tr>
                                <th>তারিখ</th>
                                <th>গ্রাহক</th>
                                <th>গাড়ির নাম্বার</th>
                                <th>মোবাইল</th>
                                <th>কিস্তি নং</th>
                                <th class="text-end">টাকা</th>
                                <th class="text-end">জরিমানা</th>
                                <th>মেথড</th>
                                <th>প্রাপক</th>
                                <th>অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($records as $row): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['payment_date'])) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><strong><?= htmlspecialchars($row['car_number'] ?: '—') ?></strong></td>
                                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                                <td><?= $row['kisti_number'] ?></td>
                                <td class="text-end fw-bold text-success"><?= number_format($row['amount'], 2) ?> ৳</td>
                                <td class="text-end"><?= $row['fine_amount'] ? number_format($row['fine_amount'], 2) . ' ৳' : '—' ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['payment_method'] == 'cash' ? 'success' : 'primary' ?>">
                                        <?= strtoupper($row['payment_method']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                                <td>
                                    <a href="edit_payment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_payment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('ডিলিট করতে চান?')">
                                        <i class="fas fa-trash"></i>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // লাইভ সার্চ
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchText = this.value.toLowerCase();
            let rows = document.querySelectorAll('#paymentTable tbody tr');

            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(searchText) ? '' : 'none';
            });
        });
    </script>
</body>
</html>