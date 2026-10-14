<?php

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

$fromDate = trim($_GET['from_date'] ?? date('Y-m-01'));
$toDate   = trim($_GET['to_date'] ?? date('Y-m-d'));

$vehicleId = (int)($_GET['vehicle_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

$fromObj = DateTime::createFromFormat('Y-m-d', $fromDate);
$toObj   = DateTime::createFromFormat('Y-m-d', $toDate);

if (
    !$fromObj ||
    $fromObj->format('Y-m-d') !== $fromDate
) {
    $fromDate = date('Y-m-01');
}

if (
    !$toObj ||
    $toObj->format('Y-m-d') !== $toDate
) {
    $toDate = date('Y-m-d');
}


/*
|--------------------------------------------------------------------------
| Vehicle List
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        vehicle_number,
        driver_name
    FROM hice_vehicles
    ORDER BY vehicle_number ASC
");

$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Report Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        r.id,
        r.collection_date,
        r.vehicle_id,
        r.total_rent,
        r.expense_amount,
        r.expense_note,
        r.net_income,
        r.driver_percent,
        r.driver_amount,
        r.owner_amount,

        v.vehicle_number,
        v.driver_name,
        v.driver_mobile

    FROM hice_rents r

    LEFT JOIN hice_vehicles v
        ON v.id = r.vehicle_id

    WHERE r.collection_date BETWEEN ? AND ?
";

$params = [
    $fromDate,
    $toDate
];


if ($vehicleId > 0) {

    $sql .= " AND r.vehicle_id = ? ";

    $params[] = $vehicleId;
}


$sql .= "
    ORDER BY
        r.collection_date DESC,
        r.id DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalRent   = 0;
$totalExpense = 0;
$totalNet    = 0;
$totalDriver = 0;
$totalOwner  = 0;

foreach ($rows as $row) {

    $rent = (float)($row['total_rent'] ?? 0);

    $expense = (float)($row['expense_amount'] ?? 0);

    $net = isset($row['net_income'])
        ? (float)$row['net_income']
        : max(0, $rent - $expense);

    $driver = isset($row['driver_amount'])
        ? (float)$row['driver_amount']
        : ($net * 0.30);

    $owner = isset($row['owner_amount'])
        ? (float)$row['owner_amount']
        : ($net - $driver);


    $totalRent += $rent;
    $totalExpense += $expense;
    $totalNet += $net;
    $totalDriver += $driver;
    $totalOwner += $owner;
}

?>


<style>

.hice-report-wrap {
    padding: 20px;
}


/* Header */

.hice-report-header {
    background: linear-gradient(135deg, #198754, #20c997);
    color: #fff;
    border-radius: 18px;
    padding: 25px;
    margin-bottom: 20px;
}

.hice-report-header h3 {
    font-weight: 700;
}


/* Filter */

.hice-filter {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,.07);
}

.hice-filter label {
    font-weight: 600;
}

.hice-filter .form-control,
.hice-filter .form-select {
    min-height: 45px;
    border-radius: 9px;
}


/* Summary */

.report-stat {
    background: #fff;
    border-radius: 15px;
    padding: 18px;
    height: 100%;
    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.report-stat-title {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 7px;
}

.report-stat-value {
    font-size: 22px;
    font-weight: 700;
}


/* Table */

.report-table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,.07);
}

.report-table-card .table {
    margin-bottom: 0;
}

.report-table-card thead th {
    background: #f8f9fa;
    white-space: nowrap;
    font-size: 13px;
}

.report-table-card tbody td {
    vertical-align: middle;
    white-space: nowrap;
}

.vehicle-number {
    font-weight: 700;
}

.driver-name {
    font-size: 13px;
    color: #6c757d;
}

.amount {
    font-weight: 600;
}


/* Print */

@media print {

    body {
        background: #fff !important;
    }

    .no-print {
        display: none !important;
    }

    .hice-report-wrap {
        padding: 0;
    }

    .hice-report-header {
        background: none !important;
        color: #000 !important;
        padding: 10px 0;
    }

    .report-stat,
    .report-table-card {
        box-shadow: none !important;
        border: 1px solid #ddd;
    }

    .table {
        font-size: 12px;
    }

}

</style>


<div class="container-fluid hice-report-wrap">


    <!-- Header -->

    <div class="hice-report-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h3 class="mb-1">
                    <i class="bi bi-bar-chart-fill"></i>
                    Hice আয়-ব্যয়ের রিপোর্ট
                </h3>

                <div>
                    <?= htmlspecialchars($fromDate) ?>
                    থেকে
                    <?= htmlspecialchars($toDate) ?>
                </div>

            </div>


            <div class="no-print">

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-light"
                >

                    <i class="bi bi-printer"></i>
                    Print

                </button>

            </div>

        </div>

    </div>


    <!-- Filter -->

    <div class="hice-filter no-print">

        <form method="GET">

            <input
                type="hidden"
                name="page"
                value="hice/report"
            >


            <div class="row g-3 align-items-end">


                <!-- From -->

                <div class="col-md-3">

                    <label class="form-label">
                        শুরু তারিখ
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        class="form-control"
                        value="<?= htmlspecialchars($fromDate) ?>"
                    >

                </div>


                <!-- To -->

                <div class="col-md-3">

                    <label class="form-label">
                        শেষ তারিখ
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        class="form-control"
                        value="<?= htmlspecialchars($toDate) ?>"
                    >

                </div>


                <!-- Vehicle -->

                <div class="col-md-4">

                    <label class="form-label">
                        গাড়ি নির্বাচন করুন
                    </label>

                    <select
                        name="vehicle_id"
                        class="form-select"
                    >

                        <option value="0">
                            🚗 সকল গাড়ি
                        </option>


                        <?php foreach ($vehicles as $vehicle): ?>

                            <option
                                value="<?= (int)$vehicle['id'] ?>"
                                <?= $vehicleId == $vehicle['id'] ? 'selected' : '' ?>
                            >

                                <?= htmlspecialchars($vehicle['vehicle_number']) ?>

                                -
                                <?= htmlspecialchars($vehicle['driver_name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Button -->

                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-success w-100"
                        style="min-height:45px;"
                    >

                        <i class="bi bi-search"></i>
                        রিপোর্ট দেখুন

                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- Summary -->

    <div class="row g-3 mb-4">


        <!-- Total Rent -->

        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat">

                <div class="report-stat-title">
                    মোট ভাড়া
                </div>

                <div class="report-stat-value text-primary">

                    ৳ <?= number_format($totalRent, 2) ?>

                </div>

            </div>

        </div>


        <!-- Expense -->

        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat">

                <div class="report-stat-title">
                    মোট খরচ
                </div>

                <div class="report-stat-value text-danger">

                    ৳ <?= number_format($totalExpense, 2) ?>

                </div>

            </div>

        </div>


        <!-- Net -->

        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat">

                <div class="report-stat-title">
                    নেট আয়
                </div>

                <div class="report-stat-value text-success">

                    ৳ <?= number_format($totalNet, 2) ?>

                </div>

            </div>

        </div>


        <!-- Driver -->

        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat">

                <div class="report-stat-title">
                    Driver ৩০%
                </div>

                <div class="report-stat-value text-warning">

                    ৳ <?= number_format($totalDriver, 2) ?>

                </div>

            </div>

        </div>


        <!-- Owner -->

        <div class="col-xl col-md-4 col-sm-6">

            <div class="report-stat">

                <div class="report-stat-title">
                    Owner ৭০%
                </div>

                <div class="report-stat-value text-success">

                    ৳ <?= number_format($totalOwner, 2) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- Table -->

    <div class="report-table-card">

        <div class="p-3 border-bottom d-flex justify-content-between">

            <div>

                <strong>
                    <i class="bi bi-list-ul"></i>
                    বিস্তারিত হিসাব
                </strong>

            </div>

            <div class="text-muted">

                মোট <?= function_exists('bn_number')
                    ? bn_number(count($rows))
                    : count($rows) ?> টি রেকর্ড

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>তারিখ</th>

                        <th>গাড়ি</th>

                        <th>ড্রাইভার</th>

                        <th>মোট ভাড়া</th>

                        <th>খরচ</th>

                        <th>নেট আয়</th>

                        <th>Driver ৩০%</th>

                        <th>Owner ৭০%</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($rows)): ?>

                    <tr>

                        <td
                            colspan="9"
                            class="text-center py-5 text-muted"
                        >

                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                            এই সময়ের মধ্যে কোনো হিসাব পাওয়া যায়নি।

                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($rows as $index => $row): ?>

                        <?php

                        $rent = (float)($row['total_rent'] ?? 0);

                        $expense = (float)($row['expense_amount'] ?? 0);

                        $net = isset($row['net_income'])
                            ? (float)$row['net_income']
                            : max(0, $rent - $expense);

                        $driver = isset($row['driver_amount'])
                            ? (float)$row['driver_amount']
                            : ($net * 0.30);

                        $owner = isset($row['owner_amount'])
                            ? (float)$row['owner_amount']
                            : ($net - $driver);

                        ?>


                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>


                            <!-- Date -->

                            <td>

                                <?= htmlspecialchars(
                                    date(
                                        'd-m-Y',
                                        strtotime($row['collection_date'])
                                    )
                                ) ?>

                            </td>


                            <!-- Vehicle -->

                            <td>

                                <div class="vehicle-number">

                                    <?= htmlspecialchars(
                                        $row['vehicle_number'] ?? 'N/A'
                                    ) ?>

                                </div>

                            </td>


                            <!-- Driver -->

                            <td>

                                <div class="driver-name">

                                    <?= htmlspecialchars(
                                        $row['driver_name'] ?? 'N/A'
                                    ) ?>

                                    <?php if (!empty($row['driver_mobile'])): ?>

                                        <br>

                                        <small>
                                            <?= htmlspecialchars(
                                                $row['driver_mobile']
                                            ) ?>
                                        </small>

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- Rent -->

                            <td>

                                <span class="amount text-primary">

                                    ৳ <?= number_format($rent, 2) ?>

                                </span>

                            </td>


                            <!-- Expense -->

                            <td>

                                <span class="amount text-danger">

                                    ৳ <?= number_format($expense, 2) ?>

                                </span>


                                <?php if (!empty($row['expense_note'])): ?>

                                    <br>

                                    <small class="text-muted">

                                        <?= htmlspecialchars(
                                            $row['expense_note']
                                        ) ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- Net -->

                            <td>

                                <span class="amount text-success">

                                    ৳ <?= number_format($net, 2) ?>

                                </span>

                            </td>


                            <!-- Driver -->

                            <td>

                                <span class="amount text-warning">

                                    ৳ <?= number_format($driver, 2) ?>

                                </span>

                            </td>


                            <!-- Owner -->

                            <td>

                                <span class="amount text-success">

                                    ৳ <?= number_format($owner, 2) ?>

                                </span>

                            </td>

                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>

                </tbody>


                <?php if (!empty($rows)): ?>

                    <tfoot>

                        <tr class="table-light fw-bold">

                            <td colspan="4" class="text-end">

                                সর্বমোট:

                            </td>

                            <td>

                                ৳ <?= number_format($totalRent, 2) ?>

                            </td>

                            <td>

                                ৳ <?= number_format($totalExpense, 2) ?>

                            </td>

                            <td>

                                ৳ <?= number_format($totalNet, 2) ?>

                            </td>

                            <td>

                                ৳ <?= number_format($totalDriver, 2) ?>

                            </td>

                            <td>

                                ৳ <?= number_format($totalOwner, 2) ?>

                            </td>

                        </tr>

                    </tfoot>

                <?php endif; ?>

            </table>

        </div>

    </div>

</div>