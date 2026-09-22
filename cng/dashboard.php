<?php
// =====================================================
// CNG DASHBOARD
// File: cng/dashboard.php
// =====================================================

if (!isset($pdo)) {
    die('Database connection not found.');
}

$today = date('Y-m-d');


// =====================================================
// TOTAL ACTIVE CNG VEHICLES
// =====================================================

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM cng_vehicles
    WHERE status = 'active'
");

$totalVehicles = (int)$stmt->fetchColumn();


// =====================================================
// TOTAL GARAGES
// =====================================================

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM garages
    WHERE status = 'active'
");

$totalGarages = (int)$stmt->fetchColumn();


// =====================================================
// TODAY COLLECTION SUMMARY
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(amount), 0) AS total_collection,
        COALESCE(SUM(expense_amount), 0) AS total_expense,
        COUNT(*) AS total_records
    FROM cng_daily_collections
    WHERE collection_date = ?
");

$stmt->execute([$today]);

$todaySummary = $stmt->fetch(PDO::FETCH_ASSOC);

$todayCollection = (float)($todaySummary['total_collection'] ?? 0);
$todayExpense    = (float)($todaySummary['total_expense'] ?? 0);
$totalRecords    = (int)($todaySummary['total_records'] ?? 0);

$todayNet = $todayCollection - $todayExpense;


// =====================================================
// TODAY PAID VEHICLES
// =====================================================

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT vehicle_id)
    FROM cng_daily_collections
    WHERE collection_date = ?
    AND amount > 0
");

$stmt->execute([$today]);

$paidVehicles = (int)$stmt->fetchColumn();


// =====================================================
// TODAY VEHICLES WITH EXPENSE
// =====================================================

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT vehicle_id)
    FROM cng_daily_collections
    WHERE collection_date = ?
    AND expense_amount > 0
");

$stmt->execute([$today]);

$expenseVehicles = (int)$stmt->fetchColumn();


// =====================================================
// DUE / NOT COLLECTED VEHICLES
// =====================================================

$dueVehicles = max(0, $totalVehicles - $paidVehicles);


// =====================================================
// TODAY VEHICLE-WISE COLLECTION
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        c.id,
        c.vehicle_id,
        c.car_number,
        c.collection_date,
        c.amount,
        c.expense_amount,
        c.expense_note,

        v.driver_name,
        v.driver_mobile,
        v.daily_rent,
        v.garage_id,

        g.garage_name

    FROM cng_daily_collections c

    INNER JOIN cng_vehicles v
        ON v.id = c.vehicle_id

    LEFT JOIN garages g
        ON g.id = v.garage_id

    WHERE c.collection_date = ?

    ORDER BY c.id DESC

    LIMIT 15
");

$stmt->execute([$today]);

$todayCollections = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// GARAGE-WISE TODAY SUMMARY
// =====================================================

$stmt = $pdo->prepare("
    SELECT

        g.id AS garage_id,
        g.garage_name,

        COUNT(DISTINCT v.id) AS total_vehicles,

        COUNT(DISTINCT CASE
            WHEN c.amount > 0
            THEN c.vehicle_id
        END) AS paid_vehicles,

        COALESCE(SUM(c.amount), 0) AS collection_amount,

        COALESCE(SUM(c.expense_amount), 0) AS expense_amount

    FROM garages g

    LEFT JOIN cng_vehicles v
        ON v.garage_id = g.id
        AND v.status = 'active'

    LEFT JOIN cng_daily_collections c
        ON c.vehicle_id = v.id
        AND c.collection_date = ?

    WHERE g.status = 'active'

    GROUP BY
        g.id,
        g.garage_name

    ORDER BY g.garage_name ASC
");

$stmt->execute([$today]);

$garageSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// TOP COLLECTION VEHICLES
// =====================================================

$stmt = $pdo->prepare("
    SELECT

        c.car_number,
        c.amount,
        c.expense_amount,

        v.driver_name,
        g.garage_name

    FROM cng_daily_collections c

    INNER JOIN cng_vehicles v
        ON v.id = c.vehicle_id

    LEFT JOIN garages g
        ON g.id = v.garage_id

    WHERE c.collection_date = ?

    ORDER BY c.amount DESC

    LIMIT 5
");

$stmt->execute([$today]);

$topVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FORMAT MONEY
// =====================================================

function cngMoney($amount)
{
    return number_format((float)$amount, 2);
}


// =====================================================
// DATE
// =====================================================

$displayDate = date('d-m-Y');

?>

<style>

/* =====================================================
   CNG DASHBOARD
===================================================== */

.cng-dashboard {
    width: 100%;
    padding: 10px 0 30px;
}


/* =====================================================
   HEADER
===================================================== */

.cng-dashboard-header {
    position: relative;
    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #198754 0%,
            #0d6efd 100%
        );

    color: #fff;

    border-radius: 20px;

    padding: 25px;

    margin-bottom: 22px;

    box-shadow:
        0 10px 30px rgba(13, 110, 253, .15);
}


.cng-dashboard-header::before {
    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);

    right: -60px;
    top: -70px;
}


.cng-dashboard-header::after {
    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    border-radius: 50%;

    background: rgba(255,255,255,.06);

    right: 100px;
    bottom: -70px;
}


.cng-dashboard-title {
    position: relative;
    z-index: 2;
}


.cng-dashboard-title h3 {
    font-weight: 800;
    margin: 0;
}


.cng-dashboard-title p {
    margin: 7px 0 0;
    opacity: .9;
}


.today-badge {
    position: relative;
    z-index: 2;

    background: rgba(255,255,255,.15);

    border: 1px solid rgba(255,255,255,.25);

    padding: 10px 15px;

    border-radius: 12px;

    font-weight: 600;
}


/* =====================================================
   STAT CARDS
===================================================== */

.cng-stat-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 16px;

    margin-bottom: 22px;
}


.cng-stat-card {
    background: #fff;

    border: 1px solid #e9ecef;

    border-radius: 17px;

    padding: 20px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 5px 18px rgba(0,0,0,.055);

    transition: all .2s ease;
}


.cng-stat-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 10px 25px rgba(0,0,0,.09);
}


.cng-stat-icon {
    width: 48px;
    height: 48px;

    border-radius: 13px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;

    margin-bottom: 14px;

    background: #f1f5f9;
}


.cng-stat-label {
    color: #6c757d;

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 5px;
}


.cng-stat-value {
    font-size: 26px;

    font-weight: 800;

    color: #212529;
}


.cng-stat-small {
    font-size: 12px;

    color: #6c757d;

    margin-top: 5px;
}


.stat-green .cng-stat-icon {
    background: #e8f7ef;
    color: #198754;
}


.stat-blue .cng-stat-icon {
    background: #e8f1ff;
    color: #0d6efd;
}


.stat-red .cng-stat-icon {
    background: #fdebec;
    color: #dc3545;
}


.stat-purple .cng-stat-icon {
    background: #f0eaff;
    color: #6f42c1;
}


/* =====================================================
   SECONDARY STATS
===================================================== */

.cng-mini-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 15px;

    margin-bottom: 22px;
}


.cng-mini-card {
    background: #fff;

    border: 1px solid #e9ecef;

    border-radius: 15px;

    padding: 17px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    box-shadow:
        0 4px 14px rgba(0,0,0,.04);
}


.cng-mini-left {
    display: flex;

    align-items: center;

    gap: 12px;
}


.cng-mini-icon {
    width: 43px;
    height: 43px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;
}


.cng-mini-title {
    font-size: 13px;

    color: #6c757d;

    font-weight: 600;
}


.cng-mini-value {
    font-size: 21px;

    font-weight: 800;
}


/* =====================================================
   QUICK ACTIONS
===================================================== */

.cng-section-title {
    font-size: 18px;

    font-weight: 800;

    margin-bottom: 14px;

    color: #212529;
}


.cng-actions {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 13px;

    margin-bottom: 24px;
}


.cng-action {
    text-decoration: none;

    background: #fff;

    border: 1px solid #e9ecef;

    border-radius: 14px;

    padding: 17px;

    color: #212529;

    display: flex;

    align-items: center;

    gap: 12px;

    transition: .2s;

    box-shadow:
        0 3px 12px rgba(0,0,0,.04);
}


.cng-action:hover {
    color: #0d6efd;

    transform: translateY(-2px);

    box-shadow:
        0 7px 20px rgba(0,0,0,.08);
}


.cng-action-icon {
    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #f1f5f9;

    font-size: 19px;
}


.cng-action-title {
    font-weight: 700;

    font-size: 14px;
}


.cng-action-sub {
    font-size: 11px;

    color: #6c757d;

    margin-top: 2px;
}


/* =====================================================
   CONTENT GRID
===================================================== */

.cng-content-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 2fr)
        minmax(300px, 1fr);

    gap: 18px;
}


.cng-panel {
    background: #fff;

    border: 1px solid #e9ecef;

    border-radius: 17px;

    overflow: hidden;

    box-shadow:
        0 4px 15px rgba(0,0,0,.045);
}


.cng-panel-header {
    padding: 17px 20px;

    border-bottom: 1px solid #edf0f2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;
}


.cng-panel-header h5 {
    margin: 0;

    font-weight: 800;

    font-size: 16px;
}


.cng-panel-body {
    padding: 0;
}


/* =====================================================
   TABLE
===================================================== */

.cng-table-responsive {
    overflow-x: auto;
}


.cng-table {
    min-width: 850px;

    margin: 0;
}


.cng-table th {
    background: #f8f9fa;

    font-size: 12px;

    color: #6c757d;

    white-space: nowrap;

    padding: 12px 13px;
}


.cng-table td {
    padding: 12px 13px;

    vertical-align: middle;

    white-space: nowrap;
}


.cng-table tbody tr:hover {
    background: #f8f9fa;
}


.cng-car-number {
    color: #0d6efd;

    font-weight: 800;
}


.cng-collection {
    color: #198754;

    font-weight: 800;
}


.cng-expense {
    color: #dc3545;

    font-weight: 700;
}


.cng-net {
    color: #0d6efd;

    font-weight: 800;
}


.driver-name {
    font-weight: 600;
}


/* =====================================================
   GARAGE LIST
===================================================== */

.garage-row {
    padding: 15px 18px;

    border-bottom: 1px solid #f0f1f2;
}


.garage-row:last-child {
    border-bottom: 0;
}


.garage-top {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 8px;
}


.garage-name {
    font-weight: 700;

    font-size: 14px;
}


.garage-money {
    font-weight: 800;

    color: #198754;
}


.garage-progress {
    height: 7px;

    background: #eef0f2;

    border-radius: 20px;

    overflow: hidden;

    margin-bottom: 7px;
}


.garage-progress-bar {
    height: 100%;

    background:
        linear-gradient(
            90deg,
            #198754,
            #20c997
        );

    border-radius: 20px;
}


.garage-bottom {
    display: flex;

    justify-content: space-between;

    color: #6c757d;

    font-size: 11px;
}


/* =====================================================
   TOP VEHICLE
===================================================== */

.top-vehicle {
    padding: 14px 18px;

    border-bottom: 1px solid #f0f1f2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;
}


.top-vehicle:last-child {
    border-bottom: 0;
}


.top-left {
    display: flex;

    align-items: center;

    gap: 11px;
}


.rank-number {
    width: 30px;
    height: 30px;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f1f5f9;

    font-size: 12px;

    font-weight: 800;
}


.top-car {
    font-weight: 800;

    color: #0d6efd;

    font-size: 13px;
}


.top-driver {
    color: #6c757d;

    font-size: 11px;

    margin-top: 2px;
}


.top-amount {
    color: #198754;

    font-weight: 800;

    white-space: nowrap;
}


/* =====================================================
   EMPTY
===================================================== */

.cng-empty {
    text-align: center;

    padding: 40px 20px;

    color: #6c757d;
}


.cng-empty i {
    font-size: 42px;

    display: block;

    margin-bottom: 10px;

    opacity: .4;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .cng-stat-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .cng-actions {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 992px) {

    .cng-content-grid {
        grid-template-columns: 1fr;
    }

    .cng-mini-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


@media (max-width: 700px) {

    .cng-stat-grid {
        grid-template-columns: 1fr;
    }

    .cng-mini-grid {
        grid-template-columns: 1fr;
    }

    .cng-actions {
        grid-template-columns: 1fr;
    }

    .cng-dashboard-header {
        padding: 20px;
    }

    .cng-dashboard-title h3 {
        font-size: 21px;
    }

}


@media print {

    .no-print {
        display: none !important;
    }

    .cng-dashboard-header {
        background: #fff !important;

        color: #000 !important;

        box-shadow: none;

        border: 1px solid #ddd;
    }

    .cng-stat-card,
    .cng-panel,
    .cng-mini-card {
        box-shadow: none;
    }

}

</style>


<div class="container-fluid cng-dashboard">


    <!-- =====================================================
         HEADER
    ===================================================== -->

    <div class="cng-dashboard-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div class="cng-dashboard-title">

                <h3>

                    <i class="bi bi-fuel-pump-fill me-2"></i>

                    CNG ড্যাশবোর্ড

                </h3>

                <p>
                    আজকের CNG গাড়ি, জমা, খরচ ও আয়-এর সারসংক্ষেপ
                </p>

            </div>


            <div class="today-badge">

                <i class="bi bi-calendar3 me-1"></i>

                <?= htmlspecialchars($displayDate) ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MAIN STAT CARDS
    ===================================================== -->

    <div class="cng-stat-grid">


        <!-- TOTAL VEHICLES -->

        <div class="cng-stat-card stat-blue">

            <div class="cng-stat-icon">

                <i class="bi bi-car-front-fill"></i>

            </div>

            <div class="cng-stat-label">
                মোট সক্রিয় CNG গাড়ি
            </div>

            <div class="cng-stat-value">
                <?= number_format($totalVehicles) ?>
            </div>

            <div class="cng-stat-small">
                বর্তমানে চালু গাড়ি
            </div>

        </div>


        <!-- COLLECTION -->

        <div class="cng-stat-card stat-green">

            <div class="cng-stat-icon">

                <i class="bi bi-cash-coin"></i>

            </div>

            <div class="cng-stat-label">
                আজকের মোট জমা
            </div>

            <div class="cng-stat-value text-success">
                <?= cngMoney($todayCollection) ?> ৳
            </div>

            <div class="cng-stat-small">
                <?= number_format($paidVehicles) ?> টি গাড়ি জমা দিয়েছে
            </div>

        </div>


        <!-- EXPENSE -->

        <div class="cng-stat-card stat-red">

            <div class="cng-stat-icon">

                <i class="bi bi-wallet2"></i>

            </div>

            <div class="cng-stat-label">
                আজকের মোট খরচ
            </div>

            <div class="cng-stat-value text-danger">
                <?= cngMoney($todayExpense) ?> ৳
            </div>

            <div class="cng-stat-small">
                <?= number_format($expenseVehicles) ?> টি গাড়িতে খরচ
            </div>

        </div>


        <!-- NET -->

        <div class="cng-stat-card stat-purple">

            <div class="cng-stat-icon">

                <i class="bi bi-graph-up-arrow"></i>

            </div>

            <div class="cng-stat-label">
                আজকের নেট আয়
            </div>

            <div class="cng-stat-value text-primary">
                <?= cngMoney($todayNet) ?> ৳
            </div>

            <div class="cng-stat-small">
                জমা − খরচ
            </div>

        </div>


    </div>


    <!-- =====================================================
         SECONDARY STATS
    ===================================================== -->

    <div class="cng-mini-grid">


        <!-- GARAGE -->

        <div class="cng-mini-card">

            <div class="cng-mini-left">

                <div
                    class="cng-mini-icon"
                    style="background:#e8f1ff;color:#0d6efd;"
                >

                    <i class="bi bi-building"></i>

                </div>

                <div>

                    <div class="cng-mini-title">
                        সক্রিয় গ্যারেজ
                    </div>

                    <div class="cng-mini-value">
                        <?= number_format($totalGarages) ?>
                    </div>

                </div>

            </div>

            <i class="bi bi-chevron-right text-muted"></i>

        </div>


        <!-- PAID -->

        <div class="cng-mini-card">

            <div class="cng-mini-left">

                <div
                    class="cng-mini-icon"
                    style="background:#e8f7ef;color:#198754;"
                >

                    <i class="bi bi-check-circle-fill"></i>

                </div>

                <div>

                    <div class="cng-mini-title">
                        আজ জমা দিয়েছে
                    </div>

                    <div class="cng-mini-value text-success">
                        <?= number_format($paidVehicles) ?>
                    </div>

                </div>

            </div>

            <span class="badge bg-success">
                জমা
            </span>

        </div>


        <!-- DUE -->

        <div class="cng-mini-card">

            <div class="cng-mini-left">

                <div
                    class="cng-mini-icon"
                    style="background:#fdebec;color:#dc3545;"
                >

                    <i class="bi bi-exclamation-circle-fill"></i>

                </div>

                <div>

                    <div class="cng-mini-title">
                        আজ জমা দেয়নি
                    </div>

                    <div class="cng-mini-value text-danger">
                        <?= number_format($dueVehicles) ?>
                    </div>

                </div>

            </div>

            <span class="badge bg-danger">
                বাকি
            </span>

        </div>


    </div>


    <!-- =====================================================
         QUICK ACTION
    ===================================================== -->

    <div class="cng-section-title">

        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>

        দ্রুত কাজ

    </div>


    <div class="cng-actions">


        <a
            href="index.php?page=cng/cng_rent"
            class="cng-action"
        >

            <div class="cng-action-icon text-success">

                <i class="bi bi-cash-coin"></i>

            </div>

            <div>

                <div class="cng-action-title">
                    দৈনিক জমা
                </div>

                <div class="cng-action-sub">
                    আজকের জমা গ্রহণ করুন
                </div>

            </div>

        </a>


        <a
            href="index.php?page=cng/add"
            class="cng-action"
        >

            <div class="cng-action-icon text-primary">

                <i class="bi bi-plus-circle-fill"></i>

            </div>

            <div>

                <div class="cng-action-title">
                    নতুন CNG গাড়ি
                </div>

                <div class="cng-action-sub">
                    নতুন গাড়ি যোগ করুন
                </div>

            </div>

        </a>


        <a
            href="index.php?page=cng/index"
            class="cng-action"
        >

            <div class="cng-action-icon text-info">

                <i class="bi bi-car-front-fill"></i>

            </div>

            <div>

                <div class="cng-action-title">
                    গাড়ির তালিকা
                </div>

                <div class="cng-action-sub">
                    সকল CNG গাড়ি দেখুন
                </div>

            </div>

        </a>


        <a
            href="index.php?page=cng/report"
            class="cng-action"
        >

            <div class="cng-action-icon text-danger">

                <i class="bi bi-bar-chart-line-fill"></i>

            </div>

            <div>

                <div class="cng-action-title">
                    CNG রিপোর্ট
                </div>

                <div class="cng-action-sub">
                    বিস্তারিত রিপোর্ট দেখুন
                </div>

            </div>

        </a>


    </div>


    <!-- =====================================================
         CONTENT
    ===================================================== -->

    <div class="cng-content-grid">


        <!-- =================================================
             TODAY COLLECTION
        ================================================= -->

        <div class="cng-panel">


            <div class="cng-panel-header">

                <div>

                    <h5>

                        <i class="bi bi-cash-stack text-success me-1"></i>

                        আজকের CNG জমা

                    </h5>

                    <small class="text-muted">

                        <?= $totalRecords ?> টি collection record

                    </small>

                </div>


                <a
                    href="index.php?page=cng/cng_rent"
                    class="btn btn-sm btn-outline-success no-print"
                >

                    সব দেখুন

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>


            <div class="cng-panel-body">


                <?php if (!empty($todayCollections)): ?>


                    <div class="cng-table-responsive">

                        <table class="table cng-table">


                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>গাড়ি</th>

                                    <th>গ্যারেজ</th>

                                    <th>চালক</th>

                                    <th class="text-end">
                                        জমা
                                    </th>

                                    <th class="text-end">
                                        খরচ
                                    </th>

                                    <th class="text-end">
                                        নেট
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($todayCollections as $i => $row): ?>


                                    <?php

                                    $collection =
                                        (float)$row['amount'];

                                    $expense =
                                        (float)$row['expense_amount'];

                                    $net =
                                        $collection - $expense;

                                    ?>


                                    <tr>


                                        <td>
                                            <?= $i + 1 ?>
                                        </td>


                                        <td>

                                            <span class="cng-car-number">

                                                <?= htmlspecialchars(
                                                    $row['car_number'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= !empty($row['garage_name'])

                                                ? htmlspecialchars(
                                                    $row['garage_name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )

                                                : '<span class="text-muted">-</span>'
                                            ?>

                                        </td>


                                        <td>

                                            <span class="driver-name">

                                                <?= !empty($row['driver_name'])

                                                    ? htmlspecialchars(
                                                        $row['driver_name'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )

                                                    : '<span class="text-muted">নেই</span>'
                                                ?>

                                            </span>

                                        </td>


                                        <td class="text-end">

                                            <span class="cng-collection">

                                                <?= cngMoney($collection) ?> ৳

                                            </span>

                                        </td>


                                        <td class="text-end">

                                            <span class="cng-expense">

                                                <?= cngMoney($expense) ?> ৳

                                            </span>

                                        </td>


                                        <td class="text-end">

                                            <span class="cng-net">

                                                <?= cngMoney($net) ?> ৳

                                            </span>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                            <tfoot>

                                <tr class="table-light fw-bold">

                                    <td colspan="4" class="text-end">
                                        মোট:
                                    </td>

                                    <td class="text-end text-success">
                                        <?= cngMoney($todayCollection) ?> ৳
                                    </td>

                                    <td class="text-end text-danger">
                                        <?= cngMoney($todayExpense) ?> ৳
                                    </td>

                                    <td class="text-end text-primary">
                                        <?= cngMoney($todayNet) ?> ৳
                                    </td>

                                </tr>

                            </tfoot>


                        </table>

                    </div>


                <?php else: ?>


                    <div class="cng-empty">

                        <i class="bi bi-inbox"></i>

                        <h6>
                            আজ কোনো জমার তথ্য নেই
                        </h6>

                        <p class="mb-0">
                            আজকের জন্য এখনো কোনো collection করা হয়নি।
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


        <!-- =================================================
             RIGHT SIDE
        ================================================= -->

        <div>


            <!-- =================================================
                 GARAGE SUMMARY
            ================================================= -->

            <div class="cng-panel mb-3">


                <div class="cng-panel-header">

                    <h5>

                        <i class="bi bi-building text-primary me-1"></i>

                        গ্যারেজ অনুযায়ী

                    </h5>

                </div>


                <div class="cng-panel-body">


                    <?php if (!empty($garageSummary)): ?>


                        <?php foreach ($garageSummary as $garage): ?>


                            <?php

                            $garageTotal =
                                (int)$garage['total_vehicles'];

                            $garagePaid =
                                (int)$garage['paid_vehicles'];

                            $garageCollection =
                                (float)$garage['collection_amount'];

                            $garageExpense =
                                (float)$garage['expense_amount'];

                            $garageNet =
                                $garageCollection - $garageExpense;


                            $percentage =
                                $garageTotal > 0
                                ? ($garagePaid / $garageTotal) * 100
                                : 0;

                            ?>


                            <div class="garage-row">


                                <div class="garage-top">

                                    <span class="garage-name">

                                        <i class="bi bi-building me-1 text-muted"></i>

                                        <?= htmlspecialchars(
                                            $garage['garage_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>


                                    <span class="garage-money">

                                        <?= cngMoney($garageNet) ?> ৳

                                    </span>

                                </div>


                                <div class="garage-progress">

                                    <div
                                        class="garage-progress-bar"
                                        style="width:<?= min(100, $percentage) ?>%;"
                                    ></div>

                                </div>


                                <div class="garage-bottom">

                                    <span>

                                        <?= $garagePaid ?>
                                        /
                                        <?= $garageTotal ?>
                                        গাড়ি জমা

                                    </span>


                                    <span>

                                        জমা:
                                        <?= cngMoney($garageCollection) ?> ৳

                                    </span>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="cng-empty">

                            <i class="bi bi-building"></i>

                            <p class="mb-0">
                                কোনো গ্যারেজ পাওয়া যায়নি।
                            </p>

                        </div>


                    <?php endif; ?>


                </div>

            </div>


            <!-- =================================================
                 TOP VEHICLES
            ================================================= -->

            <div class="cng-panel">


                <div class="cng-panel-header">

                    <h5>

                        <i class="bi bi-trophy-fill text-warning me-1"></i>

                        আজকের বেশি জমা

                    </h5>

                </div>


                <div class="cng-panel-body">


                    <?php if (!empty($topVehicles)): ?>


                        <?php foreach ($topVehicles as $i => $vehicle): ?>


                            <div class="top-vehicle">


                                <div class="top-left">


                                    <div class="rank-number">

                                        <?= $i + 1 ?>

                                    </div>


                                    <div>

                                        <div class="top-car">

                                            <?= htmlspecialchars(
                                                $vehicle['car_number'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>


                                        <div class="top-driver">

                                            <?= !empty($vehicle['driver_name'])

                                                ? htmlspecialchars(
                                                    $vehicle['driver_name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )

                                                : 'চালক নেই'
                                            ?>

                                        </div>

                                    </div>


                                </div>


                                <div class="top-amount">

                                    <?= cngMoney(
                                        $vehicle['amount']
                                    ) ?> ৳

                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="cng-empty">

                            <i class="bi bi-bar-chart"></i>

                            <p class="mb-0">
                                আজ কোনো জমা নেই।
                            </p>

                        </div>


                    <?php endif; ?>


                </div>

            </div>


        </div>


    </div>


</div>