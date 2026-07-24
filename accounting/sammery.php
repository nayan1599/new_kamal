<?php 
 
 

$head_name = trim($_GET['head_name'] ?? '');

// সব transaction (এই head অনুযায়ী)
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE head_name = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$head_name]);
$records = $stmt->fetchAll();

// summary হিসাব
$sumStmt = $pdo->prepare("
    SELECT 
        SUM(taka_in) as total_in,
        SUM(taka_out) as total_out
    FROM transactions 
    WHERE head_name = ?
");
$sumStmt->execute([$head_name]);
$summary = $sumStmt->fetch();

// balance
$balance = ($summary['total_in'] ?? 0) - ($summary['total_out'] ?? 0);

 
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📊 হেড অনুযায়ী হিসাব</h4>
        <a href="index.php?page=accounting/index" class="btn btn-secondary btn-sm">
            ← ফিরে যান
        </a>
    </div>

    <!-- Head Title -->
    <div class="alert alert-primary">
        <strong>হেড:</strong> <?= $head_name ?>
    </div>

    <!-- Summary Card -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট জমা</h6>
                    <h4 class="text-primary">
                        ৳ <?= bn_number(number_format($summary['total_in'] ?? 0, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট খরচ</h6>
                    <h4 class="text-danger">
                        ৳ <?= bn_number(number_format($summary['total_out'] ?? 0, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted">ব্যালেন্স</h6>
                    <h4 class="<?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                        ৳ <?= bn_number(number_format($balance, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>তারিখ</th>
                        <th>জমা</th>
                        <th>খরচ</th>
                        <th>বিবরণ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($records as $row): ?>
                    <tr>
                        <td><?= bn_number(date('d-m-Y', strtotime($row['date']))) ?></td>

                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['taka_in'],2)) ?>
                        </td>

                        <td class="text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($row['taka_out'],2)) ?>
                        </td>

                        <td><?= $row['description'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>