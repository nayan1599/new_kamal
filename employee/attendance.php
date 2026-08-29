<?php


/*
|--------------------------------------------------------------------------
| Selected Date
|--------------------------------------------------------------------------
*/

$attendance_date = $_GET['date'] ?? date('Y-m-d');
$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| Employee + Attendance
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        e.id AS employee_id,
        e.employee_name,
        e.mobile,

        ea.id AS attendance_id,
        ea.attendance_date,
        ea.status,
        ea.check_in,
        ea.check_out,
        ea.note

    FROM employees e

    LEFT JOIN employee_attendance ea
        ON ea.employee_id = e.id
        AND ea.attendance_date = :attendance_date
";

$params = [
    ':attendance_date' => $attendance_date
];


if ($search !== '') {

    $sql .= "
        WHERE e.employee_name LIKE :search
           OR e.mobile LIKE :search
    ";

    $params[':search'] = '%' . $search . '%';
}


$sql .= "
    ORDER BY e.employee_name ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$total_employee = count($employees);

$present = 0;
$absent = 0;
$late = 0;
$leave = 0;
$half_day = 0;
$not_marked = 0;


foreach ($employees as $employee) {

    if (empty($employee['attendance_id'])) {

        $not_marked++;

        continue;
    }

    switch ($employee['status']) {

        case 'present':
            $present++;
            break;

        case 'absent':
            $absent++;
            break;

        case 'late':
            $late++;
            break;

        case 'leave':
            $leave++;
            break;

        case 'half_day':
            $half_day++;
            break;
    }
}

?>


<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h4 class="mb-1">
                <i class="bi bi-calendar-check"></i>
                কর্মচারী উপস্থিতি
            </h4>

            <div class="text-muted">
                তারিখ অনুযায়ী কর্মচারীদের উপস্থিতি
            </div>

        </div>


        <div>

            <a
                href="index.php?page=employee/attendance_add"
                class="btn btn-primary"
            >
                <i class="bi bi-calendar-plus"></i>
                Attendance যোগ করুন
            </a>

        </div>

    </div>


    <!-- Date + Search -->
    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body">

            <form method="GET" action="index.php">

                <input
                    type="hidden"
                    name="page"
                    value="employee/attendance"
                >

                <div class="row g-2">

                    <!-- Date -->
                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            তারিখ
                        </label>

                        <input
                            type="date"
                            name="date"
                            class="form-control"
                            value="<?= htmlspecialchars($attendance_date) ?>"
                        >

                    </div>


                    <!-- Search -->
                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Employee Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="নাম অথবা মোবাইল নম্বর..."
                            value="<?= htmlspecialchars($search) ?>"
                        >

                    </div>


                    <!-- Button -->
                    <div class="col-md-3 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-search"></i>
                            দেখুন
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Summary Cards -->

    <div class="row g-3 mb-3">

        <!-- Total -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        মোট কর্মচারী
                    </small>

                    <h3 class="mb-0">
                        <?= $total_employee ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Present -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-success">
                        উপস্থিত
                    </small>

                    <h3 class="mb-0 text-success">
                        <?= $present ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Absent -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-danger">
                        অনুপস্থিত
                    </small>

                    <h3 class="mb-0 text-danger">
                        <?= $absent ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Late -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-warning">
                        দেরিতে
                    </small>

                    <h3 class="mb-0 text-warning">
                        <?= $late ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Leave -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-info">
                        ছুটি
                    </small>

                    <h3 class="mb-0 text-info">
                        <?= $leave ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Not Marked -->
        <div class="col-lg-2 col-md-4 col-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-secondary">
                        দেওয়া হয়নি
                    </small>

                    <h3 class="mb-0 text-secondary">
                        <?= $not_marked ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>


    <!-- Attendance Table -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    <i class="bi bi-list-check"></i>
                    <?= date('d-m-Y', strtotime($attendance_date)) ?>
                    তারিখের উপস্থিতি
                </strong>

                <span class="badge bg-primary">
                    <?= $total_employee ?> জন
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered mb-0 align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="60" class="text-center">
                                #
                            </th>

                            <th>
                                Employee
                            </th>

                            <th>
                                মোবাইল
                            </th>

                            <th width="130" class="text-center">
                                Status
                            </th>

                            <th width="120" class="text-center">
                                Check In
                            </th>

                            <th width="120" class="text-center">
                                Check Out
                            </th>

                            <th>
                                Note
                            </th>

                            <th width="130" class="text-center">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($employees)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bi bi-person-x"
                                    style="font-size:40px;"
                                ></i>

                                <div class="mt-2">
                                    কোনো Employee পাওয়া যায়নি।
                                </div>

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($employees as $key => $employee): ?>

                            <tr>

                                <!-- Serial -->

                                <td class="text-center">
                                    <?= $key + 1 ?>
                                </td>


                                <!-- Employee -->

                                <td>

                                    <div class="fw-semibold">

                                        <i class="bi bi-person-circle text-primary"></i>

                                        <?= htmlspecialchars(
                                            $employee['name'] ?? ''
                                        ) ?>

                                    </div>

                                    <small class="text-muted">
                                        ID:
                                        <?= (int)$employee['employee_id'] ?>
                                    </small>

                                </td>


                                <!-- Phone -->

                                <td>

                                    <?php if (!empty($employee['phone'])): ?>

                                        <?= htmlspecialchars(
                                            $employee['phone']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Status -->

                                <td class="text-center">

                                    <?php if (empty($employee['attendance_id'])): ?>

                                        <span class="badge bg-secondary">
                                            দেওয়া হয়নি
                                        </span>

                                    <?php else: ?>

                                        <?php

                                        $status = $employee['status'];

                                        $status_text = '';
                                        $status_class = '';

                                        switch ($status) {

                                            case 'present':
                                                $status_text = 'উপস্থিত';
                                                $status_class = 'bg-success';
                                                break;

                                            case 'absent':
                                                $status_text = 'অনুপস্থিত';
                                                $status_class = 'bg-danger';
                                                break;

                                            case 'late':
                                                $status_text = 'দেরিতে';
                                                $status_class = 'bg-warning text-dark';
                                                break;

                                            case 'leave':
                                                $status_text = 'ছুটি';
                                                $status_class = 'bg-info text-dark';
                                                break;

                                            case 'half_day':
                                                $status_text = 'অর্ধদিবস';
                                                $status_class = 'bg-primary';
                                                break;

                                            default:
                                                $status_text = $status;
                                                $status_class = 'bg-secondary';
                                        }

                                        ?>

                                        <span class="badge <?= $status_class ?>">
                                            <?= $status_text ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Check In -->

                                <td class="text-center">

                                    <?= !empty($employee['check_in'])
                                        ? date(
                                            'h:i A',
                                            strtotime($employee['check_in'])
                                        )
                                        : '-' ?>

                                </td>


                                <!-- Check Out -->

                                <td class="text-center">

                                    <?= !empty($employee['check_out'])
                                        ? date(
                                            'h:i A',
                                            strtotime($employee['check_out'])
                                        )
                                        : '-' ?>

                                </td>


                                <!-- Note -->

                                <td>

                                    <?= !empty($employee['note'])
                                        ? htmlspecialchars($employee['note'])
                                        : '<span class="text-muted">-</span>' ?>

                                </td>


                                <!-- Action -->

                                <td class="text-center">

                                    <?php if (!empty($employee['attendance_id'])): ?>

                                        <a
                                            href="index.php?page=employee/attendance_edit&id=<?= (int)$employee['attendance_id'] ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Edit Attendance"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="index.php?page=employee/attendance_add&employee_id=<?= (int)$employee['employee_id'] ?>&date=<?= urlencode($attendance_date) ?>"
                                            class="btn btn-sm btn-success"
                                            title="Attendance দিন"
                                        >

                                            <i class="bi bi-plus-circle"></i>

                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>