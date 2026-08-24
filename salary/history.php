<?php

/*
|--------------------------------------------------------------------------
| Salary History
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        sp.*,

        e.employee_name,
        e.mobile,
        e.designation,

        g.garage_name

    FROM salary_payments sp

    INNER JOIN employees e
        ON e.id = sp.employee_id

    LEFT JOIN garages g
        ON g.id = sp.garage_id

    ORDER BY
        sp.payment_date DESC,
        sp.id DESC
");

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalPayments = count($payments);

$totalSalary = 0;
$totalPaid   = 0;
$totalDue    = 0;

foreach ($payments as $row) {

    $totalSalary += (float)$row['total_salary'];

    $totalPaid += (float)$row['paid_amount'];

    $totalDue += (float)$row['due_amount'];

}


/*
|--------------------------------------------------------------------------
| Bengali Number
|--------------------------------------------------------------------------
*/

if (!function_exists('bn_number')) {

    function bn_number($number)
    {
        return strtr(
            (string)$number,
            [
                '0' => '০',
                '1' => '১',
                '2' => '২',
                '3' => '৩',
                '4' => '৪',
                '5' => '৫',
                '6' => '৬',
                '7' => '৭',
                '8' => '৮',
                '9' => '৯'
            ]
        );
    }

}


/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

function payment_method_bn($method)
{

    switch ($method) {

        case 'cash':
            return 'নগদ';

        case 'bank':
            return 'ব্যাংক';

        case 'mobile_banking':
            return 'মোবাইল ব্যাংকিং';

        default:
            return $method ?: '—';

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

                <i class="bi bi-clock-history text-primary me-2"></i>

                বেতন হিস্টোরি

            </h1>

            <p class="text-muted mb-0">

                সকল কর্মচারীর বেতন প্রদানের পূর্বের হিসাব

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=salary/payment"
                class="btn btn-success"
            >

                <i class="bi bi-cash-stack me-1"></i>

                বেতন প্রদান

            </a>


            <button
                type="button"
                class="btn btn-outline-primary"
                onclick="window.print()"
            >

                <i class="bi bi-printer me-1"></i>

                প্রিন্ট

            </button>

        </div>

    </div>



    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Total Entries -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-receipt-cutoff text-primary fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট বেতন এন্ট্রি

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= bn_number(
                                    $totalPayments
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



        <!-- Total Salary -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-info bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-wallet2 text-info fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট বেতন

                            </div>

                            <div class="fs-5 fw-bold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalSalary,
                                        0
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- Total Paid -->

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

                                মোট পরিশোধ

                            </div>

                            <div class="fs-5 fw-bold text-success">

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

            </div>

        </div>



        <!-- Total Due -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-exclamation-circle-fill text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট বকেয়া

                            </div>

                            <div class="fs-5 fw-bold text-danger">

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
         HISTORY TABLE
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <!-- Header -->

        <div
            class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center"
        >

            <h5 class="mb-0">

                <i class="bi bi-list-ul me-2"></i>

                বেতন প্রদানের ইতিহাস

            </h5>


            <span class="badge bg-light text-primary">

                <?= bn_number($totalPayments) ?>

                টি এন্ট্রি

            </span>

        </div>



        <!-- Search / Filter -->

        <div class="card-body border-bottom">


            <div class="row g-2">


                <!-- Search -->

                <div class="col-lg-4">

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            id="historySearch"
                            class="form-control"
                            placeholder="নাম, মোবাইল, পদ দিয়ে খুঁজুন..."
                        >

                    </div>

                </div>



                <!-- Garage -->

                <div class="col-lg-3">

                    <select
                        id="garageFilter"
                        class="form-select"
                    >

                        <option value="">
                            সকল গ্যারেজ
                        </option>


                        <?php

                        $garageNames = [];

                        foreach ($payments as $row) {

                            if (
                                !empty(
                                    $row['garage_name']
                                )
                            ) {

                                $garageNames[
                                    $row['garage_name']
                                ] = true;

                            }

                        }

                        ksort($garageNames);

                        ?>


                        <?php foreach (
                            array_keys($garageNames)
                            as $garageName
                        ): ?>

                            <option
                                value="<?= htmlspecialchars(
                                    $garageName
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    $garageName
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>



                <!-- Payment Method -->

                <div class="col-lg-3">

                    <select
                        id="methodFilter"
                        class="form-select"
                    >

                        <option value="">
                            সকল পেমেন্ট মাধ্যম
                        </option>

                        <option value="cash">
                            নগদ
                        </option>

                        <option value="bank">
                            ব্যাংক
                        </option>

                        <option value="mobile_banking">
                            মোবাইল ব্যাংকিং
                        </option>

                    </select>

                </div>



                <!-- Reset -->

                <div class="col-lg-2">

                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="resetFilter"
                    >

                        <i
                            class="bi bi-arrow-counterclockwise me-1"
                        ></i>

                        Reset

                    </button>

                </div>

            </div>

        </div>



        <!-- Table -->

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="historyTable"
                >

                    <thead class="table-light">

                    <tr>

                        <th class="text-center">
                            #
                        </th>

                        <th>
                            কর্মচারী
                        </th>

                        <th>
                            পদ
                        </th>

                        <th>
                            গ্যারেজ
                        </th>

                        <th>
                            বেতনের মাস
                        </th>

                        <th>
                            পেমেন্ট তারিখ
                        </th>

                        <th class="text-end">
                            মোট বেতন
                        </th>

                        <th class="text-end">
                            প্রদান
                        </th>

                        <th class="text-end">
                            বকেয়া
                        </th>

                        <th class="text-center">
                            মাধ্যম
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                    </thead>



                    <tbody>


                    <?php if (empty($payments)): ?>

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="bi bi-clock-history fs-1"
                                    ></i>

                                    <h5 class="mt-3">

                                        কোনো বেতন হিস্টোরি পাওয়া যায়নি

                                    </h5>

                                    <p class="mb-3">

                                        এখনো কোনো বেতন প্রদান করা হয়নি।

                                    </p>


                                    <a
                                        href="index.php?page=salary/payment"
                                        class="btn btn-success"
                                    >

                                        <i
                                            class="bi bi-cash-stack me-1"
                                        ></i>

                                        বেতন প্রদান করুন

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach (
                        $payments
                        as $index => $row
                    ): ?>


                        <?php

                        $total =
                            (float)$row['total_salary'];

                        $paid =
                            (float)$row['paid_amount'];

                        $due =
                            (float)$row['due_amount'];


                        $monthText = '—';

                        if (
                            !empty(
                                $row['salary_month']
                            )
                        ) {

                            $monthText =
                                date(
                                    'F Y',
                                    strtotime(
                                        $row['salary_month']
                                    )
                                );

                        }


                        $paymentDate = '—';

                        if (
                            !empty(
                                $row['payment_date']
                            )
                        ) {

                            $paymentDate =
                                date(
                                    'd-m-Y',
                                    strtotime(
                                        $row['payment_date']
                                    )
                                );

                        }


                        $method =
                            $row['payment_method']
                            ?? '';

                        ?>


                        <tr
                            data-garage="<?= htmlspecialchars(
                                $row['garage_name'] ?? ''
                            ) ?>"
                            data-method="<?= htmlspecialchars(
                                $method
                            ) ?>"
                        >


                            <!-- Serial -->

                            <td class="text-center">

                                <span class="badge bg-secondary">

                                    <?= bn_number(
                                        $index + 1
                                    ) ?>

                                </span>

                            </td>



                            <!-- Employee -->

                            <td>

                                <div class="fw-bold">

                                    <i
                                        class="bi bi-person-circle text-primary me-1"
                                    ></i>

                                    <?= htmlspecialchars(
                                        $row['employee_name']
                                    ) ?>

                                </div>


                                <?php if (
                                    !empty(
                                        $row['mobile']
                                    )
                                ): ?>

                                    <small class="text-muted">

                                        <i
                                            class="bi bi-telephone me-1"
                                        ></i>

                                        <?= htmlspecialchars(
                                            $row['mobile']
                                        ) ?>

                                    </small>

                                <?php endif; ?>

                            </td>



                            <!-- Designation -->

                            <td>

                                <?php if (
                                    !empty(
                                        $row['designation']
                                    )
                                ): ?>

                                    <?= htmlspecialchars(
                                        $row['designation']
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Garage -->

                            <td>

                                <?php if (
                                    !empty(
                                        $row['garage_name']
                                    )
                                ): ?>

                                    <span
                                        class="badge bg-info text-dark"
                                    >

                                        <?= htmlspecialchars(
                                            $row['garage_name']
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Salary Month -->

                            <td>

                                <span
                                    class="badge bg-light text-dark border"
                                >

                                    <?= htmlspecialchars(
                                        $monthText
                                    ) ?>

                                </span>

                            </td>



                            <!-- Payment Date -->

                            <td>

                                <i
                                    class="bi bi-calendar3 text-muted me-1"
                                ></i>

                                <?= bn_number(
                                    $paymentDate
                                ) ?>

                            </td>



                            <!-- Total Salary -->

                            <td class="text-end fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $total,
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Paid -->

                            <td class="text-end">

                                <span class="text-success fw-bold">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $paid,
                                            0
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- Due -->

                            <td class="text-end">

                                <?php if ($due > 0): ?>

                                    <span
                                        class="badge bg-danger"
                                    >

                                        ৳ <?= bn_number(
                                            number_format(
                                                $due,
                                                0
                                            )
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="badge bg-success"
                                    >

                                        সম্পূর্ণ

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Method -->

                            <td class="text-center">

                                <?php if ($method === 'cash'): ?>

                                    <span
                                        class="badge bg-success-subtle text-success"
                                    >

                                        <i
                                            class="bi bi-cash me-1"
                                        ></i>

                                        নগদ

                                    </span>


                                <?php elseif ($method === 'bank'): ?>

                                    <span
                                        class="badge bg-primary-subtle text-primary"
                                    >

                                        <i
                                            class="bi bi-bank me-1"
                                        ></i>

                                        ব্যাংক

                                    </span>


                                <?php elseif (
                                    $method === 'mobile_banking'
                                ): ?>

                                    <span
                                        class="badge bg-warning-subtle text-dark"
                                    >

                                        <i
                                            class="bi bi-phone me-1"
                                        ></i>

                                        মোবাইল ব্যাংকিং

                                    </span>


                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Action -->

                            <td class="text-center">

                                <div
                                    class="btn-group btn-group-sm"
                                >


                                    <!-- View -->

                                    <a
                                        href="index.php?page=salary/view&id=<?= (int)$row['id'] ?>"
                                        class="btn btn-primary"
                                        title="বিস্তারিত"
                                    >

                                        <i
                                            class="bi bi-eye"
                                        ></i>

                                    </a>


                                    <!-- Receipt -->

                                    <a
                                        href="index.php?page=salary/receipt&id=<?= (int)$row['id'] ?>"
                                        class="btn btn-success"
                                        title="রসিদ"
                                    >

                                        <i
                                            class="bi bi-receipt"
                                        ></i>

                                    </a>


                                </div>

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

                                সর্বমোট

                            </th>


                            <th class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalSalary,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th
                                class="text-end text-success"
                            >

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalPaid,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th
                                class="text-end text-danger"
                            >

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDue,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th colspan="2"></th>

                        </tr>

                        </tfoot>

                    <?php endif; ?>


                </table>

            </div>

        </div>

    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const historySearch =
    document.getElementById(
        'historySearch'
    );

const garageFilter =
    document.getElementById(
        'garageFilter'
    );

const methodFilter =
    document.getElementById(
        'methodFilter'
    );

const resetFilter =
    document.getElementById(
        'resetFilter'
    );


function filterHistory() {

    const search =
        historySearch.value
            .toLowerCase()
            .trim();


    const garage =
        garageFilter.value
            .toLowerCase()
            .trim();


    const method =
        methodFilter.value
            .toLowerCase()
            .trim();


    const rows =
        document.querySelectorAll(
            '#historyTable tbody tr'
        );


    rows.forEach(function (row) {

        const text =
            row.textContent
                .toLowerCase();


        const rowGarage =
            (
                row.dataset.garage || ''
            ).toLowerCase();


        const rowMethod =
            (
                row.dataset.method || ''
            ).toLowerCase();


        const searchMatch =
            !search ||
            text.includes(search);


        const garageMatch =
            !garage ||
            rowGarage === garage;


        const methodMatch =
            !method ||
            rowMethod === method;


        row.style.display =
            searchMatch
            && garageMatch
            && methodMatch
                ? ''
                : 'none';

    });

}


/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

historySearch.addEventListener(
    'input',
    filterHistory
);


garageFilter.addEventListener(
    'change',
    filterHistory
);


methodFilter.addEventListener(
    'change',
    filterHistory
);


/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

resetFilter.addEventListener(
    'click',
    function () {

        historySearch.value = '';

        garageFilter.value = '';

        methodFilter.value = '';

        filterHistory();

    }
);

</script>



<style>

/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

#historyTable th {

    white-space: nowrap;

    font-size: 13px;

}


#historyTable td {

    white-space: nowrap;

}


#historyTable tbody tr:hover {

    background-color:
        rgba(13, 110, 253, .04);

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

.card {

    border-radius: 10px;

}


.card-header {

    border-radius:
        10px 10px 0 0 !important;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .container-fluid {

        padding-left: 10px !important;

        padding-right: 10px !important;

    }

    .card-header {

        flex-direction: column;

        align-items: flex-start !important;

        gap: 10px;

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
    #historySearch,
    #garageFilter,
    #methodFilter,
    .input-group {

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


    #historyTable th:last-child,
    #historyTable td:last-child {

        display: none;

    }

}

</style>