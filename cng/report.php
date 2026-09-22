<?php

// =====================================================
// CNG REPORT
// File: cng/report.php
// =====================================================

if (!isset($pdo)) {
    die('Database connection not found.');
}


// =====================================================
// FILTER VALUES
// =====================================================

$reportType = $_GET['report_type'] ?? 'date';

$garageId = (int)($_GET['garage_id'] ?? 0);

$vehicleId = (int)($_GET['vehicle_id'] ?? 0);

$fromDate = $_GET['from_date'] ?? date('Y-m-d');

$toDate = $_GET['to_date'] ?? date('Y-m-d');


// =====================================================
// VALIDATE REPORT TYPE
// =====================================================

if (!in_array($reportType, ['date', 'vehicle'], true)) {
    $reportType = 'date';
}


// =====================================================
// VALIDATE DATE
// =====================================================

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = date('Y-m-d');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = date('Y-m-d');
}

if ($fromDate > $toDate) {
    [$fromDate, $toDate] = [$toDate, $fromDate];
}


// =====================================================
// LOAD ACTIVE GARAGES
// =====================================================

$garageStmt = $pdo->query("
    SELECT
        id,
        garage_name
    FROM garages
    WHERE status = 'active'
    ORDER BY garage_name ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// LOAD CNG VEHICLES
// =====================================================

$vehicleStmt = $pdo->query("
    SELECT
        v.id,
        v.car_number,
        v.driver_name,
        v.driver_mobile,
        v.daily_rent,
        v.garage_id,
        g.garage_name
    FROM cng_vehicles v
    LEFT JOIN garages g
        ON g.id = v.garage_id
    WHERE v.status = 'active'
    ORDER BY v.car_number ASC
");

$vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// SELECTED VEHICLE
// =====================================================

$selectedVehicle = null;

if ($vehicleId > 0) {

    $selectedVehicleStmt = $pdo->prepare("
        SELECT
            v.id,
            v.car_number,
            v.driver_name,
            v.driver_mobile,
            v.daily_rent,
            v.garage_id,
            g.garage_name
        FROM cng_vehicles v
        LEFT JOIN garages g
            ON g.id = v.garage_id
        WHERE v.id = ?
        LIMIT 1
    ");

    $selectedVehicleStmt->execute([$vehicleId]);

    $selectedVehicle = $selectedVehicleStmt->fetch(PDO::FETCH_ASSOC);
}


// =====================================================
// REPORT DATA
// =====================================================

$reportRows = [];

$totalRent = 0;
$totalCollection = 0;
$totalExpense = 0;
$totalNet = 0;


// =====================================================
// VEHICLE WISE REPORT
// =====================================================

if ($reportType === 'vehicle') {

    if ($vehicleId > 0) {

        $sql = "
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
                g.garage_name
            FROM cng_daily_collections c

            INNER JOIN cng_vehicles v
                ON v.id = c.vehicle_id

            LEFT JOIN garages g
                ON g.id = v.garage_id

            WHERE c.vehicle_id = ?
            AND c.collection_date BETWEEN ? AND ?
        ";

        $params = [
            $vehicleId,
            $fromDate,
            $toDate
        ];


        // Garage filter
        if ($garageId > 0) {

            $sql .= " AND v.garage_id = ? ";

            $params[] = $garageId;
        }


        $sql .= "
            ORDER BY
                c.collection_date DESC,
                c.id DESC
        ";


        $stmt = $pdo->prepare($sql);

        $stmt->execute($params);

        $reportRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach ($reportRows as $row) {

            $dailyRent = (float)$row['daily_rent'];

            $collection = (float)$row['amount'];

            $expense = (float)$row['expense_amount'];

            $net = $collection - $expense;


            $totalRent += $dailyRent;

            $totalCollection += $collection;

            $totalExpense += $expense;

            $totalNet += $net;
        }
    }
}


// =====================================================
// DATE WISE REPORT
// =====================================================

if ($reportType === 'date') {

    $sql = "
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
            g.garage_name

        FROM cng_daily_collections c

        INNER JOIN cng_vehicles v
            ON v.id = c.vehicle_id

        LEFT JOIN garages g
            ON g.id = v.garage_id

        WHERE c.collection_date BETWEEN ? AND ?
    ";

    $params = [
        $fromDate,
        $toDate
    ];


    // Garage filter
    if ($garageId > 0) {

        $sql .= " AND v.garage_id = ? ";

        $params[] = $garageId;
    }


    // Vehicle filter
    if ($vehicleId > 0) {

        $sql .= " AND c.vehicle_id = ? ";

        $params[] = $vehicleId;
    }


    $sql .= "
        ORDER BY
            c.collection_date DESC,
            c.car_number ASC,
            c.id DESC
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $reportRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($reportRows as $row) {

        $dailyRent = (float)$row['daily_rent'];

        $collection = (float)$row['amount'];

        $expense = (float)$row['expense_amount'];

        $net = $collection - $expense;


        $totalRent += $dailyRent;

        $totalCollection += $collection;

        $totalExpense += $expense;

        $totalNet += $net;
    }
}


// =====================================================
// TOTAL RECORDS
// =====================================================

$totalRecords = count($reportRows);


// =====================================================
// DATE FORMAT
// =====================================================

function banglaDate($date)
{
    if (!$date) {
        return '';
    }

    return date('d-m-Y', strtotime($date));
}

?>

<style>

    /* =====================================================
       REPORT PAGE
       ===================================================== */

    .cng-report-page {
        width: 100%;
    }


    .report-header {
        background: linear-gradient(
            135deg,
            #198754,
            #0d6efd
        );

        color: #fff;

        border-radius: 15px;

        padding: 22px;

        margin-bottom: 20px;

        box-shadow: 0 5px 18px rgba(0,0,0,.10);
    }


    .report-header h3 {
        margin: 0;

        font-weight: 700;
    }


    .report-header p {
        margin: 5px 0 0;

        opacity: .9;
    }


    /* =====================================================
       REPORT TYPE BUTTONS
       ===================================================== */

    .report-type-box {
        display: flex;

        gap: 10px;

        flex-wrap: wrap;

        margin-bottom: 18px;
    }


    .report-type-box a {
        text-decoration: none;

        padding: 11px 20px;

        border-radius: 10px;

        font-weight: 600;

        border: 1px solid #dee2e6;

        background: #fff;

        color: #333;

        transition: .2s;
    }


    .report-type-box a:hover {
        transform: translateY(-1px);
    }


    .report-type-box a.active {
        background: #198754;

        color: #fff;

        border-color: #198754;
    }


    /* =====================================================
       FILTER CARD
       ===================================================== */

    .filter-card {
        background: #fff;

        border: 1px solid #e5e7eb;

        border-radius: 15px;

        padding: 20px;

        margin-bottom: 20px;

        box-shadow: 0 3px 12px rgba(0,0,0,.06);
    }


    .filter-card label {
        font-weight: 600;

        margin-bottom: 6px;
    }


    .filter-card .form-control,
    .filter-card .form-select {
        min-height: 45px;

        border-radius: 9px;
    }


    .filter-btn {
        min-height: 45px;

        border-radius: 9px;

        font-weight: 600;
    }


    /* =====================================================
       SUMMARY CARDS
       ===================================================== */

    .summary-grid {
        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 15px;

        margin-bottom: 20px;
    }


    .summary-card {
        background: #fff;

        border: 1px solid #e5e7eb;

        border-radius: 15px;

        padding: 18px;

        box-shadow: 0 3px 12px rgba(0,0,0,.05);

        position: relative;

        overflow: hidden;
    }


    .summary-card::after {
        content: "";

        position: absolute;

        right: -25px;

        top: -25px;

        width: 80px;

        height: 80px;

        border-radius: 50%;

        background: rgba(13,110,253,.07);
    }


    .summary-title {
        color: #6c757d;

        font-size: 14px;

        margin-bottom: 7px;

        font-weight: 600;
    }


    .summary-value {
        font-size: 24px;

        font-weight: 800;

        color: #212529;
    }


    .summary-icon {
        font-size: 23px;

        margin-right: 5px;
    }


    /* =====================================================
       REPORT TABLE
       ===================================================== */

    .report-table-card {
        background: #fff;

        border: 1px solid #e5e7eb;

        border-radius: 15px;

        overflow: hidden;

        box-shadow: 0 3px 12px rgba(0,0,0,.06);
    }


    .report-table-header {
        padding: 17px 20px;

        border-bottom: 1px solid #e9ecef;

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;
    }


    .report-table-header h5 {
        margin: 0;

        font-weight: 700;
    }


    .table-responsive {
        width: 100%;

        overflow-x: auto;
    }


    .report-table {
        margin: 0;

        min-width: 1050px;
    }


    .report-table thead th {
        background: #f8f9fa;

        font-size: 13px;

        white-space: nowrap;

        padding: 13px 10px;

        border-bottom: 2px solid #dee2e6;

        vertical-align: middle;
    }


    .report-table tbody td {
        padding: 12px 10px;

        vertical-align: middle;

        white-space: nowrap;
    }


    .report-table tbody tr:hover {
        background: #f8f9fa;
    }


    .car-number {
        font-weight: 700;

        color: #0d6efd;
    }


    .amount-collection {
        font-weight: 700;

        color: #198754;
    }


    .amount-expense {
        font-weight: 700;

        color: #dc3545;
    }


    .amount-net {
        font-weight: 800;

        color: #0d6efd;
    }


    .expense-note {
        max-width: 220px;

        white-space: normal !important;

        line-height: 1.4;
    }


    .empty-box {
        padding: 50px 20px;

        text-align: center;

        color: #6c757d;
    }


    .empty-box i {
        font-size: 50px;

        display: block;

        margin-bottom: 12px;

        opacity: .5;
    }


    /* =====================================================
       PRINT
       ===================================================== */

    @media print {

        body {
            background: #fff !important;
        }


        .sidebar,
        .navbar,
        .no-print,
        .filter-card,
        .report-type-box,
        .report-header .no-print {
            display: none !important;
        }


        .cng-report-page {
            width: 100% !important;
        }


        .report-header {
            background: #fff !important;

            color: #000 !important;

            box-shadow: none !important;

            border: 1px solid #ddd;

            text-align: center;
        }


        .summary-grid {
            grid-template-columns:
                repeat(4, 1fr);
        }


        .summary-card {
            box-shadow: none;

            border: 1px solid #ccc;
        }


        .report-table-card {
            box-shadow: none;

            border: 1px solid #ccc;
        }


        .report-table {
            min-width: 100% !important;

            font-size: 11px;
        }


        .report-table thead th,
        .report-table tbody td {
            padding: 6px;
        }


        .print-title {
            display: block !important;
        }
    }


    .print-title {
        display: none;
    }


    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 992px) {

        .summary-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 576px) {

        .summary-grid {
            grid-template-columns: 1fr;
        }


        .report-header {
            padding: 18px;
        }


        .report-header h3 {
            font-size: 20px;
        }


        .filter-card {
            padding: 15px;
        }


        .summary-value {
            font-size: 21px;
        }

    }

</style>


<div class="container-fluid py-3 cng-report-page">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="report-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h3>
                    <i class="bi bi-bar-chart-line-fill"></i>
                    CNG রিপোর্ট
                </h3>

                <p>
                    গাড়ি ও তারিখ অনুযায়ী জমা এবং খরচের রিপোর্ট
                </p>

            </div>


            <div class="no-print">

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-light fw-bold">

                    <i class="bi bi-printer-fill"></i>
                    রিপোর্ট প্রিন্ট

                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PRINT TITLE
         ===================================================== -->

    <div class="print-title mb-3">

        <h3 class="text-center">
            CNG দৈনিক জমা ও খরচ রিপোর্ট
        </h3>

    </div>


    <!-- =====================================================
         REPORT TYPE
         ===================================================== -->

    <div class="report-type-box no-print">

        <?php

        $vehicleUrl =
            'index.php?page=cng/report'
            . '&report_type=vehicle'
            . '&garage_id=' . $garageId
            . '&vehicle_id=' . $vehicleId
            . '&from_date=' . urlencode($fromDate)
            . '&to_date=' . urlencode($toDate);

        $dateUrl =
            'index.php?page=cng/report'
            . '&report_type=date'
            . '&garage_id=' . $garageId
            . '&vehicle_id=' . $vehicleId
            . '&from_date=' . urlencode($fromDate)
            . '&to_date=' . urlencode($toDate);

        ?>


        <a
            href="<?= htmlspecialchars($vehicleUrl) ?>"
            class="<?= $reportType === 'vehicle' ? 'active' : '' ?>">

            <i class="bi bi-car-front-fill"></i>
            গাড়ি অনুযায়ী রিপোর্ট

        </a>


        <a
            href="<?= htmlspecialchars($dateUrl) ?>"
            class="<?= $reportType === 'date' ? 'active' : '' ?>">

            <i class="bi bi-calendar3"></i>
            তারিখ অনুযায়ী রিপোর্ট

        </a>

    </div>


    <!-- =====================================================
         FILTER
         ===================================================== -->

    <div class="filter-card no-print">

        <form
            method="GET"
            action="index.php">

            <input
                type="hidden"
                name="page"
                value="cng/report">


            <input
                type="hidden"
                name="report_type"
                value="<?= htmlspecialchars($reportType) ?>">


            <div class="row g-3">


                <!-- GARAGE -->

                <div class="col-lg-3 col-md-6">

                    <label class="form-label">
                        <i class="bi bi-building"></i>
                        গ্যারেজ
                    </label>


                    <select
                        name="garage_id"
                        class="form-select">

                        <option value="0">
                            সকল গ্যারেজ
                        </option>


                        <?php foreach ($garages as $garage): ?>

                            <option
                                value="<?= (int)$garage['id'] ?>"
                                <?= $garageId == $garage['id'] ? 'selected' : '' ?>>

                                <?= htmlspecialchars(
                                    $garage['garage_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- VEHICLE -->

                <div class="col-lg-3 col-md-6">

                    <label class="form-label">

                        <i class="bi bi-car-front-fill"></i>
                        গাড়ি

                    </label>


                    <select
                        name="vehicle_id"
                        class="form-select">

                        <option value="0">
                            <?= $reportType === 'vehicle'
                                ? 'গাড়ি নির্বাচন করুন'
                                : 'সকল গাড়ি' ?>
                        </option>


                        <?php foreach ($vehicles as $vehicle): ?>

                            <option
                                value="<?= (int)$vehicle['id'] ?>"
                                <?= $vehicleId == $vehicle['id'] ? 'selected' : '' ?>>

                                <?= htmlspecialchars(
                                    $vehicle['car_number'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <?php if (!empty($vehicle['driver_name'])): ?>

                                    -
                                    <?= htmlspecialchars(
                                        $vehicle['driver_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                <?php endif; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- FROM DATE -->

                <div class="col-lg-2 col-md-6">

                    <label class="form-label">

                        <i class="bi bi-calendar-event"></i>
                        শুরু তারিখ

                    </label>


                    <input
                        type="date"
                        name="from_date"
                        value="<?= htmlspecialchars($fromDate) ?>"
                        class="form-control">

                </div>


                <!-- TO DATE -->

                <div class="col-lg-2 col-md-6">

                    <label class="form-label">

                        <i class="bi bi-calendar-check"></i>
                        শেষ তারিখ

                    </label>


                    <input
                        type="date"
                        name="to_date"
                        value="<?= htmlspecialchars($toDate) ?>"
                        class="form-control">

                </div>


                <!-- BUTTON -->

                <div class="col-lg-2 col-md-12 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-primary w-100 filter-btn">

                        <i class="bi bi-search"></i>
                        রিপোর্ট দেখুন

                    </button>

                </div>


            </div>

        </form>

    </div>


    <!-- =====================================================
         VEHICLE WISE SELECTED INFO
         ===================================================== -->

    <?php if ($reportType === 'vehicle' && $selectedVehicle): ?>

        <div class="alert alert-primary d-flex align-items-center gap-3">

            <i class="bi bi-car-front-fill fs-3"></i>

            <div>

                <strong>
                    গাড়ি:
                    <?= htmlspecialchars(
                        $selectedVehicle['car_number'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>

                <?php if (!empty($selectedVehicle['driver_name'])): ?>

                    <div>
                        চালক:
                        <?= htmlspecialchars(
                            $selectedVehicle['driver_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                <?php endif; ?>


                <?php if (!empty($selectedVehicle['garage_name'])): ?>

                    <div>
                        গ্যারেজ:
                        <?= htmlspecialchars(
                            $selectedVehicle['garage_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         SUMMARY
         ===================================================== -->

    <div class="summary-grid">


        <!-- TOTAL RENT -->

        <div class="summary-card">

            <div class="summary-title">

                <span class="summary-icon">
                    🚗
                </span>

                মোট নির্ধারিত ভাড়া

            </div>


            <div class="summary-value">

                <?= number_format($totalRent, 2) ?>

                <small>৳</small>

            </div>

        </div>


        <!-- TOTAL COLLECTION -->

        <div class="summary-card">

            <div class="summary-title">

                <span class="summary-icon">
                    💰
                </span>

                মোট জমা

            </div>


            <div class="summary-value text-success">

                <?= number_format($totalCollection, 2) ?>

                <small>৳</small>

            </div>

        </div>


        <!-- TOTAL EXPENSE -->

        <div class="summary-card">

            <div class="summary-title">

                <span class="summary-icon">
                    💸
                </span>

                মোট খরচ

            </div>


            <div class="summary-value text-danger">

                <?= number_format($totalExpense, 2) ?>

                <small>৳</small>

            </div>

        </div>


        <!-- NET -->

        <div class="summary-card">

            <div class="summary-title">

                <span class="summary-icon">
                    📊
                </span>

                নেট আয়

            </div>


            <div class="summary-value text-primary">

                <?= number_format($totalNet, 2) ?>

                <small>৳</small>

            </div>

        </div>


    </div>


    <!-- =====================================================
         REPORT TABLE
         ===================================================== -->

    <div class="report-table-card">


        <div class="report-table-header">

            <div>

                <h5>

                    <?php if ($reportType === 'vehicle'): ?>

                        🚗 গাড়ি অনুযায়ী রিপোর্ট

                    <?php else: ?>

                        📅 তারিখ অনুযায়ী রিপোর্ট

                    <?php endif; ?>

                </h5>


                <small class="text-muted">

                    <?= banglaDate($fromDate) ?>

                    থেকে

                    <?= banglaDate($toDate) ?>

                    |

                    মোট <?= $totalRecords ?> টি রেকর্ড

                </small>

            </div>


            <div class="no-print">

                <span class="badge bg-success">

                    মোট জমা:
                    <?= number_format($totalCollection, 2) ?> ৳

                </span>


                <span class="badge bg-danger">

                    মোট খরচ:
                    <?= number_format($totalExpense, 2) ?> ৳

                </span>


                <span class="badge bg-primary">

                    নেট:
                    <?= number_format($totalNet, 2) ?> ৳

                </span>

            </div>

        </div>


        <?php if (!empty($reportRows)): ?>


            <div class="table-responsive">

                <table class="table table-bordered report-table">


                    <thead>

                        <tr>

                            <th>#</th>

                            <th>তারিখ</th>

                            <th>গাড়ির নাম্বার</th>

                            <th>গ্যারেজ</th>

                            <th>চালক</th>

                            <th class="text-end">
                                নির্ধারিত ভাড়া
                            </th>

                            <th class="text-end">
                                জমা
                            </th>

                            <th class="text-end">
                                খরচ
                            </th>

                            <th>
                                খরচের বিবরণ
                            </th>

                            <th class="text-end">
                                নেট আয়
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($reportRows as $index => $row): ?>


                            <?php

                            $dailyRent =
                                (float)$row['daily_rent'];

                            $collection =
                                (float)$row['amount'];

                            $expense =
                                (float)$row['expense_amount'];

                            $net =
                                $collection - $expense;

                            ?>


                            <tr>


                                <!-- NUMBER -->

                                <td>
                                    <?= $index + 1 ?>
                                </td>


                                <!-- DATE -->

                                <td>

                                    <?= banglaDate(
                                        $row['collection_date']
                                    ) ?>

                                </td>


                                <!-- CAR -->

                                <td>

                                    <span class="car-number">

                                        <?= htmlspecialchars(
                                            $row['car_number'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- GARAGE -->

                                <td>

                                    <?= !empty($row['garage_name'])

                                        ? htmlspecialchars(
                                            $row['garage_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )

                                        : '<span class="text-muted">
                                            নির্ধারিত নেই
                                           </span>'
                                    ?>

                                </td>


                                <!-- DRIVER -->

                                <td>

                                    <?= !empty($row['driver_name'])

                                        ? htmlspecialchars(
                                            $row['driver_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )

                                        : '<span class="text-muted">
                                            নেই
                                           </span>'
                                    ?>

                                </td>


                                <!-- RENT -->

                                <td class="text-end">

                                    <?= number_format(
                                        $dailyRent,
                                        2
                                    ) ?>

                                    ৳

                                </td>


                                <!-- COLLECTION -->

                                <td class="text-end">

                                    <span class="amount-collection">

                                        <?= number_format(
                                            $collection,
                                            2
                                        ) ?>

                                        ৳

                                    </span>

                                </td>


                                <!-- EXPENSE -->

                                <td class="text-end">

                                    <?php if ($expense > 0): ?>

                                        <span class="amount-expense">

                                            <?= number_format(
                                                $expense,
                                                2
                                            ) ?>

                                            ৳

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            0.00 ৳
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- EXPENSE NOTE -->

                                <td class="expense-note">

                                    <?php if (!empty($row['expense_note'])): ?>

                                        <?= htmlspecialchars(
                                            $row['expense_note'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- NET -->

                                <td class="text-end">

                                    <span class="amount-net">

                                        <?= number_format(
                                            $net,
                                            2
                                        ) ?>

                                        ৳

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                    <!-- =================================================
                         FOOTER TOTAL
                         ================================================= -->

                    <tfoot>

                        <tr class="table-light fw-bold">


                            <td
                                colspan="5"
                                class="text-end">

                                সর্বমোট:

                            </td>


                            <td class="text-end">

                                <?= number_format(
                                    $totalRent,
                                    2
                                ) ?>

                                ৳

                            </td>


                            <td class="text-end text-success">

                                <?= number_format(
                                    $totalCollection,
                                    2
                                ) ?>

                                ৳

                            </td>


                            <td class="text-end text-danger">

                                <?= number_format(
                                    $totalExpense,
                                    2
                                ) ?>

                                ৳

                            </td>


                            <td></td>


                            <td class="text-end text-primary">

                                <?= number_format(
                                    $totalNet,
                                    2
                                ) ?>

                                ৳

                            </td>


                        </tr>

                    </tfoot>


                </table>

            </div>


        <?php else: ?>


            <!-- =================================================
                 NO DATA
                 ================================================= -->

            <div class="empty-box">

                <i class="bi bi-file-earmark-bar-graph"></i>

                <h5>
                    কোনো রিপোর্ট পাওয়া যায়নি
                </h5>

                <p class="mb-0">

                    নির্বাচিত গাড়ি অথবা তারিখ অনুযায়ী
                    কোনো জমা/খরচের তথ্য নেই।

                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const reportType =
        document.querySelector('input[name="report_type"]');

    const vehicleSelect =
        document.querySelector('select[name="vehicle_id"]');

    const garageSelect =
        document.querySelector('select[name="garage_id"]');


    /*
     * =====================================================
     * VEHICLE FILTER BY GARAGE
     * =====================================================
     */

    if (garageSelect && vehicleSelect) {

        const allVehicleOptions =
            Array.from(
                vehicleSelect.options
            ).map(function (option) {

                return {
                    value: option.value,
                    text: option.text,
                    vehicleGarage:
                        option.dataset.garage
                };

            });


        /*
         * This works only if data-garage is available.
         * Server-side filtering remains active as well.
         */

    }


    /*
     * =====================================================
     * PRINT
     * =====================================================
     */

    window.printCngReport = function () {

        window.print();

    };

});

</script>