<?php
// ======================================================
// সকল রেকর্ড Load
// ======================================================
// এখানে completed বাদ দেওয়া হয়নি,
// কারণ নিচের Status Filter দিয়ে running/completed দুটোই
// filter করা হবে।

$stmt = $pdo->query("
    SELECT *
    FROM customer_records
    ORDER BY created_at DESC
");

$records = $stmt->fetchAll();
?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- ==================================================
         Page Header
    ================================================== -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="fw-bold">
            📋 রেকর্ড তালিকা
        </h4>

        <span class="badge bg-dark">
            মোট: <?= count($records) ?>
        </span>

    </div>


    <!-- ==================================================
         Filter Section
    ================================================== -->
    <div class="row g-2 align-items-center mb-3">

        <!-- Title -->
        <div class="col-md-2 col-sm-12">

            <h2 class="h6 mb-0">
                <i class="bi bi-table"></i>
                সকল রেকর্ড
            </h2>

        </div>


        <!-- ==================================================
             Day Search
             এখানে শুধু 1 থেকে 31 পর্যন্ত দিন লিখবেন
             যেমন: 1, 2, 15, 30
             মাস/বছর ধরা হবে না
        ================================================== -->
        <div class="col-md-2 col-sm-12">

            <input
                type="search"
                id="searchInput"
                class="form-control form-control-sm"
                placeholder="🔍 তারিখের দিন..."
                inputmode="numeric"
                autocomplete="off"
            >

        </div>


        <!-- From Date -->
        <div class="col-md-2 col-sm-12">

            <input
                type="date"
                id="fromDate"
                class="form-control form-control-sm"
            >

        </div>


        <!-- To Date -->
        <div class="col-md-2 col-sm-12">

            <input
                type="date"
                id="toDate"
                class="form-control form-control-sm"
            >

        </div>


        <!-- Status -->
        <div class="col-md-2 col-sm-12">

            <select
                id="statusFilter"
                class="form-select form-select-sm"
            >

                <option value="">
                    সব স্ট্যাটাস
                </option>

                <option value="running">
                    চলমান
                </option>

                <option value="completed">
                    সময় শেষ
                </option>

                <option value="cancelled">
                    ফেরত
                </option>

            </select>

        </div>


        <!-- Buttons -->
        <div class="col-md-2 col-sm-12">

            <div class="d-flex gap-2 flex-wrap">

                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    onclick="applyFilter()"
                >
                    ফিল্টার
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="resetFilter()"
                >
                    রিসেট
                </button>

            </div>

        </div>

    </div>


    <!-- ==================================================
         Search Information
    ================================================== -->
    <div
        id="searchInfo"
        class="small text-muted mb-2"
        style="display:none;"
    ></div>


    <!-- ==================================================
         Table Section
    ================================================== -->
    <section class="panel mt-3">

        <div class="table-responsive">

            <table class="table table-bordered align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th style="min-width:40px">
                            #
                        </th>

                        <th>
                            তারিখ
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

                        <th>
                            মোট সময়
                        </th>

                        <th>
                            বাকি সময়
                        </th>

                        <th>
                            স্ট্যাটাস
                        </th>

                        <th>
                            অ্যাকশন
                        </th>

                    </tr>

                </thead>


                <tbody id="tableBody">

                <?php

                $i = 0;

                foreach ($records as $row):

                    // ==================================================
                    // Database Data
                    // ==================================================

                    $startDate = $row['kisti_start_date'];

                    $monthlyAmount =
                        $row['monthly_kisti'] ?? 0;

                    $totalPaid =
                        $row['paid_amount'] ?? 0;

                    $totalPlanMonth =
                        $row['total_kisti'] ?? 0;


                    // ==================================================
                    // Today
                    // ==================================================

                    $today = date('Y-m-d');


                    // ==================================================
                    // Start Date
                    // ==================================================

                    try {

                        $start = new DateTime($startDate);
                        $end   = new DateTime($today);

                        $diff = $start->diff($end);

                    } catch (Exception $e) {

                        $start = new DateTime($today);
                        $end   = new DateTime($today);

                        $diff = $start->diff($end);

                    }


                    // ==================================================
                    // Passed Months
                    // ==================================================

                    $passedMonths =
                        ($diff->y * 12) + $diff->m;

                    $passedDays =
                        $diff->d;


                    // 30 দিন = 1 মাস হিসেবে হিসাব
                    $passedTotalMonths =
                        $passedMonths + ($passedDays / 30);


                    // ==================================================
                    // Total Duration
                    // ==================================================

                    $totalDuration =
                        $totalPlanMonth . " মাস";


                    // ==================================================
                    // Remaining Duration
                    // ==================================================

                    $remainingMonths =
                        max(
                            0,
                            $totalPlanMonth - $passedTotalMonths
                        );


                    $remMonths =
                        floor($remainingMonths);


                    $remDays =
                        round(
                            ($remainingMonths - $remMonths) * 30
                        );


                    // যদি 30 দিন হয়ে যায়
                    if ($remDays >= 30) {

                        $remMonths++;
                        $remDays = 0;

                    }


                    $remainingDuration =
                        $remMonths . " মাস " .
                        $remDays . " দিন";


                    // ==================================================
                    // Status
                    // ==================================================

                    // ==================================================
                    // Status
                    // ==================================================
                    // Database-এ cancelled থাকলে সেটি "ফেরত" হিসেবে থাকবে।
                    // অন্যথায় remaining duration অনুযায়ী completed/running হবে।

                    $status =
                        (($row['status'] ?? '') === 'cancelled')
                            ? 'cancelled'
                            : (($remainingMonths <= 0)
                                ? 'completed'
                                : 'running');


                    // ==================================================
                    // Date
                    // ==================================================

                    $formattedDate =
                        date(
                            'd-m-Y',
                            strtotime($startDate)
                        );


                    $dataDate =
                        date(
                            'Y-m-d',
                            strtotime($startDate)
                        );


                    // ==================================================
                    // Day Only
                    // ==================================================

                    $dayNumber =
                        (int) date(
                            'd',
                            strtotime($startDate)
                        );

                ?>

                    <tr
                        data-day="<?= $dayNumber ?>"
                        data-date="<?= $dataDate ?>"
                        data-status="<?= $status ?>"
                    >

                        <!-- ==================================================
                             Serial
                        ================================================== -->
                        <td>
                            <?= ++$i ?>
                        </td>


                        <!-- ==================================================
                             Date
                        ================================================== -->
                        <td data-date="<?= $dataDate ?>">

                            <?= bn_number($formattedDate) ?>

                        </td>


                        <!-- ==================================================
                             Customer
                        ================================================== -->
                        <td>

                            <?= htmlspecialchars(
                                $row['customer_name'] ?? '-'
                            ) ?>

                        </td>


                        <!-- ==================================================
                             Phone
                        ================================================== -->
                        <td>

                            <?= bn_number(
                                htmlspecialchars(
                                    $row['customer_phone'] ?? '-'
                                )
                            ) ?>

                        </td>


                        <!-- ==================================================
                             Car
                        ================================================== -->
                        <td>

                            <?= htmlspecialchars(
                                $row['car_number'] ?? '-'
                            ) ?>

                        </td>


                        <!-- ==================================================
                             Total Duration
                        ================================================== -->
                        <td class="text-success fw-semibold">

                            <?= bn_number(
                                $totalDuration
                            ) ?>

                        </td>


                        <!-- ==================================================
                             Remaining Duration
                        ================================================== -->
                        <td class="text-danger fw-semibold">

                            <?= bn_number(
                                $remainingDuration
                            ) ?>

                        </td>


                        <!-- ==================================================
                             Status
                        ================================================== -->
                        <td>

                            <span class="badge bg-<?=
                                ($status == 'completed')
                                    ? 'danger'
                                    : (($status == 'cancelled')
                                        ? 'secondary'
                                        : 'warning')
                            ?>">
                                <?= ($status == 'completed')
                                    ? 'সময় শেষ'
                                    : (($status == 'cancelled')
                                        ? 'ফেরত'
                                        : 'চলমান')
                                ?>
                            </span>

                        </td>


                        <!-- ==================================================
                             Actions
                        ================================================== -->
                        <td class="text-end">

                            <!-- View -->
                            <a
                                href="index.php?page=car/view&car_number=<?= urlencode(
                                    $row['car_number'] ?? ''
                                ) ?>"
                                class="btn btn-info btn-sm text-white"
                            >
                                দেখুন
                            </a>


                            <!-- Edit -->
                            <a
                                href="index.php?page=car/edit&id=<?= (int)$row['id'] ?>"
                                class="btn btn-warning btn-sm"
                            >
                                সম্পাদনা
                            </a>


                            <!-- Receipt -->
                            <a
                                href="index.php?page=car/receipt&id=<?= (int)$row['id'] ?>"
                                class="btn btn-success btn-sm"
                            >
                                রসিদ
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- =========================================================
     FILTER SCRIPT
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const rows =
        document.querySelectorAll("#tableBody tr");

    const searchInput =
        document.getElementById("searchInput");

    const fromDate =
        document.getElementById("fromDate");

    const toDate =
        document.getElementById("toDate");

    const statusFilter =
        document.getElementById("statusFilter");

    const searchInfo =
        document.getElementById("searchInfo");


    // =========================================================
    // প্রথমে ১০টি দেখাবে
    // =========================================================

    function showDefault() {

        let visibleCount = 0;

        rows.forEach(function (row) {

            if (visibleCount < 10) {

                row.style.display = "";

                visibleCount++;

            } else {

                row.style.display = "none";

            }

        });

        searchInfo.style.display = "none";
    }


    // =========================================================
    // Apply Filter
    // =========================================================

    window.applyFilter = function () {

        /*
         * এখানে searchInput থেকে শুধু দিন নেওয়া হবে।
         *
         * উদাহরণ:
         *
         * 1  = 01 তারিখ
         * 2  = 02 তারিখ
         * 15 = 15 তারিখ
         *
         * মাস এবং বছর কোনোভাবেই বিবেচনা করা হবে না।
         */

        let searchDay =
            searchInput.value.trim();


        let from =
            fromDate.value;


        let to =
            toDate.value;


        let status =
            statusFilter.value;


        // =====================================================
        // Search Day Validate
        // =====================================================

        let inputDay = null;


        if (searchDay !== "") {

            // শুধু সংখ্যা হতে হবে
            if (/^\d+$/.test(searchDay)) {

                inputDay =
                    parseInt(searchDay, 10);


                // 1 থেকে 31 এর বাইরে হলে কিছু দেখাবে না
                if (
                    inputDay < 1 ||
                    inputDay > 31
                ) {

                    rows.forEach(function (row) {

                        row.style.display = "none";

                    });

                    searchInfo.style.display = "";

                    searchInfo.innerHTML =
                        "⚠️ ১ থেকে ৩১ এর মধ্যে দিন লিখুন।";

                    return;
                }

            } else {

                rows.forEach(function (row) {

                    row.style.display = "none";

                });

                searchInfo.style.display = "";

                searchInfo.innerHTML =
                    "⚠️ শুধু তারিখের দিন লিখুন। যেমন: 1, 2, 15";

                return;
            }
        }


        // =====================================================
        // Filter Rows
        // =====================================================

        let visibleCount = 0;


        rows.forEach(function (row) {

            // -------------------------------------------------
            // Row Data
            // -------------------------------------------------

            let date =
                row.getAttribute("data-date");


            let day =
                parseInt(
                    row.getAttribute("data-day"),
                    10
                );


            let rowStatus =
                row.getAttribute("data-status");


            // -------------------------------------------------
            // Day Search
            // -------------------------------------------------

            let matchSearch = true;


            if (inputDay !== null) {

                matchSearch =
                    (day === inputDay);

            }


            // -------------------------------------------------
            // Date Range
            // -------------------------------------------------

            let matchDate = true;


            if (from !== "" && date < from) {

                matchDate = false;

            }


            if (to !== "" && date > to) {

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

                row.style.display = "";

                visibleCount++;

            } else {

                row.style.display = "none";

            }

        });


        // =====================================================
        // Search Information
        // =====================================================

        if (
            searchDay !== "" ||
            from !== "" ||
            to !== "" ||
            status !== ""
        ) {

            searchInfo.style.display = "";

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

            searchInfo.style.display =
                "none";

        }

    };


    // =========================================================
    // Reset Filter
    // =========================================================

    window.resetFilter = function () {

        searchInput.value = "";

        fromDate.value = "";

        toDate.value = "";

        statusFilter.value = "";

        showDefault();

    };


    // =========================================================
    // Search Input
    // =========================================================

    searchInput.addEventListener(
        "input",
        function () {

            /*
             * এখানে সরাসরি filter হবে।
             *
             * 1 লিখলেই ১ তারিখ
             * 2 লিখলেই ২ তারিখ
             * 15 লিখলেই ১৫ তারিখ
             */

            applyFilter();

        }
    );


    // =========================================================
    // From Date
    // =========================================================

    fromDate.addEventListener(
        "change",
        function () {

            applyFilter();

        }
    );


    // =========================================================
    // To Date
    // =========================================================

    toDate.addEventListener(
        "change",
        function () {

            applyFilter();

        }
    );


    // =========================================================
    // Status
    // =========================================================

    statusFilter.addEventListener(
        "change",
        function () {

            applyFilter();

        }
    );


    // =========================================================
    // Initial Load
    // =========================================================

    showDefault();

});

</script>