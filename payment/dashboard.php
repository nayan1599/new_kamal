
<?php

// ==========================================================
// কিস্তি পেমেন্ট ড্যাশবোর্ড
// ==========================================================

$today = date('Y-m-d');

// ==========================================================
// ১. মোট পেমেন্ট
// ==========================================================

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total_payment,
        COALESCE(SUM(amount), 0) AS total_amount,
        COALESCE(SUM(fine_amount), 0) AS total_fine,
        COALESCE(SUM(total_received), 0) AS total_received
    FROM kisti_payments
");

$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$totalPayment  = (int)($summary['total_payment'] ?? 0);
$totalAmount   = (float)($summary['total_amount'] ?? 0);
$totalFine     = (float)($summary['total_fine'] ?? 0);
$totalReceived = (float)($summary['total_received'] ?? 0);


// ==========================================================
// ২. আজকের পেমেন্ট
// ==========================================================

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(amount), 0) AS amount,
        COALESCE(SUM(fine_amount), 0) AS fine,
        COALESCE(SUM(total_received), 0) AS received
    FROM kisti_payments
    WHERE DATE(payment_date) = ?
");

$stmt->execute([$today]);

$todayData = $stmt->fetch(PDO::FETCH_ASSOC);

$todayPayment  = (int)($todayData['total'] ?? 0);
$todayAmount   = (float)($todayData['amount'] ?? 0);
$todayFine     = (float)($todayData['fine'] ?? 0);
$todayReceived = (float)($todayData['received'] ?? 0);


// ==========================================================
// ৩. গত ৭ দিনের পেমেন্ট
// ==========================================================

$stmt = $pdo->prepare("
    SELECT
        DATE(payment_date) AS payment_day,
        COALESCE(SUM(total_received), 0) AS total
    FROM kisti_payments
    WHERE DATE(payment_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(payment_date)
    ORDER BY payment_day ASC
");

$stmt->execute();

$weeklyRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// Chart Data
// ==========================================================

$chartLabels = [];
$chartValues = [];

foreach ($weeklyRows as $row) {

    $chartLabels[] = date(
        'd/m',
        strtotime($row['payment_day'])
    );

    $chartValues[] = (float)$row['total'];
}


// ==========================================================
// ৪. Payment Method Summary
// ==========================================================

$stmt = $pdo->query("
    SELECT
        payment_method,
        COUNT(*) AS total,
        COALESCE(SUM(total_received), 0) AS amount
    FROM kisti_payments
    GROUP BY payment_method
    ORDER BY amount DESC
");

$methodRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// Payment Method বাংলা নাম
// ==========================================================

$methodMap = [

    'cash' =>
        'ক্যাশ',

    'bank_transfer' =>
        'ব্যাংক ট্রান্সফার',

    'bkash' =>
        'বিকাশ',

    'nagad' =>
        'নগদ',

    'rocket' =>
        'রকেট',

    'cheque' =>
        'চেক',

    'others' =>
        'অন্যান্য'

];


// ==========================================================
// ৫. সর্বশেষ ১০টি পেমেন্ট
// ==========================================================

$stmt = $pdo->query("
    SELECT *
    FROM kisti_payments
    ORDER BY payment_date DESC, id DESC
    LIMIT 10
");

$latestPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- ======================================================
         HEADER
    ======================================================= -->

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="fw-bold mb-1 dashboard-title">

                <i class="bi bi-speedometer2 text-primary me-2"></i>

                কিস্তি পেমেন্ট ড্যাশবোর্ড

            </h1>

            <p class="text-muted mb-0">

                কিস্তি আদায়, জরিমানা ও পেমেন্টের সম্পূর্ণ হিসাব

            </p>

        </div>


        <div class="d-flex gap-2">

            <button
                type="button"
                class="btn btn-outline-secondary"
                onclick="window.print()"
            >

                <i class="bi bi-printer me-1"></i>

                প্রিন্ট

            </button>


            <a
                href="index.php?page=payment/add"
                class="btn btn-success"
            >

                <i class="bi bi-plus-circle me-1"></i>

                নতুন কিস্তি আদায়

            </a>

        </div>

    </div>


    <!-- ======================================================
         SUMMARY CARDS
    ======================================================= -->

    <div class="row g-3 mb-4">


        <!-- মোট আদায় -->

        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card card-total">

                <div class="card-icon">

                    <i class="bi bi-cash-stack"></i>

                </div>

                <div class="card-content">

                    <div class="card-label">
                        মোট কিস্তি আদায়
                    </div>

                    <div class="card-value">

                        ৳<?= bn_number(
                            number_format($totalAmount, 2)
                        ) ?>

                    </div>

                    <div class="card-small">

                        <?= bn_number($totalPayment) ?>
                        টি পেমেন্ট

                    </div>

                </div>

            </div>

        </div>


        <!-- আজকের আদায় -->

        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card card-today">

                <div class="card-icon">

                    <i class="bi bi-calendar-check"></i>

                </div>

                <div class="card-content">

                    <div class="card-label">
                        আজকের আদায়
                    </div>

                    <div class="card-value">

                        ৳<?= bn_number(
                            number_format($todayAmount, 2)
                        ) ?>

                    </div>

                    <div class="card-small">

                        <?= bn_number($todayPayment) ?>
                        টি পেমেন্ট

                    </div>

                </div>

            </div>

        </div>


        <!-- জরিমানা -->

        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card card-fine">

                <div class="card-icon">

                    <i class="bi bi-exclamation-triangle"></i>

                </div>

                <div class="card-content">

                    <div class="card-label">
                        মোট জরিমানা
                    </div>

                    <div class="card-value">

                        ৳<?= bn_number(
                            number_format($totalFine, 2)
                        ) ?>

                    </div>

                    <div class="card-small">

                        আজকের জরিমানা:
                        ৳<?= bn_number(
                            number_format($todayFine, 2)
                        ) ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- মোট Received -->

        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card card-received">

                <div class="card-icon">

                    <i class="bi bi-wallet2"></i>

                </div>

                <div class="card-content">

                    <div class="card-label">
                        মোট গ্রহণ করা হয়েছে
                    </div>

                    <div class="card-value">

                        ৳<?= bn_number(
                            number_format($totalReceived, 2)
                        ) ?>

                    </div>

                    <div class="card-small">

                        আজ:
                        ৳<?= bn_number(
                            number_format($todayReceived, 2)
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         CHART + PAYMENT METHOD
    ======================================================= -->

    <div class="row g-3 mb-4">


        <!-- Weekly Chart -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-bar-chart-line text-primary me-2"></i>

                                গত ৭ দিনের কিস্তি আদায়

                            </h5>

                            <small class="text-muted">

                                প্রতিদিনের মোট গ্রহণ

                            </small>

                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <div style="height:300px;">

                        <canvas id="paymentChart"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- Payment Method -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-1">

                        <i class="bi bi-wallet2 text-success me-2"></i>

                        পেমেন্ট মেথড

                    </h5>

                    <small class="text-muted">

                        মেথড অনুযায়ী মোট আদায়

                    </small>

                </div>


                <div class="card-body p-0">

                    <?php if (empty($methodRows)): ?>

                        <div class="text-center text-muted py-5">

                            <i
                                class="bi bi-inbox"
                                style="font-size:40px;"
                            ></i>

                            <div class="mt-2">

                                কোনো ডাটা নেই

                            </div>

                        </div>

                    <?php else: ?>

                        <div class="method-list">

                            <?php foreach ($methodRows as $methodRow): ?>

                                <?php

                                $methodKey =
                                    $methodRow['payment_method']
                                    ?? '';

                                $methodName =
                                    $methodMap[$methodKey]
                                    ?? ($methodKey ?: 'অন্যান্য');

                                $methodAmount =
                                    (float)($methodRow['amount'] ?? 0);

                                $methodTotal =
                                    (int)($methodRow['total'] ?? 0);

                                ?>

                                <div class="method-item">

                                    <div class="method-left">

                                        <span class="method-icon">

                                            <?php if ($methodKey === 'cash'): ?>

                                                <i class="bi bi-cash"></i>

                                            <?php elseif ($methodKey === 'bkash'): ?>

                                                <i class="bi bi-phone"></i>

                                            <?php elseif ($methodKey === 'nagad'): ?>

                                                <i class="bi bi-wallet2"></i>

                                            <?php elseif ($methodKey === 'bank_transfer'): ?>

                                                <i class="bi bi-bank"></i>

                                            <?php elseif ($methodKey === 'cheque'): ?>

                                                <i class="bi bi-file-earmark-text"></i>

                                            <?php else: ?>

                                                <i class="bi bi-credit-card"></i>

                                            <?php endif; ?>

                                        </span>


                                        <div>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $methodName
                                                ) ?>

                                            </div>

                                            <small class="text-muted">

                                                <?= bn_number(
                                                    $methodTotal
                                                ) ?>
                                                টি

                                            </small>

                                        </div>

                                    </div>


                                    <div class="fw-bold text-success">

                                        ৳<?= bn_number(
                                            number_format(
                                                $methodAmount,
                                                2
                                            )
                                        ) ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         LATEST PAYMENTS
    ======================================================= -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-primary text-white py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>

                    <h5 class="mb-1 fw-bold">

                        <i class="bi bi-clock-history me-2"></i>

                        সর্বশেষ কিস্তি পেমেন্ট

                    </h5>

                    <small class="opacity-75">

                        সর্বশেষ ১০টি পেমেন্ট রেকর্ড

                    </small>

                </div>


                <a
                    href="index.php?page=payment/index"
                    class="btn btn-light btn-sm"
                >

                    সকল রেকর্ড

                    <i class="bi bi-arrow-right ms-1"></i>

                </a>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="text-center">
                                #
                            </th>

                            <th>
                                তারিখ
                            </th>

                            <th>
                                গ্রাহক
                            </th>

                            <th>
                                গাড়ির নং
                            </th>

                            <th>
                                কিস্তি নং
                            </th>

                            <th class="text-end">
                                কিস্তি
                            </th>

                            <th class="text-end">
                                জরিমানা
                            </th>

                            <th class="text-end">
                                মোট
                            </th>

                            <th>
                                মেথড
                            </th>

                            <th>
                                স্ট্যাটাস
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($latestPayments)): ?>

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bi bi-inbox"
                                    style="font-size:45px;"
                                ></i>

                                <div class="mt-2">

                                    কোনো পেমেন্ট পাওয়া যায়নি।

                                </div>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php $serial = 1; ?>

                        <?php foreach ($latestPayments as $row): ?>

                            <?php

                            $amount =
                                (float)($row['amount'] ?? 0);

                            $fine =
                                (float)($row['fine_amount'] ?? 0);

                            $received =
                                (float)(
                                    $row['total_received']
                                    ?? ($amount + $fine)
                                );

                            $status =
                                strtolower(
                                    trim(
                                        $row['status'] ?? ''
                                    )
                                );

                            ?>

                            <tr>

                                <td class="text-center">

                                    <?= bn_number(
                                        $serial++
                                    ) ?>

                                </td>


                                <td>

                                    <?= !empty($row['payment_date'])
                                        ? bn_number(
                                            date(
                                                'd/m/Y',
                                                strtotime(
                                                    $row['payment_date']
                                                )
                                            )
                                        )
                                        : '—'
                                    ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['customer_name']
                                            ?? '—'
                                        ) ?>

                                    </strong>

                                    <?php if (!empty($row['customer_phone'])): ?>

                                        <div class="small text-muted">

                                            <i class="bi bi-telephone"></i>

                                            <?= htmlspecialchars(
                                                $row['customer_phone']
                                            ) ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <span class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $row['car_number']
                                            ?? '—'
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="badge bg-secondary">

                                        <?= bn_number(
                                            $row['kisti_number']
                                            ?? '—'
                                        ) ?>

                                    </span>

                                </td>


                                <td class="text-end fw-bold">

                                    ৳<?= bn_number(
                                        number_format(
                                            $amount,
                                            2
                                        )
                                    ) ?>

                                </td>


                                <td class="text-end text-danger">

                                    <?php if ($fine > 0): ?>

                                        ৳<?= bn_number(
                                            number_format(
                                                $fine,
                                                2
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>


                                <td class="text-end fw-bold text-success">

                                    ৳<?= bn_number(
                                        number_format(
                                            $received,
                                            2
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span class="badge bg-info text-dark">

                                        <?= htmlspecialchars(
                                            $methodMap[
                                                $row['payment_method']
                                                ?? ''
                                            ]
                                            ?? (
                                                $row['payment_method']
                                                ?? '—'
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if ($status === 'paid'): ?>

                                        <span class="badge bg-success">

                                            <i class="bi bi-check-circle me-1"></i>

                                            পরিশোধিত

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">

                                            <i class="bi bi-clock me-1"></i>

                                            <?= htmlspecialchars(
                                                $status ?: 'অজানা'
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ==========================================================
     CHART JS
========================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const canvas =
            document.getElementById(
                "paymentChart"
            );

        if (!canvas) {
            return;
        }


        const labels =
            <?= json_encode(
                $chartLabels,
                JSON_UNESCAPED_UNICODE
            ) ?>;


        const values =
            <?= json_encode(
                $chartValues
            ) ?>;


        new Chart(
            canvas,
            {
                type: "bar",

                data: {

                    labels: labels,

                    datasets: [

                        {

                            label:
                                "মোট আদায়",

                            data:
                                values,

                            borderWidth:
                                1,

                            borderRadius:
                                8

                        }

                    ]

                },


                options: {

                    responsive:
                        true,

                    maintainAspectRatio:
                        false,

                    plugins: {

                        legend: {

                            display:
                                false

                        },

                        tooltip: {

                            callbacks: {

                                label:
                                    function(context) {

                                        return " ৳" +
                                            Number(
                                                context.raw
                                            ).toLocaleString(
                                                "en-US"
                                            );

                                    }

                            }

                        }

                    },


                    scales: {

                        y: {

                            beginAtZero:
                                true,

                            ticks: {

                                callback:
                                    function(value) {

                                        return "৳" +
                                            Number(
                                                value
                                            ).toLocaleString(
                                                "en-US"
                                            );

                                    }

                            }

                        }

                    }

                }

            }
        );

    }
);

</script>


<!-- ==========================================================
     STYLE
========================================================== -->

<style>

.dashboard-title {
    letter-spacing: -.3px;
}


/* ==========================================================
   Dashboard Cards
========================================================== */

.dashboard-card {

    position: relative;

    overflow: hidden;

    min-height: 145px;

    padding: 24px;

    border-radius: 16px;

    background: #fff;

    border: 1px solid #eee;

    box-shadow:
        0 5px 20px rgba(0,0,0,.06);

    display: flex;

    align-items: center;

    gap: 18px;

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.dashboard-card:hover {

    transform:
        translateY(-4px);

    box-shadow:
        0 10px 30px rgba(0,0,0,.10);

}


.card-icon {

    width: 60px;

    height: 60px;

    min-width: 60px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;

}


.card-total .card-icon {

    background:
        rgba(13,110,253,.10);

    color:
        #0d6efd;

}


.card-today .card-icon {

    background:
        rgba(25,135,84,.10);

    color:
        #198754;

}


.card-fine .card-icon {

    background:
        rgba(220,53,69,.10);

    color:
        #dc3545;

}


.card-received .card-icon {

    background:
        rgba(111,66,193,.10);

    color:
        #6f42c1;

}


.card-label {

    color:
        #6c757d;

    font-size:
        14px;

    margin-bottom:
        5px;

}


.card-value {

    font-size:
        24px;

    font-weight:
        800;

    color:
        #212529;

    line-height:
        1.3;

}


.card-small {

    color:
        #8a8f98;

    font-size:
        12px;

    margin-top:
        5px;

}


/* ==========================================================
   Payment Method
========================================================== */

.method-list {

    padding:
        0 16px;

}


.method-item {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    padding:
        15px 4px;

    border-bottom:
        1px solid #eee;

}


.method-item:last-child {

    border-bottom:
        0;

}


.method-left {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

}


.method-icon {

    width:
        40px;

    height:
        40px;

    border-radius:
        10px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #f1f5f9;

    color:
        #0d6efd;

    font-size:
        18px;

}


/* ==========================================================
   Table
========================================================== */

.table th {

    font-size:
        13px;

    font-weight:
        700;

    white-space:
        nowrap;

}


.table td {

    font-size:
        13px;

    white-space:
        nowrap;

}


.table tbody tr {

    transition:
        background .15s ease;

}


.table tbody tr:hover {

    background:
        rgba(13,110,253,.035);

}


/* ==========================================================
   Responsive
========================================================== */

@media (max-width: 768px) {

    .dashboard-title {

        font-size:
            21px;

    }


    .dashboard-card {

        min-height:
            125px;

        padding:
            18px;

    }


    .card-value {

        font-size:
            20px;

    }


    .card-icon {

        width:
            50px;

        height:
            50px;

        min-width:
            50px;

        font-size:
            22px;

    }

}


/* ==========================================================
   Print
========================================================== */

@media print {

    .btn,
    .navbar,
    .sidebar {

        display:
            none !important;

    }


    .dashboard-card,
    .card {

        box-shadow:
            none !important;

        border:
            1px solid #ddd !important;

    }


    body {

        background:
            #fff !important;

    }

}

</style>

