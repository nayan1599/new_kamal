<?php

// =====================================================
// CALL REPORT
// =====================================================

$today = date('Y-m-d');


// =====================================================
// DATE FILTER
// =====================================================

$from_date = $_GET['from_date'] ?? $today;
$to_date   = $_GET['to_date'] ?? $today;


// =====================================================
// SAFE DATE
// =====================================================

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) {
    $from_date = $today;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
    $to_date = $today;
}


// =====================================================
// FETCH CALL REPORT
// =====================================================
//
// created_at ধরে report করা হচ্ছে।
// আপনার call_stories-এ যদি created_at না থাকে,
// তাহলে নিচের DATE(created_at) পরিবর্তন করতে হবে।
//

$sql = "
    SELECT *
    FROM call_stories
    WHERE DATE(created_at) BETWEEN :from_date AND :to_date
    ORDER BY created_at DESC, id DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':from_date' => $from_date,
    ':to_date'   => $to_date
]);

$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// SUMMARY
// =====================================================

$total_calls = count($calls);

$connected = 0;
$not_connected = 0;
$busy = 0;
$switched_off = 0;
$wrong_number = 0;

$total_attempt = 0;

$total_followup = 0;
$total_promise = 0;


// =====================================================
// CALL STATUS COUNT
// =====================================================

foreach ($calls as $row) {

    $status = strtolower(
        trim($row['call_status'] ?? '')
    );


    // -----------------------------
    // CONNECTED
    // -----------------------------

    if ($status === 'connected') {

        $connected++;

    }


    // -----------------------------
    // NOT CONNECTED
    // -----------------------------

    elseif ($status === 'not_connected') {

        $not_connected++;

    }


    // -----------------------------
    // BUSY
    // -----------------------------

    elseif ($status === 'busy') {

        $busy++;

    }


    // -----------------------------
    // SWITCHED OFF
    // -----------------------------

    elseif ($status === 'switched_off') {

        $switched_off++;

    }


    // -----------------------------
    // WRONG NUMBER
    // -----------------------------

    elseif ($status === 'wrong_number') {

        $wrong_number++;

    }


    // -----------------------------
    // ATTEMPT
    // -----------------------------

    $attempt =
        (int)($row['call_attempt'] ?? 1);

    $total_attempt += $attempt;


    // -----------------------------
    // FOLLOW-UP
    // -----------------------------

    if (
        !empty($row['next_followup_date'])
    ) {

        $total_followup++;

    }


    // -----------------------------
    // PROMISE
    // -----------------------------

    if (
        !empty($row['promise_date'])
    ) {

        $total_promise++;

    }

}


// =====================================================
// CONNECTION RATE
// =====================================================

$connection_rate = 0;

if ($total_calls > 0) {

    $connection_rate =
        round(
            ($connected / $total_calls) * 100,
            1
        );

}

?>


<div class="container-fluid px-3 px-md-4 py-4">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                📊 কল রিপোর্ট

            </h3>

            <div class="text-muted">

                কল স্টোরির বিস্তারিত রিপোর্ট

            </div>

        </div>


        <div class="mt-3 mt-md-0">

            <button
                type="button"
                onclick="window.print()"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-printer"></i>

                Print Report

            </button>

        </div>

    </div>



    <!-- =================================================
         DATE FILTER
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="index.php"
            >

                <input
                    type="hidden"
                    name="page"
                    value="car/call_report"
                >


                <div class="row g-3 align-items-end">


                    <!-- FROM -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            শুরু তারিখ

                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="<?= htmlspecialchars($from_date) ?>"
                            class="form-control"
                            required
                        >

                    </div>



                    <!-- TO -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            শেষ তারিখ

                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="<?= htmlspecialchars($to_date) ?>"
                            class="form-control"
                            required
                        >

                    </div>



                    <!-- BUTTON -->

                    <div class="col-md-4">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-search"></i>

                            রিপোর্ট দেখুন

                        </button>

                    </div>


                </div>

            </form>

        </div>

    </div>



    <!-- =================================================
         SUMMARY CARDS
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL CALL -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                মোট কল

                            </div>

                            <div class="fs-3 fw-bold">

                                <?= bn_number($total_calls) ?>

                            </div>

                        </div>


                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center"
                            style="
                                width:52px;
                                height:52px;
                                background:#e8f1ff;
                                color:#2563eb;
                            "
                        >

                            <i class="bi bi-telephone fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- CONNECTED -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                কথা হয়েছে

                            </div>

                            <div class="fs-3 fw-bold text-success">

                                <?= bn_number($connected) ?>

                            </div>

                        </div>


                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center"
                            style="
                                width:52px;
                                height:52px;
                                background:#e9f9ef;
                                color:#198754;
                            "
                        >

                            <i class="bi bi-telephone-check fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- NOT CONNECTED -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                কথা হয়নি

                            </div>

                            <div class="fs-3 fw-bold text-danger">

                                <?= bn_number($not_connected) ?>

                            </div>

                        </div>


                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center"
                            style="
                                width:52px;
                                height:52px;
                                background:#fff0f0;
                                color:#dc3545;
                            "
                        >

                            <i class="bi bi-telephone-x fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- RATE -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                Connection Rate

                            </div>

                            <div class="fs-3 fw-bold text-primary">

                                <?= bn_number($connection_rate) ?>%

                            </div>

                        </div>


                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center"
                            style="
                                width:52px;
                                height:52px;
                                background:#eef2ff;
                                color:#4f46e5;
                            "
                        >

                            <i class="bi bi-graph-up-arrow fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- =================================================
         STATUS SUMMARY
    ================================================== -->

    <div class="row g-3 mb-4">


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        ব্যস্ত
                    </div>

                    <div class="fs-4 fw-bold text-warning">

                        <?= bn_number($busy) ?>

                    </div>

                </div>

            </div>

        </div>



        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        বন্ধ
                    </div>

                    <div class="fs-4 fw-bold text-secondary">

                        <?= bn_number($switched_off) ?>

                    </div>

                </div>

            </div>

        </div>



        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        Follow-up
                    </div>

                    <div class="fs-4 fw-bold text-info">

                        <?= bn_number($total_followup) ?>

                    </div>

                </div>

            </div>

        </div>



        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        Promise
                    </div>

                    <div class="fs-4 fw-bold text-success">

                        <?= bn_number($total_promise) ?>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- =================================================
         SEARCH
    ================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="input-group">

                <span class="input-group-text bg-white">

                    🔍

                </span>

                <input
                    type="text"
                    id="callSearch"
                    class="form-control"
                    placeholder="নাম, ফোন, গাড়ি, কল স্ট্যাটাস বা Note দিয়ে সার্চ করুন..."
                >

            </div>

        </div>

    </div>



    <!-- =================================================
         CALL REPORT TABLE
    ================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">

                    📞 কলের বিস্তারিত রিপোর্ট

                </h5>


                <span class="badge bg-primary">

                    <?= bn_number($total_calls) ?>

                    টি কল

                </span>

            </div>

        </div>



        <div class="card-body p-0">


            <?php if (!empty($calls)): ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                        id="callReportTable"
                    >

                        <thead class="table-light">

                            <tr>

                                <th class="ps-3">
                                    #
                                </th>

                                <th>
                                    তারিখ
                                </th>

                                <th>
                                    গ্রাহক
                                </th>

                                <th>
                                    ফোন
                                </th>

                                <th>
                                    গাড়ি
                                </th>

                                <th>
                                    কল স্ট্যাটাস
                                </th>

                                <th>
                                    কলের ধরন
                                </th>

                                <th>
                                    Attempt
                                </th>

                                <th>
                                    Follow-up
                                </th>

                                <th>
                                    Promise
                                </th>

                                <th>
                                    Note
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach (
                            $calls
                            as $index => $row
                        ): ?>


                            <?php

                            $status =
                                strtolower(
                                    trim(
                                        $row['call_status']
                                        ?? ''
                                    )
                                );


                            if (
                                $status === 'connected'
                            ) {

                                $status_text =
                                    'কথা হয়েছে';

                                $status_class =
                                    'bg-success';

                            } elseif (
                                $status === 'not_connected'
                            ) {

                                $status_text =
                                    'কথা হয়নি';

                                $status_class =
                                    'bg-danger';

                            } elseif (
                                $status === 'busy'
                            ) {

                                $status_text =
                                    'ব্যস্ত';

                                $status_class =
                                    'bg-warning text-dark';

                            } elseif (
                                $status === 'switched_off'
                            ) {

                                $status_text =
                                    'বন্ধ';

                                $status_class =
                                    'bg-secondary';

                            } elseif (
                                $status === 'wrong_number'
                            ) {

                                $status_text =
                                    'ভুল নম্বর';

                                $status_class =
                                    'bg-dark';

                            } else {

                                $status_text =
                                    'নির্ধারিত নয়';

                                $status_class =
                                    'bg-light text-dark';

                            }


                            ?>


                            <tr>


                                <!-- NUMBER -->

                                <td class="ps-3">

                                    <?= bn_number(
                                        $index + 1
                                    ) ?>

                                </td>



                                <!-- DATE -->

                                <td>

                                    <?php

                                    $created =
                                        $row['created_at']
                                        ?? '';

                                    ?>

                                    <?php if (!empty($created)): ?>

                                        <?= bn_number(
                                            date(
                                                'd-m-Y',
                                                strtotime(
                                                    $created
                                                )
                                            )
                                        ) ?>

                                        <br>

                                        <small class="text-muted">

                                            <?= date(
                                                'h:i A',
                                                strtotime(
                                                    $created
                                                )
                                            ) ?>

                                        </small>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['name']
                                            ?? 'N/A'
                                        ) ?>

                                    </strong>

                                </td>



                                <!-- PHONE -->

                                <td>

                                    <?php

                                    $phone =
                                        $row['customer_phone']
                                        ??
                                        $row['phone']
                                        ??
                                        '';

                                    ?>

                                    <?php if (!empty($phone)): ?>

                                        <a
                                            href="tel:<?= htmlspecialchars($phone) ?>"
                                            class="text-decoration-none"
                                        >

                                            📞

                                            <?= htmlspecialchars(
                                                $phone
                                            ) ?>

                                        </a>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>



                                <!-- CAR -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                        ?? '—'
                                    ) ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="badge <?= $status_class ?>"
                                    >

                                        <?= $status_text ?>

                                    </span>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <?php

                                    $category =
                                        $row['call_category']
                                        ?? '';

                                    $categoryText = [

                                        'payment_reminder'
                                            => 'পেমেন্ট রিমাইন্ডার',

                                        'due_collection'
                                            => 'বকেয়া আদায়',

                                        'promise'
                                            => 'Promise',

                                        'followup'
                                            => 'Follow-up',

                                        'general'
                                            => 'সাধারণ কল',

                                    ];

                                    ?>

                                    <?= htmlspecialchars(
                                        $categoryText[$category]
                                        ?? ($category ?: '—')
                                    ) ?>

                                </td>



                                <!-- ATTEMPT -->

                                <td>

                                    <?= bn_number(
                                        (int)(
                                            $row['call_attempt']
                                            ?? 1
                                        )
                                    ) ?>

                                </td>



                                <!-- FOLLOW-UP -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $row['next_followup_date']
                                        )
                                    ): ?>

                                        <span class="badge bg-info text-dark">

                                            <?= bn_number(
                                                date(
                                                    'd-m-Y',
                                                    strtotime(
                                                        $row[
                                                            'next_followup_date'
                                                        ]
                                                    )
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>



                                <!-- PROMISE -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $row['promise_date']
                                        )
                                    ): ?>

                                        <span class="badge bg-success">

                                            <?= bn_number(
                                                date(
                                                    'd-m-Y',
                                                    strtotime(
                                                        $row[
                                                            'promise_date'
                                                        ]
                                                    )
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>



                                <!-- NOTE -->

                                <td
                                    style="
                                        max-width:220px;
                                    "
                                >

                                    <div
                                        class="text-truncate"
                                        style="max-width:200px;"
                                        title="<?= htmlspecialchars(
                                            $row['note']
                                            ?? ''
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $row['note']
                                            ?? '—'
                                        ) ?>

                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- =================================================
                     NO DATA
                ================================================== -->

                <div class="text-center py-5">

                    <div
                        class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                        style="
                            width:80px;
                            height:80px;
                            background:#f1f5f9;
                        "
                    >

                        <i
                            class="bi bi-telephone-x fs-1 text-muted"
                        ></i>

                    </div>


                    <h5 class="fw-bold">

                        কোনো কল রিপোর্ট পাওয়া যায়নি

                    </h5>


                    <p class="text-muted">

                        নির্বাচিত তারিখের মধ্যে কোনো Call Story নেই।

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>



<!-- =====================================================
     SEARCH SCRIPT
====================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const input =
            document.getElementById(
                'callSearch'
            );

        const table =
            document.getElementById(
                'callReportTable'
            );


        if (!input || !table) {
            return;
        }


        const rows =
            table.querySelectorAll(
                'tbody tr'
            );


        input.addEventListener(
            'input',
            function () {

                const value =
                    this.value
                        .toLowerCase()
                        .trim();


                rows.forEach(
                    function (row) {

                        const text =
                            row.innerText
                                .toLowerCase();


                        row.style.display =
                            text.includes(value)
                                ? ''
                                : 'none';

                    }
                );

            }
        );

    }
);

</script>



<!-- =====================================================
     PRINT CSS
====================================================== -->

<style>

@media print {

    body {
        background:#fff !important;
    }

    .btn,
    form,
    #callSearch,
    .card-header .badge {
        display:none !important;
    }

    .card {
        box-shadow:none !important;
        border:1px solid #ddd !important;
    }

    .container-fluid {
        width:100% !important;
        max-width:100% !important;
    }

    table {
        font-size:11px;
    }

}

</style>