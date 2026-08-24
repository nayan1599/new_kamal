<?php

/*
|--------------------------------------------------------------------------
| Metro Car List
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
            ), 0
        ) AS total_paid

    FROM metro_cars mc
    ORDER BY mc.id DESC
");

$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalCars       = count($cars);
$totalCollection = 0;
$totalDue        = 0;
$activeCars      = 0;

$currentMonth = date('Y-m');

foreach ($cars as $car) {

    $totalPaid = (float)$car['total_paid'];

    $monthlyAmount = (float)$car['monthly_amount'];

    $totalCollection += $totalPaid;


    /*
    |--------------------------------------------------------------------------
    | Active Car
    |--------------------------------------------------------------------------
    */

    if ($car['status'] === 'active') {
        $activeCars++;
    }


    /*
    |--------------------------------------------------------------------------
    | Current Month Payment
    |--------------------------------------------------------------------------
    */

    $stmtDue = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0)
        FROM metro_payments
        WHERE metro_car_id = ?
        AND month_year = ?
        AND payment_type = 'monthly'
    ");

    $stmtDue->execute([
        $car['id'],
        $currentMonth
    ]);

    $currentPaid = (float)$stmtDue->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Current Month Due
    |--------------------------------------------------------------------------
    */

    $due = max(
        0,
        $monthlyAmount - $currentPaid
    );

    $totalDue += $due;
}

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-car-front-fill text-primary me-2"></i>

                গাড়ির তালিকা

            </h1>

            <p class="text-muted mb-0">

                সকল মেট্রো গাড়ি, মাসিক জমা ও বকেয়ার হিসাব

            </p>

        </div>


        <a
            href="index.php?page=metro/add"
            class="btn btn-success btn-lg"
        >

            <i class="bi bi-plus-circle me-2"></i>

            নতুন গাড়ি

        </a>

    </div>



    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- মোট গাড়ি -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-car-front-fill text-primary fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                মোট গাড়ি
                            </div>

                            <div class="fs-4 fw-bold">

                                <?= bn_number($totalCars) ?>

                                <span class="fs-6 text-muted">
                                    টি
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- চলমান গাড়ি -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-success bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-check-circle-fill text-success fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                চলমান গাড়ি
                            </div>

                            <div class="fs-4 fw-bold text-success">

                                <?= bn_number($activeCars) ?>

                                <span class="fs-6 text-muted">
                                    টি
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- মোট জমা -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-info bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-cash-stack text-info fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                মোট জমা
                            </div>

                            <div class="fs-4 fw-bold text-info">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalCollection,
                                        0
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- মোট বকেয়া -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-exclamation-triangle-fill text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                এই মাসের বকেয়া
                            </div>

                            <div class="fs-4 fw-bold text-danger">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDue,
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
         TABLE CARD
    ========================================================== -->

    <div class="card shadow-sm border-0">


        <!-- Header -->

        <div
            class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3"
        >

            <h5 class="mb-0">

                <i class="bi bi-list-ul me-2"></i>

                সকল গাড়ির তালিকা

            </h5>


            <div class="d-flex gap-2">


                <!-- Search -->

                <input
                    type="text"
                    id="searchInput"
                    class="form-control form-control-sm search-box"
                    placeholder="গাড়ি, চালক, ফোন দিয়ে সার্চ..."
                >


                <!-- Print -->

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



        <!-- Table -->

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="carTable"
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

                        <th class="text-end">
                            প্রাথমিক জমা
                        </th>

                        <th class="text-end">
                            মাসিক
                        </th>

                        <th class="text-end">
                            মোট জমা
                        </th>

                        <th class="text-center">
                            মোট মাস
                        </th>

                        <th class="text-end">
                            বকেয়া
                        </th>

                        <th class="text-center">
                            স্ট্যাটাস
                        </th>

                        <th class="text-center">
                            অ্যাকশন
                        </th>

                    </tr>

                    </thead>



                    <tbody>


                    <?php if (empty($cars)): ?>

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="bi bi-car-front fs-1"
                                    ></i>

                                    <h5 class="mt-3">
                                        কোনো গাড়ি পাওয়া যায়নি
                                    </h5>


                                    <a
                                        href="index.php?page=metro/add"
                                        class="btn btn-success"
                                    >

                                        <i class="bi bi-plus-circle me-1"></i>

                                        নতুন গাড়ি যোগ করুন

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach ($cars as $i => $car): ?>


                        <?php

                        $totalPaid =
                            (float)$car['total_paid'];

                        $initialDeposit =
                            (float)$car['initial_deposit'];

                        $monthlyAmount =
                            (float)$car['monthly_amount'];


                        /*
                        |--------------------------------------------------------------------------
                        | মাসিক জমা
                        |--------------------------------------------------------------------------
                        */

                        $monthlyPaid = max(
                            0,
                            $totalPaid - $initialDeposit
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | মোট মাস
                        |--------------------------------------------------------------------------
                        */

                        $paidMonths =
                            $monthlyAmount > 0
                            ? floor(
                                $monthlyPaid /
                                $monthlyAmount
                            )
                            : 0;


                        /*
                        |--------------------------------------------------------------------------
                        | Current Month Paid
                        |--------------------------------------------------------------------------
                        */

                        $stmtMonth = $pdo->prepare("
                            SELECT COALESCE(SUM(amount), 0)
                            FROM metro_payments
                            WHERE metro_car_id = ?
                            AND month_year = ?
                            AND payment_type = 'monthly'
                        ");

                        $stmtMonth->execute([
                            $car['id'],
                            $currentMonth
                        ]);

                        $currentPaid =
                            (float)$stmtMonth->fetchColumn();


                        /*
                        |--------------------------------------------------------------------------
                        | Due
                        |--------------------------------------------------------------------------
                        */

                        $due = max(
                            0,
                            $monthlyAmount - $currentPaid
                        );

                        ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <span class="badge bg-secondary">

                                    <?= bn_number($i + 1) ?>

                                </span>

                            </td>



                            <!-- Car Number -->

                            <td>

                                <a
                                    href="index.php?page=metro/view&id=<?= (int)$car['id'] ?>"
                                    class="text-decoration-none fw-bold text-primary"
                                >

                                    <?= htmlspecialchars(
                                        $car['car_number']
                                    ) ?>

                                </a>

                            </td>



                            <!-- Driver -->

                            <td>

                                <?php if (
                                    !empty($car['driver_name'])
                                ): ?>

                                    <?= htmlspecialchars(
                                        $car['driver_name']
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

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

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Initial -->

                            <td class="text-end fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $initialDeposit,
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Monthly -->

                            <td class="text-end fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $monthlyAmount,
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Total Paid -->

                            <td class="text-end">

                                <span class="fw-bold text-success">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $totalPaid,
                                            0
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- Months -->

                            <td class="text-center">

                                <span class="badge bg-info text-dark">

                                    <?= bn_number(
                                        $paidMonths
                                    ) ?>

                                    মাস

                                </span>

                            </td>



                            <!-- Due -->

                            <td class="text-end">

                                <?php if ($due > 0): ?>

                                    <span class="badge bg-danger fs-6">

                                        ৳ <?= bn_number(
                                            number_format(
                                                $due,
                                                0
                                            )
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-check-circle me-1"></i>

                                        পরিশোধিত

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Status -->

                            <td class="text-center">

                                <?php if (
                                    $car['status'] === 'active'
                                ): ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-check-circle me-1"></i>

                                        চলমান

                                    </span>

                                <?php elseif (
                                    $car['status'] === 'completed'
                                ): ?>

                                    <span class="badge bg-primary">

                                        সম্পন্ন

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        বন্ধ

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
                                        title="মাসিক জমা"
                                    >

                                        <i class="bi bi-cash-stack"></i>

                                        জমা

                                    </a>


                                    <a
                                        href="index.php?page=metro/view&id=<?= (int)$car['id'] ?>"
                                        class="btn btn-primary"
                                        title="বিস্তারিত"
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
     LIVE SEARCH
========================================================== -->

<script>

document
    .getElementById('searchInput')
    .addEventListener('keyup', function () {

        let searchText =
            this.value
                .toLowerCase()
                .trim();


        let rows =
            document.querySelectorAll(
                '#carTable tbody tr'
            );


        rows.forEach(function (row) {

            let text =
                row.textContent
                    .toLowerCase();


            row.style.display =
                text.includes(searchText)
                    ? ''
                    : 'none';

        });

    });

</script>



<style>

/*
|--------------------------------------------------------------------------
| Search Box
|--------------------------------------------------------------------------
*/

.search-box {
    width: 300px;
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

#carTable th {
    white-space: nowrap;
}

#carTable td {
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| Hover
|--------------------------------------------------------------------------
*/

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04);
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .card-header {
        flex-direction: column;
        align-items: stretch !important;
        gap: 12px;
    }

    .search-box {
        width: 100%;
    }

}


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

@media print {

    body {
        background: #fff !important;
    }

    .btn,
    .search-box {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .card-header {
        background: #fff !important;
        color: #000 !important;
    }

}

</style>