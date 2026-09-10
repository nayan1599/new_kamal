<?php
// ======================================================
// সময় শেষ হওয়া গাড়ির তালিকা
// ======================================================

$stmt = $pdo->query("
    SELECT *
    FROM customer_records
    WHERE kisti_start_date IS NOT NULL
      AND kisti_start_date != ''
      AND total_kisti > 0
      AND status = 'active'
      AND DATE_ADD(
            kisti_start_date,
            INTERVAL total_kisti MONTH
          ) <= CURDATE()
    ORDER BY kisti_start_date ASC
");

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// Helper
// ======================================================

function safe_bn_number($value)
{
    if (function_exists('bn_number')) {
        return bn_number($value);
    }

    return $value;
}
?>


<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- ==================================================
         Page Header
    ================================================== -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history text-danger"></i>
            সময় শেষ হওয়া গাড়ির তালিকা
        </h4>

        <span class="badge bg-danger fs-6">
            মোট: <?= safe_bn_number(count($records)) ?> টি
        </span>

    </div>


    <!-- ==================================================
         Filter Section
    ================================================== -->
    <div class="card border-0 shadow-sm mb-3">

        <div class="card-body">

            <div class="row g-2 align-items-center">

                <!-- Title -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <div class="fw-semibold text-danger">
                        <i class="bi bi-filter"></i>
                        গাড়ি খুঁজুন
                    </div>

                </div>


                <!-- Day Search -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <input type="search" id="searchInput" class="form-control form-control-sm"
                        placeholder="🔍 তারিখের দিন..." inputmode="numeric" autocomplete="off">

                </div>


                <!-- From Date -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <input type="date" id="fromDate" class="form-control form-control-sm">

                </div>


                <!-- To Date -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <input type="date" id="toDate" class="form-control form-control-sm">

                </div>


                <!-- Status -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <select id="statusFilter" class="form-select form-select-sm">

                        <option value="">
                            সব স্ট্যাটাস
                        </option>

                        <option value="completed">
                            ✅ সময় শেষ
                        </option>

                    </select>

                </div>


                <!-- Buttons -->
                <div class="col-lg-2 col-md-3 col-sm-12">

                    <div class="d-flex gap-2">

                        <button type="button" class="btn btn-primary btn-sm" onclick="applyFilter()">
                            <i class="bi bi-search"></i>
                            খুঁজুন
                        </button>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilter()">
                            <i class="bi bi-arrow-clockwise"></i>
                            রিসেট
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         Search Information
    ================================================== -->

    <div id="searchInfo" class="small text-muted mb-2" style="display:none;"></div>


    <!-- ==================================================
         Table
    ================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="text-center">
                            #
                        </th>

                        <th>
                            শুরু তারিখ
                        </th>

                        <th>
                            কাস্টমার
                        </th>

                        <th>
                            ফোন
                        </th>

                        <th>
                            গাড়ি
                        </th>

                        <th class="text-center">
                            মোট সময়
                        </th>

                        <th class="text-center text-danger">
                            সময় শেষ
                        </th>

                        <th class="text-center">
                            স্ট্যাটাস
                        </th>

                        <th class="text-end">
                            অ্যাকশন
                        </th>

                    </tr>

                </thead>


                <tbody id="tableBody">

                    <?php if (empty($records)): ?>

                    <tr>

                        <td colspan="9" class="text-center py-5 text-muted">

                            <div class="mb-2">
                                <i class="bi bi-check-circle-fill text-success" style="font-size:40px;"></i>
                            </div>

                            <div class="fw-semibold">
                                বর্তমানে কোনো সময় শেষ হওয়া গাড়ি নেই।
                            </div>

                        </td>

                    </tr>

                    <?php else: ?>


                    <?php
                    $serial = 1;
                    ?>


                    <?php foreach ($records as $row): ?>

                    <?php

                    // ==================================================
                    // Database Data
                    // ==================================================

                    $startDate =
                        $row['kisti_start_date'] ?? '';

                    $monthlyAmount =
                        (float)($row['monthly_kisti'] ?? 0);

                    $totalPaid =
                        (float)($row['paid_amount'] ?? 0);

                    $totalPlanMonth =
                        (int)($row['total_kisti'] ?? 0);


                    // ==================================================
                    // Today
                    // ==================================================

                    $today = date('Y-m-d');


                    // ==================================================
                    // Start Date
                    // ==================================================

                    try {

                        if (!empty($startDate)) {

                            $start =
                                new DateTime($startDate);

                        } else {

                            $start =
                                new DateTime($today);

                        }

                    } catch (Exception $e) {

                        $start =
                            new DateTime($today);

                    }


                    // ==================================================
                    // End Date
                    // ==================================================

                    try {

                        $endDateObj =
                            clone $start;

                        $endDateObj->modify(
                            "+{$totalPlanMonth} months"
                        );

                        $endDate =
                            $endDateObj->format('Y-m-d');

                        $formattedEndDate =
                            $endDateObj->format('d-m-Y');

                    } catch (Exception $e) {

                        $endDate =
                            $today;

                        $formattedEndDate =
                            date('d-m-Y');

                    }


                    // ==================================================
                    // Start Date Format
                    // ==================================================

                    if (!empty($startDate)) {

                        $formattedStartDate =
                            date(
                                'd-m-Y',
                                strtotime($startDate)
                            );

                        $dataDate =
                            date(
                                'Y-m-d',
                                strtotime($startDate)
                            );

                        $dayNumber =
                            (int)date(
                                'd',
                                strtotime($startDate)
                            );

                    } else {

                        $formattedStartDate = '-';

                        $dataDate = '';

                        $dayNumber = 0;

                    }


                    // ==================================================
                    // Status
                    // ==================================================

                    $status = 'completed';


                    $statusText =
                        'সময় শেষ';

                    $statusClass =
                        'danger';

                    $statusIcon =
                        '⏰';


                    // ==================================================
                    // Remaining
                    // ==================================================

                    $remainingDuration =
                        'সময় শেষ';


                    ?>

                    <tr data-day="<?= $dayNumber ?>" data-date="<?= htmlspecialchars($dataDate) ?>"
                        data-status="completed">


                        <!-- Serial -->
                        <td class="text-center fw-semibold">
                            <?= safe_bn_number($serial++) ?>
                        </td>


                        <!-- Start Date -->
                        <td>

                            <?= safe_bn_number(
                                $formattedStartDate
                            ) ?>

                        </td>


                        <!-- Customer -->
                        <td>

                            <div class="fw-semibold">

                                <?= htmlspecialchars(
                                    $row['customer_name'] ?? '-'
                                ) ?>

                            </div>

                        </td>


                        <!-- Phone -->
                        <td>

                            <?= safe_bn_number(
                                htmlspecialchars(
                                    $row['customer_phone'] ?? '-'
                                )
                            ) ?>

                        </td>


                        <!-- Car -->
                        <td>

                            <span class="fw-bold">

                                <?= htmlspecialchars(
                                    $row['car_number'] ?? '-'
                                ) ?>

                            </span>

                        </td>


                        <!-- Total Duration -->
                        <td class="text-center">

                            <span class="badge bg-secondary">

                                <?= safe_bn_number(
                                    $totalPlanMonth
                                ) ?>

                                মাস

                            </span>

                        </td>


                        <!-- End Date -->
                        <td class="text-center">

                            <span class="badge bg-danger" title="কিস্তির সময় শেষ">

                                <i class="bi bi-calendar-x"></i>

                                <?= safe_bn_number(
                                    $formattedEndDate
                                ) ?>

                            </span>

                        </td>


                        <!-- Status -->
                        <td class="text-center">

                            <span class="badge bg-<?= $statusClass ?>">

                                <?= $statusIcon ?>

                                <?= $statusText ?>

                            </span>

                        </td>


                        <!-- Actions -->
                        <td class="text-end text-nowrap">


                            <!-- View -->
                            <a href="index.php?page=car/view&car_number=<?= urlencode(
                                    $row['car_number'] ?? ''
                                ) ?>" class="btn btn-info btn-sm text-white" title="দেখুন">

                                <i class="bi bi-eye"></i>

                            </a>


                            <!-- Edit -->
                            <a href="index.php?page=car/edit&id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm"
                                title="সম্পাদনা">

                                <i class="bi bi-pencil-square"></i>

                            </a>


                            <!-- Receipt -->
                            <a href="index.php?page=car/receipt&id=<?= (int)$row['id'] ?>"
                                class="btn btn-success btn-sm" title="রসিদ">

                                <i class="bi bi-receipt"></i>

                            </a>


                            <!-- Call Story -->
                            <a href="index.php?page=call_story/callstory&id=<?= (int)$row['id'] ?>"
                                class="btn btn-primary btn-sm" title="কল স্টোরি">

                                <i class="bi bi-telephone"></i>

                            </a>
                            <a href="index.php?page=car/delete&id=<?= (int)$row['id'] ?>" class="btn btn-danger btn-sm"
                                title="ডিলিট"
                                onclick="return confirm('আপনি কি নিশ্চিতভাবে এই গাড়িটি ডিলিট করতে চান?');">
                                <i class="bi bi-trash"></i>
                            </a>

                        </td>

                    </tr>


                    <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- =========================================================
     FILTER SCRIPT
========================================================= -->

<script>
document.addEventListener(
    "DOMContentLoaded",
    function() {

        const rows =
            document.querySelectorAll(
                "#tableBody tr"
            );


        const searchInput =
            document.getElementById(
                "searchInput"
            );


        const fromDate =
            document.getElementById(
                "fromDate"
            );


        const toDate =
            document.getElementById(
                "toDate"
            );


        const statusFilter =
            document.getElementById(
                "statusFilter"
            );


        const searchInfo =
            document.getElementById(
                "searchInfo"
            );


        // =====================================================
        // প্রথমে সর্বোচ্চ ১০টি দেখাবে
        // =====================================================

        function showDefault() {

            let visibleCount = 0;


            rows.forEach(function(row) {

                // Empty row হলে
                if (
                    row.querySelector(
                        "td[colspan]"
                    )
                ) {

                    row.style.display = "";

                    return;

                }


                if (visibleCount < 10) {

                    row.style.display = "";

                    visibleCount++;

                } else {

                    row.style.display = "none";

                }

            });


            searchInfo.style.display =
                "none";

        }


        // =====================================================
        // Apply Filter
        // =====================================================

        window.applyFilter = function() {

            let searchDay =
                searchInput.value.trim();


            let from =
                fromDate.value;


            let to =
                toDate.value;


            let status =
                statusFilter.value;


            // =================================================
            // Day Validate
            // =================================================

            let inputDay = null;


            if (searchDay !== "") {

                if (/^\d+$/.test(searchDay)) {

                    inputDay =
                        parseInt(
                            searchDay,
                            10
                        );


                    if (
                        inputDay < 1 ||
                        inputDay > 31
                    ) {

                        rows.forEach(
                            function(row) {

                                row.style.display =
                                    "none";

                            }
                        );


                        searchInfo.style.display =
                            "";


                        searchInfo.innerHTML =
                            "⚠️ ১ থেকে ৩১ এর মধ্যে দিন লিখুন।";


                        return;

                    }

                } else {

                    rows.forEach(
                        function(row) {

                            row.style.display =
                                "none";

                        }
                    );


                    searchInfo.style.display =
                        "";


                    searchInfo.innerHTML =
                        "⚠️ শুধু তারিখের দিন লিখুন। যেমন: 1, 2, 15";


                    return;

                }

            }


            // =================================================
            // Filter Rows
            // =================================================

            let visibleCount = 0;


            rows.forEach(function(row) {

                // Empty message row
                if (
                    row.querySelector(
                        "td[colspan]"
                    )
                ) {

                    row.style.display =
                        "none";

                    return;

                }


                let date =
                    row.getAttribute(
                        "data-date"
                    );


                let day =
                    parseInt(
                        row.getAttribute(
                            "data-day"
                        ),
                        10
                    );


                let rowStatus =
                    row.getAttribute(
                        "data-status"
                    );


                // -------------------------------------------------
                // Day Search
                // -------------------------------------------------

                let matchSearch = true;


                if (inputDay !== null) {

                    matchSearch =
                        (
                            day === inputDay
                        );

                }


                // -------------------------------------------------
                // Date Range
                // -------------------------------------------------

                let matchDate = true;


                if (
                    from !== "" &&
                    (
                        date === "" ||
                        date < from
                    )
                ) {

                    matchDate = false;

                }


                if (
                    to !== "" &&
                    (
                        date === "" ||
                        date > to
                    )
                ) {

                    matchDate = false;

                }


                // -------------------------------------------------
                // Status
                // -------------------------------------------------

                let matchStatus = true;


                if (
                    status !== "" &&
                    rowStatus !== status
                ) {

                    matchStatus = false;

                }


                // -------------------------------------------------
                // Final
                // -------------------------------------------------

                if (
                    matchSearch &&
                    matchDate &&
                    matchStatus
                ) {

                    row.style.display =
                        "";

                    visibleCount++;

                } else {

                    row.style.display =
                        "none";

                }

            });


            // =================================================
            // Search Information
            // =================================================

            if (
                searchDay !== "" ||
                from !== "" ||
                to !== "" ||
                status !== ""
            ) {

                searchInfo.style.display =
                    "";


                let message =
                    "মোট পাওয়া গেছে: " +
                    visibleCount +
                    " টি";


                if (inputDay !== null) {

                    message +=
                        " | তারিখ: " +
                        inputDay;

                }


                searchInfo.innerHTML =
                    message;

            } else {

                showDefault();

            }

        };


        // =====================================================
        // Reset
        // =====================================================

        window.resetFilter = function() {

            searchInput.value = "";

            fromDate.value = "";

            toDate.value = "";

            statusFilter.value = "";

            showDefault();

        };


        // =====================================================
        // Search Input
        // =====================================================

        searchInput.addEventListener(
            "input",
            function() {

                applyFilter();

            }
        );


        // =====================================================
        // From Date
        // =====================================================

        fromDate.addEventListener(
            "change",
            function() {

                applyFilter();

            }
        );


        // =====================================================
        // To Date
        // =====================================================

        toDate.addEventListener(
            "change",
            function() {

                applyFilter();

            }
        );


        // =====================================================
        // Status
        // =====================================================

        statusFilter.addEventListener(
            "change",
            function() {

                applyFilter();

            }
        );


        // =====================================================
        // Initial Load
        // =====================================================

        showDefault();

    }
);
</script>