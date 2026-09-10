<?php

// ======================================================
// কিস্তি পেমেন্ট রেকর্ড
// ======================================================

$stmt = $pdo->query("
    SELECT *
    FROM kisti_payments
    ORDER BY payment_date DESC
    
");

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- ==================================================
         Page Header
    ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-cash-stack text-success me-2"></i>
                কিস্তি পেমেন্ট ম্যানেজমেন্ট
            </h1>

            <p class="text-muted mb-0">
                সকল কিস্তি আদায়ের রেকর্ড ও সামারি
            </p>
        </div>


        <!-- নতুন কিস্তি -->
        <a href="index.php?page=payment/add" class="btn btn-success btn-lg" title="নতুন কিস্তি আদায়">
            <i class="bi bi-plus-circle me-1"></i>
            নতুন কিস্তি আদায়
        </a>

    </div>


    <!-- ==================================================
         Main Table
    ================================================== -->

    <div class="card shadow-sm border-0">

        <!-- Card Header -->

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">

            <h5 class="mb-0">

                <i class="bi bi-list-ul me-2"></i>

                সকল কিস্তি পেমেন্ট রেকর্ড

            </h5>


            <div class="d-flex gap-2 align-items-center">

                <!-- Search -->

                <div class="position-relative">

                    <i class="bi bi-search position-absolute" style="
                            left: 10px;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #777;
                        "></i>

                    <input type="text" id="searchInput" class="form-control form-control-sm search-box"
                        placeholder="নাম, গাড়ি নং, ফোন দিয়ে সার্চ...">

                </div>


                <!-- Print -->

                <button type="button" class="btn btn-light btn-sm" onclick="window.print()" title="প্রিন্ট">

                    <i class="bi bi-printer"></i>

                </button>

            </div>

        </div>


        <!-- ==================================================
             Card Body
        ================================================== -->

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0" id="paymentTable">

                    <!-- ==================================================
                         Table Header
                    ================================================== -->

                    <thead class="table-light">

                        <tr>

                            <th class="text-center">
                                #
                            </th>

                            <th>
                                তারিখ
                            </th>

                            <th>
                                গাড়ির নং
                            </th>

                            <th>
                                কিস্তি নং
                            </th>

                            <th class="text-end">
                                টাকা
                            </th>

                            <th class="text-end">
                                জরিমানা
                            </th>

                            <th>
                                মেথড
                            </th>

                            <th>
                                স্ট্যাটাস
                            </th>

                            <th>
                                প্রাপক
                            </th>

                            <th class="text-center">
                                অ্যাকশন
                            </th>

                        </tr>

                    </thead>


                    <!-- ==================================================
                         Table Body
                    ================================================== -->

                    <tbody>

                        <?php if (empty($records)): ?>

                        <tr>

                            <td colspan="10" class="text-center py-5">

                                <i class="bi bi-inbox text-muted" style="font-size: 45px;"></i>

                                <div class="text-muted mt-2">
                                    কোনো কিস্তি পেমেন্ট রেকর্ড পাওয়া যায়নি।
                                </div>

                            </td>

                        </tr>

                        <?php else: ?>


                        <?php $serial = 1; ?>


                        <?php foreach ($records as $row): ?>

                        <?php

                        // ==================================================
                        // Payment Method
                        // ==================================================

                        $methodMap = [

                            'cash' =>
                                'ক্যাশ',

                            'bank_transfer' =>
                                'ব্যাংক ট্রান্সফার',

                            'bkash' =>
                                'বিকাশ',

                            'nagad' =>
                                'নগদ',

                            'rocket' =>
                                'রকেট',

                            'cheque' =>
                                'চেক',

                            'others' =>
                                'অন্যান্য'

                        ];


                        $method =
                            $row['payment_method'] ?? '';


                        $methodText =
                            $methodMap[$method]
                            ?? $method
                            ?? '—';


                        // ==================================================
                        // Status
                        // ==================================================

                        $status =
                            strtolower(
                                trim(
                                    $row['status'] ?? ''
                                )
                            );


                        if ($status === 'paid') {

                            $statusText =
                                'পরিশোধিত';

                            $statusClass =
                                'success';

                            $statusIcon =
                                'check-circle';

                        } else {

                            $statusText =
                                $status ?: 'অজানা';

                            $statusClass =
                                'warning text-dark';

                            $statusIcon =
                                'clock';

                        }


                        // ==================================================
                        // Payment Date
                        // ==================================================

                        $paymentDate =
                            $row['payment_date']
                            ?? null;


                        if (!empty($paymentDate)) {

                            $formattedDate =
                                date(
                                    'd/m/Y',
                                    strtotime($paymentDate)
                                );

                        } else {

                            $formattedDate =
                                '—';

                        }


                        // ==================================================
                        // Amount
                        // ==================================================

                        $amount =
                            (float)(
                                $row['amount'] ?? 0
                            );


                        // ==================================================
                        // Fine
                        // ==================================================

                        $fine =
                            (float)(
                                $row['fine_amount'] ?? 0
                            );

                        ?>

                        <tr>

                            <!-- Serial -->

                            <td class="text-center">

                                <?= bn_number($serial++) ?>

                            </td>


                            <!-- Date -->

                            <td>

                                <?= bn_number(
                                    $formattedDate
                                ) ?>

                            </td>


                            <!-- Car Number -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['car_number']
                                        ?? '—'
                                    ) ?>

                                </strong>

                            </td>


                            <!-- Kisti Number -->

                            <td>

                                <span class="badge bg-secondary">

                                    <?= bn_number(
                                        $row['kisti_number']
                                        ?? '—'
                                    ) ?>

                                </span>

                            </td>


                            <!-- Amount -->

                            <td class="text-end fw-bold text-success">

                                ৳

                                <?= bn_number(
                                    number_format(
                                        $amount,
                                        2
                                    )
                                ) ?>

                            </td>


                            <!-- Fine -->

                            <td class="text-end text-danger">

                                <?php if ($fine > 0): ?>

                                ৳

                                <?= bn_number(
                                        number_format(
                                            $fine,
                                            2
                                        )
                                    ) ?>

                                <?php else: ?>

                                —

                                <?php endif; ?>

                            </td>


                            <!-- Payment Method -->


                            <td>

                                <?php
    $methodBadge =
        $method === 'cash'
        ? 'success'
        : 'info';
    ?>

                                <span class="badge bg-<?= $methodBadge ?>">

                                    <?php if ($method === 'cash'): ?>

                                    <i class="bi bi-cash"></i>

                                    <?php elseif ($method === 'bkash'): ?>

                                    <i class="bi bi-phone"></i>

                                    <?php elseif ($method === 'nagad'): ?>

                                    <i class="bi bi-wallet2"></i>

                                    <?php elseif ($method === 'bank_transfer'): ?>

                                    <i class="bi bi-bank"></i>

                                    <?php elseif ($method === 'cheque'): ?>

                                    <i class="bi bi-file-earmark-text"></i>

                                    <?php else: ?>

                                    <i class="bi bi-credit-card"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars($methodText) ?>

                                    <?php if (!empty($row['bank_name'])): ?>
                                    - <?= htmlspecialchars($row['bank_name']) ?>
                                    <?php endif; ?>

                                </span>

                            </td>




                            <!-- Status -->

                            <td>

                                <span class="badge bg-<?= $statusClass ?>">

                                    <i class="bi bi-<?= $statusIcon ?>"></i>

                                    <?= htmlspecialchars(
                                        $statusText
                                    ) ?>

                                </span>

                            </td>


                            <!-- Received By -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['received_by']
                                    ?? '—'
                                ) ?>

                            </td>


                            <!-- Actions -->

                            <td class="text-center text-nowrap">


                                <!-- View -->

                                <a href="index.php?page=payment/view&id=<?= (int)$row['id'] ?>"
                                    class="btn btn-sm btn-success" title="দেখুন">

                                    <i class="bi bi-eye"></i>

                                </a>


                                <!-- Edit -->

                                <a href="index.php?page=payment/edit&id=<?= (int)$row['id'] ?>"
                                    class="btn btn-sm btn-warning" title="সম্পাদনা">

                                    <i class="bi bi-pencil-square"></i>

                                </a>


                                <!-- Delete -->

                                <a href="index.php?page=payment/delete&id=<?= (int)$row['id'] ?>"
                                    class="btn btn-sm btn-danger" title="মুছুন" onclick="
                                        return confirm(
                                            'আপনি কি এই কিস্তি পেমেন্ট রেকর্ডটি মুছে ফেলতে চান?'
                                        );
                                    ">

                                    <i class="bi bi-trash3"></i>

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

</div>



<!-- =========================================================
     Live Search
========================================================= -->

<script>
document.addEventListener(
    "DOMContentLoaded",
    function() {

        const searchInput =
            document.getElementById(
                "searchInput"
            );


        const table =
            document.getElementById(
                "paymentTable"
            );


        if (!searchInput || !table) {
            return;
        }


        searchInput.addEventListener(
            "keyup",
            function() {

                const searchText =
                    this.value
                    .toLowerCase()
                    .trim();


                const rows =
                    table.querySelectorAll(
                        "tbody tr"
                    );


                rows.forEach(
                    function(row) {

                        const text =
                            row.textContent
                            .toLowerCase();


                        if (
                            text.includes(
                                searchText
                            )
                        ) {

                            row.style.display =
                                "";

                        } else {

                            row.style.display =
                                "none";

                        }

                    }
                );

            }
        );

    }
);
</script>



<!-- =========================================================
     Style
========================================================= -->

<style>
.search-box {

    width: 300px;

    padding-left: 32px;

}


#paymentTable th {

    white-space: nowrap;

}


#paymentTable td {

    white-space: nowrap;

}


#paymentTable tbody tr {

    transition:
        background-color 0.2s ease;

}


#paymentTable tbody tr:hover {

    background-color:
        rgba(13,
            110,
            253,
            0.04);

}


.btn-sm {

    border-radius:
        6px;

}


@media (max-width: 768px) {

    .search-box {

        width: 180px;

    }


    .card-header {

        flex-direction:
            column;

        gap: 10px;

        align-items:
            stretch !important;

    }


    .card-header .d-flex {

        justify-content:
            space-between;

    }

}


@media print {

    .btn,
    #searchInput {

        display:
            none !important;

    }


    .card {

        box-shadow:
            none !important;

        border:
            0 !important;

    }


    .card-header {

        background:
            #fff !important;

        color:
            #000 !important;

    }

}
</style>