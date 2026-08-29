<?php

/*
|--------------------------------------------------------------------------
| Employee View
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            কর্মচারীর ID পাওয়া যায়নি।
        </div>
    ';

    return;
}


$employeeId = (int)$_GET['id'];


/*
|--------------------------------------------------------------------------
| Employee Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        e.*,
        g.garage_name
    FROM employees e
    LEFT JOIN garages g
        ON g.id = e.garage_id
    WHERE e.id = ?
    LIMIT 1
");

$stmt->execute([
    $employeeId
]);

$employee = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$employee) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-person-x me-2"></i>
            কর্মচারীর তথ্য পাওয়া যায়নি।
        </div>
    ';

    return;
}


/*
|--------------------------------------------------------------------------
| Salary History
|--------------------------------------------------------------------------
*/

$salaryStmt = $pdo->prepare("
    SELECT *
    FROM salary_payments
    WHERE employee_id = ?
    ORDER BY payment_date DESC, id DESC
");

$salaryStmt->execute([
    $employeeId
]);

$salaryHistory =
    $salaryStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Salary Summary
|--------------------------------------------------------------------------
*/

$totalSalary = 0;
$totalPaid   = 0;
$totalDue    = 0;

foreach ($salaryHistory as $salary) {

    $totalSalary +=
        (float)$salary['total_salary'];

    $totalPaid +=
        (float)$salary['paid_amount'];

    $totalDue +=
        (float)$salary['due_amount'];

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
| Joining Date
|--------------------------------------------------------------------------
*/

$joiningDate = '—';

if (!empty($employee['joining_date'])) {

    $joiningDate = date(
        'd-m-Y',
        strtotime(
            $employee['joining_date']
        )
    );

}

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

                <i
                    class="bi bi-person-vcard-fill text-primary me-2"
                ></i>

                কর্মচারীর বিস্তারিত

            </h1>

            <p class="text-muted mb-0">

                কর্মচারীর সম্পূর্ণ তথ্য ও বেতন হিস্টোরি

            </p>

        </div>


        <div class="d-flex gap-2">


            <a
                href="index.php?page=employee/index"
                class="btn btn-outline-secondary"
            >

                <i
                    class="bi bi-arrow-left me-1"
                ></i>

                তালিকায় ফিরে যান

            </a>


            <a
                href="index.php?page=employee/edit&id=<?= $employeeId ?>"
                class="btn btn-primary"
            >

                <i
                    class="bi bi-pencil-square me-1"
                ></i>

                Edit

            </a>


            <button
                type="button"
                class="btn btn-outline-dark"
                onclick="window.print()"
            >

                <i
                    class="bi bi-printer me-1"
                ></i>

                Print

            </button>

        </div>

    </div>



    <!-- =========================================================
         EMPLOYEE PROFILE
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">


        <div class="card-body p-4">


            <div class="row align-items-center">


                <!-- Avatar -->

                <div class="col-auto">

                    <div class="employee-avatar">

                        <i
                            class="bi bi-person-fill"
                        ></i>

                    </div>

                </div>



                <!-- Name -->

                <div class="col">

                    <h3 class="mb-1">

                        <?= htmlspecialchars(
                            $employee['employee_name']
                        ) ?>

                    </h3>


                    <div class="text-muted">

                        <?= !empty(
                            $employee['designation']
                        )
                            ? htmlspecialchars(
                                $employee['designation']
                            )
                            : 'কর্মচারী'
                        ?>

                    </div>


                    <div class="mt-2">

                        <span class="text-muted">

                            Employee ID:

                        </span>

                        <strong>

                            #<?= bn_number(
                                $employeeId
                            ) ?>

                        </strong>

                    </div>

                </div>



                <!-- Status -->

                <div class="col-auto">

                    <?php if (
                        ($employee['status']
                            ?? 'active')
                        === 'active'
                    ): ?>

                        <span
                            class="badge bg-success fs-6 px-3 py-2"
                        >

                            <i
                                class="bi bi-check-circle me-1"
                            ></i>

                            সক্রিয়

                        </span>

                    <?php else: ?>

                        <span
                            class="badge bg-secondary fs-6 px-3 py-2"
                        >

                            <i
                                class="bi bi-dash-circle me-1"
                            ></i>

                            নিষ্ক্রিয়

                        </span>

                    <?php endif; ?>

                </div>


            </div>

        </div>

    </div>



    <!-- =========================================================
         INFORMATION + SALARY
    ========================================================== -->

    <div class="row g-4 mb-4">


        <!-- =====================================================
             PERSONAL INFORMATION
        ====================================================== -->

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">


                <div class="card-header bg-primary text-white py-3">

                    <h5 class="mb-0">

                        <i
                            class="bi bi-person-lines-fill me-2"
                        ></i>

                        ব্যক্তিগত তথ্য

                    </h5>

                </div>


                <div class="card-body">


                    <!-- Mobile -->

                    <div class="info-row">

                        <div class="info-label">

                            <i
                                class="bi bi-telephone text-primary"
                            ></i>

                            মোবাইল

                        </div>


                        <div class="info-value">

                            <?php if (
                                !empty(
                                    $employee['mobile']
                                )
                            ): ?>

                                <a
                                    href="tel:<?= htmlspecialchars(
                                        $employee['mobile']
                                    ) ?>"
                                    class="text-decoration-none"
                                >

                                    <?= htmlspecialchars(
                                        $employee['mobile']
                                    ) ?>

                                </a>

                            <?php else: ?>

                                <span class="text-muted">
                                    দেওয়া নেই
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>



                    <!-- Designation -->

                    <div class="info-row">

                        <div class="info-label">

                            <i
                                class="bi bi-briefcase text-primary"
                            ></i>

                            পদ / দায়িত্ব

                        </div>


                        <div class="info-value">

                            <?= !empty(
                                $employee['designation']
                            )
                                ? htmlspecialchars(
                                    $employee['designation']
                                )
                                : '<span class="text-muted">দেওয়া নেই</span>'
                            ?>

                        </div>

                    </div>



                    <!-- Garage -->

                    <div class="info-row">

                        <div class="info-label">

                            <i
                                class="bi bi-building text-primary"
                            ></i>

                            গ্যারেজ

                        </div>


                        <div class="info-value">

                            <?php if (
                                !empty(
                                    $employee['garage_name']
                                )
                            ): ?>

                                <span
                                    class="badge bg-info text-dark"
                                >

                                    <?= htmlspecialchars(
                                        $employee['garage_name']
                                    ) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    দেওয়া নেই
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>



                    <!-- Joining Date -->

                    <div class="info-row">

                        <div class="info-label">

                            <i
                                class="bi bi-calendar-check text-primary"
                            ></i>

                            যোগদানের তারিখ

                        </div>


                        <div class="info-value">

                            <?= bn_number(
                                $joiningDate
                            ) ?>

                        </div>

                    </div>



                    <!-- Address -->

                    <div class="info-row align-items-start">

                        <div class="info-label">

                            <i
                                class="bi bi-geo-alt text-primary"
                            ></i>

                            ঠিকানা

                        </div>


                        <div class="info-value">

                            <?php if (
                                !empty(
                                    $employee['address']
                                )
                            ): ?>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $employee['address']
                                    )
                                ) ?>

                            <?php else: ?>

                                <span class="text-muted">

                                    ঠিকানা দেওয়া নেই

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>



        <!-- =====================================================
             SALARY INFORMATION
        ====================================================== -->

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">


                <div class="card-header bg-success text-white py-3">

                    <h5 class="mb-0">

                        <i
                            class="bi bi-cash-stack me-2"
                        ></i>

                        বেতন তথ্য

                    </h5>

                </div>


                <div class="card-body">


                    <!-- Basic Salary -->

                    <div class="salary-main">

                        <div class="text-muted">

                            মাসিক বেতন

                        </div>


                        <div class="salary-amount">

                            ৳ <?= bn_number(
                                number_format(
                                    (float)(
                                        $employee['basic_salary']
                                        ?? 0
                                    ),
                                    0
                                )
                            ) ?>

                        </div>

                    </div>


                    <hr>


                    <div class="row g-3">


                        <!-- Total Salary -->

                        <div class="col-4">

                            <div class="mini-stat">

                                <div class="text-muted small">

                                    মোট বেতন

                                </div>

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



                        <!-- Paid -->

                        <div class="col-4">

                            <div class="mini-stat">

                                <div class="text-muted small">

                                    প্রদান

                                </div>

                                <strong class="text-success">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $totalPaid,
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </div>

                        </div>



                        <!-- Due -->

                        <div class="col-4">

                            <div class="mini-stat">

                                <div class="text-muted small">

                                    বকেয়া

                                </div>

                                <strong class="text-danger">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $totalDue,
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </div>

                        </div>


                    </div>


                    <div class="mt-4">

                        <a
                            href="index.php?page=salary/payment&employee_id=<?= $employeeId ?>"
                            class="btn btn-success w-100"
                        >

                            <i
                                class="bi bi-cash-stack me-1"
                            ></i>

                            এই কর্মচারীর বেতন প্রদান

                        </a>

                    </div>


                </div>

            </div>

        </div>


    </div>



    <!-- =========================================================
         SALARY HISTORY
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div
            class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center"
        >

            <h5 class="mb-0">

                <i
                    class="bi bi-clock-history me-2"
                ></i>

                বেতন হিস্টোরি

            </h5>


            <span class="badge bg-light text-dark">

                <?= bn_number(
                    count($salaryHistory)
                ) ?>

                টি রেকর্ড

            </span>

        </div>



        <div class="card-body p-0">


            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                >

                    <thead class="table-light">

                    <tr>

                        <th class="text-center">
                            #
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


                    <?php if (
                        empty(
                            $salaryHistory
                        )
                    ): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-receipt fs-1 text-muted"
                                ></i>

                                <h6 class="mt-3">

                                    কোনো বেতন হিস্টোরি নেই

                                </h6>

                                <p class="text-muted mb-0">

                                    এই কর্মচারীর কোনো বেতন প্রদান করা হয়নি।

                                </p>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach (
                        $salaryHistory
                        as $index => $salary
                    ): ?>


                        <?php

                        $salaryMonth = '—';

                        if (
                            !empty(
                                $salary['salary_month']
                            )
                        ) {

                            $salaryMonth =
                                date(
                                    'F Y',
                                    strtotime(
                                        $salary['salary_month']
                                    )
                                );

                        }


                        $paymentDate = '—';

                        if (
                            !empty(
                                $salary['payment_date']
                            )
                        ) {

                            $paymentDate =
                                date(
                                    'd-m-Y',
                                    strtotime(
                                        $salary['payment_date']
                                    )
                                );

                        }


                        $method =
                            $salary['payment_method']
                            ?? '';

                        ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <?= bn_number(
                                    $index + 1
                                ) ?>

                            </td>



                            <!-- Month -->

                            <td>

                                <span
                                    class="badge bg-light text-dark border"
                                >

                                    <?= htmlspecialchars(
                                        $salaryMonth
                                    ) ?>

                                </span>

                            </td>



                            <!-- Payment Date -->

                            <td>

                                <?= bn_number(
                                    $paymentDate
                                ) ?>

                            </td>



                            <!-- Total -->

                            <td class="text-end fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        (float)$salary['total_salary'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Paid -->

                            <td class="text-end text-success fw-bold">

                                ৳ <?= bn_number(
                                    number_format(
                                        (float)$salary['paid_amount'],
                                        0
                                    )
                                ) ?>

                            </td>



                            <!-- Due -->

                            <td class="text-end">

                                <?php if (
                                    (float)$salary['due_amount']
                                    > 0
                                ): ?>

                                    <span
                                        class="badge bg-danger"
                                    >

                                        ৳ <?= bn_number(
                                            number_format(
                                                (float)$salary['due_amount'],
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

                                <?php

                                if (
                                    $method === 'cash'
                                ) {

                                    echo '
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-cash me-1"></i>
                                            নগদ
                                        </span>
                                    ';

                                } elseif (
                                    $method === 'bank'
                                ) {

                                    echo '
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="bi bi-bank me-1"></i>
                                            ব্যাংক
                                        </span>
                                    ';

                                } elseif (
                                    $method === 'mobile_banking'
                                ) {

                                    echo '
                                        <span class="badge bg-warning-subtle text-dark">
                                            <i class="bi bi-phone me-1"></i>
                                            মোবাইল ব্যাংকিং
                                        </span>
                                    ';

                                } else {

                                    echo '<span class="text-muted">—</span>';

                                }

                                ?>

                            </td>



                            <!-- Action -->

                            <td class="text-center">

                                <div
                                    class="btn-group btn-group-sm"
                                >

                                    <a
                                        href="index.php?page=salary/view&id=<?= (int)$salary['id'] ?>"
                                        class="btn btn-primary"
                                        title="বেতন বিস্তারিত"
                                    >

                                        <i
                                            class="bi bi-eye"
                                        ></i>

                                    </a>


                                    <a
                                        href="index.php?page=salary/receipt&id=<?= (int)$salary['id'] ?>"
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


                    <?php if (
                        !empty(
                            $salaryHistory
                        )
                    ): ?>

                        <tfoot class="table-light">

                        <tr>

                            <th
                                colspan="3"
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



<style>

/*
|--------------------------------------------------------------------------
| Card
|--------------------------------------------------------------------------
*/

.card {

    border-radius: 10px;

}


/*
|--------------------------------------------------------------------------
| Employee Avatar
|--------------------------------------------------------------------------
*/

.employee-avatar {

    width: 75px;

    height: 75px;

    border-radius: 50%;

    background:
        rgba(13, 110, 253, .1);

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 34px;

}


/*
|--------------------------------------------------------------------------
| Info Row
|--------------------------------------------------------------------------
*/

.info-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 13px 0;

    border-bottom:
        1px solid #edf0f2;

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    color: #6b7280;

    min-width: 150px;

}


.info-label i {

    width: 22px;

}


.info-value {

    text-align: right;

    font-weight: 500;

    color: #374151;

}


/*
|--------------------------------------------------------------------------
| Salary
|--------------------------------------------------------------------------
*/

.salary-main {

    text-align: center;

    padding: 10px 0;

}


.salary-amount {

    font-size: 32px;

    font-weight: 700;

    color: #198754;

    margin-top: 5px;

}


/*
|--------------------------------------------------------------------------
| Mini Stats
|--------------------------------------------------------------------------
*/

.mini-stat {

    background: #f8f9fa;

    border-radius: 8px;

    padding: 12px;

    text-align: center;

}


.mini-stat strong {

    display: block;

    margin-top: 5px;

    font-size: 15px;

}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.table th {

    white-space: nowrap;

    font-size: 13px;

}


.table td {

    white-space: nowrap;

}


.table tbody tr:hover {

    background:
        rgba(13, 110, 253, .04);

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


    .info-row {

        flex-direction: column;

        gap: 5px;

    }


    .info-value {

        text-align: left;

    }


    .employee-avatar {

        width: 60px;

        height: 60px;

        font-size: 28px;

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


    .btn {

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


    .table th:last-child,
    .table td:last-child {

        display: none;

    }

}

</style>