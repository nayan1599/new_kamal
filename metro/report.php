<?php

/*
|--------------------------------------------------------------------------
| Metro Report
|--------------------------------------------------------------------------
*/

$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate   = $_GET['to_date'] ?? date('Y-m-d');
$carId    = (int)($_GET['car_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Active Cars
|--------------------------------------------------------------------------
*/

$carsStmt = $pdo->query("
    SELECT *
    FROM metro_cars
    WHERE status = 'active'
    ORDER BY car_number ASC
");

$allCars = $carsStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Payment Report
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        mp.*,
        mc.car_number,
        mc.driver_name,
        mc.mobile

    FROM metro_payments mp

    INNER JOIN metro_cars mc
        ON mc.id = mp.metro_car_id

    WHERE mp.payment_date BETWEEN ? AND ?
";

$params = [
    $fromDate,
    $toDate
];


if ($carId > 0) {

    $sql .= "
        AND mp.metro_car_id = ?
    ";

    $params[] = $carId;
}


$sql .= "
    ORDER BY mp.payment_date DESC, mp.id DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalPayment = 0;
$totalInitial = 0;
$totalMonthly = 0;
$totalOther = 0;

foreach ($payments as $row) {

    $amount = (float)$row['amount'];

    $totalPayment += $amount;


    if ($row['payment_type'] === 'initial') {

        $totalInitial += $amount;

    } elseif ($row['payment_type'] === 'monthly') {

        $totalMonthly += $amount;

    } else {

        $totalOther += $amount;
    }
}


/*
|--------------------------------------------------------------------------
| Due Calculation
|--------------------------------------------------------------------------
*/

$reportCars = [];

$totalDue = 0;
$totalDueMonths = 0;


foreach ($allCars as $car) {

    /*
    |--------------------------------------------------------------------------
    | যদি নির্দিষ্ট গাড়ি নির্বাচন করা হয়
    |--------------------------------------------------------------------------
    */

    if ($carId > 0 && (int)$car['id'] !== $carId) {
        continue;
    }


    $monthlyAmount =
        (float)$car['monthly_amount'];


    /*
    |--------------------------------------------------------------------------
    | গাড়ি নেওয়ার তারিখ
    |--------------------------------------------------------------------------
    */

    $startDate =
        $car['start_date'];


    if (!$startDate) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | গাড়ি কত মাস চলছে
    |--------------------------------------------------------------------------
    */

    $start = new DateTime(
        date(
            'Y-m-01',
            strtotime($startDate)
        )
    );


    $current = new DateTime(
        date('Y-m-01')
    );


    $interval =
        $start->diff($current);


    $runningMonths =
        ($interval->y * 12)
        + $interval->m
        + 1;


    /*
    |--------------------------------------------------------------------------
    | মোট মাসিক জমা
    |--------------------------------------------------------------------------
    */

    $monthlyStmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(amount), 0)

        FROM metro_payments

        WHERE metro_car_id = ?

        AND payment_type = 'monthly'
    ");

    $monthlyStmt->execute([
        $car['id']
    ]);


    $monthlyPaid =
        (float)$monthlyStmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Expected
    |--------------------------------------------------------------------------
    */

    $expected =
        $runningMonths
        * $monthlyAmount;


    /*
    |--------------------------------------------------------------------------
    | Due
    |--------------------------------------------------------------------------
    */

    $due =
        max(
            0,
            $expected - $monthlyPaid
        );


    /*
    |--------------------------------------------------------------------------
    | Due Months
    |--------------------------------------------------------------------------
    */

    $dueMonths =
        $monthlyAmount > 0
        ? ceil(
            $due /
            $monthlyAmount
        )
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Add
    |--------------------------------------------------------------------------
    */

    $car['running_months'] =
        $runningMonths;

    $car['monthly_paid'] =
        $monthlyPaid;

    $car['expected'] =
        $expected;

    $car['due'] =
        $due;

    $car['due_months'] =
        $dueMonths;


    $reportCars[] =
        $car;


    $totalDue +=
        $due;

    $totalDueMonths +=
        $dueMonths;
}


/*
|--------------------------------------------------------------------------
| Total Cars
|--------------------------------------------------------------------------
*/

$totalCars =
    count($reportCars);

?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-bar-chart-fill text-primary me-2"></i>

                মেট্রো গাড়ির রিপোর্ট

            </h1>

            <p class="text-muted mb-0">

                গাড়ির জমা, মাসিক কিস্তি ও বকেয়া রিপোর্ট

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=metro/index"
                class="btn btn-secondary"
            >

                <i class="bi bi-car-front me-1"></i>

                গাড়ির তালিকা

            </a>


            <button
                onclick="window.print()"
                class="btn btn-dark"
            >

                <i class="bi bi-printer me-1"></i>

                প্রিন্ট

            </button>

        </div>

    </div>



    <!-- =========================================================
         FILTER
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                <i class="bi bi-funnel me-2"></i>

                রিপোর্ট ফিল্টার

            </h5>

        </div>


        <div class="card-body">

            <form
                method="GET"
                class="row g-3 align-items-end"
            >

                <input
                    type="hidden"
                    name="page"
                    value="metro/report"
                >


                <!-- From -->

                <div class="col-12 col-md-3">

                    <label class="form-label fw-semibold">

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

                <div class="col-12 col-md-3">

                    <label class="form-label fw-semibold">

                        শেষ তারিখ

                    </label>

                    <input
                        type="date"
                        name="to_date"
                        class="form-control"
                        value="<?= htmlspecialchars($toDate) ?>"
                    >

                </div>


                <!-- Car -->

                <div class="col-12 col-md-4">

                    <label class="form-label fw-semibold">

                        গাড়ি নির্বাচন

                    </label>

                    <select
                        name="car_id"
                        class="form-select"
                    >

                        <option value="0">

                            সকল গাড়ি

                        </option>


                        <?php foreach ($allCars as $car): ?>

                            <option
                                value="<?= (int)$car['id'] ?>"
                                <?= $carId == $car['id']
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= htmlspecialchars(
                                    $car['car_number']
                                ) ?>

                                -
                                <?= htmlspecialchars(
                                    $car['driver_name'] ?? ''
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Submit -->

                <div class="col-12 col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >

                        <i class="bi bi-search me-1"></i>

                        রিপোর্ট দেখুন

                    </button>

                </div>

            </form>

        </div>

    </div>



    <!-- =========================================================
         SUMMARY
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Cars -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট গাড়ি

                    </div>

                    <div class="fs-3 fw-bold text-primary">

                        <?= bn_number(
                            $totalCars
                        ) ?>

                        <span class="fs-6">
                            টি
                        </span>

                    </div>

                </div>

            </div>

        </div>



        <!-- Initial -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        প্রাথমিক জমা

                    </div>

                    <div class="fs-4 fw-bold text-primary">

                        ৳ <?= bn_number(
                            number_format(
                                $totalInitial,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Monthly -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        মাসিক জমা

                    </div>

                    <div class="fs-4 fw-bold text-success">

                        ৳ <?= bn_number(
                            number_format(
                                $totalMonthly,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Total -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট আদায়

                    </div>

                    <div class="fs-4 fw-bold text-success">

                        ৳ <?= bn_number(
                            number_format(
                                $totalPayment,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         DUE SUMMARY
    ========================================================== -->

    <div class="row g-3 mb-4">


        <div class="col-12 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted">

                                মোট বাকি মাস

                            </div>

                            <h3 class="text-warning mb-0">

                                <?= bn_number(
                                    $totalDueMonths
                                ) ?>

                                মাস

                            </h3>

                        </div>


                        <i
                            class="bi bi-calendar-x text-warning fs-1"
                        ></i>

                    </div>

                </div>

            </div>

        </div>



        <div class="col-12 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted">

                                মোট বকেয়া

                            </div>

                            <h3 class="text-danger mb-0">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDue,
                                        0
                                    )
                                ) ?>

                            </h3>

                        </div>


                        <i
                            class="bi bi-exclamation-circle text-danger fs-1"
                        ></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         PAYMENT REPORT
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">


        <div class="card-header bg-success text-white">

            <h5 class="mb-0">

                <i class="bi bi-cash-stack me-2"></i>

                পেমেন্ট রিপোর্ট

            </h5>

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
                            গাড়ির নম্বর
                        </th>

                        <th>
                            চালকের নাম
                        </th>

                        <th>
                            ধরন
                        </th>

                        <th>
                            মাস
                        </th>

                        <th class="text-end">
                            টাকা
                        </th>

                        <th>
                            নোট
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($payments)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                কোনো পেমেন্ট পাওয়া যায়নি।

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach (
                        $payments
                        as $i => $row
                    ): ?>


                        <tr>

                            <td class="text-center">

                                <?= bn_number(
                                    $i + 1
                                ) ?>

                            </td>


                            <td>

                                <?= bn_number(
                                    date(
                                        'd/m/Y',
                                        strtotime(
                                            $row['payment_date']
                                        )
                                    )
                                ) ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['driver_name'] ?? '—'
                                ) ?>

                            </td>


                            <td>

                                <?php if (
                                    $row['payment_type']
                                    === 'initial'
                                ): ?>

                                    <span class="badge bg-primary">

                                        প্রাথমিক

                                    </span>

                                <?php elseif (
                                    $row['payment_type']
                                    === 'monthly'
                                ): ?>

                                    <span class="badge bg-success">

                                        মাসিক

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        অন্যান্য

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if (
                                    !empty(
                                        $row['month_year']
                                    )
                                ): ?>

                                    <?= bn_number(
                                        date(
                                            'm/Y',
                                            strtotime(
                                                $row['month_year']
                                                . '-01'
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>


                            <td class="text-end">

                                <strong class="text-success">

                                    ৳ <?= bn_number(
                                        number_format(
                                            (float)$row['amount'],
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['note'] ?? '—'
                                ) ?>

                            </td>

                        </tr>


                    <?php endforeach; ?>

                    </tbody>


                    <?php if (!empty($payments)): ?>

                        <tfoot class="table-light">

                        <tr>

                            <th
                                colspan="6"
                                class="text-end"
                            >

                                মোট:

                            </th>

                            <th class="text-end text-success">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalPayment,
                                        0
                                    )
                                ) ?>

                            </th>

                            <th></th>

                        </tr>

                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        </div>

    </div>



    <!-- =========================================================
         CAR WISE REPORT
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div class="card-header bg-danger text-white">

            <h5 class="mb-0">

                <i class="bi bi-car-front-fill me-2"></i>

                গাড়ি অনুযায়ী হিসাব

            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            গাড়ির নম্বর
                        </th>

                        <th>
                            চালক
                        </th>

                        <th>
                            গাড়ি চলছে
                        </th>

                        <th class="text-end">
                            মাসিক
                        </th>

                        <th class="text-end">
                            মোট দেওয়া
                        </th>

                        <th class="text-center">
                            বাকি মাস
                        </th>

                        <th class="text-end">
                            বকেয়া
                        </th>

                        <th class="text-center">
                            অবস্থা
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $reportCars
                        as $i => $car
                    ): ?>


                        <tr>


                            <td>

                                <?= bn_number(
                                    $i + 1
                                ) ?>

                            </td>


                            <td>

                                <strong class="text-primary">

                                    <?= htmlspecialchars(
                                        $car['car_number']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $car['driver_name'] ?? '—'
                                ) ?>

                            </td>


                            <td>

                                <?= bn_number(
                                    $car['running_months']
                                ) ?>

                                মাস

                            </td>


                            <td class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        (float)$car['monthly_amount'],
                                        0
                                    )
                                ) ?>

                            </td>


                            <td class="text-end text-success">

                                ৳ <?= bn_number(
                                    number_format(
                                        $car['monthly_paid'],
                                        0
                                    )
                                ) ?>

                            </td>


                            <td class="text-center">

                                <?php if (
                                    $car['due_months'] > 0
                                ): ?>

                                    <span class="badge bg-danger">

                                        <?= bn_number(
                                            $car['due_months']
                                        ) ?>

                                        মাস

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-success">

                                        নেই

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td class="text-end">

                                <?php if (
                                    $car['due'] > 0
                                ): ?>

                                    <strong class="text-danger">

                                        ৳ <?= bn_number(
                                            number_format(
                                                $car['due'],
                                                0
                                            )
                                        ) ?>

                                    </strong>

                                <?php else: ?>

                                    <span class="text-success">

                                        ৳ ০

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td class="text-center">

                                <?php if (
                                    $car['due'] > 0
                                ): ?>

                                    <span class="badge bg-danger">

                                        বকেয়া

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-success">

                                        নিয়মিত

                                    </span>

                                <?php endif; ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



<style>

.card {

    border-radius: 12px;

}


.table th {

    white-space: nowrap;

}


.table td {

    white-space: nowrap;

}


@media print {

    .btn,
    form,
    .card-header {

        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;

    }


    body {

        background: #fff !important;

    }


    .card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;

    }

}

</style>