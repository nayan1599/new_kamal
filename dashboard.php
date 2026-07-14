<?php
$today = date('Y-m-d');

// ================== FILTER ==================
$from = $_GET['from'] ?? $today;
$to   = $_GET['to'] ?? $today;

$where = "";
$params = [];

if(!empty($from) && !empty($to)){
    $where = "WHERE DATE(created_at) BETWEEN ? AND ?";
    $params = [$from, $to];
}

// ================== SUMMARY ==================

// মোট গাড়ি
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM customer_records WHERE 1=1 ".(!empty($from) ? "AND DATE(created_at) BETWEEN ? AND ?" : ""));
$stmt->execute(!empty($from) ? [$from,$to] : []);
$totalCar = $stmt->fetch()['total'] ?? 0;

// মোট কিস্তি
$stmt = $pdo->prepare("
    SELECT SUM(total_received) as total 
    FROM kisti_payments
    ".(!empty($from) ? "WHERE DATE(payment_date) BETWEEN ? AND ?" : "")
);
$stmt->execute(!empty($from) ? [$from,$to] : []);
$totalKisti = $stmt->fetch()['total'] ?? 0;

// Accounting
$stmt = $pdo->prepare("SELECT SUM(taka_in) as total FROM transactions $where");
$stmt->execute($params);
$totalIn = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT SUM(taka_out) as total FROM transactions $where");
$stmt->execute($params);
$totalOut = $stmt->fetch()['total'] ?? 0;

$balance = $totalIn - $totalOut;

// আজকের হিসাব
$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT 
        SUM(taka_in) as in_total,
        SUM(taka_out) as out_total
    FROM transactions
    WHERE DATE(created_at) = ?
");
$stmt->execute([$today]);
$todayRow = $stmt->fetch();

// ================== RECENT DATA ==================

// Cars
$stmt = $pdo->prepare("
    SELECT * FROM customer_records 
    ".(!empty($from) ? "WHERE DATE(created_at) BETWEEN ? AND ?" : "")."
    ORDER BY id DESC LIMIT 5
");
$stmt->execute(!empty($from) ? [$from,$to] : []);
$cars = $stmt->fetchAll();

// Kisti
$stmt = $pdo->prepare("
    SELECT * FROM kisti_payments 
    ".(!empty($from) ? "WHERE DATE(payment_date) BETWEEN ? AND ?" : "")."
    ORDER BY id DESC LIMIT 5
");
$stmt->execute(!empty($from) ? [$from,$to] : []);
$kisti = $stmt->fetchAll();

// Transactions
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    $where
    ORDER BY id DESC LIMIT 5
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

?>

<div class="container-fluid py-4">

<h3 class="mb-4">📊 ড্যাশবোর্ড - জাহিরুল এন্টারপ্রাইজ</h3>

<!-- ================= FILTER ================= -->
<form method="GET" class="row g-2 mb-4">

    <input type="hidden" name="page" value="dashboard">

    <div class="col-md-3">
        <input type="date" name="from" value="<?= $from ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" name="to" value="<?= $to ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary w-100">🔍 Filter</button>
    </div>

    <div class="col-md-2">
        <a href="index.php?page=dashboard" class="btn btn-secondary w-100">Reset</a>
    </div>

</form>

<!-- ================= SUMMARY ================= -->
<div class="row">

    <div class="col-md-2 my-2">
        <div class="card bg-info text-white p-3">
            <h6>মোট গাড়ি</h6>
            <h4><?= $totalCar ?></h4>
        </div>
    </div>

    <div class="col-md-2 my-2">
        <div class="card bg-success text-white p-3">
            <h6>মোট কিস্তি</h6>
            <h4>৳ <?= number_format($totalKisti,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2 my-2">
        <div class="card bg-primary text-white p-3">
            <h6>মোট আয়</h6>
            <h4>৳ <?= number_format($totalIn,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2 my-2">
        <div class="card bg-danger text-white p-3">
            <h6>মোট খরচ</h6>
            <h4>৳ <?= number_format($totalOut,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2 my-2">
        <div class="card bg-dark text-white p-3">
            <h6>ব্যালেন্স</h6>
            <h4>৳ <?= number_format($balance,2) ?></h4>
        </div>
    </div>

    <div class="col-md-2 my-2">
        <div class="card bg-warning text-dark p-3">
            <h6>আজকের আয়</h6>
            <h4>৳ <?= number_format($todayRow['in_total'] ?? 0,2) ?></h4>
        </div>
    </div>

</div>

<!-- ================= TABLES ================= -->
<div class="row mt-4">

    <!-- CAR -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">🚗 নতুন গাড়ি</div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <tr><th>নাম</th><th>গাড়ি</th></tr>
                    <?php foreach($cars as $c): ?>
                    <tr onclick="window.location='index.php?page=car/receipt&id=<?= $c['id'] ?>'" style="cursor:pointer;">
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
                    <tr><th>গাড়ি</th><th>টাকা</th></tr>
                    <?php foreach($kisti as $k): ?>
                    <tr onclick="window.location='index.php?page=payment/view&id=<?= $k['id'] ?>'" style="cursor:pointer;">
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