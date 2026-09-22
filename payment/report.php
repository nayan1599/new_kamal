<?php

// =====================================================
// FETCH DATA
// =====================================================

$stmt = $pdo->query("
    SELECT *
    FROM kisti_payments
    ORDER BY payment_date DESC, id DESC
");

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// SUMMARY
// =====================================================

$total_amount = 0;
$total_paid   = 0;
$total_due    = 0;
$total_fine   = 0;


foreach ($records as $r) {

    $amount = (float)($r['amount'] ?? 0);
    $paid   = (float)($r['paid'] ?? 0);
    $fine   = (float)($r['fine_amount'] ?? 0);
    

    // Due
    $due =  (float)($r['due_amount'] ?? 0);

 

    $total_amount += $amount;
    $total_paid   += $paid;
    $total_due    += $due;
    $total_fine   += $fine;
}


// =====================================================
// PAYMENT METHOD
// =====================================================

$methodMap = [

    'cash'          => 'ক্যাশ',
    'bank_transfer' => 'ব্যাংক ট্রান্সফার',
    'bkash'         => 'বিকাশ',
    'nagad'         => 'নগদ',
    'rocket'        => 'রকেট',
    'cheque'        => 'চেক',
    'others'        => 'অন্যান্য'

];

?>

<div class="container-fluid py-4">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                📊 কিস্তি রিপোর্ট
            </h4>

            <small class="text-muted">
                সকল কিস্তি লেনদেন, পরিশোধ, বকেয়া ও জরিমানা হিসাব
            </small>

        </div>


        <div class="no-print">

            <button
                onclick="window.print()"
                class="btn btn-dark btn-sm">

                🖨️ প্রিন্ট

            </button>

        </div>

    </div>



    <!-- =================================================
         SUMMARY
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- মোট টাকা -->
        <div class="col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        মোট টাকা
                    </div>

                    <h4
                        class="text-primary fw-bold mb-0"
                        id="sumAmount">

                        ৳ <?= bn_number(number_format($total_amount, 2)) ?>

                    </h4>

                </div>

            </div>

        </div>



        <!-- মোট পরিশোধ -->
        <!-- <div class="col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        মোট পরিশোধ
                    </div>

                    <h4
                        class="text-success fw-bold mb-0"
                        id="sumPaid">

                        ৳ <?= bn_number(number_format($total_paid, 2)) ?>

                    </h4>

                </div>

            </div>

        </div> -->



        <!-- মোট বাকি -->
        <div class="col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        মোট বাকি
                    </div>

                    <h4
                        class="text-danger fw-bold mb-0"
                        id="sumDue">

                        ৳ <?= bn_number(number_format($total_due, 2)) ?>

                    </h4>

                </div>

            </div>

        </div>



        <!-- মোট জরিমানা -->
        <div class="col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        মোট জরিমানা
                    </div>

                    <h4
                        class="text-warning fw-bold mb-0"
                        id="sumFine">

                        ৳ <?= bn_number(number_format($total_fine, 2)) ?>

                    </h4>

                </div>

            </div>

        </div>

<!-- মোট আদায় -->
<div class="col-md-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">

            <div class="text-muted mb-2">
                মোট আদায়
            </div>

            <h4 class="text-success fw-bold mb-0" id="sumPaid">
                ৳ <?= bn_number(number_format($total_paid + $total_fine, 2)) ?>
            </h4>

        </div>
    </div>
</div>

    </div>



    <!-- =================================================
         SEARCH + DATE FILTER
    ================================================== -->

    <div class="card shadow-sm border-0 mb-3 no-print">

        <div class="card-body">

            <div class="row g-2">


                <!-- SEARCH -->

                <div class="col-md-5">

                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="🔍 নাম / মোবাইল / গাড়ি নম্বর সার্চ করুন...">

                </div>



                <!-- FROM DATE -->

                <div class="col-md-3">

                    <input
                        type="date"
                        id="fromDate"
                        class="form-control"
                        title="শুরুর তারিখ">

                </div>



                <!-- TO DATE -->

                <div class="col-md-3">

                    <input
                        type="date"
                        id="toDate"
                        class="form-control"
                        title="শেষ তারিখ">

                </div>



                <!-- CLEAR -->

                <div class="col-md-1">

                    <button
                        type="button"
                        id="clearFilter"
                        class="btn btn-outline-secondary w-100"
                        title="ক্লিয়ার">

                        ✕

                    </button>

                </div>


            </div>

        </div>

    </div>



    <!-- =================================================
         TABLE
    ================================================== -->

    <div class="card shadow-sm border-0">

        <div class="table-responsive">

            <table
                class="table table-hover align-middle mb-0"
                id="dataTable">


                <!-- TABLE HEAD -->

                <thead class="table-dark">

                    <tr>

                        <th class="text-center">
                            #
                        </th>

                        <th>
                            তারিখ
                        </th>

                        <th>
                            গাড়ি
                        </th>

                        <th class="text-end">
                            মোট টাকা
                        </th>
                   

                        <th class="text-end">
                            জরিমানা
                        </th>
      <th class="text-center">
                            আগের বাকি
                        </th>

                        
                        <th>
                            মেথড
                        </th>

                        <th class="text-center">
                            অ্যাকশন
                        </th>

                    </tr>

                </thead>



                <!-- TABLE BODY -->

                <tbody>


                    <?php if (empty($records)): ?>


                    <!-- NO DATA -->

                    <tr>

                        <td
                            colspan="9"
                            class="text-center py-5">

                            <div class="text-muted">

                                কোনো কিস্তি রেকর্ড পাওয়া যায়নি।

                            </div>

                        </td>

                    </tr>


                    <?php else: ?>


                    <?php $serial = 1; ?>


                    <?php foreach ($records as $row): ?>


                    <?php

                    // =================================================
                    // AMOUNT
                    // =================================================

                    $amount = (float)($row['amount'] ?? 0);

                    $paid = (float)($row['paid'] ?? 0);

                    $fine = (float)($row['fine_amount'] ?? 0);
                   $due = (float)($row['due_amount'] ?? 0);

                    // =================================================
                    // DUE
                    // =================================================
 

                    // =================================================
                    // DATE
                    // =================================================

                    $paymentDate = $row['payment_date'] ?? '';


                    $isoDate = !empty($paymentDate)
                        ? date('Y-m-d', strtotime($paymentDate))
                        : '';


                    $displayDate = !empty($paymentDate)
                        ? date('d-m-Y', strtotime($paymentDate))
                        : '—';


                    // =================================================
                    // METHOD
                    // =================================================

                    $method = strtolower(
                        trim($row['payment_method'] ?? '')
                    );


                    $methodMap = [

                        'cash'          => 'ক্যাশ',
                        'bkash'         => 'বিকাশ',
                        'nagad'         => 'নগদ',
                        'rocket'        => 'রকেট',
                        'bank_transfer' => 'ব্যাংক ট্রান্সফার',
                        'cheque'        => 'চেক',
                        'others'        => 'অন্যান্য'

                    ];


                    $methodText =
                        $methodMap[$method]
                        ?? 'অন্যান্য';



                    // =================================================
                    // BANK
                    // =================================================

                    $bankMap = [

                        'Dutch-Bangla Bank' => 'ডাচ্-বাংলা ব্যাংক',
                        'BRAC Bank'         => 'ব্র্যাক ব্যাংক'

                    ];


                    $bankName = '';


                    if (!empty($row['bank_name'])) {

                        $bankName =
                            $bankMap[$row['bank_name']]
                            ?? $row['bank_name'];

                    }



                    // =================================================
                    // BADGE
                    // =================================================

                    $methodBadge = match ($method) {

                        'cash'          => 'success',
                        'bkash'         => 'primary',
                        'nagad'         => 'danger',
                        'rocket'        => 'info',
                        'bank_transfer' => 'info',
                        'cheque'        => 'warning',
                        'others'        => 'secondary',

                        default => 'secondary'

                    };



                    // =================================================
                    // ICON
                    // =================================================

                    $methodIcon = match ($method) {

                        'cash'          => 'cash',
                        'bkash'         => 'phone',
                        'nagad'         => 'wallet2',
                        'rocket'        => 'send',
                        'bank_transfer' => 'bank',
                        'cheque'        => 'file-earmark-text',
                        'others'        => 'credit-card',

                        default => 'credit-card'

                    };

                    ?>


                    <!-- =================================================
                         ROW
                    ================================================== -->

                    <tr

                        data-date="<?= htmlspecialchars($isoDate) ?>"

                        data-amount="<?= htmlspecialchars($amount) ?>"

                        data-paid="<?= htmlspecialchars($paid) ?>"

                        data-due="<?= htmlspecialchars($due) ?>"
                         

                        data-fine="<?= htmlspecialchars($fine) ?>"

                    >


                        <!-- SERIAL -->

                        <td class="text-center">

                            <?= bn_number($serial++) ?>

                        </td>



                        <!-- DATE -->

                        <td>

                            <?= bn_number($displayDate) ?>

                        </td>



                        <!-- CAR -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $row['car_number'] ?? '—'
                                ) ?>

                            </strong>

                        </td>



                        <!-- TOTAL -->

                        <td class="text-end fw-bold text-primary">

                            ৳
                            <?= bn_number(
                                number_format($amount, 2)
                            ) ?>

                        </td>







                        <!-- FINE -->

                        <td class="text-end fw-bold">


                            <?php if ($fine > 0): ?>


                            <span class="text-warning">

                                ৳
                                <?= bn_number(
                                    number_format($fine, 2)
                                ) ?>

                            </span>


                            <?php else: ?>


                            <span class="text-muted">

                                ৳ ০.০০

                            </span>


                            <?php endif; ?>


                        </td>
          <td class="text-center fw-bold">


                            


                            <span class="text-red">

                                ৳
                                <?= bn_number(
                                    number_format($row['due_amount'], 2)
                                ) ?>

                            </span>


                            

 


                        </td>


                        <!-- METHOD -->

                        <td>

                            <span
                                class="badge bg-<?= $methodBadge ?>">

                                <i
                                    class="bi bi-<?= $methodIcon ?>">
                                </i>


                                <?= htmlspecialchars(
                                    $methodText
                                ) ?>


                                <?php if (!empty($bankName)): ?>

                                    -
                                    <?= htmlspecialchars(
                                        $bankName
                                    ) ?>

                                <?php endif; ?>


                            </span>

                        </td>



                        <!-- ACTION -->

                        <td class="text-center text-nowrap">


                            <!-- VIEW -->

                            <a
                                href="index.php?page=payment/view&id=<?= (int)$row['id'] ?>"
                                class="btn btn-sm btn-success"
                                title="দেখুন">

                                <i class="bi bi-eye"></i>

                            </a>



                            <!-- EDIT -->

                            <a
                                href="index.php?page=payment/edit&id=<?= (int)$row['id'] ?>"
                                class="btn btn-sm btn-warning"
                                title="সম্পাদনা">

                                <i class="bi bi-pencil"></i>

                            </a>



                            <!-- DELETE -->

                            <a
                                href="index.php?page=payment/delete&id=<?= (int)$row['id'] ?>"
                                class="btn btn-sm btn-danger"
                                title="মুছুন"

                                onclick="
                                    return confirm(
                                        'আপনি কি এই রেকর্ডটি মুছে ফেলতে চান?'
                                    );
                                ">

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



<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>

(function() {


    // =================================================
    // ELEMENTS
    // =================================================

    const searchInput =
        document.getElementById("searchInput");


    const fromDate =
        document.getElementById("fromDate");


    const toDate =
        document.getElementById("toDate");


    const clearBtn =
        document.getElementById("clearFilter");


    const rows =
        document.querySelectorAll(
            "#dataTable tbody tr[data-date]"
        );



    // =================================================
    // BANGLA NUMBER
    // =================================================

    function bnNumber(num) {

        return Number(num).toLocaleString(
            'en-IN',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        ).replace(
            /\d/g,
            d => '০১২৩৪৫৬৭৮৯'[d]
        );

    }



    // =================================================
    // SUMMARY
    // =================================================

    function updateSummary() {


        let totalAmount = 0;

        let totalPaid = 0;

        let totalDue = 0;

        let totalFine = 0;



        rows.forEach(row => {


            // Hidden row skip

            if (row.style.display === "none") {
                return;
            }



            // Amount

            totalAmount += parseFloat(
                row.dataset.amount || 0
            );



            // Paid

            totalPaid += parseFloat(
                row.dataset.paid || 0
            );



            // Due

            totalDue += parseFloat(
                row.dataset.due || 0
            );



            // Fine

            totalFine += parseFloat(
                row.dataset.fine || 0
            );


        });



        // TOTAL AMOUNT

        document.getElementById(
            "sumAmount"
        ).textContent =
            "৳ " + bnNumber(totalAmount);



        // TOTAL PAID

        document.getElementById(
            "sumPaid"
        ).textContent =
           "৳ " + bnNumber(totalAmount + totalFine + totalDue);



        // TOTAL DUE

        document.getElementById(
            "sumDue"
        ).textContent =
            "৳ " + bnNumber(totalDue);



        // TOTAL FINE

        document.getElementById(
            "sumFine"
        ).textContent =
            "৳ " + bnNumber(totalFine);


    }



    // =================================================
    // FILTER
    // =================================================

    function filterRows() {


        const text =
            (searchInput.value || "")
            .toLowerCase()
            .trim();


        const from =
            fromDate.value;


        const to =
            toDate.value;



        rows.forEach(row => {


            // Row text

            const rowText =
                row.innerText
                    .toLowerCase();


            // Row date

            const rowDate =
                row.dataset.date;



            // Search match

            const textMatch =
                !text ||
                rowText.includes(text);



            // Date match

            let dateMatch = true;



            // From date

            if (
                from &&
                rowDate < from
            ) {

                dateMatch = false;

            }



            // To date

            if (
                to &&
                rowDate > to
            ) {

                dateMatch = false;

            }



            // Display

            row.style.display =
                (
                    textMatch &&
                    dateMatch
                )
                ? ""
                : "none";


        });



        // Update summary

        updateSummary();

    }



    // =================================================
    // TODAY
    // =================================================

    const now = new Date();


    const year =
        now.getFullYear();


    const month =
        String(
            now.getMonth() + 1
        ).padStart(2, '0');


    const day =
        String(
            now.getDate()
        ).padStart(2, '0');


    const today =
        `${year}-${month}-${day}`;



    // Default today

    fromDate.value = today;

    toDate.value = today;



    // =================================================
    // EVENTS
    // =================================================

    searchInput.addEventListener(
        "input",
        filterRows
    );


    fromDate.addEventListener(
        "change",
        filterRows
    );


    toDate.addEventListener(
        "change",
        filterRows
    );



    // =================================================
    // CLEAR FILTER
    // =================================================

    clearBtn.addEventListener(
        "click",
        function() {


            searchInput.value = "";

            fromDate.value = "";

            toDate.value = "";


            filterRows();


        }
    );



    // =================================================
    // INITIAL
    // =================================================

    filterRows();


})();

</script>



<!-- =====================================================
     PRINT CSS
====================================================== -->

<style>

@media print {

    .no-print {
        display: none !important;
    }


    body {
        background: #fff !important;
    }


    .container-fluid {
        width: 100% !important;
        padding: 0 !important;
    }


    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }


    .btn {
        display: none !important;
    }


    table {
        font-size: 12px;
    }


    th,
    td {
        padding: 6px !important;
    }


}

</style>