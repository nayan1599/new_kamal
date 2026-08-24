<?php

/*
|--------------------------------------------------------------------------
| Metro Due List
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');
$currentMonth = date('Y-m');


/*
|--------------------------------------------------------------------------
| Metro Cars
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        mc.*,

        COALESCE(
            (
                SELECT SUM(mp.amount)
                FROM metro_payments mp
                WHERE mp.metro_car_id = mc.id
                AND mp.payment_type = 'monthly'
            ),
            0
        ) AS total_monthly_paid

    FROM metro_cars mc

    WHERE mc.status = 'active'

    ORDER BY mc.id DESC
");

$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Due Calculation
|--------------------------------------------------------------------------
*/

$dueCars = [];

$totalDueAmount = 0;
$totalDueMonths = 0;


foreach ($cars as $car) {

    $monthlyAmount = (float)$car['monthly_amount'];

    $startDate = $car['start_date'];


    /*
    |--------------------------------------------------------------------------
    | কত মাস গাড়ি চলছে
    |--------------------------------------------------------------------------
    */

    $start = new DateTime(
        date('Y-m-01', strtotime($startDate))
    );

    $current = new DateTime(
        date('Y-m-01')
    );


    /*
    |--------------------------------------------------------------------------
    | গাড়ি নেওয়ার মাস থেকে বর্তমান মাস পর্যন্ত
    |--------------------------------------------------------------------------
    */

    $interval = $start->diff($current);

    $runningMonths =
        ($interval->y * 12)
        + $interval->m
        + 1;


    /*
    |--------------------------------------------------------------------------
    | মোট কত মাসের টাকা দেওয়ার কথা
    |--------------------------------------------------------------------------
    */

    $expectedAmount =
        $runningMonths *
        $monthlyAmount;


    /*
    |--------------------------------------------------------------------------
    | মোট মাসিক জমা
    |--------------------------------------------------------------------------
    */

    $monthlyPaid =
        (float)$car['total_monthly_paid'];


    /*
    |--------------------------------------------------------------------------
    | বাকি টাকা
    |--------------------------------------------------------------------------
    */

    $dueAmount = max(
        0,
        $expectedAmount - $monthlyPaid
    );


    /*
    |--------------------------------------------------------------------------
    | বাকি মাস
    |--------------------------------------------------------------------------
    */

    $dueMonths =
        $monthlyAmount > 0
        ? ceil(
            $dueAmount /
            $monthlyAmount
        )
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Last Monthly Payment
    |--------------------------------------------------------------------------
    */

    $lastPaymentStmt = $pdo->prepare("
        SELECT
            month_year,
            payment_date,
            amount
        FROM metro_payments
        WHERE metro_car_id = ?
        AND payment_type = 'monthly'
        ORDER BY month_year DESC, id DESC
        LIMIT 1
    ");

    $lastPaymentStmt->execute([
        $car['id']
    ]);

    $lastPayment =
        $lastPaymentStmt->fetch(
            PDO::FETCH_ASSOC
        );


    /*
    |--------------------------------------------------------------------------
    | Current Month Paid
    |--------------------------------------------------------------------------
    */

    $currentPaidStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount),0)
        FROM metro_payments
        WHERE metro_car_id = ?
        AND payment_type = 'monthly'
        AND month_year = ?
    ");

    $currentPaidStmt->execute([
        $car['id'],
        $currentMonth
    ]);

    $currentPaid =
        (float)$currentPaidStmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Current Month Due
    |--------------------------------------------------------------------------
    */

    $currentDue =
        max(
            0,
            $monthlyAmount -
            $currentPaid
        );


    /*
    |--------------------------------------------------------------------------
    | শুধু যাদের বাকি আছে
    |--------------------------------------------------------------------------
    */

    if ($dueAmount > 0) {

        $car['running_months'] =
            $runningMonths;

        $car['expected_amount'] =
            $expectedAmount;

        $car['monthly_paid'] =
            $monthlyPaid;

        $car['due_amount'] =
            $dueAmount;

        $car['due_months'] =
            $dueMonths;

        $car['current_paid'] =
            $currentPaid;

        $car['current_due'] =
            $currentDue;

        $car['last_payment'] =
            $lastPayment;

        $dueCars[] =
            $car;


        $totalDueAmount +=
            $dueAmount;

        $totalDueMonths +=
            $dueMonths;
    }
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalDueCars =
    count($dueCars);

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>

                বকেয়া গাড়ির তালিকা

            </h1>

            <p class="text-muted mb-0">

                যারা মাসিক টাকা দেয়নি তাদের বকেয়া হিসাব

            </p>

        </div>


        <a
            href="index.php?page=metro/index"
            class="btn btn-secondary"
        >

            <i class="bi bi-car-front me-1"></i>

            গাড়ির তালিকা

        </a>

    </div>



    <!-- =========================================================
         SUMMARY
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Due Cars -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-car-front-fill text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                বকেয়া গাড়ি

                            </div>

                            <div class="fs-4 fw-bold text-danger">

                                <?= bn_number(
                                    $totalDueCars
                                ) ?>

                                <span class="fs-6 text-muted">
                                    টি
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- Due Months -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-warning bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-calendar-x text-warning fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট বাকি মাস

                            </div>

                            <div class="fs-4 fw-bold text-warning">

                                <?= bn_number(
                                    $totalDueMonths
                                ) ?>

                                <span class="fs-6 text-muted">
                                    মাস
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- Due Amount -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-cash-stack text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট বকেয়া টাকা

                            </div>

                            <div class="fs-4 fw-bold text-danger">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDueAmount,
                                        0
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         TABLE
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <!-- Header -->

        <div class="card-header bg-danger text-white py-3">

            <div
                class="d-flex justify-content-between align-items-center"
            >

                <h5 class="mb-0">

                    <i class="bi bi-list-ul me-2"></i>

                    বকেয়া গাড়ির তালিকা

                </h5>


                <div class="d-flex gap-2">

                    <input
                        type="text"
                        id="searchInput"
                        class="form-control form-control-sm search-box"
                        placeholder="গাড়ি, চালক, মোবাইল..."
                    >


                    <button
                        type="button"
                        class="btn btn-light btn-sm"
                        onclick="window.print()"
                    >

                        <i class="bi bi-printer me-1"></i>

                        প্রিন্ট

                    </button>

                </div>

            </div>

        </div>



        <!-- Body -->

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="dueTable"
                >

                    <thead class="table-light">

                    <tr>

                        <th class="text-center">
                            #
                        </th>

                        <th>
                            গাড়ির নম্বর
                        </th>

                        <th>
                            চালকের নাম
                        </th>

                        <th>
                            মোবাইল
                        </th>

                        <th class="text-center">
                            গাড়ি নেওয়ার তারিখ
                        </th>

                        <th class="text-center">
                            চলেছে
                        </th>

                        <th class="text-center">
                            বাকি মাস
                        </th>

                        <th class="text-end">
                            মাসিক
                        </th>

                        <th class="text-end">
                            মোট বকেয়া
                        </th>

                        <th>
                            সর্বশেষ জমা
                        </th>

                        <th class="text-center">
                            অ্যাকশন
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($dueCars)): ?>

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-check-circle-fill text-success fs-1"
                                ></i>

                                <h5 class="text-success mt-3">

                                    কোনো বকেয়া গাড়ি নেই

                                </h5>

                                <p class="text-muted mb-0">

                                    সবাই নিয়মিত মাসিক টাকা পরিশোধ করেছে।

                                </p>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach (
                        $dueCars
                        as $i => $car
                    ): ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <span class="badge bg-secondary">

                                    <?= bn_number(
                                        $i + 1
                                    ) ?>

                                </span>

                            </td>



                            <!-- Car -->

                            <td>

                                <a
                                    href="index.php?page=metro/view&id=<?= (int)$car['id'] ?>"
                                    class="text-decoration-none"
                                >

                                    <strong class="text-primary">

                                        <?= htmlspecialchars(
                                            $car['car_number']
                                        ) ?>

                                    </strong>

                                </a>

                            </td>



                            <!-- Driver -->

                            <td>

                                <?= htmlspecialchars(
                                    $car['driver_name'] ?? '—'
                                ) ?>

                            </td>



                            <!-- Mobile -->

                            <td>

                                <?php if (
                                    !empty($car['mobile'])
                                ): ?>

                                    <a
                                        href="tel:<?= htmlspecialchars($car['mobile']) ?>"
                                        class="text-decoration-none"
                                    >

                                        <i class="bi bi-telephone me-1"></i>

                                        <?= htmlspecialchars(
                                            $car['mobile']
                                        ) ?>

                                    </a>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>



                            <!-- Start Date -->

                            <td class="text-center">

                                <?= bn_number(
                                    date(
                                        'd/m/Y',
                                        strtotime(
                                            $car['start_date']
                                        )
                                    )
                                ) ?>

                            </td>



                            <!-- Running Months -->

                            <td class="text-center">

                                <span class="badge bg-info text-dark">

                                    <?= bn_number(
                                        $car['running_months']
                                    ) ?>

                                    মাস

                                </span>

                            </td>



                            <!-- Due Months -->

                            <td class="text-center">

                                <span
                                    class="badge bg-danger fs-6"
                                >

                                    <?= bn_number(
                                        $car['due_months']
                                    ) ?>

                                    মাস

                                </span>

                            </td>



                            <!-- Monthly -->

                            <td class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        (float)$car['monthly_amount'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Total Due -->

                            <td class="text-end">

                                <strong class="text-danger fs-6">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $car['due_amount'],
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </td>



                            <!-- Last Payment -->

                            <td>

                                <?php
                                if (
                                    !empty(
                                        $car['last_payment']
                                    )
                                ):
                                ?>

                                    <div>

                                        <strong>

                                            <?= bn_number(
                                                number_format(
                                                    (float)$car['last_payment']['amount'],
                                                    0
                                                )
                                            ) ?>

                                            টাকা

                                        </strong>

                                    </div>

                                    <small class="text-muted">

                                        <?= bn_number(
                                            date(
                                                'd/m/Y',
                                                strtotime(
                                                    $car['last_payment']['payment_date']
                                                )
                                            )
                                        ) ?>

                                    </small>

                                <?php else: ?>

                                    <span class="text-danger">

                                        কোনো মাসিক জমা নেই

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Action -->

                            <td class="text-center">

                                <div
                                    class="btn-group btn-group-sm"
                                >

                                    <a
                                        href="index.php?page=metro/payment&id=<?= (int)$car['id'] ?>"
                                        class="btn btn-success"
                                    >

                                        <i class="bi bi-cash-stack"></i>

                                        জমা

                                    </a>


                                    <a
                                        href="index.php?page=metro/view&id=<?= (int)$car['id'] ?>"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-eye"></i>

                                        View

                                    </a>

                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     SEARCH
========================================================== -->

<script>

document
    .getElementById('searchInput')
    .addEventListener('keyup', function () {

        const search =
            this.value
                .toLowerCase()
                .trim();


        const rows =
            document.querySelectorAll(
                '#dueTable tbody tr'
            );


        rows.forEach(function (row) {

            const text =
                row.textContent
                    .toLowerCase();


            row.style.display =
                text.includes(search)
                    ? ''
                    : 'none';

        });

    });

</script>



<style>

.search-box {
    width: 300px;
}

#dueTable th {
    white-space: nowrap;
}

#dueTable td {
    white-space: nowrap;
}

.table-hover tbody tr:hover {
    background-color: rgba(220, 53, 69, 0.04);
}


@media (max-width: 768px) {

    .card-header > div {
        flex-direction: column;
        align-items: stretch !important;
        gap: 10px;
    }

    .search-box {
        width: 100%;
    }

}


@media print {

    .btn,
    .search-box {
        display: none !important;
    }

    .card {
        border: 0 !important;
        box-shadow: none !important;
    }

}

</style>