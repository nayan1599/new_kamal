<?php
session_start();
require_once 'db.php';

if (!isset($_GET['car_number']) || empty($_GET['car_number'])) {
    die("<h3 class='text-center mt-5 text-danger'>গাড়ির নম্বর দেয়া হয়নি!</h3>");
}
$stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC");
$records = $stmt->fetchAll();
$car_number = trim($_GET['car_number']);

// সব কিস্তি ফেচ করা
$stmt = $pdo->prepare("SELECT * FROM kisti_payments 
                       WHERE car_number = ? 
                       ORDER BY kisti_number ASC, payment_date DESC");
$stmt->execute([$car_number]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($payments)) {
    die("<h3 class='text-center mt-5 text-danger'>এই গাড়ির কোনো কিস্তি পাওয়া যায়নি!</h3>");
}

// গ্রাহকের তথ্য
$customer_name = $payments[0]['customer_name'];
$customer_phone = $payments[0]['customer_phone'];

// সামারি ক্যালকুলেশন
$totalPaid = 0;
$totalFine = 0;
$totalKistiPaid = count($payments);
$maxKisti = 0;

foreach ($payments as $p) {
    $totalPaid += $p['amount'];
    $totalFine += $p['fine_amount'] ?? 0;
    if ($p['kisti_number'] > $maxKisti) $maxKisti = $p['kisti_number'];
}

// বাকি টাকা দেখানোর জন্য (যদি মোট কন্ট্রাক্ট অ্যামাউন্ট জানা থাকে)
// এখানে উদাহরণ হিসেবে ধরে নিলাম যে মোট কিস্তি ১২ টা (আপনি চাইলে পরে ডাটাবেস থেকে আনতে পারবেন)
$totalExpectedKisti = 12; // পরে ডাটাবেস থেকে নিতে পারবেন
$expectedTotalAmount = 0; // এখানে মোট চুক্তির টাকা বসাতে পারবেন
$remainingAmount = $expectedTotalAmount - $totalPaid; // বাকি টাকা
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কিস্তি হিসাব - <?= htmlspecialchars($car_number) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%); }
        .receipt {
            max-width: 950px;
            margin: 30px auto;
            background: white;
            border: 6px solid #1e40af;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        }
        .receipt-header {
            background: linear-gradient(135deg, #1e40af, #2563eb);
            color: white;
            padding: 35px 20px;
            text-align: center;
        }
        .receipt-header h2 { margin: 0; font-size: 2.5rem; }
        .summary-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }
        .table th { background: #1e40af; color: white; }
        .amount { font-weight: bold; color: #166534; }
        .remaining { color: #b91c1c; font-weight: bold; }
        .footer {
            background: #f1f5f9;
            padding: 25px;
            text-align: center;
            border-top: 5px solid #1e40af;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100;
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="receipt-header">
        <h2>জাহিরুল এন্টারপ্রাইজ</h2>
        <h4>কিস্তি হিসাব বিবরণী</h4>
        <h5>গাড়ির নম্বর: <strong><?= htmlspecialchars($car_number) ?></strong></h5>
    </div>

    <div class="receipt-body p-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="text-primary"><i class="fas fa-user"></i> গ্রাহকের তথ্য</h5>
                <p><strong>নাম:</strong> <?= htmlspecialchars($customer_name) ?></p>
                <p><strong>মোবাইল:</strong> <?= htmlspecialchars($customer_phone) ?></p>
            </div>
            
            <!-- সামারি বক্স -->
            <div class="col-md-6">
                <div class="summary-box">
                    <h5 class="text-center text-primary mb-3">হিসাব সারাংশ</h5>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <strong>মোট কিস্তি</strong><br>
                            <h4><?= $totalKistiPaid ?> টি</h4>
                            <small>(সর্বোচ্চ <?= $maxKisti ?> নং)</small>
                        </div>
                        <div class="col-6">
                            <strong>মোট আদায়</strong><br>
                            <h4 class="amount"><?= number_format($totalPaid, 2) ?> টাকা</h4>
                        </div>
                    </div>
                    <?php if($totalFine > 0): ?>
                    <div class="text-center mt-3">
                        <strong class="text-danger">মোট জরিমানা: <?= number_format($totalFine, 2) ?> টাকা</strong>
                    </div>
                    <?php endif; ?>
                    
                    <!-- বাকি টাকা -->
                    <hr>
                    <div class="text-center">
                        <strong>বাকি টাকা:</strong><br>
                        <h3 class="remaining"><?= number_format($remainingAmount, 2) ?> টাকা</h3>
                        <small class="text-muted">(মোট চুক্তির টাকা অনুযায়ী)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- কিস্তির টেবিল -->
        <h5 class="mb-3 text-primary"><i class="fas fa-list-ul"></i> কিস্তির বিস্তারিত তথ্য</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>কিস্তি নং</th>
                        <th class="text-end">মূল টাকা</th>
                        <th class="text-end">জরিমানা</th>
                        <th class="text-end">মোট</th>
                        <th>মেথড</th>
                        <th>প্রাপক</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $row): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['payment_date'])) ?></td>
                        <td><strong><?= $row['kisti_number'] ?></strong></td>
                        <td class="text-end amount"><?= number_format($row['amount'], 2) ?></td>
                        <td class="text-end"><?= $row['fine_amount'] ? number_format($row['fine_amount'], 2) : '—' ?></td>
                        <td class="text-end fw-bold"><?= number_format($row['amount'] + ($row['fine_amount'] ?? 0), 2) ?></td>
                        <td><?= strtoupper(htmlspecialchars($row['payment_method'])) ?></td>
                        <td><?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>ধন্যবাদ! আপনার সাথে ব্যবসা করতে পেরে আমরা আনন্দিত।</p>
        <small>এই ডকুমেন্ট কম্পিউটার জেনারেটেড এবং অফিসিয়াল।</small>
    </div>
</div>

<button onclick="window.print()" class="btn btn-primary btn-lg print-btn">
    <i class="fas fa-print"></i> প্রিন্ট করুন
</button>

<div class="text-center mt-4 mb-5">
    <a href="kisti_payment_list.php" class="btn btn-secondary btn-lg">← লিস্টে ফিরুন</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>