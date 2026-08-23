<?php

// =====================================================
// PROMISE LIST
// =====================================================

$today = date('Y-m-d');


// =====================================================
// FETCH PROMISE DATA
// =====================================================

$sql = "
    SELECT *
    FROM call_stories
    WHERE promise_date IS NOT NULL
      AND promise_date != ''
    ORDER BY promise_date ASC, id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$promises = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// SUMMARY
// =====================================================

$total_promise = count($promises);

$today_promise = 0;
$pending_promise = 0;
$expired_promise = 0;


foreach ($promises as $row) {

    $promise_date = $row['promise_date'] ?? '';

    if (empty($promise_date)) {
        continue;
    }

    $promiseDate = date(
        'Y-m-d',
        strtotime($promise_date)
    );


    if ($promiseDate === $today) {

        $today_promise++;

    } elseif ($promiseDate < $today) {

        $expired_promise++;

    } else {

        $pending_promise++;

    }

}

?>


<div class="container-fluid px-3 px-md-4 py-4">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                🤝 Promise তালিকা
            </h3>

            <div class="text-muted">
                গ্রাহকের প্রতিশ্রুত পেমেন্টের তালিকা
            </div>

        </div>


        <div class="mt-3 mt-md-0">

            <a
                href="index.php?page=car/callstory"
                class="btn btn-primary"
            >

                <i class="bi bi-telephone-forward"></i>

                নতুন Call Story

            </a>

        </div>

    </div>



    <!-- =====================================================
         SUMMARY CARDS
    ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                মোট Promise
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= bn_number($total_promise) ?>
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

                            <i class="bi bi-hand-thumbs-up fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- TODAY -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                আজকের Promise
                            </div>

                            <div class="fs-3 fw-bold text-primary">
                                <?= bn_number($today_promise) ?>
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

                            <i class="bi bi-calendar-event fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- PENDING -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                সামনে Promise
                            </div>

                            <div class="fs-3 fw-bold text-success">
                                <?= bn_number($pending_promise) ?>
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

                            <i class="bi bi-check-circle fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- EXPIRED -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Promise বাকি
                            </div>

                            <div class="fs-3 fw-bold text-danger">
                                <?= bn_number($expired_promise) ?>
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

                            <i class="bi bi-exclamation-circle fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- =====================================================
         SEARCH / FILTER
    ====================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">


                <!-- SEARCH -->

                <div class="col-md-7">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            🔍
                        </span>

                        <input
                            type="text"
                            id="promiseSearch"
                            class="form-control"
                            placeholder="নাম, ফোন, গাড়ি বা Promise Note দিয়ে সার্চ..."
                        >

                    </div>

                </div>



                <!-- DATE FILTER -->

                <div class="col-md-5">

                    <select
                        id="promiseFilter"
                        class="form-select"
                    >

                        <option value="">
                            সব Promise
                        </option>

                        <option value="today">
                            আজকের Promise
                        </option>

                        <option value="pending">
                            সামনে Promise
                        </option>

                        <option value="expired">
                            বাকি Promise
                        </option>

                    </select>

                </div>


            </div>

        </div>

    </div>



    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">

                    🤝 Promise তালিকা

                </h5>

                <span class="badge bg-primary">

                    <?= bn_number($total_promise) ?> টি

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <?php if (!empty($promises)): ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                        id="promiseTable"
                    >

                        <thead class="table-light">

                            <tr>

                                <th class="ps-3">
                                    #
                                </th>

                                <th>
                                    গ্রাহক
                                </th>

                                <th>
                                    ফোন
                                </th>

                                <th>
                                    গাড়ির নম্বর
                                </th>

                                <th>
                                    Promise Date
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Note
                                </th>

                                <th class="text-end pe-3">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach (
                            $promises
                            as $index => $row
                        ): ?>


                            <?php

                            $promise_date =
                                $row['promise_date']
                                ?? '';

                            $promise_status =
                                'pending';


                            if (!empty($promise_date)) {

                                $promiseDate =
                                    date(
                                        'Y-m-d',
                                        strtotime(
                                            $promise_date
                                        )
                                    );


                                if (
                                    $promiseDate === $today
                                ) {

                                    $promise_status =
                                        'today';

                                } elseif (
                                    $promiseDate < $today
                                ) {

                                    $promise_status =
                                        'expired';

                                } else {

                                    $promise_status =
                                        'pending';

                                }

                            }


                            if (
                                $promise_status === 'today'
                            ) {

                                $badgeClass =
                                    'bg-primary';

                                $badgeText =
                                    'আজ';

                            } elseif (
                                $promise_status === 'expired'
                            ) {

                                $badgeClass =
                                    'bg-danger';

                                $badgeText =
                                    'বাকি';

                            } else {

                                $badgeClass =
                                    'bg-success';

                                $badgeText =
                                    'Pending';

                            }

                            ?>


                            <tr
                                data-status="<?= $promise_status ?>"
                            >


                                <!-- NUMBER -->

                                <td class="ps-3">

                                    <?= bn_number(
                                        $index + 1
                                    ) ?>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <div class="fw-bold">

                                        <?= htmlspecialchars(
                                            $row['name']
                                            ?? 'N/A'
                                        ) ?>

                                    </div>

                                </td>



                                <!-- PHONE -->

                                <td>

                                    <?php

                                    $phone =
                                        $row['customer_phone']
                                        ?? $row['phone']
                                        ?? '';

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

                                        <span class="text-muted">
                                            নেই
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- CAR NUMBER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                        ?? 'N/A'
                                    ) ?>

                                </td>



                                <!-- PROMISE DATE -->

                                <td>

                                    <?php if (!empty($promise_date)): ?>

                                        <div class="fw-semibold">

                                            <?= bn_number(
                                                date(
                                                    'd-m-Y',
                                                    strtotime(
                                                        $promise_date
                                                    )
                                                )
                                            ) ?>

                                        </div>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            নেই
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="badge <?= $badgeClass ?>"
                                    >

                                        <?= $badgeText ?>

                                    </span>

                                </td>



                                <!-- NOTE -->

                                <td style="max-width:220px;">

                                    <div
                                        class="text-truncate"
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



                                <!-- ACTION -->

                                <td class="text-end pe-3">

                                    <div
                                        class="d-flex justify-content-end gap-2"
                                    >


                                        <?php if (!empty($phone)): ?>

                                            <a
                                                href="tel:<?= htmlspecialchars($phone) ?>"
                                                class="btn btn-sm btn-success"
                                                title="কল করুন"
                                            >

                                                <i class="bi bi-telephone-fill"></i>

                                            </a>

                                        <?php endif; ?>


                                        <?php
                                        $customer_id =
                                            $row['customer_record_id']
                                            ?? $row['customer_id']
                                            ?? 0;
                                        ?>


                                        <?php if ($customer_id): ?>

                                            <a
                                                href="index.php?page=car/callstory&id=<?= (int)$customer_id ?>"
                                                class="btn btn-sm btn-primary"
                                                title="Call Story"
                                            >

                                                <i class="bi bi-journal-text"></i>

                                            </a>

                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- =================================================
                     EMPTY
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
                            class="bi bi-hand-thumbs-up fs-1 text-primary"
                        ></i>

                    </div>


                    <h5 class="fw-bold">
                        কোনো Promise পাওয়া যায়নি
                    </h5>


                    <p class="text-muted mb-0">

                        এখনো কোনো Promise Date দেওয়া হয়নি।

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>



<!-- =====================================================
     SEARCH + FILTER SCRIPT
====================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const search =
            document.getElementById(
                'promiseSearch'
            );

        const filter =
            document.getElementById(
                'promiseFilter'
            );

        const table =
            document.getElementById(
                'promiseTable'
            );


        if (!table) {
            return;
        }


        const rows =
            table.querySelectorAll(
                'tbody tr'
            );


        function applyFilter() {

            const searchText =
                search.value
                    .toLowerCase()
                    .trim();


            const filterValue =
                filter.value;


            rows.forEach(function (row) {

                const rowText =
                    row.innerText
                        .toLowerCase();


                const rowStatus =
                    row.getAttribute(
                        'data-status'
                    );


                const searchMatch =
                    rowText.includes(
                        searchText
                    );


                const statusMatch =
                    filterValue === ''
                    ||
                    rowStatus === filterValue;


                row.style.display =
                    searchMatch &&
                    statusMatch
                        ? ''
                        : 'none';

            });

        }


        search.addEventListener(
            'input',
            applyFilter
        );


        filter.addEventListener(
            'change',
            applyFilter
        );

    }
);

</script>