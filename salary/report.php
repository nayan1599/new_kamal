<?php

/*
|--------------------------------------------------------------------------
| Salary Report
|--------------------------------------------------------------------------
*/

$selectedMonth = $_GET['month'] ?? date('Y-m');
$selectedGarage = (int)($_GET['garage_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Garage List
|--------------------------------------------------------------------------
*/

$garageStmt = $pdo->query("
    SELECT id, garage_name
    FROM garages
    ORDER BY garage_name ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];


/*
|--------------------------------------------------------------------------
| Month Filter
|--------------------------------------------------------------------------
*/

if ($selectedMonth !== '') {

    $where[] = "DATE_FORMAT(sp.salary_month, '%Y-%m') = :salary_month";

    $params[':salary_month'] = $selectedMonth;

}


/*
|--------------------------------------------------------------------------
| Garage Filter
|--------------------------------------------------------------------------
*/

if ($selectedGarage > 0) {

    $where[] = "sp.garage_id = :garage_id";

    $params[':garage_id'] = $selectedGarage;

}


$whereSql = '';

if (!empty($where)) {

    $whereSql =
        'WHERE ' . implode(
            ' AND ',
            $where
        );

}


/*
|--------------------------------------------------------------------------
| Main Report
|--------------------------------------------------------------------------
*/

$sql = "
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

    $whereSql

    ORDER BY
        sp.payment_date DESC,
        e.employee_name ASC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalEmployee = [];

$totalBasic     = 0;
$totalBonus     = 0;
$totalOvertime  = 0;
$totalDeduction = 0;
$totalSalary    = 0;
$totalPaid      = 0;
$totalDue       = 0;
$totalAdvance   = 0;


foreach ($reports as $row) {

    $totalEmployee[
        $row['employee_id']
    ] = true;


    $totalBasic +=
        (float)$row['basic_salary'];


    $totalBonus +=
        (float)$row['bonus'];


    $totalOvertime +=
        (float)$row['overtime'];


    $totalDeduction +=
        (float)$row['deduction'];


    $totalSalary +=
        (float)$row['total_salary'];


    $totalPaid +=
        (float)$row['paid_amount'];


    $totalDue +=
        (float)$row['due_amount'];


    $totalAdvance +=
        (float)$row['advance'];

}


$employeeCount =
    count($totalEmployee);


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
| Month Name
|--------------------------------------------------------------------------
*/

$monthTitle = date(
    'F Y',
    strtotime(
        $selectedMonth . '-01'
    )
);

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i
                    class="bi bi-bar-chart-line-fill text-primary me-2"
                ></i>

                বেতন রিপোর্ট

            </h1>

            <p class="text-muted mb-0">

                কর্মচারীদের মাসিক বেতন, প্রদান ও বকেয়ার রিপোর্ট

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=salary/payment"
                class="btn btn-success"
            >

                <i
                    class="bi bi-cash-stack me-1"
                ></i>

                বেতন প্রদান

            </a>


            <button
                type="button"
                class="btn btn-outline-primary"
                onclick="window.print()"
            >

                <i
                    class="bi bi-printer me-1"
                ></i>

                প্রিন্ট

            </button>

        </div>

    </div>



    <!-- =========================================================
         FILTER
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="index.php"
                class="row g-3 align-items-end"
            >

                <input
                    type="hidden"
                    name="page"
                    value="salary/report"
                >


                <!-- Month -->

                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        বেতনের মাস

                    </label>

                    <input
                        type="month"
                        name="month"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $selectedMonth
                        ) ?>"
                    >

                </div>



                <!-- Garage -->

                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        গ্যারেজ

                    </label>

                    <select
                        name="garage_id"
                        class="form-select"
                    >

                        <option value="0">

                            সকল গ্যারেজ

                        </option>


                        <?php foreach (
                            $garages
                            as $garage
                        ): ?>

                            <option
                                value="<?= (int)$garage['id'] ?>"
                                <?= $selectedGarage ==
                                    $garage['id']
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $garage['garage_name']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>



                <!-- Search -->

                <div class="col-md-4">

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                        >

                            <i
                                class="bi bi-search me-1"
                            ></i>

                            রিপোর্ট দেখুন

                        </button>


                        <a
                            href="index.php?page=salary/report"
                            class="btn btn-outline-secondary"
                            title="Reset"
                        >

                            <i
                                class="bi bi-arrow-counterclockwise"
                            ></i>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>



    <!-- =========================================================
         REPORT TITLE
    ========================================================== -->

    <div class="report-title mb-3">

        <div>

            <h5 class="mb-1">

                বেতন রিপোর্ট —

                <?= htmlspecialchars(
                    $monthTitle
                ) ?>

            </h5>

            <small class="text-muted">

                <?php if ($selectedGarage > 0): ?>

                    <?php

                    $selectedGarageName = '';


                    foreach (
                        $garages
                        as $garage
                    ) {

                        if (
                            (int)$garage['id']
                            === $selectedGarage
                        ) {

                            $selectedGarageName =
                                $garage['garage_name'];

                            break;

                        }

                    }

                    ?>

                    গ্যারেজ:
                    <strong>

                        <?= htmlspecialchars(
                            $selectedGarageName
                        ) ?>

                    </strong>

                <?php else: ?>

                    সকল গ্যারেজ

                <?php endif; ?>

            </small>

        </div>

    </div>



    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Employee -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="summary-icon bg-primary-subtle">

                            <i
                                class="bi bi-people-fill text-primary"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                কর্মচারী

                            </div>

                            <div class="fs-4 fw-bold">

                                <?= bn_number(
                                    $employeeCount
                                ) ?>

                                <span class="fs-6">
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

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="summary-icon bg-info-subtle">

                            <i
                                class="bi bi-wallet2 text-info"
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



        <!-- Paid -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="summary-icon bg-success-subtle">

                            <i
                                class="bi bi-check-circle-fill text-success"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">

                                মোট প্রদান

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



        <!-- Due -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="summary-icon bg-danger-subtle">

                            <i
                                class="bi bi-exclamation-circle-fill text-danger"
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
         BREAKDOWN
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">


        <div class="card-header bg-dark text-white py-3">

            <h5 class="mb-0">

                <i
                    class="bi bi-calculator me-2"
                ></i>

                বেতন হিসাবের সারাংশ

            </h5>

        </div>


        <div class="card-body">

            <div class="row g-3">


                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box">

                        <span>
                            মূল বেতন
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalBasic,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>



                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box success">

                        <span>
                            Bonus
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalBonus,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>



                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box info">

                        <span>
                            Overtime
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalOvertime,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>



                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box danger">

                        <span>
                            Deduction
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalDeduction,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>



                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box warning">

                        <span>
                            Advance
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalAdvance,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>



                <div class="col-md-4 col-xl-2">

                    <div class="breakdown-box primary">

                        <span>
                            নেট বেতন
                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $totalSalary,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>


            </div>

        </div>

    </div>



    <!-- =========================================================
         EMPLOYEE REPORT
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div
            class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center"
        >

            <h5 class="mb-0">

                <i
                    class="bi bi-table me-2"
                ></i>

                কর্মচারীভিত্তিক বেতন রিপোর্ট

            </h5>


            <span class="badge bg-light text-primary">

                <?= bn_number(
                    count($reports)
                ) ?>

                টি রেকর্ড

            </span>

        </div>



        <!-- Search -->

        <div class="card-body border-bottom">

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-search"></i>

                </span>

                <input
                    type="text"
                    id="reportSearch"
                    class="form-control"
                    placeholder="কর্মচারীর নাম, মোবাইল, পদ বা গ্যারেজ দিয়ে খুঁজুন..."
                >

            </div>

        </div>



        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="reportTable"
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

                        <th class="text-end">
                            মূল বেতন
                        </th>

                        <th class="text-end">
                            Bonus
                        </th>

                        <th class="text-end">
                            Overtime
                        </th>

                        <th class="text-end">
                            Deduction
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


                    <?php if (empty($reports)): ?>

                        <tr>

                            <td
                                colspan="12"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="bi bi-bar-chart fs-1"
                                    ></i>

                                    <h5 class="mt-3">

                                        এই মাসে কোনো বেতন রিপোর্ট পাওয়া যায়নি

                                    </h5>

                                    <p class="mb-0">

                                        অন্য মাস অথবা গ্যারেজ নির্বাচন করে দেখুন।

                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach (
                        $reports
                        as $index => $row
                    ): ?>


                        <tr>


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
                                    : '—'
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



                            <!-- Basic -->

                            <td class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        $row['basic_salary'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Bonus -->

                            <td class="text-end text-success">

                                + ৳ <?= bn_number(
                                    number_format(
                                        $row['bonus'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Overtime -->

                            <td class="text-end text-info">

                                + ৳ <?= bn_number(
                                    number_format(
                                        $row['overtime'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Deduction -->

                            <td class="text-end text-danger">

                                - ৳ <?= bn_number(
                                    number_format(
                                        $row['deduction'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Total -->

                            <td class="text-end fw-bold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $row['total_salary'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Paid -->

                            <td class="text-end text-success fw-bold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $row['paid_amount'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Due -->

                            <td class="text-end">

                                <?php if (
                                    (float)$row['due_amount'] > 0
                                ): ?>

                                    <span
                                        class="badge bg-danger"
                                    >

                                        ৳ <?= bn_number(
                                            number_format(
                                                $row['due_amount'],
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



                            <!-- Action -->

                            <td class="text-center">

                                <div
                                    class="btn-group btn-group-sm"
                                >

                                    <a
                                        href="index.php?page=salary/view&id=<?= (int)$row['id'] ?>"
                                        class="btn btn-primary"
                                        title="বিস্তারিত"
                                    >

                                        <i
                                            class="bi bi-eye"
                                        ></i>

                                    </a>


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



                    <?php if (!empty($reports)): ?>

                        <tfoot class="table-light">

                        <tr>

                            <th
                                colspan="4"
                                class="text-end"
                            >

                                সর্বমোট

                            </th>


                            <th class="text-end">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalBasic,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th class="text-end text-success">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalBonus,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th class="text-end text-info">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalOvertime,
                                        0
                                    )
                                ) ?>

                            </th>


                            <th class="text-end text-danger">

                                ৳ <?= bn_number(
                                    number_format(
                                        $totalDeduction,
                                        0
                                    )
                                ) ?>

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

const reportSearch =
    document.getElementById(
        'reportSearch'
    );


reportSearch.addEventListener(
    'input',
    function () {

        const search =
            this.value
                .toLowerCase()
                .trim();


        const rows =
            document.querySelectorAll(
                '#reportTable tbody tr'
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

    }
);

</script>



<style>

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

.summary-card {

    border-radius: 10px;

}


.summary-icon {

    width: 50px;

    height: 50px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

    margin-right: 14px;

}


/*
|--------------------------------------------------------------------------
| Breakdown
|--------------------------------------------------------------------------
*/

.breakdown-box {

    border: 1px solid #e5e7eb;

    border-radius: 9px;

    padding: 14px;

    display: flex;

    flex-direction: column;

    gap: 5px;

    background: #fff;

}


.breakdown-box span {

    font-size: 12px;

    color: #6b7280;

}


.breakdown-box strong {

    font-size: 16px;

}


.breakdown-box.success strong {

    color: #198754;

}


.breakdown-box.info strong {

    color: #0dcaf0;

}


.breakdown-box.danger strong {

    color: #dc3545;

}


.breakdown-box.warning strong {

    color: #f59e0b;

}


.breakdown-box.primary strong {

    color: #0d6efd;

}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

#reportTable th {

    white-space: nowrap;

    font-size: 13px;

}


#reportTable td {

    white-space: nowrap;

}


#reportTable tbody tr:hover {

    background:
        rgba(13, 110, 253, .04);

}


/*
|--------------------------------------------------------------------------
| Report Title
|--------------------------------------------------------------------------
*/

.report-title {

    background: #f8f9fa;

    border-left: 4px solid #0d6efd;

    padding: 12px 16px;

    border-radius: 5px;

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
    #reportSearch,
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


    #reportTable th:last-child,
    #reportTable td:last-child {

        display: none;

    }

}

</style>