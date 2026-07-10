<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM kisti_payments ORDER BY payment_date DESC, id DESC");
$kistis = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কিস্তি লিস্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="mb-4">কিস্তি আদায়ের লিস্ট</h2>
    <a href="kisti_payment.php" class="btn btn-primary mb-3">+ নতুন কিস্তি আদায়</a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>তারিখ</th>
                <th>ইনভয়েস</th>
                <th>কাস্টমার</th>
                <th>কিস্তি নং</th>
                <th>টাকা</th>
                <th>ফাইন</th>
                <th>মোট</th>
                <th>পেমেন্ট মেথড</th>
                <th>নোট</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($kistis as $k): ?>
            <tr>
                <td><?= date('d-m-Y', strtotime($k['payment_date'])) ?></td>
                <td><?= htmlspecialchars($k['invoice_no']) ?></td>
                <td><?= htmlspecialchars($k['customer_name']) ?></td>
                <td><?= $k['kisti_number'] ?> তম</td>
                <td><?= number_format($k['amount'], 2) ?></td>
                <td><?= number_format($k['fine_amount'], 2) ?></td>
                <td><strong><?= number_format($k['total_received'], 2) ?></strong></td>
                <td><?= strtoupper($k['payment_method']) ?></td>
                <td><?= htmlspecialchars($k['note'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>