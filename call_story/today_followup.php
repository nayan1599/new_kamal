<?php
 

$today = date('Y-m-d');

$sql = "
    SELECT *
    FROM call_stories
    WHERE next_followup_date >= :start_date
      AND next_followup_date < :end_date
    ORDER BY next_followup_date ASC
";

$stmt = $pdo->prepare($sql);

$start_date = $today . ' 00:00:00';
$end_date   = date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00';

$stmt->execute([
    ':start_date' => $start_date,
    ':end_date'   => $end_date
]);

$followups = $stmt->fetchAll(PDO::FETCH_ASSOC);

 
// =====================================================
// STATISTICS
// =====================================================

$total_followup = count($followups);

$connected = 0;
$not_connected = 0;
$pending = 0;


foreach ($followups as $row) {

    $call_status = $row['call_status'] ?? '';

    if ($call_status === 'connected') {

        $connected++;

    } elseif ($call_status === 'not_connected') {

        $not_connected++;

    } else {

        $pending++;

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

                📅 আজকের Follow-up

            </h3>

            <div class="text-muted">

                <?= htmlspecialchars(
                    date('d-m-Y')
                ) ?>

                — আজ যাদের সাথে Follow-up করার কথা

            </div>

        </div>


        <div class="mt-3 mt-md-0">

            <a
                href="index.php?page=call_story/followup"
                class="btn btn-outline-primary"
            >

                <i class="bi bi-calendar-check"></i>

                সকল Follow-up

            </a>

        </div>

    </div>



    <!-- =====================================================
         SUMMARY CARDS
    ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                আজকের মোট Follow-up
                            </div>

                            <div class="fs-3 fw-bold mt-1">

                                <?= bn_number($total_followup) ?>

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



        <!-- CONNECTED -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                কথা হয়েছে
                            </div>

                            <div class="fs-3 fw-bold text-success mt-1">

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



        <!-- PENDING -->

        <div class="col-12 col-sm-6 col-xl-4">

            <div
                class="card border-0 shadow-sm h-100"
                style="border-radius:14px;"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                Follow-up বাকি
                            </div>

                            <div class="fs-3 fw-bold text-warning mt-1">

                                <?= bn_number($pending) ?>

                            </div>

                        </div>


                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center"
                            style="
                                width:52px;
                                height:52px;
                                background:#fff7df;
                                color:#d39e00;
                            "
                        >

                            <i class="bi bi-clock-history fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- =====================================================
         SEARCH + FILTER
    ====================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-center">


                <!-- SEARCH -->

                <div class="col-md-8">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            🔍

                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="নাম, ফোন, গাড়ির নম্বর বা ইনভয়েস দিয়ে সার্চ করুন..."
                        >

                    </div>

                </div>



                <!-- STATUS FILTER -->

                <div class="col-md-4">

                    <select
                        id="statusFilter"
                        class="form-select"
                    >

                        <option value="">
                            সব স্ট্যাটাস
                        </option>

                        <option value="connected">
                            কথা হয়েছে
                        </option>

                        <option value="not_connected">
                            কথা হয়নি
                        </option>

                        <option value="pending">
                            Follow-up বাকি
                        </option>

                    </select>

                </div>


            </div>

        </div>

    </div>



    <!-- =====================================================
         FOLLOW-UP LIST
    ====================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">

                    📞 আজকের Follow-up তালিকা

                </h5>


                <span class="badge bg-primary">

                    <?= bn_number($total_followup) ?>

                    জন

                </span>

            </div>

        </div>



        <div class="card-body p-0">

            <?php if (!empty($followups)): ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                        id="followupTable"
                    >

                        <thead class="table-light">

                            <tr>

                                <th class="ps-3">  # </th>
                                <th>গ্রাহক </th>
                                <th>ফোন </th>
                                <th>গাড়ি </th>
                                <th>জাবিনের নাম</th>
                                <th>জাবিনের ফোন</th>
                                <th>Follow-up </th>
                                <th>কল স্ট্যাটাস </th>
                                <th class="text-end pe-3">  Action </th>
                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($followups as $index => $row): ?>


                            <?php

                            $call_status =
                                $row['call_status'] ?? '';

                            $status_text =
                                'Follow-up বাকি';

                            $status_class =
                                'warning';

                            if ($call_status === 'connected') {

                                $status_text =
                                    'কথা হয়েছে';

                                $status_class =
                                    'success';

                            } elseif ($call_status === 'not_connected') {

                                $status_text =
                                    'কথা হয়নি';

                                $status_class =
                                    'danger';

                            } elseif ($call_status === 'busy') {

                                $status_text =
                                    'ব্যস্ত';

                                $status_class =
                                    'warning';

                            } elseif ($call_status === 'switched_off') {

                                $status_text =
                                    'বন্ধ';

                                $status_class =
                                    'secondary';

                            }

                            ?>


                            <tr
                                data-status="<?= htmlspecialchars(
                                    $call_status ?: 'pending'
                                ) ?>"
                            >


                                <!-- NUMBER -->

                                <td class="ps-3">

                                    <span class="text-muted">

                                        <?= bn_number(
                                            $index + 1
                                        ) ?>

                                    </span>

                                </td>



                                <!-- CUSTOMER -->

                                <td>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars(  $row['name'] ?? 'N/A' ) ?>
                                    </div>
                                </td>



                                <!-- PHONE -->

                                <td>  <?= htmlspecialchars(  $row['phone'] ) ?> </td>

                                <td>

                             

                                        <span class="fw-semibold">
                                         
                                            <?=    
                                          

                                            htmlspecialchars(
                                                $row['car_number']
                                            ) ?>

                                        </span>
                                 </td>


                                <td>

                             

                                        <span class="fw-semibold">
                                         
                                            <?=    
                                       

                                            htmlspecialchars(
                                                $row['jabin_name']
                                            ) ?>

                                        </span>
                                 </td>
                                 
                                <td>

                             

                                        <span class="fw-semibold">
                                         
                                            <?=    
                                          

                                            htmlspecialchars(
                                                $row['jabin_phone']
                                            ) ?>

                                        </span>
                                 </td>




                                <!-- FOLLOW-UP DATE -->

                                <td>
                                    <?php  $followup_date =  $row['next_followup_date'] ?? ''; ?>
                                    <?php if (!empty($followup_date)): ?>
                                        <div class="fw-semibold">
                                            <?= bn_number(
                                                date(
                                                    'd-m-Y',
                                                    strtotime(
                                                        $followup_date
                                                    )
                                                )
                                            ) ?>

                                        </div>


                                        <?php if (
                                            strtotime($followup_date)
                                            !== false
                                            &&
                                            date(
                                                'Y-m-d',
                                                strtotime($followup_date)
                                            ) === $today
                                        ): ?>

                                            <span class="badge bg-primary">

                                                আজ

                                            </span>

                                        <?php endif; ?>

                                    <?php endif; ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="badge text-bg-<?= $status_class ?>"
                                    >

                                        <?= $status_text ?>

                                    </span>

                                </td>



                                <!-- ACTION -->

                                <td class="text-end pe-3">

                                    <div class="d-flex justify-content-end gap-2">


                                        <!-- CALL -->

                                        <?php if (!empty($row['customer_phone'])): ?>

                                            <a
                                                href="tel:<?= htmlspecialchars(
                                                    $row['customer_phone']
                                                ) ?>"
                                                class="btn btn-sm btn-success"
                                                title="কল করুন"
                                            >

                                                <i class="bi bi-telephone-fill"></i>

                                            </a>

                                        <?php endif; ?>



                                        <!-- CALL STORY -->

                                        <a href="index.php?page=car/callstory&id=<?= (int)($row['id'] ?? $row['id'] ?? 0 ) ?>"
                                            class="btn btn-sm btn-primary"
                                            title="Call Story"
                                        >

                                            <i class="bi bi-journal-text"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- =================================================
                     NO FOLLOW-UP
                ================================================== -->

                <div class="text-center py-5">

                    <div
                        class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                        style="
                            width:75px;
                            height:75px;
                            background:#f1f5f9;
                        "
                    >

                        <i class="bi bi-calendar-check fs-1 text-success"></i>

                    </div>


                    <h5 class="fw-bold">

                        🎉 আজ কোনো Follow-up নেই

                    </h5>


                    <p class="text-muted mb-0">

                        আজকের জন্য কোনো Follow-up নির্ধারিত নেই।

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

document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('searchInput');

    const statusFilter =
        document.getElementById('statusFilter');

    const table =
        document.getElementById('followupTable');


    if (!table) {
        return;
    }


    const rows =
        table.querySelectorAll('tbody tr');


    function filterRows() {

        const search =
            searchInput.value
                .toLowerCase()
                .trim();

        const status =
            statusFilter.value;


        rows.forEach(function (row) {

            const text =
                row.innerText
                    .toLowerCase();


            const rowStatus =
                row.getAttribute(
                    'data-status'
                );


            const searchMatch =
                text.includes(search);


            let statusMatch = true;


            if (status === 'pending') {

                statusMatch =
                    !rowStatus ||
                    rowStatus === 'pending';

            } else if (status !== '') {

                statusMatch =
                    rowStatus === status;

            }


            row.style.display =
                searchMatch && statusMatch
                    ? ''
                    : 'none';

        });

    }


    searchInput.addEventListener(
        'input',
        filterRows
    );


    statusFilter.addEventListener(
        'change',
        filterRows
    );

});

</script>