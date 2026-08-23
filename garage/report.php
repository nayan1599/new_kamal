<?php

// =====================================================
// GARAGE REPORT
// =====================================================

$today = date('Y-m-d');


// =====================================================
// FILTER
// =====================================================

$from_date = $_GET['from_date'] ?? $today;
$to_date   = $_GET['to_date'] ?? $today;
$garage_id = $_GET['garage_id'] ?? '';


// =====================================================
// GARAGE LIST
// =====================================================

$garageStmt = $pdo->query("
    SELECT *
    FROM garages
    WHERE status = 'active'
    ORDER BY id ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// MAIN WHERE
// =====================================================

$where = "
    gt.transaction_date BETWEEN :from_date AND :to_date
";

$params = [
    ':from_date' => $from_date,
    ':to_date'   => $to_date
];


if ($garage_id !== '') {

    $where .= "
        AND gt.garage_id = :garage_id
    ";

    $params[':garage_id'] = (int)$garage_id;
}


// =====================================================
// TOTAL SUMMARY
// =====================================================

$summaryStmt = $pdo->prepare("
    SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN gt.type = 'income'
                    THEN gt.amount
                    ELSE 0
                END
            ),
            0
        ) AS total_income,

        COALESCE(
            SUM(
                CASE
                    WHEN gt.type = 'expense'
                    THEN gt.amount
                    ELSE 0
                END
            ),
            0
        ) AS total_expense,

        COUNT(gt.id) AS total_transaction

    FROM garage_transactions gt

    WHERE $where
");

$summaryStmt->execute($params);

$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);


$total_income =
    (float)($summary['total_income'] ?? 0);

$total_expense =
    (float)($summary['total_expense'] ?? 0);

$total_transaction =
    (int)($summary['total_transaction'] ?? 0);

$total_balance =
    $total_income - $total_expense;


// =====================================================
// GARAGE WISE SUMMARY
// =====================================================

$garageSummaryStmt = $pdo->prepare("
    SELECT

        g.id,
        g.garage_name,

        COALESCE(
            SUM(
                CASE
                    WHEN gt.type = 'income'
                    THEN gt.amount
                    ELSE 0
                END
            ),
            0
        ) AS total_income,

        COALESCE(
            SUM(
                CASE
                    WHEN gt.type = 'expense'
                    THEN gt.amount
                    ELSE 0
                END
            ),
            0
        ) AS total_expense

    FROM garages g

    LEFT JOIN garage_transactions gt
        ON gt.garage_id = g.id

        AND gt.transaction_date
        BETWEEN :from_date
        AND :to_date

    WHERE g.status = 'active'
");


$garageParams = [
    ':from_date' => $from_date,
    ':to_date' => $to_date
];


if ($garage_id !== '') {

    $garageSummaryStmt =
        $pdo->prepare("
            SELECT

                g.id,
                g.garage_name,

                COALESCE(
                    SUM(
                        CASE
                            WHEN gt.type = 'income'
                            THEN gt.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN gt.type = 'expense'
                            THEN gt.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_expense

            FROM garages g

            LEFT JOIN garage_transactions gt
                ON gt.garage_id = g.id

                AND gt.transaction_date
                BETWEEN :from_date
                AND :to_date

            WHERE g.status = 'active'
            AND g.id = :garage_id

            GROUP BY g.id, g.garage_name

            ORDER BY g.id ASC
        ");

    $garageParams[':garage_id'] =
        (int)$garage_id;
}


if ($garage_id === '') {

    $garageSummaryStmt =
        $pdo->prepare("
            SELECT

                g.id,
                g.garage_name,

                COALESCE(
                    SUM(
                        CASE
                            WHEN gt.type = 'income'
                            THEN gt.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN gt.type = 'expense'
                            THEN gt.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_expense

            FROM garages g

            LEFT JOIN garage_transactions gt
                ON gt.garage_id = g.id

                AND gt.transaction_date
                BETWEEN :from_date
                AND :to_date

            WHERE g.status = 'active'

            GROUP BY
                g.id,
                g.garage_name

            ORDER BY g.id ASC
        ");

}


$garageSummaryStmt->execute(
    $garageParams
);

$garage_summaries =
    $garageSummaryStmt->fetchAll(
        PDO::FETCH_ASSOC
    );


// =====================================================
// CATEGORY WISE REPORT
// =====================================================

$categoryStmt = $pdo->prepare("
    SELECT

        gt.type,
        gt.category,

        SUM(gt.amount) AS total_amount,
        COUNT(gt.id) AS total_count

    FROM garage_transactions gt

    WHERE $where

    GROUP BY
        gt.type,
        gt.category

    ORDER BY
        gt.type ASC,
        total_amount DESC
");

$categoryStmt->execute($params);

$categories =
    $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// TRANSACTION DETAILS
// =====================================================

$transactionStmt = $pdo->prepare("
    SELECT

        gt.*,
        g.garage_name

    FROM garage_transactions gt

    LEFT JOIN garages g
        ON g.id = gt.garage_id

    WHERE $where

    ORDER BY
        gt.transaction_date DESC,
        gt.id DESC
");

$transactionStmt->execute($params);

$transactions =
    $transactionStmt->fetchAll(
        PDO::FETCH_ASSOC
    );

?>


<div class="container-fluid p-4">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                📊 গ্যারেজ রিপোর্ট
            </h3>

            <div class="text-muted">
                গ্যারেজের আয় ও ব্যয়ের বিস্তারিত হিসাব
            </div>

        </div>


        <div class="d-flex gap-2">

            <button
                onclick="window.print()"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-printer"></i>
                Print
            </button>


            <a
                href="index.php?page=garage/add"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-circle"></i>

                নতুন হিসাব

            </a>

        </div>

    </div>



    <!-- =================================================
         FILTER
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="index.php"
            >

                <input
                    type="hidden"
                    name="page"
                    value="garage/report"
                >


                <div class="row g-3 align-items-end">


                    <!-- GARAGE -->

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
                                    value="<?= (int)$garage['id'] ?>"
                                    <?= (
                                        (string)$garage_id ===
                                        (string)$garage['id']
                                    )
                                    ? 'selected'
                                    : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $garage['garage_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>



                    <!-- FROM -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            শুরু তারিখ
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="<?= htmlspecialchars($from_date) ?>"
                            class="form-control"
                            required
                        >

                    </div>



                    <!-- TO -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            শেষ তারিখ
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="<?= htmlspecialchars($to_date) ?>"
                            class="form-control"
                            required
                        >

                    </div>



                    <!-- BUTTON -->

                    <div class="col-md-3">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >

                            <i class="bi bi-search"></i>

                            রিপোর্ট দেখুন

                        </button>

                    </div>


                </div>

            </form>

        </div>

    </div>



    <!-- =================================================
         SUMMARY CARDS
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- INCOME -->

        <div class="col-md-3">

            <div class="card bg-success text-white border-0 shadow-sm">

                <div class="card-body">

                    <small>
                        মোট আয়
                    </small>

                    <h3 class="fw-bold mb-0">

                        ৳ <?= bn_number(
                            number_format(
                                $total_income,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>



        <!-- EXPENSE -->

        <div class="col-md-3">

            <div class="card bg-danger text-white border-0 shadow-sm">

                <div class="card-body">

                    <small>
                        মোট ব্যয়
                    </small>

                    <h3 class="fw-bold mb-0">

                        ৳ <?= bn_number(
                            number_format(
                                $total_expense,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>



        <!-- BALANCE -->

        <div class="col-md-3">

            <div
                class="card <?= $total_balance >= 0
                    ? 'bg-primary'
                    : 'bg-warning'
                ?> text-white border-0 shadow-sm"
            >

                <div class="card-body">

                    <small>
                        মোট ব্যালেন্স
                    </small>

                    <h3 class="fw-bold mb-0">

                        ৳ <?= bn_number(
                            number_format(
                                $total_balance,
                                2
                            )
                        ) ?>

                    </h3>

                </div>

            </div>

        </div>



        <!-- TRANSACTION -->

        <div class="col-md-3">

            <div class="card bg-dark text-white border-0 shadow-sm">

                <div class="card-body">

                    <small>
                        মোট লেনদেন
                    </small>

                    <h3 class="fw-bold mb-0">

                        <?= bn_number(
                            $total_transaction
                        ) ?>

                        টি

                    </h3>

                </div>

            </div>

        </div>


    </div>



    <!-- =================================================
         GARAGE WISE
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                🏢 গ্যারেজ অনুযায়ী হিসাব

            </h5>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            গ্যারেজ
                        </th>

                        <th>
                            মোট আয়
                        </th>

                        <th>
                            মোট ব্যয়
                        </th>

                        <th>
                            ব্যালেন্স
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach (
                    $garage_summaries
                    as $row
                ): ?>


                    <?php

                    $income =
                        (float)$row['total_income'];

                    $expense =
                        (float)$row['total_expense'];

                    $balance =
                        $income - $expense;

                    ?>


                    <tr>

                        <td>

                            <strong>

                                🏢

                                <?= htmlspecialchars(
                                    $row['garage_name']
                                ) ?>

                            </strong>

                        </td>


                        <td class="text-success fw-bold">

                            ৳ <?= bn_number(
                                number_format(
                                    $income,
                                    2
                                )
                            ) ?>

                        </td>


                        <td class="text-danger fw-bold">

                            ৳ <?= bn_number(
                                number_format(
                                    $expense,
                                    2
                                )
                            ) ?>

                        </td>


                        <td>

                            <span
                                class="badge <?= $balance >= 0
                                    ? 'bg-primary'
                                    : 'bg-danger'
                                ?> fs-6"
                            >

                                ৳ <?= bn_number(
                                    number_format(
                                        $balance,
                                        2
                                    )
                                ) ?>

                            </span>

                        </td>

                    </tr>


                <?php endforeach; ?>


                </tbody>

            </table>

        </div>

    </div>



    <!-- =================================================
         CATEGORY REPORT
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                📁 খাত অনুযায়ী রিপোর্ট

            </h5>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            ধরন
                        </th>

                        <th>
                            খাত
                        </th>

                        <th>
                            লেনদেন
                        </th>

                        <th>
                            মোট টাকা
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!empty($categories)): ?>


                    <?php foreach (
                        $categories
                        as $row
                    ): ?>


                        <tr>


                            <td>

                                <?php if (
                                    $row['type']
                                    === 'income'
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

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['category']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= bn_number(
                                    $row['total_count']
                                ) ?>

                                টি

                            </td>


                            <td
                                class="<?= $row['type']
                                    === 'income'
                                    ? 'text-success'
                                    : 'text-danger'
                                ?> fw-bold"
                            >

                                ৳ <?= bn_number(
                                    number_format(
                                        $row['total_amount'],
                                        2
                                    )
                                ) ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="4"
                            class="text-center text-muted py-4"
                        >

                            কোনো তথ্য পাওয়া যায়নি।

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>



    <!-- =================================================
         DETAILS
    ================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                📋 বিস্তারিত লেনদেন

            </h5>

        </div>


        <div class="card-body p-0">

            <?php if (!empty($transactions)): ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                    >

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    #
                                </th>

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
                                    টাকা
                                </th>

                                <th>
                                    বিবরণ
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach (
                            $transactions
                            as $index => $row
                        ): ?>


                            <tr>


                                <td>

                                    <?= bn_number(
                                        $index + 1
                                    ) ?>

                                </td>


                                <td>

                                    <?= bn_number(
                                        date(
                                            'd-m-Y',
                                            strtotime(
                                                $row[
                                                    'transaction_date'
                                                ]
                                            )
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['garage_name']
                                            ?? 'N/A'
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php if (
                                        $row['type']
                                        === 'income'
                                    ): ?>

                                        <span class="badge bg-success">

                                            + আয়

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">

                                            - ব্যয়

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $row['category']
                                    ) ?>

                                </td>


                                <td
                                    class="fw-bold <?= $row['type']
                                        === 'income'
                                        ? 'text-success'
                                        : 'text-danger'
                                    ?>"
                                >

                                    <?= $row['type']
                                        === 'income'
                                        ? '+'
                                        : '-'
                                    ?>

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


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="text-center py-5 text-muted">

                    <i
                        class="bi bi-receipt fs-1"
                    ></i>

                    <h5 class="mt-3">

                        কোনো লেনদেন পাওয়া যায়নি

                    </h5>

                    <p>

                        নির্বাচিত তারিখের মধ্যে কোনো হিসাব নেই।

                    </p>

                </div>


            <?php endif; ?>

        </div>

    </div>


</div>



<!-- =====================================================
     PRINT STYLE
====================================================== -->

<style>

@media print {

    body {
        background: #fff !important;
    }

    .btn,
    form {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

    .container-fluid {
        width: 100% !important;
        padding: 10px !important;
    }

}

</style>