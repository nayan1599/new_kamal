<?php

$today = date('Y-m-d');

$from_date = $_GET['from_date'] ?? $today;
$to_date   = $_GET['to_date'] ?? $today;
$garage_id = $_GET['garage_id'] ?? '';


// =========================
// GARAGE LIST
// =========================

$garageStmt = $pdo->query("
    SELECT *
    FROM garages
    WHERE status = 'active'
    ORDER BY id ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// SUMMARY QUERY
// =========================

$where = "
    transaction_date BETWEEN :from_date AND :to_date
";

$params = [
    ':from_date' => $from_date,
    ':to_date'   => $to_date
];


if ($garage_id !== '') {

    $where .= " AND garage_id = :garage_id";

    $params[':garage_id'] = $garage_id;
}


$stmt = $pdo->prepare("
    SELECT
        garage_id,

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'income'
                    THEN amount
                    ELSE 0
                END
            ), 0
        ) AS total_income,

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'expense'
                    THEN amount
                    ELSE 0
                END
            ), 0
        ) AS total_expense

    FROM garage_transactions

    WHERE $where

    GROUP BY garage_id

    ORDER BY garage_id ASC
");

$stmt->execute($params);

$summaries = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// SUMMARY MAP
// =========================

$garageSummary = [];

foreach ($summaries as $row) {

    $income = (float)$row['total_income'];
    $expense = (float)$row['total_expense'];

    $garageSummary[$row['garage_id']] = [
        'income' => $income,
        'expense' => $expense,
        'balance' => $income - $expense
    ];
}


// =========================
// GRAND TOTAL
// =========================

$grand_income = 0;
$grand_expense = 0;

foreach ($garageSummary as $summary) {

    $grand_income += $summary['income'];
    $grand_expense += $summary['expense'];

}

$grand_balance =
    $grand_income - $grand_expense;


// =========================
// RECENT TRANSACTIONS
// =========================

$transactionWhere = "
    gt.transaction_date
    BETWEEN :from_date
    AND :to_date
";

$transactionParams = [
    ':from_date' => $from_date,
    ':to_date' => $to_date
];


if ($garage_id !== '') {

    $transactionWhere .= "
        AND gt.garage_id = :garage_id
    ";

    $transactionParams[':garage_id'] = $garage_id;
}


$transactionStmt = $pdo->prepare("
    SELECT
        gt.*,
        g.garage_name

    FROM garage_transactions gt

    LEFT JOIN garages g
        ON g.id = gt.garage_id

    WHERE $transactionWhere

    ORDER BY
        gt.transaction_date DESC,
        gt.id DESC

    LIMIT 100
");

$transactionStmt->execute($transactionParams);

$transactions =
    $transactionStmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="container-fluid p-4">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                🏢 গ্যারেজ হিসাব
            </h3>

            <div class="text-muted">
                দৈনিক আয় ও ব্যয়ের হিসাব
            </div>

        </div>


        <a
            href="index.php?page=garage/add"
            class="btn btn-primary"
        >

            <i class="bi bi-plus-circle"></i>

            নতুন হিসাব

        </a>

    </div>


    <!-- FILTER -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <input
                    type="hidden"
                    name="page"
                    value="garage/index"
                >

                <div class="row g-3 align-items-end">


                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            গ্যারেজ
                        </label>

                        <select
                            name="garage_id"
                            class="form-select"
                        >

                            <option value="">
                                সব গ্যারেজ
                            </option>

                            <?php foreach ($garages as $garage): ?>

                                <option
                                    value="<?= $garage['id'] ?>"
                                    <?= $garage_id == $garage['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $garage['garage_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            শুরু
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="<?= htmlspecialchars($from_date) ?>"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            শেষ
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="<?= htmlspecialchars($to_date) ?>"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-3">

                        <button
                            class="btn btn-dark w-100"
                        >

                            🔍 হিসাব দেখুন

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- GRAND TOTAL -->

    <div class="row g-3 mb-4">


        <div class="col-md-4">

            <div class="card bg-success text-white shadow-sm">

                <div class="card-body">

                    <div>
                        মোট আয়
                    </div>

                    <h3 class="fw-bold">

                        ৳ <?= bn_number(
                            number_format(
                                $grand_income,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card bg-danger text-white shadow-sm">

                <div class="card-body">

                    <div>
                        মোট ব্যয়
                    </div>

                    <h3 class="fw-bold">

                        ৳ <?= bn_number(
                            number_format(
                                $grand_expense,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card <?= $grand_balance >= 0
                ? 'bg-primary'
                : 'bg-warning'
            ?> text-white shadow-sm">

                <div class="card-body">

                    <div>
                        বর্তমান ব্যালেন্স
                    </div>

                    <h3 class="fw-bold">

                        ৳ <?= bn_number(
                            number_format(
                                $grand_balance,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>

    </div>


    <!-- GARAGE CARDS -->

    <div class="row g-3 mb-4">

        <?php foreach ($garages as $garage): ?>

            <?php

            $gid = $garage['id'];

            $income =
                $garageSummary[$gid]['income']
                ?? 0;

            $expense =
                $garageSummary[$gid]['expense']
                ?? 0;

            $balance =
                $garageSummary[$gid]['balance']
                ?? 0;

            ?>

            <div class="col-md-6">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h5 class="fw-bold mb-3">

                            🏢
                            <?= htmlspecialchars(
                                $garage['garage_name']
                            ) ?>

                        </h5>


                        <div class="row">


                            <div class="col-4">

                                <small class="text-muted">
                                    আয়
                                </small>

                                <div class="fw-bold text-success">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $income,
                                            2
                                        )
                                    ) ?>

                                </div>

                            </div>


                            <div class="col-4">

                                <small class="text-muted">
                                    ব্যয়
                                </small>

                                <div class="fw-bold text-danger">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $expense,
                                            2
                                        )
                                    ) ?>

                                </div>

                            </div>


                            <div class="col-4">

                                <small class="text-muted">
                                    ব্যালেন্স
                                </small>

                                <div class="fw-bold <?= $balance >= 0
                                    ? 'text-primary'
                                    : 'text-danger'
                                ?>">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $balance,
                                            2
                                        )
                                    ) ?>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>


    <!-- TRANSACTION TABLE -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">
                📋 লেনদেনের তালিকা
            </h5>

        </div>


        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead class="table-dark">

                    <tr>

                        <th>
                            তারিখ
                        </th>

                        <th>
                            গ্যারেজ
                        </th>

                        <th>
                            ধরন
                        </th>

                        <th>
                            খাত
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            বিবরণ
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($transactions): ?>

                    <?php foreach ($transactions as $row): ?>

                        <tr>

                            <td>

                                <?= bn_number(
                                    date(
                                        'd-m-Y',
                                        strtotime(
                                            $row['transaction_date']
                                        )
                                    )
                                ) ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['garage_name']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?php if (
                                    $row['type'] === 'income'
                                ): ?>

                                    <span class="badge bg-success">
                                        আয়
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-danger">
                                        ব্যয়
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['category']
                                ) ?>

                            </td>


                            <td class="fw-bold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $row['amount'],
                                        2
                                    )
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['description']
                                    ?? ''
                                ) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="6"
                            class="text-center py-5 text-muted"
                        >

                            কোনো লেনদেন পাওয়া যায়নি।

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>