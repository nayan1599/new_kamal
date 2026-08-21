<?php

$stmt = $pdo->query("
    SELECT c.*
    FROM customer_records AS c

    WHERE NOT EXISTS (
        SELECT 1
        FROM kisti_payments AS k
        WHERE k.car_number = c.car_number

        AND k.payment_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')

        AND k.payment_date < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-%m-01'),
            INTERVAL 1 MONTH
        )
    )

    ORDER BY c.created_at DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid mt-4">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">

        <!-- Title -->

        <div>

            <h4 class="mb-1">
                📋 এই মাসে কিস্তি না দেওয়া গ্রাহক
            </h4>

            <small class="text-muted">
                যাদের এই মাসে কোনো payment পাওয়া যায়নি
            </small>

        </div>


        <!-- Total -->

        <div>

            <span class="badge bg-danger fs-6">
                মোট:
                <span id="totalCount">
                    <?= count($rows) ?>
                </span>
                জন
            </span>

        </div>


        <!-- Search + Print -->

        <div class="d-flex gap-2">

            <input
                type="text"
                id="searchInput"
                class="form-control form-control-sm"
                style="width:300px;"
                placeholder="নাম, গাড়ি নং, ফোন দিয়ে সার্চ করুন..."
                autocomplete="off"
            >


            <button
                type="button"
                class="btn btn-light btn-sm"
                onclick="window.print()"
            >

                <i class="fas fa-print"></i>
                প্রিন্ট

            </button>

        </div>

    </div>



    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <!-- IMPORTANT:
                     table id added here
                -->

                <table
                    id="paymentTable"
                    class="table table-bordered table-hover align-middle text-center"
                >

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>
                                গাড়ি নম্বর
                            </th>

                            <th>
                                গ্রাহকের নাম
                            </th>

                            <th>
                                মোবাইল
                            </th>

                            <th class="text-danger">
                                বকেয়া
                            </th>

                            <th>
                                অ্যাকশন
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($rows)): ?>


                        <?php $sl = 1; ?>


                        <?php foreach ($rows as $row): ?>


                            <tr class="payment-row">

                                <!-- Serial -->

                                <td class="serial-number">

                                    <?= $sl++ ?>

                                </td>


                                <!-- Car Number -->

                                <td class="fw-bold">

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                    ) ?>

                                </td>


                                <!-- Customer Name -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['customer_name']
                                    ) ?>

                                </td>


                                <!-- Mobile -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['customer_phone']
                                    ) ?>

                                </td>


                                <!-- Due -->

                                <td
                                    class="text-danger fw-bold"
                                >

                                    ৳

                                    <?= number_format(
                                        (float)$row['monthly_kisti'],
                                        2
                                    ) ?>

                                </td>


                                <!-- Action -->

                                <td>

                                    <a
                                        href="index.php?page=car/view&car_number=<?= urlencode($row['car_number']) ?>"
                                        class="btn btn-info btn-sm text-white"
                                    >

                                        👁️ দেখুন

                                    </a>


                                    <a
                                        href="index.php?page=payment/add&car_number=<?= urlencode($row['car_number']) ?>"
                                        class="btn btn-success btn-sm"
                                    >

                                        💰 কিস্তি নিন

                                    </a>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr id="noDataRow">

                            <td
                                colspan="6"
                                class="py-4"
                            >

                                <div
                                    class="text-success fw-bold fs-5"
                                >

                                    ✅ এই মাসে সবাই কিস্তি দিয়েছে

                                </div>


                                <div class="text-muted">

                                    এই মাসে কোনো কিস্তি বাকি নেই।

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>


            <!-- Search result message -->

            <div
                id="searchNoResult"
                class="text-center text-danger fw-bold py-4"
                style="display:none;"
            >

                🔍 কোনো গ্রাহক পাওয়া যায়নি।

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     LIVE SEARCH
========================================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("searchInput");

    const table =
        document.getElementById("paymentTable");

    const searchNoResult =
        document.getElementById("searchNoResult");

    const totalCount =
        document.getElementById("totalCount");


    // যদি table না থাকে তাহলে stop
    if (!searchInput || !table) {
        return;
    }


    // সব customer row
    const rows =
        table.querySelectorAll(
            "tbody tr.payment-row"
        );


    // =====================================================
    // SEARCH FUNCTION
    // =====================================================

    searchInput.addEventListener(
        "input",
        function () {

            // Search text
            const searchText =
                this.value
                    .toLowerCase()
                    .trim();


            let visibleCount = 0;


            // সব row check
            rows.forEach(function (row) {

                const rowText =
                    row.textContent
                        .toLowerCase()
                        .trim();


                // Match হলে show
                if (
                    searchText === "" ||
                    rowText.includes(searchText)
                ) {

                    row.style.display = "";

                    visibleCount++;

                } else {

                    row.style.display = "none";

                }

            });


            // =================================================
            // SERIAL NUMBER UPDATE
            // =================================================

            let serial = 1;

            rows.forEach(function (row) {

                if (row.style.display !== "none") {

                    const serialCell =
                        row.querySelector(
                            ".serial-number"
                        );

                    if (serialCell) {

                        serialCell.textContent =
                            serial++;

                    }

                }

            });


            // =================================================
            // TOTAL COUNT UPDATE
            // =================================================

            if (totalCount) {

                totalCount.textContent =
                    visibleCount;

            }


            // =================================================
            // NO RESULT MESSAGE
            // =================================================

            if (visibleCount === 0) {

                searchNoResult.style.display =
                    "block";

            } else {

                searchNoResult.style.display =
                    "none";

            }

        }
    );


    // =====================================================
    // ESC চাপলে SEARCH CLEAR
    // =====================================================

    searchInput.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                this.value = "";

                this.dispatchEvent(
                    new Event("input")
                );

                this.focus();

            }

        }
    );

});

</script>