<?php

/*
|--------------------------------------------------------------------------
| Employee Attendance Report
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
| Month Date Range
|--------------------------------------------------------------------------
*/

$monthStart = $selectedMonth . '-01';

$monthEnd = date(
    'Y-m-t',
    strtotime($monthStart)
);


/*
|--------------------------------------------------------------------------
| Employee List
|--------------------------------------------------------------------------
*/

$whereEmployee = "";
$paramsEmployee = [];

if ($selectedGarage > 0) {

    $whereEmployee = "
        WHERE e.garage_id = :garage_id
    ";

    $paramsEmployee[':garage_id'] =
        $selectedGarage;
}

$employeeStmt = $pdo->prepare("
    SELECT
        e.id,
        e.employee_name,
        e.mobile,
        e.designation,
        e.garage_id,
        g.garage_name

    FROM employees e

    LEFT JOIN garages g
        ON g.id = e.garage_id

    $whereEmployee

    ORDER BY e.employee_name ASC
");

$employeeStmt->execute(
    $paramsEmployee
);

$employees =
    $employeeStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Attendance Data
|--------------------------------------------------------------------------
*/

$attendanceStmt = $pdo->prepare("
    SELECT
        employee_id,
        status,
        attendance_date,
        note

    FROM employee_attendance

    WHERE attendance_date BETWEEN :start_date
    AND :end_date

    ORDER BY attendance_date ASC
");

$attendanceStmt->execute([

    ':start_date' => $monthStart,
    ':end_date'   => $monthEnd

]);

$attendanceRows =
    $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Make Attendance Array
|--------------------------------------------------------------------------
*/

$attendance = [];

foreach ($attendanceRows as $row) {

    $employeeId =
        (int)$row['employee_id'];

    $date =
        $row['attendance_date'];

    $attendance[
        $employeeId
    ][$date] = [

        'status' =>
            $row['status'],

        'note' =>
            $row['note'] ?? ''

    ];

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
| Summary
|--------------------------------------------------------------------------
*/

$totalPresent = 0;
$totalAbsent  = 0;
$totalLeave   = 0;
$totalLate    = 0;


foreach ($attendanceRows as $row) {

    switch ($row['status']) {

        case 'present':
            $totalPresent++;
            break;

        case 'absent':
            $totalAbsent++;
            break;

        case 'leave':
            $totalLeave++;
            break;

        case 'late':
            $totalLate++;
            break;

    }

}


/*
|--------------------------------------------------------------------------
| Month Name
|--------------------------------------------------------------------------
*/

$monthTitle = date(
    'F Y',
    strtotime($monthStart)
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

                <i
                    class="bi bi-calendar-check-fill text-primary me-2"
                ></i>

                হাজিরা রিপোর্ট

            </h1>

            <p class="text-muted mb-0">

                কর্মচারীদের মাসিক হাজিরার বিস্তারিত রিপোর্ট

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=employee/attendance"
                class="btn btn-success no-print"
            >

                <i
                    class="bi bi-calendar-plus me-1"
                ></i>

                হাজিরা প্রদান

            </a>


            <button
                type="button"
                class="btn btn-outline-primary no-print"
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

    <div class="card border-0 shadow-sm mb-4 no-print">

        <div class="card-body">

            <form
                method="GET"
                action="index.php"
                class="row g-3 align-items-end"
            >

                <input
                    type="hidden"
                    name="page"
                    value="employee/attendance_report"
                >


                <div class="col-md-4">

                    <label class="form-label fw-semibold">

                        মাস নির্বাচন করুন

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
                            $garages as $garage
                        ): ?>

                            <option
                                value="<?= (int)$garage['id'] ?>"
                                <?= (
                                    $selectedGarage ==
                                    $garage['id']
                                )
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


                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >

                        <i
                            class="bi bi-search me-1"
                        ></i>

                        রিপোর্ট দেখুন

                    </button>

                </div>

            </form>

        </div>

    </div>



    <!-- =========================================================
         REPORT TITLE
    ========================================================== -->

    <div class="report-title mb-4">

        <div>

            <h5 class="mb-1">

                মাসিক হাজিরা রিপোর্ট

            </h5>

            <div class="text-muted">

                মাস:

                <strong>

                    <?= htmlspecialchars(
                        $monthTitle
                    ) ?>

                </strong>

                |

                <?= count($employees) ?>

                জন কর্মচারী

            </div>

        </div>

    </div>



    <!-- =========================================================
         SUMMARY
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- Present -->

        <div class="col-6 col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট উপস্থিত

                    </div>

                    <div class="fs-3 fw-bold text-success">

                        <?= bn_number(
                            $totalPresent
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Absent -->

        <div class="col-6 col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট অনুপস্থিত

                    </div>

                    <div class="fs-3 fw-bold text-danger">

                        <?= bn_number(
                            $totalAbsent
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Leave -->

        <div class="col-6 col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট ছুটি

                    </div>

                    <div class="fs-3 fw-bold text-warning">

                        <?= bn_number(
                            $totalLeave
                        ) ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Late -->

        <div class="col-6 col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">

                        মোট দেরিতে উপস্থিত

                    </div>

                    <div class="fs-3 fw-bold text-primary">

                        <?= bn_number(
                            $totalLate
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================================
         EMPLOYEE SUMMARY
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-primary text-white py-3">

            <h5 class="mb-0">

                <i
                    class="bi bi-people-fill me-2"
                ></i>

                কর্মচারীভিত্তিক হাজিরা

            </h5>

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
                            কর্মচারী
                        </th>

                        <th>
                            পদ
                        </th>

                        <th>
                            গ্যারেজ
                        </th>

                        <th class="text-center">
                            উপস্থিত
                        </th>

                        <th class="text-center">
                            অনুপস্থিত
                        </th>

                        <th class="text-center">
                            ছুটি
                        </th>

                        <th class="text-center">
                            দেরি
                        </th>

                        <th class="text-center">
                            মোট হাজিরা
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php if (
                        empty($employees)
                    ): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-people fs-1 text-muted"
                                ></i>

                                <h5 class="mt-3">

                                    কোনো কর্মচারী পাওয়া যায়নি

                                </h5>

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach (
                        $employees
                        as $index => $employee
                    ): ?>


                        <?php

                        $eid =
                            (int)$employee['id'];

                        $present = 0;
                        $absent  = 0;
                        $leave   = 0;
                        $late    = 0;

                        if (
                            isset(
                                $attendance[$eid]
                            )
                        ) {

                            foreach (
                                $attendance[$eid]
                                as $record
                            ) {

                                switch (
                                    $record['status']
                                ) {

                                    case 'present':
                                        $present++;
                                        break;

                                    case 'absent':
                                        $absent++;
                                        break;

                                    case 'leave':
                                        $leave++;
                                        break;

                                    case 'late':
                                        $late++;
                                        break;

                                }

                            }

                        }

                        $totalMarked =
                            $present +
                            $absent +
                            $leave +
                            $late;

                        ?>


                        <tr>

                            <td class="text-center">

                                <?= bn_number(
                                    $index + 1
                                ) ?>

                            </td>


                            <td>

                                <div class="fw-bold">

                                    <i
                                        class="bi bi-person-circle text-primary me-1"
                                    ></i>

                                    <?= htmlspecialchars(
                                        $employee['employee_name']
                                    ) ?>

                                </div>


                                <?php if (
                                    !empty(
                                        $employee['mobile']
                                    )
                                ): ?>

                                    <small class="text-muted">

                                        <?= htmlspecialchars(
                                            $employee['mobile']
                                        ) ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= !empty(
                                    $employee['designation']
                                )
                                    ? htmlspecialchars(
                                        $employee['designation']
                                    )
                                    : '—'
                                ?>

                            </td>


                            <td>

                                <?= !empty(
                                    $employee['garage_name']
                                )
                                    ? htmlspecialchars(
                                        $employee['garage_name']
                                    )
                                    : '—'
                                ?>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-success"
                                >

                                    <?= bn_number(
                                        $present
                                    ) ?>

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-danger"
                                >

                                    <?= bn_number(
                                        $absent
                                    ) ?>

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-warning text-dark"
                                >

                                    <?= bn_number(
                                        $leave
                                    ) ?>

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-primary"
                                >

                                    <?= bn_number(
                                        $late
                                    ) ?>

                                </span>

                            </td>


                            <td class="text-center fw-bold">

                                <?= bn_number(
                                    $totalMarked
                                ) ?>

                            </td>

                        </tr>


                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <!-- =========================================================
         DAILY ATTENDANCE
    ========================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-dark text-white py-3">

            <h5 class="mb-0">

                <i
                    class="bi bi-calendar3 me-2"
                ></i>

                দৈনিক হাজিরার বিস্তারিত

            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-bordered table-sm mb-0"
                >

                    <thead class="table-light">

                    <tr>

                        <th
                            style="min-width:180px;"
                        >

                            কর্মচারী

                        </th>


                        <?php

                        $daysInMonth =
                            (int)date(
                                't',
                                strtotime(
                                    $monthStart
                                )
                            );

                        for (
                            $day = 1;
                            $day <= $daysInMonth;
                            $day++
                        ):

                            $date =
                                $selectedMonth .
                                '-' .
                                str_pad(
                                    $day,
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                );

                        ?>

                            <th class="text-center day-head">

                                <?= bn_number(
                                    $day
                                ) ?>

                            </th>

                        <?php endfor; ?>

                    </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $employees
                        as $employee
                    ): ?>


                        <?php
                        $eid =
                            (int)$employee['id'];
                        ?>


                        <tr>

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $employee['employee_name']
                                    ) ?>

                                </strong>

                            </td>


                            <?php

                            for (
                                $day = 1;
                                $day <= $daysInMonth;
                                $day++
                            ):

                                $date =
                                    $selectedMonth .
                                    '-' .
                                    str_pad(
                                        $day,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    );

                                $record =
                                    $attendance[$eid][$date]
                                    ?? null;

                            ?>


                                <td
                                    class="text-center attendance-cell"
                                >

                                    <?php if (!$record): ?>

                                        <span
                                            class="text-muted"
                                            title="হাজিরা দেওয়া হয়নি"
                                        >
                                            —
                                        </span>

                                    <?php elseif (
                                        $record['status']
                                        === 'present'
                                    ): ?>

                                        <span
                                            class="status-present"
                                            title="উপস্থিত"
                                        >
                                            ✓
                                        </span>

                                    <?php elseif (
                                        $record['status']
                                        === 'absent'
                                    ): ?>

                                        <span
                                            class="status-absent"
                                            title="অনুপস্থিত"
                                        >
                                            ✕
                                        </span>

                                    <?php elseif (
                                        $record['status']
                                        === 'leave'
                                    ): ?>

                                        <span
                                            class="status-leave"
                                            title="ছুটি"
                                        >
                                            ছু
                                        </span>

                                    <?php elseif (
                                        $record['status']
                                        === 'late'
                                    ): ?>

                                        <span
                                            class="status-late"
                                            title="দেরিতে উপস্থিত"
                                        >
                                            দে
                                        </span>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>


                            <?php endfor; ?>

                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>



<style>

/*
|--------------------------------------------------------------------------
| Report Title
|--------------------------------------------------------------------------
*/

.report-title {

    border-left: 4px solid #0d6efd;

    background: #f8f9fa;

    padding: 14px 18px;

    border-radius: 6px;

}


/*
|--------------------------------------------------------------------------
| Cards
|--------------------------------------------------------------------------
*/

.card {

    border-radius: 10px;

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


/*
|--------------------------------------------------------------------------
| Day Header
|--------------------------------------------------------------------------
*/

.day-head {

    min-width: 38px;

}


/*
|--------------------------------------------------------------------------
| Attendance Cell
|--------------------------------------------------------------------------
*/

.attendance-cell {

    width: 40px;

    height: 38px;

    padding: 5px !important;

    font-weight: 700;

}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

.status-present {

    color: #198754;

    font-size: 18px;

}


.status-absent {

    color: #dc3545;

    font-size: 17px;

}


.status-leave {

    color: #d39e00;

    font-size: 11px;

}


.status-late {

    color: #0d6efd;

    font-size: 11px;

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


    .no-print {

        display: none !important;

    }


    .card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;

    }


    .table {

        font-size: 10px;

    }


    .attendance-cell {

        width: 25px;

    }


    @page {

        size: landscape;

        margin: 8mm;

    }

}

</style>