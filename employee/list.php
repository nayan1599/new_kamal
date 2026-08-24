<?php

/*
|--------------------------------------------------------------------------
| Employee List
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        e.*,
        g.garage_name,

        COALESCE(
            (
                SELECT SUM(sp.paid_amount)
                FROM salary_payments sp
                WHERE sp.employee_id = e.id
            ), 0
        ) AS total_paid

    FROM employees e

    LEFT JOIN garages g
        ON g.id = e.garage_id

    ORDER BY e.id DESC
");

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalEmployees    = count($employees);
$activeEmployees   = 0;
$inactiveEmployees = 0;
$totalSalary       = 0;
$totalPaid         = 0;

foreach ($employees as $employee) {

    if ($employee['status'] === 'active') {

        $activeEmployees++;

        $totalSalary += (float)$employee['salary'];

    }

    if ($employee['status'] === 'inactive') {

        $inactiveEmployees++;

    }

    $totalPaid += (float)$employee['total_paid'];
}

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-people-fill text-primary me-2"></i>

                কর্মচারী তালিকা

            </h1>

            <p class="text-muted mb-0">

                সকল কর্মচারী, গ্যারেজ ও বেতনের তথ্য

            </p>

        </div>


        <a
            href="index.php?page=employee/add"
            class="btn btn-success btn-lg"
        >

            <i class="bi bi-person-plus-fill me-2"></i>

            নতুন কর্মচারী

        </a>

    </div>



    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- মোট কর্মচারী -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-people-fill text-primary fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                মোট কর্মচারী
                            </div>

                            <div class="fs-4 fw-bold">

                                <?= bn_number($totalEmployees) ?>

                                <span class="fs-6 text-muted">
                                    জন
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- সক্রিয় কর্মচারী -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-success bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-person-check-fill text-success fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                সক্রিয় কর্মচারী
                            </div>

                            <div class="fs-4 fw-bold text-success">

                                <?= bn_number($activeEmployees) ?>

                                <span class="fs-6 text-muted">
                                    জন
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- নিষ্ক্রিয় কর্মচারী -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"
                        >

                            <i
                                class="bi bi-person-x-fill text-danger fs-4"
                            ></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                নিষ্ক্রিয় কর্মচারী
                            </div>

                            <div class="fs-4 fw-bold text-danger">

                                <?= bn_number($inactiveEmployees) ?>

                                <span class="fs-6 text-muted">
                                    জন
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- মোট মাসিক বেতন -->

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
                                মোট মাসিক বেতন
                            </div>

                            <div class="fs-4 fw-bold text-info">

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

                সকল কর্মচারীর তালিকা

            </h5>


            <div class="d-flex gap-2">

                <!-- Search -->

                <input
                    type="text"
                    id="searchInput"
                    class="form-control form-control-sm search-box"
                    placeholder="নাম, ফোন, পদ দিয়ে সার্চ..."
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
                    id="employeeTable"
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
                            মোবাইল
                        </th>

                        <th>
                            গ্যারেজ
                        </th>

                        <th>
                            যোগদানের তারিখ
                        </th>

                        <th class="text-end">
                            মাসিক বেতন
                        </th>

                        <th class="text-end">
                            মোট প্রদান
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


                    <?php if (empty($employees)): ?>

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="bi bi-people fs-1"
                                    ></i>

                                    <h5 class="mt-3">
                                        কোনো কর্মচারী পাওয়া যায়নি
                                    </h5>


                                    <a
                                        href="index.php?page=employee/add"
                                        class="btn btn-success"
                                    >

                                        <i class="bi bi-person-plus me-1"></i>

                                        নতুন কর্মচারী যোগ করুন

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>



                    <?php foreach ($employees as $i => $employee): ?>


                        <?php

                        $salary =
                            (float)$employee['salary'];

                        $totalPaid =
                            (float)$employee['total_paid'];

                        ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <span class="badge bg-secondary">

                                    <?= bn_number($i + 1) ?>

                                </span>

                            </td>



                            <!-- Employee -->

                            <td>

                                <a
                                    href="index.php?page=employee/view&id=<?= (int)$employee['id'] ?>"
                                    class="text-decoration-none fw-bold text-primary"
                                >

                                    <?= htmlspecialchars(
                                        $employee['employee_name']
                                    ) ?>

                                </a>

                            </td>



                            <!-- Designation -->

                            <td>

                                <?php if (
                                    !empty($employee['designation'])
                                ): ?>

                                    <?= htmlspecialchars(
                                        $employee['designation']
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
                                    !empty($employee['mobile'])
                                ): ?>

                                    <a
                                        href="tel:<?= htmlspecialchars($employee['mobile']) ?>"
                                        class="text-decoration-none"
                                    >

                                        <i class="bi bi-telephone me-1"></i>

                                        <?= htmlspecialchars(
                                            $employee['mobile']
                                        ) ?>

                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Garage -->

                            <td>

                                <?php if (
                                    !empty($employee['garage_name'])
                                ): ?>

                                    <span class="badge bg-info text-dark">

                                        <?= htmlspecialchars(
                                            $employee['garage_name']
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Joining Date -->

                            <td>

                                <?php if (
                                    !empty($employee['joining_date'])
                                ): ?>

                                    <?= bn_number(
                                        date(
                                            'd-m-Y',
                                            strtotime(
                                                $employee['joining_date']
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Salary -->

                            <td class="text-end fw-semibold">

                                ৳ <?= bn_number(
                                    number_format(
                                        $salary,
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



                            <!-- Status -->

                            <td class="text-center">

                                <?php if (
                                    $employee['status'] === 'active'
                                ): ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-check-circle me-1"></i>

                                        চলমান

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        <i class="bi bi-x-circle me-1"></i>

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
                                        href="index.php?page=salary/payment&employee_id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-success"
                                        title="বেতন প্রদান"
                                    >

                                        <i class="bi bi-cash-stack"></i>

                                        বেতন

                                    </a>


                                    <a
                                        href="index.php?page=employee/view&id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-primary"
                                        title="বিস্তারিত"
                                    >

                                        <i class="bi bi-eye"></i>

                                        View

                                    </a>


                                    <a
                                        href="index.php?page=employee/edit&id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-warning"
                                        title="Edit"
                                    >

                                        <i class="bi bi-pencil"></i>

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
                '#employeeTable tbody tr'
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

#employeeTable th {
    white-space: nowrap;
}

#employeeTable td {
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