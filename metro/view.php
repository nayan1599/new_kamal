<?php

/*
|--------------------------------------------------------------------------
| Metro Car View
|--------------------------------------------------------------------------
*/

$carId = (int)($_GET['id'] ?? 0);

if ($carId <= 0) {
    die("
        <div class='container py-5'>
            <div class='alert alert-danger'>
                গাড়ির ID পাওয়া যায়নি!
            </div>
        </div>
    ");
}


/*
|--------------------------------------------------------------------------
| Car Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM metro_cars
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$carId]);

$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    die("
        <div class='container py-5'>
            <div class='alert alert-danger'>
                গাড়ির তথ্য পাওয়া যায়নি!
            </div>
        </div>
    ");
}


$initialDeposit =
    (float)$car['initial_deposit'];

$monthlyAmount =
    (float)$car['monthly_amount'];



/*
|--------------------------------------------------------------------------
| Initial Payment
|--------------------------------------------------------------------------
*/

$initialStmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
    AND payment_type = 'initial'
");

$initialStmt->execute([
    $carId
]);

$totalInitialPaid =
    (float)$initialStmt->fetchColumn();



/*
|--------------------------------------------------------------------------
| Monthly Payment
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
    $carId
]);

$totalMonthlyPaid =
    (float)$monthlyStmt->fetchColumn();



/*
|--------------------------------------------------------------------------
| Total Paid
|--------------------------------------------------------------------------
*/

$totalPaid =
    $totalInitialPaid
    + $totalMonthlyPaid;



/*
|--------------------------------------------------------------------------
| Running Months
|--------------------------------------------------------------------------
*/

$startDate =
    $car['start_date'];

$start =
    new DateTime(
        date(
            'Y-m-01',
            strtotime($startDate)
        )
    );

$current =
    new DateTime(
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
| Expected Monthly Amount
|--------------------------------------------------------------------------
*/

$expectedMonthly =
    $runningMonths
    * $monthlyAmount;



/*
|--------------------------------------------------------------------------
| Paid Months
|--------------------------------------------------------------------------
*/

$paidMonths =
    $monthlyAmount > 0
    ? floor(
        $totalMonthlyPaid
        / $monthlyAmount
    )
    : 0;



/*
|--------------------------------------------------------------------------
| Due
|--------------------------------------------------------------------------
*/

$dueAmount =
    max(
        0,
        $expectedMonthly
        - $totalMonthlyPaid
    );


$dueMonths =
    $monthlyAmount > 0
    ? ceil(
        $dueAmount
        / $monthlyAmount
    )
    : 0;



/*
|--------------------------------------------------------------------------
| Current Month
|--------------------------------------------------------------------------
*/

$currentMonth =
    date('Y-m');


$currentMonthStmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
    AND payment_type = 'monthly'
    AND month_year = ?
");

$currentMonthStmt->execute([
    $carId,
    $currentMonth
]);

$currentMonthPaid =
    (float)$currentMonthStmt->fetchColumn();


$currentMonthDue =
    max(
        0,
        $monthlyAmount
        - $currentMonthPaid
    );



/*
|--------------------------------------------------------------------------
| All Payments
|--------------------------------------------------------------------------
*/

$paymentStmt = $pdo->prepare("
    SELECT *
    FROM metro_payments
    WHERE metro_car_id = ?
    ORDER BY payment_date DESC, id DESC
");

$paymentStmt->execute([
    $carId
]);

$payments =
    $paymentStmt->fetchAll(
        PDO::FETCH_ASSOC
    );

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-car-front-fill text-primary me-2"></i>

                গাড়ির বিস্তারিত

            </h1>

            <p class="text-muted mb-0">

                <?= htmlspecialchars(
                    $car['car_number']
                ) ?>

                - এর সম্পূর্ণ হিসাব

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=metro/index"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                তালিকা

            </a>


            <a
                href="index.php?page=metro/payment&id=<?= $carId ?>"
                class="btn btn-success"
            >

                <i class="bi bi-cash-stack me-1"></i>

                টাকা জমা

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
         CAR INFORMATION
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">


        <div class="card-body p-4">

            <div class="row align-items-center">


                <!-- Car Icon -->

                <div class="col-12 col-md-2 text-center mb-3 mb-md-0">

                    <div class="car-icon mx-auto">

                        <i class="bi bi-car-front-fill"></i>

                    </div>

                </div>


                <!-- Main Information -->

                <div class="col-12 col-md-7">

                    <h2 class="fw-bold mb-2">

                        <?= htmlspecialchars(
                            $car['car_number']
                        ) ?>

                    </h2>


                    <div class="row g-2">

                        <div class="col-sm-6">

                            <span class="text-muted">

                                <i class="bi bi-person me-1"></i>

                                চালক:

                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $car['driver_name'] ?? '—'
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-sm-6">

                            <span class="text-muted">

                                <i class="bi bi-telephone me-1"></i>

                                মোবাইল:

                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $car['mobile'] ?? '—'
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-sm-6">

                            <span class="text-muted">

                                <i class="bi bi-calendar3 me-1"></i>

                                গাড়ি নেওয়ার তারিখ:

                            </span>

                            <strong>

                                <?= bn_number(
                                    date(
                                        'd/m/Y',
                                        strtotime(
                                            $car['start_date']
                                        )
                                    )
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-sm-6">

                            <span class="text-muted">

                                স্ট্যাটাস:

                            </span>


                            <?php if (
                                $car['status'] === 'active'
                            ): ?>

                                <span class="badge bg-success">

                                    চলমান

                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">

                                    <?= htmlspecialchars(
                                        $car['status']
                                    ) ?>

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- Monthly -->

                <div class="col-12 col-md-3 text-md-end mt-3 mt-md-0">

                    <small class="text-muted">

                        প্রতি মাসে জমা

                    </small>

                    <div class="fs-2 fw-bold text-primary">

                        ৳ <?= bn_number(
                            number_format(
                                $monthlyAmount,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


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
                                $totalInitialPaid,
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

                        মোট মাসিক জমা

                    </div>

                    <div class="fs-4 fw-bold text-success">

                        ৳ <?= bn_number(
                            number_format(
                                $totalMonthlyPaid,
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

                        সর্বমোট জমা

                    </div>

                    <div class="fs-4 fw-bold text-success">

                        ৳ <?= bn_number(
                            number_format(
                                $totalPaid,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Due -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট বকেয়া

                    </div>

                    <div class="fs-4 fw-bold text-danger">

                        ৳ <?= bn_number(
                            number_format(
                                $dueAmount,
                                0
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         MONTH STATUS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Running -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted">

                                গাড়ি চলছে

                            </div>

                            <h3 class="mb-0 text-info">

                                <?= bn_number(
                                    $runningMonths
                                ) ?>

                                মাস

                            </h3>

                        </div>

                        <i
                            class="bi bi-calendar-range text-info fs-1"
                        ></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- Paid Months -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted">

                                টাকা দিয়েছে

                            </div>

                            <h3 class="mb-0 text-success">

                                <?= bn_number(
                                    $paidMonths
                                ) ?>

                                মাস

                            </h3>

                        </div>

                        <i
                            class="bi bi-check-circle text-success fs-1"
                        ></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- Due Months -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted">

                                বাকি

                            </div>

                            <h3 class="mb-0 text-danger">

                                <?= bn_number(
                                    $dueMonths
                                ) ?>

                                মাস

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
         CURRENT MONTH
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">


        <div class="card-header bg-warning py-3">

            <h5 class="mb-0">

                <i class="bi bi-calendar-check me-2"></i>

                এই মাসের হিসাব

            </h5>

        </div>


        <div class="card-body">


            <div class="row text-center g-3">


                <div class="col-12 col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">

                            মাসিক কিস্তি

                        </small>

                        <h4 class="text-primary mb-0">

                            ৳ <?= bn_number(
                                number_format(
                                    $monthlyAmount,
                                    0
                                )
                            ) ?>

                        </h4>

                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">

                            এই মাসে জমা

                        </small>

                        <h4 class="text-success mb-0">

                            ৳ <?= bn_number(
                                number_format(
                                    $currentMonthPaid,
                                    0
                                )
                            ) ?>

                        </h4>

                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">

                            এই মাসে বাকি

                        </small>


                        <?php if (
                            $currentMonthDue > 0
                        ): ?>

                            <h4 class="text-danger mb-0">

                                ৳ <?= bn_number(
                                    number_format(
                                        $currentMonthDue,
                                        0
                                    )
                                ) ?>

                            </h4>

                        <?php else: ?>

                            <h4 class="text-success mb-0">

                                সম্পূর্ণ জমা

                            </h4>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         PAYMENT HISTORY
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div class="card-header bg-primary text-white py-3">

            <div
                class="d-flex justify-content-between align-items-center"
            >

                <h5 class="mb-0">

                    <i class="bi bi-clock-history me-2"></i>

                    পেমেন্টের ইতিহাস

                </h5>


                <span class="badge bg-light text-dark">

                    মোট <?= bn_number(
                        count($payments)
                    ) ?>

                    টি

                </span>

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
                            পেমেন্ট ধরন
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


                    <?php if (
                        empty($payments)
                    ): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-5 text-muted"
                            >

                                কোনো পেমেন্ট পাওয়া যায়নি।

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach (
                        $payments
                        as $i => $payment
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
                                            $payment['payment_date']
                                        )
                                    )
                                ) ?>

                            </td>


                            <td>

                                <?php if (
                                    $payment['payment_type']
                                    === 'initial'
                                ): ?>

                                    <span class="badge bg-primary">

                                        প্রাথমিক জমা

                                    </span>

                                <?php elseif (
                                    $payment['payment_type']
                                    === 'monthly'
                                ): ?>

                                    <span class="badge bg-success">

                                        মাসিক জমা

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
                                        $payment['month_year']
                                    )
                                ): ?>

                                    <?= bn_number(
                                        date(
                                            'm/Y',
                                            strtotime(
                                                $payment['month_year']
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
                                            (float)$payment['amount'],
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $payment['note'] ?? '—'
                                ) ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                    <?php if (
                        !empty($payments)
                    ): ?>

                        <tfoot class="table-light">

                        <tr>

                            <th
                                colspan="4"
                                class="text-end"
                            >

                                সর্বমোট জমা:

                            </th>

                            <th class="text-end text-success">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalPaid,
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

</div>



<style>

.car-icon {

    width: 90px;
    height: 90px;

    border-radius: 50%;

    background: rgba(13, 110, 253, .10);

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 45px;

}


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

    .btn {

        display: none !important;
    }

    .card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;
    }

}

</style>