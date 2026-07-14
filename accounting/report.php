<?php
// FILTER
$where = "";
$params = [];

if(!empty($_GET['from']) && !empty($_GET['to'])){
    $where = "WHERE DATE(created_at) BETWEEN ? AND ?";
    $params = [$_GET['from'], $_GET['to']];
}

// DATA
$stmt = $pdo->prepare("SELECT * FROM transactions $where ORDER BY created_at ASC");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// CALCULATION
$totalIn = 0;
$totalOut = 0;
$balance = 0;
?>

<div class="container-fluid mt-4">

    <!-- 🔷 HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">📊 Accounting Report</h4>

        <button onclick="window.print()" class="btn btn-dark btn-sm">
            🖨 Print
        </button>
    </div>

    <!-- 🔷 FILTER -->
    <form method="GET" action="index.php" class="row g-2 mb-3">
         <input type="hidden" name="page" value="accounting/report">
        <div class="col-md-3">
            <input type="date" name="from" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- 🔷 SUMMARY CARDS -->
    <div class="row mb-4 text-center">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Income</h6>
                    <h4 class="text-success fw-bold">
                        ৳ <?php
                        foreach($transactions as $t){ $totalIn += $t['taka_in'] ?? 0; }
                        echo bn_number(number_format($totalIn,2));
                        ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Expense</h6>
                    <h4 class="text-danger fw-bold">
                        ৳ <?php
                        foreach($transactions as $t){ $totalOut += $t['taka_out'] ?? 0; }
                        echo bn_number(number_format($totalOut,2));
                        ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Net Balance</h6>
                    <h4 class="text-primary fw-bold">
                        ৳ <?= bn_number(number_format($totalIn - $totalOut,2)) ?>
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <!-- 🔷 TABLE -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle ledger-table">

            <thead>
                <tr class="text-center table-dark">
                    <th>#</th>
                    <th>তারিখ</th>
                    <th>বিবরণ</th>
                    <th>হেড</th>
                    <th class="text-end">Income</th>
                    <th class="text-end">Expense</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>

            <tbody>
                <?php 
                $i = 0;
                $balance = 0;

                foreach($transactions as $row):

                    $in  = $row['taka_in'] ?? 0;
                    $out = $row['taka_out'] ?? 0;

                    $balance += ($in - $out);
                ?>
                <tr>
                    <td><?= ++$i ?></td>

                    <td><?= bn_number(date('d-m-Y', strtotime($row['created_at']))) ?></td>

                    <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>

                    <td>
                        <span class="badge bg-secondary">
                            <?= htmlspecialchars($row['head_name'] ?? '-') ?>
                        </span>
                    </td>

                    <td class="text-end text-success fw-semibold">
                        <?= $in ? '৳ '.bn_number(number_format($in,2)) : '-' ?>
                    </td>

                    <td class="text-end text-danger fw-semibold">
                        <?= $out ? '৳ '.bn_number(number_format($out,2)) : '-' ?>
                    </td>

                    <td class="text-end fw-bold">
                        ৳ <?= bn_number(number_format($balance,2)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <!-- FOOTER -->
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end">Total</td>
                    <td class="text-end text-success">
                        ৳ <?= bn_number(number_format($totalIn,2)) ?>
                    </td>
                    <td class="text-end text-danger">
                        ৳ <?= bn_number(number_format($totalOut,2)) ?>
                    </td>
                    <td class="text-end text-primary">
                        ৳ <?= bn_number(number_format($totalIn - $totalOut,2)) ?>
                    </td>
                </tr>
            </tfoot>

        </table>
    </div>

</div>