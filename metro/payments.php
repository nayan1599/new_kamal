<?php

/*
|--------------------------------------------------------------------------
| Metro Payments
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        mp.*,
        mc.car_number,
        mc.driver_name,
        mc.mobile

    FROM metro_payments mp

    LEFT JOIN metro_cars mc
        ON mc.id = mp.metro_car_id

    ORDER BY mp.payment_date DESC, mp.id DESC
");

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalPayment = 0;
$totalInitial = 0;
$totalMonthly = 0;

foreach ($payments as $payment) {

    $amount = (float)$payment['amount'];

    $totalPayment += $amount;

    if ($payment['payment_type'] === 'initial') {

        $totalInitial += $amount;

    } elseif ($payment['payment_type'] === 'monthly') {

        $totalMonthly += $amount;
    }
}

?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-cash-stack text-success me-2"></i>

                মেট্রো পেমেন্ট

            </h1>

            <p class="text-muted mb-0">

                সকল মেট্রো গাড়ির জমা ও পেমেন্টের হিসাব

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


        <!-- মোট পেমেন্ট -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-success bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-cash-stack text-success fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট জমা

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

        </div>



        <!-- প্রাথমিক -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-wallet2 text-primary fs-4"
                            ></i>

                        </div>


                        <div>

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

            </div>

        </div>



        <!-- মাসিক -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-info bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-calendar-check text-info fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মাসিক জমা

                            </div>

                            <div class="fs-4 fw-bold text-info">

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

            </div>

        </div>

    </div>



    <!-- =========================================================
         PAYMENT TABLE
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <!-- Header -->

        <div
            class="card-header bg-primary text-white py-3"
        >

            <div
                class="d-flex justify-content-between align-items-center"
            >

                <h5 class="mb-0">

                    <i class="bi bi-list-ul me-2"></i>

                    সকল পেমেন্ট রেকর্ড

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
                    id="paymentTable"
                >

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
                            মোবাইল
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

                        <th class="text-center">
                            অ্যাকশন
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($payments)): ?>

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-cash-stack fs-1 text-muted"
                                ></i>

                                <h5 class="text-muted mt-3">

                                    কোনো পেমেন্ট পাওয়া যায়নি

                                </h5>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach ($payments as $i => $row): ?>


                        <?php

                        $type =
                            $row['payment_type'] ?? '';


                        if ($type === 'initial') {

                            $typeText =
                                'প্রাথমিক জমা';

                            $typeClass =
                                'primary';

                        } elseif ($type === 'monthly') {

                            $typeText =
                                'মাসিক জমা';

                            $typeClass =
                                'success';

                        } else {

                            $typeText =
                                $type ?: 'অন্যান্য';

                            $typeClass =
                                'secondary';
                        }

                        ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <?= bn_number($i + 1) ?>

                            </td>



                            <!-- Date -->

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



                            <!-- Car -->

                            <td>

                                <a
                                    href="index.php?page=metro/view&id=<?= (int)$row['metro_car_id'] ?>"
                                    class="fw-bold text-primary text-decoration-none"
                                >

                                    <?= htmlspecialchars(
                                        $row['car_number'] ?? '—'
                                    ) ?>

                                </a>

                            </td>



                            <!-- Driver -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['driver_name'] ?? '—'
                                ) ?>

                            </td>



                            <!-- Mobile -->

                            <td>

                                <?php if (
                                    !empty($row['mobile'])
                                ): ?>

                                    <a
                                        href="tel:<?= htmlspecialchars($row['mobile']) ?>"
                                        class="text-decoration-none"
                                    >

                                        <?= htmlspecialchars(
                                            $row['mobile']
                                        ) ?>

                                    </a>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>



                            <!-- Type -->

                            <td>

                                <span
                                    class="badge bg-<?= $typeClass ?>"
                                >

                                    <?= $typeText ?>

                                </span>

                            </td>



                            <!-- Month -->

                            <td>

                                <?php if (
                                    !empty($row['month_year'])
                                ): ?>

                                    <?= bn_number(
                                        date(
                                            'm/Y',
                                            strtotime(
                                                $row['month_year'] . '-01'
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>



                            <!-- Amount -->

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



                            <!-- Note -->

                            <td>

                                <span
                                    class="text-muted small"
                                >

                                    <?= htmlspecialchars(
                                        $row['note'] ?? '—'
                                    ) ?>

                                </span>

                            </td>



                            <!-- Action -->

                            <td class="text-center">

                                <a
                                    href="index.php?page=metro/view&id=<?= (int)$row['metro_car_id'] ?>"
                                    class="btn btn-sm btn-primary"
                                >

                                    <i class="bi bi-eye"></i>

                                    View

                                </a>

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
                '#paymentTable tbody tr'
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

#paymentTable th {
    white-space: nowrap;
}

#paymentTable td {
    white-space: nowrap;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04);
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