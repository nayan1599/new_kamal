<?php
// require_once 'db.php';

// Filter
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$car  = $_GET['car_number'] ?? '';

// Dynamic WHERE
$where = [];
$params = [];

// Date filter
if (!empty($from) && !empty($to)) {
    $where[] = "rent_date BETWEEN :from AND :to";
    $params[':from'] = $from;
    $params[':to']   = $to;
}

// Car filter (LIKE for better search)
if (!empty($car)) {
    $where[] = "car_number LIKE :car";
    $params[':car'] = "%$car%";
}

// Final WHERE
$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

// Query
$sql = "SELECT * FROM rents $whereSQL ORDER BY rent_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary
$totalRent = 0;
$totalAdvance = 0;
$totalDue = 0;

foreach ($rows as $r) {
    $totalRent += $r['rent_amount'];
    $totalAdvance += $r['advance_amount'];
    $totalDue += $r['due_amount'];
}
?>

<div class="container-fluid mt-4">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📊 ভাড়া রিপোর্ট</h4>
    <button onclick="window.print()" class="btn btn-dark btn-sm">🖨️ প্রিন্ট</button>
</div>

<!-- ✅ Filter Form -->
<form method="GET" action="index.php" class="row g-2 mb-3">

    <!-- 🔥 Important -->
    <input type="hidden" name="page" value="rent/report">

    <div class="col-md-3">
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="text" name="car_number" value="<?= htmlspecialchars($car) ?>" placeholder="গাড়ি নম্বর" class="form-control">
    </div>

    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100">ফিল্টার</button>
        <a href="index.php?page=rent/report" class="btn btn-secondary w-100">রিসেট</a>
    </div>

</form>

<!-- Summary -->
<div class="row text-center mb-3">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <h6>মোট ভাড়া</h6>
                <h5>৳ <?= bn_number(number_format($totalRent, 2)) ?></h5>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <h6>মোট অগ্রিম</h6>
                <h5>৳ <?= bn_number(number_format($totalAdvance, 2)) ?></h5>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-danger text-white shadow">
            <div class="card-body">
                <h6>মোট বকেয়া</h6>
                <h5>৳ <?= bn_number(number_format($totalDue, 2)) ?></h5>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card shadow">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr class="text-white">
                        <th>তারিখ</th>
                        <th>গাড়ি নম্বর</th>
                        <th>গ্রাহক</th>
                        <th>মোবাইল</th>
                        <th>ভাড়া</th>
                        <th>অগ্রিম</th>
                        <th>বকেয়া</th>
                        <th>মাস</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= bn_number(htmlspecialchars($row['rent_date'])) ?></td>
                        <td><?= bn_number(htmlspecialchars($row['car_number'])) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>

                        <td>৳ <?= bn_number(number_format($row['rent_amount'], 2)) ?></td>
                        <td class="text-success">৳ <?= bn_number(number_format($row['advance_amount'], 2)) ?></td>
                        <td class="text-danger fw-bold">৳ <?= bn_number(number_format($row['due_amount'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['rent_month']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">কোন ডাটা পাওয়া যায়নি ❌</td>
                    </tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>

</div>

<style>
@media print {
    button, form {
        display: none !important;
    }
}
</style>