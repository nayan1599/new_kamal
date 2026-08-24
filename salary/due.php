<?php

/*
|--------------------------------------------------------------------------
| Salary Due List
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');


/*
|--------------------------------------------------------------------------
| Due Salary Query
|--------------------------------------------------------------------------
|
| যেসব payment-এর due_amount > 0
|
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

    WHERE sp.due_amount > 0

    ORDER BY
        sp.salary_month ASC,
        sp.due_amount DESC
");

$duePayments = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalDueEmployees = count($duePayments);

$totalSalary = 0;
$totalPaid   = 0;
$totalDue    = 0;

foreach ($duePayments as $row) {

    $totalSalary += (float)$row['total_salary'];

    $totalPaid += (float)$row['paid_amount'];

    $totalDue += (float)$row['due_amount'];

}


/*
|--------------------------------------------------------------------------
| Bengali Number Helper
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

?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-exclamation-circle-fill text-danger me-2"></i>

                বেতন বকেয়া

            </h1>

            <p class="text-muted mb-0">

                যেসব কর্মচারীর বেতন সম্পূর্ণ পরিশোধ করা হয়নি

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


        <!-- Due Employees -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-people-fill text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                বকেয়া কর্মচারী

                            </div>

                            <div class="fs-4 fw-bold text-danger">

                                <?= bn_number(
                                    $totalDueEmployees
                                ) ?>

                                <span class="fs-6 text-muted">
                                    জন
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
                            class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-wallet2 text-primary fs-4"
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
                            class="rounded-circle bg-warning bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-exclamation-triangle-fill text-warning fs-4"
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
         DUE TABLE
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div
            class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center"
        >

            <h5 class="mb-0">

                <i class="bi bi-list-ul me-2"></i>

                বেতন বকেয়ার তালিকা

            </h5>


            <div>

                <span class="badge bg-light text-danger">

                    <?= bn_number(
                        $totalDueEmployees
                    ) ?>

                    টি বকেয়া

                </span>

            </div>

        </div>



        <div class="card-body p-0">


            <!-- Search -->

            <div class="p-3 border-bottom">

                <div class="row g-2">

                    <div class="col-md-5">

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-search"></i>

                            </span>

                            <input
                                type="text"
                                id="dueSearch"
                                class="form-control"
                                placeholder="নাম, মোবাইল, গ্যারেজ দিয়ে খুঁজুন..."
                            >

                        </div>

                    </div>


                    <div class="col-md-3">

                        <select
                            id="garageFilter"
                            class="form-select"
                        >

                            <option value="">
                                সকল গ্যারেজ
                            </option>


                            <?php

                            $garageNames = [];

                            foreach ($duePayments as $row) {

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

                </div>

            </div>



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
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($duePayments)): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="text-center py-5"
                            >

                                <div class="text-success">

                                    <i
                                        class="bi bi-check-circle-fill fs-1"
                                    ></i>

                                    <h5 class="mt-3">

                                        কোনো বেতন বকেয়া নেই

                                    </h5>

                                    <p class="text-muted mb-0">

                                        সকল কর্মচারীর বেতন সম্পূর্ণ পরিশোধ করা হয়েছে।

                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach (
                        $duePayments
                        as $index => $row
                    ): ?>


                        <?php

                        $total =
                            (float)$row['total_salary'];

                        $paid =
                            (float)$row['paid_amount'];

                        $due =
                            (float)$row['due_amount'];


                        /*
                        |--------------------------------------------------------------------------
                        | Month
                        |--------------------------------------------------------------------------
                        */

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

                        ?>


                        <tr
                            data-garage="<?= htmlspecialchars(
                                $row['garage_name'] ?? ''
                            ) ?>"
                        >


                            <!-- Serial -->

                            <td class="text-center">

                                <?= bn_number(
                                    $index + 1
                                ) ?>

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

                                <?= !empty(
                                    $row['designation']
                                )
                                    ? htmlspecialchars(
                                        $row['designation']
                                    )
                                    : '<span class="text-muted">—</span>'
                                ?>

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



                            <!-- Month -->

                            <td>

                                <span
                                    class="badge bg-light text-dark border"
                                >

                                    <?= htmlspecialchars(
                                        $monthText
                                    ) ?>

                                </span>

                            </td>



                            <!-- Total -->

                            <td class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        $total,
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Paid -->

                            <td class="text-end text-success fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $paid,
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Due -->

                            <td class="text-end">

                                <span
                                    class="badge bg-danger fs-6"
                                >

                                    ৳ <?= bn_number(
                                        number_format(
                                            $due,
                                            0
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- Action -->

                            <td class="text-center">

                                <div
                                    class="btn-group btn-group-sm"
                                >


                                    <!-- Pay -->

                                    <a
                                        href="index.php?page=salary/payment&employee_id=<?= (int)$row['employee_id'] ?>"
                                        class="btn btn-success"
                                        title="বেতন প্রদান"
                                    >

                                        <i
                                            class="bi bi-cash-stack"
                                        ></i>

                                        প্রদান

                                    </a>


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

                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                    <?php if (!empty($duePayments)): ?>

                        <tfoot class="table-light">

                        <tr>

                            <th colspan="5" class="text-end">

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


                            <th class="text-end text-success">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalPaid,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th class="text-end text-danger">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDue,
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



<script>

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const dueSearch =
    document.getElementById('dueSearch');

const garageFilter =
    document.getElementById('garageFilter');


function filterDueTable() {

    const search =
        dueSearch.value
            .toLowerCase()
            .trim();


    const garage =
        garageFilter.value
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


        const rowGarage =
            (
                row.dataset.garage || ''
            ).toLowerCase();


        const searchMatch =
            text.includes(search);


        const garageMatch =
            !garage ||
            rowGarage === garage;


        row.style.display =
            searchMatch && garageMatch
                ? ''
                : 'none';

    });

}


dueSearch.addEventListener(
    'input',
    filterDueTable
);


garageFilter.addEventListener(
    'change',
    filterDueTable
);

</script>



<style>

/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

#dueTable th {

    white-space: nowrap;

    font-size: 13px;

}


#dueTable td {

    white-space: nowrap;

}


#dueTable tbody tr:hover {

    background-color:
        rgba(220, 53, 69, .04);

}


/*
|--------------------------------------------------------------------------
| Badge
|--------------------------------------------------------------------------
*/

#dueTable .badge {

    font-weight: 600;

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

    .btn,
    #dueSearch,
    #garageFilter,
    .input-group {

        display: none !important;

    }


    .card {

        border: none !important;

        box-shadow: none !important;

    }


    .card-header {

        background: white !important;

        color: black !important;

    }


    #dueTable th:last-child,
    #dueTable td:last-child {

        display: none;

    }

}

</style>