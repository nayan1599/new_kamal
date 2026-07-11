<?php
// ================== DATA ==================

// মোট গাড়ি
$totalCar = $pdo->query("SELECT COUNT(*) as total FROM customer_records")->fetch()['total'] ?? 0;

// মোট কিস্তি
$totalKisti = $pdo->query("SELECT SUM(total_received) as total FROM kisti_payments")->fetch()['total'] ?? 0;

// Accounting
$totalIn = $pdo->query("SELECT SUM(taka_in) as total FROM transactions")->fetch()['total'] ?? 0;
$totalOut = $pdo->query("SELECT SUM(taka_out) as total FROM transactions")->fetch()['total'] ?? 0;

$balance = $totalIn - $totalOut;

// আজকের হিসাব
$today = date('Y-m-d');
$todayData = $pdo->prepare("
    SELECT 
        SUM(taka_in) as in_total,
        SUM(taka_out) as out_total
    FROM transactions
    WHERE date = ?
");
$todayData->execute([$today]);
$todayRow = $todayData->fetch();


// Recent Data
$cars = $pdo->query("SELECT * FROM customer_records ORDER BY id DESC LIMIT 5")->fetchAll();
$kisti = $pdo->query("SELECT * FROM kisti_payments ORDER BY id DESC LIMIT 5")->fetchAll();
$transactions = $pdo->query("SELECT * FROM transactions ORDER BY id DESC LIMIT 5")->fetchAll();

?>

<div class="container-fluid py-4">

<h3 class="mb-4">📊 ড্যাশবোর্ড - জাহিরুল এন্টারপ্রাইজ</h3>

<!-- SUMMARY -->
<div class="row">

    <div class="col-md-2">
        <div class="card bg-info text-white p-3">
            <h6>মোট গাড়ি</h6>
            <h4><?= $totalCar ?></h4>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-success text-white p-3">
            <h6>মোট কিস্তি</h6>
            <h4>৳ <?= number_format($totalKisti,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-primary text-white p-3">
            <h6>মোট আয়</h6>
            <h4>৳ <?= number_format($totalIn,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-danger text-white p-3">
            <h6>মোট খরচ</h6>
            <h4>৳ <?= number_format($totalOut,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-dark text-white p-3">
            <h6>ব্যালেন্স</h6>
            <h4>৳ <?= number_format($balance,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-warning text-dark p-3">
            <h6>আজকের আয়</h6>
            <h4>৳ <?= number_format($todayRow['in_total'] ?? 0,2) ?></h4>
        </div>
    </div>

</div>

<!-- ROW 1 -->
<div class="row mt-4">

    <!-- CAR -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">🚗 নতুন গাড়ি</div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <tr><th>নাম</th><th>গাড়ি</th></tr>
                    <?php foreach($cars as $c): ?>
                    <tr>
                        <td><?= $c['customer_name'] ?></td>
                        <td><?= $c['car_number'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- KISTI -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">💳 কিস্তি</div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <tr><th>নাম</th><th>টাকা</th></tr>
                    <?php foreach($kisti as $k): ?>
                    <tr>
                        <td><?= $k['car_number'] ?></td>
                        <td>৳ <?= number_format($k['total_received'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- TRANSACTION -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">📊 লেনদেন</div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <tr><th>টাইপ</th><th>টাকা</th></tr>
                    <?php foreach($transactions as $t): ?>
                    <tr>
                        <td><?= $t['type']=='in'?'আয়':'খরচ' ?></td>
                        <td>
                            ৳ <?= number_format($t['taka_in']>0?$t['taka_in']:$t['taka_out'],2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

</div>

</div>