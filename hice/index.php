
<?php

/*
|--------------------------------------------------------------------------
| Hice Rent Index
|--------------------------------------------------------------------------
| URL:
| index.php?page=hice/index
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT 
        r.*,
        v.vehicle_number,
        v.driver_name,
        v.driver_mobile
    FROM hice_rents r
    LEFT JOIN hice_vehicles v ON v.id = r.vehicle_id
    ORDER BY r.collection_date DESC, r.id DESC
");

$records = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalRecords = count($records);

$today = date('Y-m-d');

$todayRent = $pdo->prepare("
    SELECT 
        COALESCE(SUM(total_rent), 0)
    FROM hice_rents
    WHERE collection_date = ?
");
$todayRent->execute([$today]);
$todayRent = $todayRent->fetchColumn();

$todayExpense = $pdo->prepare("
    SELECT 
        COALESCE(SUM(expense_amount), 0)
    FROM hice_rents
    WHERE collection_date = ?
");
$todayExpense->execute([$today]);
$todayExpense = $todayExpense->fetchColumn();

$todayDriver = $pdo->prepare("
    SELECT 
        COALESCE(SUM(driver_amount), 0)
    FROM hice_rents
    WHERE collection_date = ?
");
$todayDriver->execute([$today]);
$todayDriver = $todayDriver->fetchColumn();

$todayOwner = $pdo->prepare("
    SELECT 
        COALESCE(SUM(owner_amount), 0)
    FROM hice_rents
    WHERE collection_date = ?
");
$todayOwner->execute([$today]);
$todayOwner = $todayOwner->fetchColumn();

$todayNet = $todayRent - $todayExpense;


/*
|--------------------------------------------------------------------------
| Active Vehicles
|--------------------------------------------------------------------------
*/

$activeVehicles = $pdo->query("
    SELECT COUNT(*)
    FROM hice_vehicles
    WHERE status = 'active'
")->fetchColumn();


?>

<style>

/* =====================================================
   PAGE WRAPPER
===================================================== */

.container-fluid {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, sans-serif;
}


/* =====================================================
   PAGE HEADING
===================================================== */

.page-heading {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 16px;

    flex-wrap: wrap;

    background:
        linear-gradient(
            120deg,
            #14532d 0%,
            #16a34a 50%,
            #22c55e 100%
        );

    border-radius: 18px;

    padding: 22px 26px;

    margin-bottom: 20px;

    box-shadow:
        0 12px 30px rgba(22, 163, 74, .22);

    position: relative;

    overflow: hidden;
}


.page-heading::before {

    content: "";

    position: absolute;

    width: 220px;

    height: 220px;

    background: rgba(255, 255, 255, .08);

    border-radius: 50%;

    top: -90px;

    right: -60px;

}


.page-heading-copy {

    display: flex;

    align-items: center;

    gap: 16px;

    position: relative;

    z-index: 2;
}


.page-icon {

    width: 54px;

    height: 54px;

    border-radius: 14px;

    background: rgba(255, 255, 255, .16);

    backdrop-filter: blur(6px);

    border: 1px solid rgba(255, 255, 255, .25);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 24px;

    color: #fff;

    flex-shrink: 0;
}


.page-heading .eyebrow {

    color: rgba(255, 255, 255, .8);

    font-size: 12px;

    font-weight: 700;

    letter-spacing: .5px;

    text-transform: uppercase;
}


.page-heading h1 {

    color: #fff;

    font-weight: 800;
}


.page-heading .text-muted {

    color: rgba(255, 255, 255, .85) !important;
}


.page-heading .btn {

    position: relative;

    z-index: 2;

    border-radius: 10px;

    font-weight: 600;

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .12);

    white-space: nowrap;
}


/* =====================================================
   QUICK STAT
===================================================== */

.mini-stat-grid {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 14px;

    margin-bottom: 20px;
}


.mini-stat {

    background: #fff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 14px 16px;

    box-shadow:
        0 5px 16px rgba(15, 23, 42, .05);

    display: flex;

    align-items: center;

    gap: 12px;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}


.mini-stat:hover {

    transform: translateY(-3px);

    box-shadow:
        0 10px 22px rgba(15, 23, 42, .10);
}


.mini-stat .mini-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;

    flex-shrink: 0;
}


.mini-stat .mini-value {

    font-size: 20px;

    font-weight: 800;

    line-height: 1.1;
}


.mini-stat .mini-label {

    font-size: 12px;

    color: #64748b;

    font-weight: 600;
}


/* COLORS */

.mi-total .mini-icon {

    background: #dbeafe;

    color: #2563eb;
}


.mi-rent .mini-icon {

    background: #dcfce7;

    color: #059669;
}


.mi-expense .mini-icon {

    background: #fee2e2;

    color: #dc2626;
}


.mi-driver .mini-icon {

    background: #fef3c7;

    color: #d97706;
}


.mi-owner .mini-icon {

    background: #cffafe;

    color: #0891b2;
}


/* =====================================================
   PANEL
===================================================== */

.panel {

    background: #fff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 6px 20px rgba(15, 23, 42, .06);
}


.panel-header {

    padding: 16px 20px;

    background: #f8fafc;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    justify-content: space-between;

    align-items: center;

    flex-wrap: wrap;

    gap: 12px;
}


.panel-header .section-title {

    display: flex;

    align-items: center;

    gap: 8px;

    font-weight: 700;

    color: #1f2937;

    margin: 0;
}


.table-search {

    max-width: 320px;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    padding: 8px 14px;
}


.table-search:focus {

    border-color: #93c5fd;

    box-shadow:
        0 0 0 3px rgba(59, 130, 246, .15);
}


/* =====================================================
   TABLE
===================================================== */

.table-responsive {

    max-height: 640px;

    overflow-y: auto;
}


#dataTable {

    margin: 0;
}


#dataTable thead th {

    position: sticky;

    top: 0;

    background: #f1f5f9;

    color: #334155;

    font-size: 13px;

    font-weight: 700;

    border-bottom: 2px solid #e2e8f0;

    padding: 12px 14px;

    white-space: nowrap;

    z-index: 1;
}


#dataTable tbody td {

    padding: 12px 14px;

    font-size: 13.5px;

    vertical-align: middle;

    border-bottom:
        1px solid #f1f5f9;
}


#dataTable tbody tr {

    transition:
        background .15s ease;
}


#dataTable tbody tr:hover {

    background: #f8fafc;
}


#dataTable .badge {

    font-size: 11.5px;

    font-weight: 600;

    padding: 5px 10px;

    border-radius: 20px;
}


#dataTable .btn-sm {

    border-radius: 8px;

    padding: 4px 9px;

    margin-left: 3px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1100px) {

    .mini-stat-grid {

        grid-template-columns:
            repeat(3, 1fr);
    }

}


@media (max-width: 768px) {

    .mini-stat-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 500px) {

    .mini-stat-grid {

        grid-template-columns: 1fr;
    }

}

</style>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =================================================
         PAGE HEADING
    ================================================= -->

    <div class="page-heading">

        <div class="page-heading-copy">

            <span class="page-icon">

                <i class="bi bi-truck"></i>

            </span>


            <div>

                <p class="eyebrow mb-1">
                    HICE RENT MANAGEMENT
                </p>

                <h1 class="h3 mb-1">
                    Hice ভাড়া হিসাব
                </h1>

                <p class="text-muted mb-0">

                    গাড়ি, ড্রাইভার, খরচ ও মালিকের আয়

                </p>

            </div>

        </div>


        <div>

            <a
                href="index.php?page=hice/add"
                class="btn btn-light me-2"
            >

                <i class="bi bi-plus-circle"></i>

                নতুন ভাড়া

            </a>


            <a
                href="index.php?page=hice/report"
                class="btn btn-warning"
            >

                <i class="bi bi-bar-chart"></i>

                রিপোর্ট

            </a>

        </div>

    </div>



    <!-- =================================================
         QUICK STAT
    ================================================= -->

    <div class="mini-stat-grid">


        <!-- TOTAL RECORD -->

        <div class="mini-stat mi-total">

            <div class="mini-icon">

                <i class="bi bi-list-ul"></i>

            </div>

            <div>

                <div class="mini-value">

                    <?= bn_number($totalRecords) ?>

                </div>

                <div class="mini-label">

                    মোট এন্ট্রি

                </div>

            </div>

        </div>



        <!-- TODAY RENT -->

        <div class="mini-stat mi-rent">

            <div class="mini-icon">

                <i class="bi bi-cash-stack"></i>

            </div>

            <div>

                <div class="mini-value">

                    <?= bn_number(number_format($todayRent, 0)) ?>

                </div>

                <div class="mini-label">

                    আজকের মোট ভাড়া

                </div>

            </div>

        </div>



        <!-- EXPENSE -->

        <div class="mini-stat mi-expense">

            <div class="mini-icon">

                <i class="bi bi-wallet2"></i>

            </div>

            <div>

                <div class="mini-value">

                    <?= bn_number(number_format($todayExpense, 0)) ?>

                </div>

                <div class="mini-label">

                    আজকের খরচ

                </div>

            </div>

        </div>



        <!-- DRIVER -->

        <div class="mini-stat mi-driver">

            <div class="mini-icon">

                <i class="bi bi-person-badge"></i>

            </div>

            <div>

                <div class="mini-value">

                    <?= bn_number(number_format($todayDriver, 0)) ?>

                </div>

                <div class="mini-label">

                    ড্রাইভার ৩০%

                </div>

            </div>

        </div>



        <!-- OWNER -->

        <div class="mini-stat mi-owner">

            <div class="mini-icon">

                <i class="bi bi-person-check"></i>

            </div>

            <div>

                <div class="mini-value">

                    <?= bn_number(number_format($todayOwner, 0)) ?>

                </div>

                <div class="mini-label">

                    মালিকের আয়

                </div>

            </div>

        </div>


    </div>



    <!-- =================================================
         TODAY SUMMARY
    ================================================= -->

    <div class="panel mb-4">

        <div class="panel-header">

            <h2 class="h5 mb-0 section-title">

                <i class="bi bi-calendar-check"></i>

                আজকের হিসাব

            </h2>


            <span class="badge bg-success">

                <?= bn_number(date('d-m-Y')) ?>

            </span>

        </div>


        <div class="p-3">

            <div class="row g-3">


                <div class="col-md-3">

                    <div class="alert alert-primary mb-0">

                        <strong>
                            মোট ভাড়া
                        </strong>

                        <div class="fs-5 fw-bold">

                            <?= bn_number(number_format($todayRent, 2)) ?>

                            টাকা

                        </div>

                    </div>

                </div>



                <div class="col-md-3">

                    <div class="alert alert-danger mb-0">

                        <strong>
                            খরচ
                        </strong>

                        <div class="fs-5 fw-bold">

                            <?= bn_number(number_format($todayExpense, 2)) ?>

                            টাকা

                        </div>

                    </div>

                </div>



                <div class="col-md-3">

                    <div class="alert alert-warning mb-0">

                        <strong>
                            ড্রাইভার ৩০%
                        </strong>

                        <div class="fs-5 fw-bold">

                            <?= bn_number(number_format($todayDriver, 2)) ?>

                            টাকা

                        </div>

                    </div>

                </div>



                <div class="col-md-3">

                    <div class="alert alert-success mb-0">

                        <strong>
                            মালিকের আয়
                        </strong>

                        <div class="fs-5 fw-bold">

                            <?= bn_number(number_format($todayOwner, 2)) ?>

                            টাকা

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </div>



    <!-- =================================================
         TABLE
    ================================================= -->

    <section class="panel">


        <div class="panel-header">


            <h2 class="h5 mb-1 section-title">

                <i class="bi bi-truck"></i>

                Hice ভাড়া রেকর্ড

            </h2>


            <input

                class="form-control form-control-sm table-search"

                type="search"

                id="searchInput"

                placeholder="🔍 গাড়ি, ড্রাইভার বা তারিখ সার্চ করুন..."

            >


        </div>



        <div class="table-responsive">


            <table
                class="table align-middle mb-0"
                id="dataTable"
            >


                <thead>

                    <tr>

                        <th>#</th>

                        <th>তারিখ</th>

                        <th>গাড়ি</th>

                        <th>ড্রাইভার</th>

                        <th>মোট ভাড়া</th>

                        <th>খরচ</th>

                        <th>নিট আয়</th>

                        <th>ড্রাইভার ৩০%</th>

                        <th>মালিক</th>

                        <th>অ্যাকশন</th>

                    </tr>

                </thead>



                <tbody id="tableBody">


                <?php

                $i = 0;

                foreach ($records as $row):


                    $rent =
                        (float)($row['total_rent'] ?? 0);


                    $expense =
                        (float)($row['expense_amount'] ?? 0);


                    $net =
                        (float)($row['net_income'] ?? ($rent - $expense));


                    $driver =
                        (float)($row['driver_amount'] ?? ($net * .30));


                    $owner =
                        (float)($row['owner_amount'] ?? ($net - $driver));


                ?>


                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?= bn_number(++$i) ?>

                        </td>



                        <!-- DATE -->

                        <td>

                            <?= bn_number(
                                date(
                                    'd-m-Y',
                                    strtotime($row['collection_date'])
                                )
                            ) ?>

                        </td>



                        <!-- VEHICLE -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $row['vehicle_number'] ?? '-'
                                ) ?>

                            </strong>

                        </td>



                        <!-- DRIVER -->

                        <td>

                            <?= htmlspecialchars(
                                $row['driver_name'] ?? '-'
                            ) ?>


                            <?php if (!empty($row['driver_mobile'])): ?>

                                <br>

                                <small class="text-muted">

                                    <?= bn_number(
                                        htmlspecialchars(
                                            $row['driver_mobile']
                                        )
                                    ) ?>

                                </small>

                            <?php endif; ?>

                        </td>



                        <!-- RENT -->

                        <td>

                            <span class="text-primary fw-bold">

                                <?= bn_number(
                                    number_format($rent, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- EXPENSE -->

                        <td>

                            <span class="text-danger fw-semibold">

                                <?= bn_number(
                                    number_format($expense, 2)
                                ) ?>

                            </span>

                            <?php if (!empty($row['expense_note'])): ?>

                                <br>

                                <small class="text-muted">

                                    <?= htmlspecialchars(
                                        $row['expense_note']
                                    ) ?>

                                </small>

                            <?php endif; ?>

                        </td>



                        <!-- NET -->

                        <td>

                            <span class="badge bg-info text-dark">

                                <?= bn_number(
                                    number_format($net, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- DRIVER -->

                        <td>

                            <span class="badge bg-warning text-dark">

                                <?= bn_number(
                                    number_format($driver, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- OWNER -->

                        <td>

                            <span class="badge bg-success">

                                <?= bn_number(
                                    number_format($owner, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- ACTION -->

                        <td class="text-end">


                            <!-- VIEW -->

                            <a
                                href="index.php?page=hice/view&id=<?= (int)$row['id'] ?>"
                                class="btn btn-info btn-sm text-white"
                                title="দেখুন"
                            >

                                <i class="bi bi-eye"></i>

                            </a>



                            <!-- EDIT -->

                            <a
                                href="index.php?page=hice/add&edit=<?= (int)$row['id'] ?>"
                                class="btn btn-warning btn-sm"
                                title="সম্পাদনা"
                            >

                                <i class="bi bi-pencil-square"></i>

                            </a>



                            <!-- PRINT -->

                            <a
                                href="index.php?page=hice/report&id=<?= (int)$row['id'] ?>"
                                class="btn btn-success btn-sm"
                                title="রিপোর্ট"
                            >

                                <i class="bi bi-printer"></i>

                            </a>



                            <!-- DELETE -->

                            <a
                                href="index.php?page=hice/delete&id=<?= (int)$row['id'] ?>"
                                class="btn btn-danger btn-sm"
                                title="ডিলিট"

                                onclick="
                                    return confirm(
                                        'আপনি কি নিশ্চিতভাবে এই Hice হিসাবটি ডিলিট করতে চান?'
                                    );
                                "
                            >

                                <i class="bi bi-trash"></i>

                            </a>


                        </td>


                    </tr>


                <?php endforeach; ?>


                <?php if (empty($records)): ?>


                    <tr>

                        <td
                            colspan="10"
                            class="text-center py-5"
                        >

                            <div class="text-muted">

                                <i
                                    class="bi bi-inbox fs-1 d-block mb-2"
                                ></i>

                                এখনো কোনো Hice ভাড়া হিসাব নেই।

                            </div>


                            <a
                                href="index.php?page=hice/add"
                                class="btn btn-success mt-3"
                            >

                                <i class="bi bi-plus-circle"></i>

                                প্রথম হিসাব যোগ করুন

                            </a>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>


        </div>

    </section>


</div>



<!-- =====================================================
     SEARCH
===================================================== -->

<script>

const rows =
    document.querySelectorAll("#tableBody tr");

const searchInput =
    document.getElementById("searchInput");

let searchTimer;


function showDefault() {

    rows.forEach((row, index) => {

        row.style.display =
            index < 30 ? "" : "none";

    });

}


searchInput.addEventListener(
    "input",
    function() {

        clearTimeout(searchTimer);

        const value =
            this.value.toLowerCase().trim();


        searchTimer = setTimeout(() => {


            if (value === "") {

                showDefault();

                return;

            }


            rows.forEach(row => {

                const text =
                    row.innerText.toLowerCase();


                row.style.display =
                    text.includes(value)
                        ? ""
                        : "none";

            });


        }, 200);

    }
);


showDefault();

</script>

