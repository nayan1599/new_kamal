
<?php

$today = date('Y-m-d');


// ==========================================================
// আজকের ভাড়া Summary
// ==========================================================

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_entry,

        COALESCE(SUM(rent_amount), 0) AS total_amount,

        COALESCE(
            SUM(
                CASE
                    WHEN LOWER(payment_status) = 'paid'
                    THEN rent_amount
                    ELSE 0
                END
            ), 0
        ) AS paid_amount,

        COALESCE(
            SUM(
                CASE
                    WHEN LOWER(payment_status) = 'due'
                    THEN rent_amount
                    ELSE 0
                END
            ), 0
        ) AS due_amount,

        COALESCE(
            SUM(
                CASE
                    WHEN LOWER(payment_status) NOT IN ('paid','due')
                    OR payment_status IS NULL
                    THEN rent_amount
                    ELSE 0
                END
            ), 0
        ) AS pending_amount

    FROM rents
    WHERE DATE(created_at) = ?
");

$stmt->execute([$today]);

$summary = $stmt->fetch(PDO::FETCH_ASSOC);


$totalEntry     = (int)($summary['total_entry'] ?? 0);
$totalAmount    = (float)($summary['total_amount'] ?? 0);
$paidAmount     = (float)($summary['paid_amount'] ?? 0);
$dueAmount      = (float)($summary['due_amount'] ?? 0);
$pendingAmount  = (float)($summary['pending_amount'] ?? 0);


// ==========================================================
// আজকের সব রেকর্ড
// ==========================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM rents
    WHERE DATE(created_at) = ?
    ORDER BY created_at DESC
");

$stmt->execute([$today]);

$rents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- ======================================================
         HEADER
    ======================================================= -->

    <div class="row align-items-center mb-4">

        <div class="col-md-8 col-sm-12 mb-3 mb-md-0">

            <div class="page-heading-copy d-flex align-items-center gap-3">

                <span class="page-icon-big">

                    <i class="bi bi-car-front-fill"></i>

                </span>

                <div>

                    <p class="eyebrow mb-1">
                        আজকের রেকর্ড
                    </p>

                    <h1 class="h3 fw-bold mb-1">
                        গাড়ি ভাড়া Dashboard
                    </h1>

                    <p class="text-muted mb-0">

                        আজকের গাড়ি ভাড়ার সম্পূর্ণ হিসাব

                        <span class="fw-semibold text-primary ms-1">

                            <?= bn_number(
                                date('d-m-Y')
                            ) ?>

                        </span>

                    </p>

                </div>

            </div>

        </div>


        <div class="col-md-4 col-sm-12 text-md-end">

            <a
                href="index.php?page=rent/collection"
                class="btn btn-success"
            >

                <i class="bi bi-plus-circle me-1"></i>

                নতুন এন্ট্রি

            </a>


            <a
                href="index.php?page=rent/due"
                class="btn btn-danger"
            >

                <i class="bi bi-exclamation-circle me-1"></i>

                বকেয়া ভাড়া

            </a>

        </div>

    </div>


    <!-- ======================================================
         SUMMARY CARDS
    ======================================================= -->

    <div class="row g-3 mb-4">


        <!-- মোট ভাড়া -->

        <div class="col-xl-3 col-md-6">

            <div class="rent-card rent-card-blue">

                <div class="rent-card-icon">

                    <i class="bi bi-cash-stack"></i>

                </div>

                <div>

                    <div class="rent-card-label">
                        আজকের মোট ভাড়া
                    </div>

                    <div class="rent-card-value">

                        ৳<?= bn_number(
                            number_format(
                                $totalAmount,
                                2
                            )
                        ) ?>

                    </div>

                    <small class="text-muted">

                        <?= bn_number($totalEntry) ?>
                        টি এন্ট্রি

                    </small>

                </div>

            </div>

        </div>


        <!-- Paid -->

        <div class="col-xl-3 col-md-6">

            <div class="rent-card rent-card-green">

                <div class="rent-card-icon">

                    <i class="bi bi-check-circle-fill"></i>

                </div>

                <div>

                    <div class="rent-card-label">
                        Paid ভাড়া
                    </div>

                    <div class="rent-card-value text-success">

                        ৳<?= bn_number(
                            number_format(
                                $paidAmount,
                                2
                            )
                        ) ?>

                    </div>

                    <small class="text-success">
                        পরিশোধিত
                    </small>

                </div>

            </div>

        </div>


        <!-- Due -->

        <div class="col-xl-3 col-md-6">

            <div class="rent-card rent-card-red">

                <div class="rent-card-icon">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <div>

                    <div class="rent-card-label">
                        বকেয়া ভাড়া
                    </div>

                    <div class="rent-card-value text-danger">

                        ৳<?= bn_number(
                            number_format(
                                $dueAmount,
                                2
                            )
                        ) ?>

                    </div>

                    <small class="text-danger">
                        Due
                    </small>

                </div>

            </div>

        </div>


        <!-- Pending -->

        <div class="col-xl-3 col-md-6">

            <div class="rent-card rent-card-orange">

                <div class="rent-card-icon">

                    <i class="bi bi-clock-fill"></i>

                </div>

                <div>

                    <div class="rent-card-label">
                        Pending
                    </div>

                    <div class="rent-card-value text-warning">

                        ৳<?= bn_number(
                            number_format(
                                $pendingAmount,
                                2
                            )
                        ) ?>

                    </div>

                    <small class="text-muted">
                        অপেক্ষমাণ
                    </small>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         TODAY LIST
    ======================================================= -->

    <section class="panel shadow-sm border-0">


        <!-- Panel Header -->

        <div class="panel-header-custom">

            <div>

                <h2 class="h5 mb-1 fw-bold">

                    <i class="bi bi-list-ul text-primary me-2"></i>

                    আজকের গাড়ি ভাড়া লিস্ট

                </h2>

                <small class="text-muted">

                    শুধুমাত্র আজকের রেকর্ড দেখানো হচ্ছে

                </small>

            </div>


            <div class="d-flex align-items-center gap-2">

                <!-- Search -->

                <div class="search-wrapper">

                    <i class="bi bi-search"></i>

                    <input
                        class="form-control form-control-sm"
                        type="search"
                        id="searchInput"
                        placeholder="নাম, ফোন বা গাড়ি সার্চ..."
                    >

                </div>


                <!-- Print -->

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    onclick="window.print()"
                    title="প্রিন্ট"
                >

                    <i class="bi bi-printer"></i>

                </button>

            </div>

        </div>


        <!-- ==================================================
             TABLE
        =================================================== -->

        <div class="table-responsive">

            <table
                class="table table-hover align-middle mb-0 custom-table"
                id="dataTable"
            >

                <thead>

                    <tr>

                        <th class="text-center">
                            #
                        </th>

                        <th>
                            সময়
                        </th>

                        <th>
                            কাস্টমার
                        </th>

                        <th>
                            ফোন
                        </th>

                        <th>
                            গাড়ির নম্বর
                        </th>

                        <th class="text-end">
                            ভাড়া
                        </th>

                        <th class="text-center">
                            স্ট্যাটাস
                        </th>

                        <th>
                            নোট
                        </th>

                        <th class="text-center">
                            একশন
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (empty($rents)): ?>

                    <tr>

                        <td
                            colspan="9"
                            class="text-center py-5"
                        >

                            <div class="empty-state">

                                <i class="bi bi-car-front"></i>

                                <h6 class="mt-3 mb-1">
                                    আজ কোনো ভাড়া এন্ট্রি নেই
                                </h6>

                                <p class="text-muted mb-3">
                                    আজকের নতুন ভাড়া এন্ট্রি এখানে দেখা যাবে।
                                </p>

                                <a
                                    href="index.php?page=rent/collection"
                                    class="btn btn-success btn-sm"
                                >

                                    <i class="bi bi-plus-circle me-1"></i>

                                    নতুন এন্ট্রি

                                </a>

                            </div>

                        </td>

                    </tr>


                <?php else: ?>


                    <?php $serial = 1; ?>


                    <?php foreach ($rents as $row): ?>


                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $row['payment_status']
                                    ?? ''
                                )
                            );


                        if ($status === 'paid') {

                            $statusText = 'PAID';

                            $statusClass = 'success';

                            $statusIcon = 'check-circle-fill';

                        } elseif ($status === 'due') {

                            $statusText = 'DUE';

                            $statusClass = 'danger';

                            $statusIcon = 'exclamation-circle-fill';

                        } else {

                            $statusText = 'PENDING';

                            $statusClass = 'warning';

                            $statusIcon = 'clock-fill';

                        }


                        $rentAmount =
                            (float)(
                                $row['rent_amount']
                                ?? 0
                            );

                        ?>


                        <tr>


                            <!-- Serial -->

                            <td class="text-center">

                                <span class="serial-badge">

                                    <?= bn_number(
                                        $serial++
                                    ) ?>

                                </span>

                            </td>


                            <!-- Time -->

                            <td>

                                <span class="time-badge">

                                    <i class="bi bi-clock me-1"></i>

                                    <?= bn_number(
                                        date(
                                            'h:i A',
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    ) ?>

                                </span>

                            </td>


                            <!-- Customer -->

                            <td>

                                <div class="fw-semibold">

                                    <?= htmlspecialchars(
                                        $row['customer_name']
                                        ?? '—'
                                    ) ?>

                                </div>

                            </td>


                            <!-- Phone -->

                            <td>

                                <?php if (!empty($row['customer_phone'])): ?>

                                    <a
                                        href="tel:<?= htmlspecialchars(
                                            $row['customer_phone']
                                        ) ?>"
                                        class="phone-link"
                                    >

                                        <i class="bi bi-telephone-fill me-1"></i>

                                        <?= bn_number(
                                            htmlspecialchars(
                                                $row['customer_phone']
                                            )
                                        ) ?>

                                    </a>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>


                            <!-- Car Number -->

                            <td>

                                <span class="car-number">

                                    <i class="bi bi-car-front-fill me-1"></i>

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                        ?? '—'
                                    ) ?>

                                </span>

                            </td>


                            <!-- Amount -->

                            <td class="text-end">

                                <strong class="amount-text">

                                    ৳<?= bn_number(
                                        number_format(
                                            $rentAmount,
                                            2
                                        )
                                    ) ?>

                                </strong>

                            </td>


                            <!-- Status -->

                            <td class="text-center">

                                <span
                                    class="badge bg-<?= $statusClass ?>"
                                >

                                    <i class="bi bi-<?= $statusIcon ?> me-1"></i>

                                    <?= $statusText ?>

                                </span>

                            </td>


                            <!-- Note -->

                            <td>

                                <span
                                    class="text-muted small"
                                    title="<?= htmlspecialchars(
                                        $row['note'] ?? ''
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $row['note'] ?? '—'
                                    ) ?>

                                </span>

                            </td>


                            <!-- Action -->

                            <td class="text-center text-nowrap">

                                <?php if ($status === 'due'): ?>

                                    <a
                                        href="index.php?page=rent/edit&id=<?= (int)$row['id'] ?>"
                                        class="btn btn-sm btn-outline-success"
                                        title="সম্পাদনা"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                <?php endif; ?>


                                <a
                                    href="index.php?page=rent/edit&id=<?= (int)$row['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="দেখুন / এডিট"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- ==========================================================
     SEARCH
========================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const searchInput =
            document.getElementById(
                "searchInput"
            );


        const table =
            document.getElementById(
                "dataTable"
            );


        if (!searchInput || !table) {
            return;
        }


        searchInput.addEventListener(
            "keyup",
            function () {

                const value =
                    this.value
                    .toLowerCase()
                    .trim();


                const rows =
                    table.querySelectorAll(
                        "tbody tr"
                    );


                rows.forEach(
                    function (row) {

                        const text =
                            row.innerText
                            .toLowerCase();


                        row.style.display =
                            text.includes(value)
                            ? ""
                            : "none";

                    }
                );

            }
        );

    }
);

</script>


<!-- ==========================================================
     STYLE
========================================================== -->

<style>

/* ==========================================================
   Header
========================================================== */

.page-icon-big {

    width: 58px;

    height: 58px;

    min-width: 58px;

    border-radius: 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #e8f1ff;

    color: #0d6efd;

    font-size: 26px;

}


.eyebrow {

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 1px;

    color: #6c757d;

    font-weight: 700;

}


/* ==========================================================
   Cards
========================================================== */

.rent-card {

    background: #fff;

    border-radius: 16px;

    padding: 20px;

    min-height: 125px;

    display: flex;

    align-items: center;

    gap: 15px;

    border: 1px solid #eee;

    box-shadow:
        0 5px 20px rgba(0,0,0,.05);

    transition:
        all .2s ease;

}


.rent-card:hover {

    transform:
        translateY(-4px);

    box-shadow:
        0 10px 28px rgba(0,0,0,.09);

}


.rent-card-icon {

    width: 55px;

    height: 55px;

    min-width: 55px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 24px;

}


.rent-card-blue .rent-card-icon {

    background: #e8f1ff;

    color: #0d6efd;

}


.rent-card-green .rent-card-icon {

    background: #e8f7ef;

    color: #198754;

}


.rent-card-red .rent-card-icon {

    background: #fdebed;

    color: #dc3545;

}


.rent-card-orange .rent-card-icon {

    background: #fff4df;

    color: #fd7e14;

}


.rent-card-label {

    font-size: 13px;

    color: #6c757d;

    margin-bottom: 3px;

}


.rent-card-value {

    font-size: 23px;

    font-weight: 800;

    line-height: 1.3;

}


/* ==========================================================
   Panel
========================================================== */

.panel {

    background: #fff;

    border-radius: 16px;

    overflow: hidden;

    border: 1px solid #eee;

}


.panel-header-custom {

    padding: 18px 20px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    border-bottom: 1px solid #eee;

    background: #fff;

}


/* ==========================================================
   Search
========================================================== */

.search-wrapper {

    position: relative;

}


.search-wrapper i {

    position: absolute;

    left: 11px;

    top: 50%;

    transform:
        translateY(-50%);

    color: #777;

    z-index: 2;

}


.search-wrapper input {

    width: 280px;

    padding-left: 33px;

    border-radius: 8px;

}


/* ==========================================================
   Table
========================================================== */

.custom-table th {

    background: #f8f9fa;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    padding: 13px 12px;

}


.custom-table td {

    font-size: 13px;

    padding: 13px 12px;

    white-space: nowrap;

}


.custom-table tbody tr {

    transition:
        background .15s ease;

}


.custom-table tbody tr:hover {

    background:
        rgba(13,110,253,.035);

}


/* ==========================================================
   Table Elements
========================================================== */

.serial-badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 30px;

    height: 30px;

    border-radius: 8px;

    background: #f1f3f5;

    font-weight: 700;

}


.time-badge {

    color: #6c757d;

    font-size: 12px;

}


.phone-link {

    color: #198754;

    text-decoration: none;

    font-weight: 600;

}


.phone-link:hover {

    text-decoration: underline;

}


.car-number {

    background: #f1f3f5;

    padding: 6px 10px;

    border-radius: 7px;

    font-weight: 600;

}


.amount-text {

    color: #198754;

    font-size: 14px;

}


/* ==========================================================
   Empty
========================================================== */

.empty-state {

    padding: 20px;

}


.empty-state > i {

    font-size: 50px;

    color: #adb5bd;

}


/* ==========================================================
   Mobile
========================================================== */

@media(max-width: 768px) {

    .panel-header-custom {

        flex-direction: column;

        align-items: stretch;

    }


    .search-wrapper input {

        width: 100%;

    }


    .panel-header-custom .d-flex {

        width: 100%;

    }


    .page-icon-big {

        width: 48px;

        height: 48px;

        min-width: 48px;

        font-size: 21px;

    }


    .rent-card-value {

        font-size: 20px;

    }

}


/* ==========================================================
   Print
========================================================== */

@media print {

    .btn,
    .search-wrapper,
    .page-icon-big {

        display: none !important;

    }


    .panel,
    .rent-card {

        box-shadow: none !important;

        border: 1px solid #ddd !important;

    }

}

</style>

