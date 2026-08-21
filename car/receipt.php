<?php

// ============================================================
// CUSTOMER / CAR RECEIPT
// ============================================================


// ============================================================
// ID CHECK
// ============================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {

    die("
        <h3 style='
            text-align:center;
            color:red;
            margin-top:50px;
        '>
            গাড়ির আইডি দেয়া হয়নি!
        </h3>
    ");
}

$id = (int) $_GET['id'];


// ============================================================
// FETCH CUSTOMER RECORD
// ============================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM customer_records
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch(PDO::FETCH_ASSOC);


// ============================================================
// DATA NOT FOUND
// ============================================================

if (!$record) {

    die("
        <h3 style='
            text-align:center;
            color:red;
            margin-top:50px;
        '>
            ডাটা পাওয়া যায়নি!
        </h3>
    ");
}


// ============================================================
// SAFE OUTPUT FUNCTION
// ============================================================

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// ============================================================
// CUSTOMER INFORMATION
// ============================================================

$customerName = $record['customer_name'] ?? '';

$customerPhone = $record['customer_phone'] ?? '';

$carNumber = $record['car_number'] ?? '';

$nid = $record['nid'] ?? '';

$address = $record['address'] ?? '';


// ============================================================
// PAYMENT INFORMATION
// ============================================================

$totalPrice = (float) (
    $record['total_price'] ?? 0
);

$paidAmount = (float) (
    $record['paid_amount'] ?? 0
);

$monthlyKisti = (float) (
    $record['monthly_kisti'] ?? 0
);

$totalKisti = (int) (
    $record['total_kisti'] ?? 0
);


// ============================================================
// DUE CALCULATION
// ============================================================

$totalPaid = $paidAmount;

$dueAmount = max(
    0,
    $totalPrice - $totalPaid
);


// ============================================================
// OTHER INFORMATION
// ============================================================

$invoiceNo = $record['invoice_no'] ?? '';

$quantity = (int) (
    $record['quantity'] ?? 0
);

$note = $record['note'] ?? '';

$kistiStartDate = $record['kisti_start_date'] ?? null;


// ============================================================
// RECEIPT DATE
// ============================================================

if (!empty($kistiStartDate)) {

    $receiptDate = bn_number(
        date(
            'd-m-Y',
            strtotime($kistiStartDate)
        )
    );

} else {

    $receiptDate = bn_number(
        date('d-m-Y')
    );
}


// ============================================================
// KISTI TIME CALCULATION
// ============================================================

$kistiEndDate = null;

$timeStatus = 'unknown';


// Overdue information
$overdueMonths = 0;

$overdueDays = 0;

$overdueTotalDays = 0;


// Remaining information
$remainingMonths = 0;

$remainingDays = 0;


// ============================================================
// START DATE + TOTAL KISTI AVAILABLE
// ============================================================

if (
    !empty($kistiStartDate)
    &&
    $totalKisti > 0
) {

    try {

        // --------------------------------------------------------
        // KISTI START DATE
        // --------------------------------------------------------

        $startDate = new DateTime(
            $kistiStartDate
        );


        // --------------------------------------------------------
        // KISTI END DATE
        // --------------------------------------------------------

        $kistiEndDate = clone $startDate;

        $kistiEndDate->modify(
            '+' . $totalKisti . ' months'
        );


        // --------------------------------------------------------
        // TODAY
        // --------------------------------------------------------

        $today = new DateTime();


        // ========================================================
        // TIME FINISHED
        // ========================================================

        if ($today > $kistiEndDate) {

            $overdue = $kistiEndDate->diff(
                $today
            );


            // মোট মাস
            $overdueMonths =
                ($overdue->y * 12)
                + $overdue->m;


            // অতিরিক্ত দিন
            $overdueDays =
                $overdue->d;


            // মোট দিন
            $overdueTotalDays =
                $overdue->days;


            $timeStatus = 'overdue';
        }


        // ========================================================
        // TIME STILL ACTIVE
        // ========================================================

        else {

            $remaining = $today->diff(
                $kistiEndDate
            );


            // বাকি মাস
            $remainingMonths =
                ($remaining->y * 12)
                + $remaining->m;


            // বাকি দিন
            $remainingDays =
                $remaining->d;


            $timeStatus = 'active';
        }

    } catch (Exception $e) {

        $timeStatus = 'unknown';
    }
}


// ============================================================
// END DATE DISPLAY
// ============================================================

$kistiEndDateText = '';

if ($kistiEndDate) {

    $kistiEndDateText = bn_number(
        $kistiEndDate->format('d-m-Y')
    );
}


// ============================================================
// START DATE DISPLAY
// ============================================================

$kistiStartDateText = '';

if (!empty($kistiStartDate)) {

    $kistiStartDateText = bn_number(
        date(
            'd-m-Y',
            strtotime($kistiStartDate)
        )
    );
}

?>


<!-- ============================================================
     MAIN CONTAINER
============================================================= -->

<div class="container-fluid mt-5">


    <!-- ========================================================
         PRINT BUTTON
    ========================================================= -->

    <div class="text-end mb-3">

        <button
            type="button"
            class="btn btn-primary"
            onclick="printDiv('receiptArea')"
        >
            🖨️ প্রিন্ট
        </button>

    </div>



    <!-- ========================================================
         RECEIPT AREA
    ========================================================= -->

    <div id="receiptArea">

        <div
            style="
                max-width:900px;
                margin:auto;
                border:2px solid #000;
                padding:25px;
                font-family:'SolaimanLipi', Arial, sans-serif;
                background:#fff;
            "
        >


            <!-- ==================================================
                 HEADER
            =================================================== -->

            <h2
                style="
                    text-align:center;
                    margin:0;
                    font-size:28px;
                "
            >
                জাহিরুল এন্টারপ্রাইজ
            </h2>


            <p
                style="
                    text-align:center;
                    margin:5px 0 15px;
                    font-size:14px;
                "
            >
                গাড়ি কিস্তি বিক্রয় ও পরিষেবা
            </p>


            <hr
                style="
                    border:0;
                    border-top:2px solid #000;
                "
            >



            <!-- ==================================================
                 RECEIPT INFORMATION
            =================================================== -->

            <table
                style="
                    width:100%;
                    font-size:14px;
                    margin-bottom:15px;
                "
            >

                <tr>

                    <td>

                        <b>রিসিট নং:</b>

                        <?= e($invoiceNo) ?>

                    </td>


                    <td
                        style="
                            text-align:right;
                        "
                    >

                        <b>তারিখ:</b>

                        <?= $receiptDate ?>

                    </td>

                </tr>

            </table>



            <!-- ==================================================
                 CUSTOMER INFORMATION
            =================================================== -->

            <table
                style="
                    width:100%;
                    font-size:15px;
                    margin-bottom:15px;
                "
            >

                <tr>

                    <td
                        style="
                            padding:4px 0;
                        "
                    >

                        <b>নাম:</b>

                        <?= e($customerName) ?>

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:4px 0;
                        "
                    >

                        <b>মোবাইল:</b>

                        <?= e($customerPhone) ?>

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:4px 0;
                        "
                    >

                        <b>আইডি:</b>

                        <?= e($nid) ?>

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:4px 0;
                        "
                    >

                        <b>ঠিকানা:</b>

                        <?= e($address) ?>

                    </td>

                </tr>

            </table>



            <!-- ==================================================
                 CAR INFORMATION
            =================================================== -->

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    font-size:14px;
                    margin-bottom:15px;
                "
            >

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                            width:25%;
                        "
                    >

                        <b>গাড়ি নম্বর</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <?= e($carNumber) ?>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                            width:20%;
                        "
                    >

                        <b>পরিমাণ</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <?= bn_number($quantity) ?>

                    </td>

                </tr>

            </table>



            <!-- ==================================================
                 PAYMENT INFORMATION
            =================================================== -->

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    font-size:14px;
                "
            >


                <!-- TOTAL PRICE -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                            width:40%;
                        "
                    >

                        <b>মোট মূল্য</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        ৳

                        <?= bn_number(
                            number_format($totalPrice)
                        ) ?>

                    </td>

                </tr>



                <!-- TOTAL PAID -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <b>মোট পরিশোধ</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                            color:green;
                            font-weight:bold;
                        "
                    >

                        ৳

                        <?= bn_number(
                            number_format($totalPaid)
                        ) ?>

                    </td>

                </tr>



                <!-- DUE -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <b>বাকি টাকা</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                            color:red;
                            font-weight:bold;
                        "
                    >

                        ৳

                        <?= bn_number(
                            number_format($dueAmount)
                        ) ?>

                    </td>

                </tr>



                <!-- MONTHLY KISTI -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <b>মাসিক কিস্তি</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        ৳

                        <?= bn_number(
                            number_format($monthlyKisti)
                        ) ?>

                    </td>

                </tr>



                <!-- TOTAL KISTI -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <b>মোট কিস্তি</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <?= bn_number($totalKisti) ?>

                        মাস

                    </td>

                </tr>



                <!-- GUARANTOR -->

                <tr>

                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <b>জামিনদার</b>

                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            padding:8px;
                        "
                    >

                        <?= e($note) ?>

                    </td>

                </tr>

            </table>



            <!-- ==================================================
                 KISTI TIME INFORMATION
            =================================================== -->

            <div
                style="
                    margin-top:20px;
                    padding:15px;
                    border:2px solid #000;
                    border-radius:8px;
                    background:#f8f9fa;
                "
            >

                <h4
                    style="
                        margin:0 0 15px;
                        text-align:center;
                    "
                >

                    ⏰ কিস্তির সময়ের হিসাব

                </h4>



                <?php if ($kistiEndDate): ?>


                    <!-- START DATE -->

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:8px;
                            border-bottom:1px dashed #999;
                        "
                    >

                        <b>
                            কিস্তি শুরু:
                        </b>


                        <strong>

                            <?= $kistiStartDateText ?>

                        </strong>

                    </div>



                    <!-- END DATE -->

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:8px;
                            border-bottom:1px dashed #999;
                        "
                    >

                        <b>
                            কিস্তির সময় শেষ:
                        </b>


                        <strong>

                            <?= $kistiEndDateText ?>

                        </strong>

                    </div>



                    <?php if ($timeStatus === 'overdue'): ?>


                        <!-- =========================================
                             OVERDUE
                        ========================================== -->

                        <div
                            style="
                                margin-top:15px;
                                padding:15px;
                                background:#ffe5e5;
                                border:2px solid #dc3545;
                                border-radius:8px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    color:#dc3545;
                                    font-size:20px;
                                    font-weight:bold;
                                "
                            >

                                🔴 কিস্তির সময় শেষ

                            </div>


                            <div
                                style="
                                    margin-top:8px;
                                    font-size:19px;
                                    font-weight:bold;
                                    color:#b02a37;
                                "
                            >

                                <?= bn_number($overdueMonths) ?>

                                মাস

                                <?= bn_number($overdueDays) ?>

                                দিন

                                হয়েছে

                            </div>


                            <div
                                style="
                                    margin-top:7px;
                                    color:#842029;
                                    font-size:14px;
                                "
                            >

                                মোট

                                <?= bn_number($overdueTotalDays) ?>

                                দিন পার হয়েছে

                            </div>

                        </div>



                    <?php elseif ($timeStatus === 'active'): ?>


                        <!-- =========================================
                             ACTIVE
                        ========================================== -->

                        <div
                            style="
                                margin-top:15px;
                                padding:15px;
                                background:#e8f7ee;
                                border:2px solid #198754;
                                border-radius:8px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    color:#198754;
                                    font-size:20px;
                                    font-weight:bold;
                                "
                            >

                                🟢 কিস্তির সময় চলছে

                            </div>


                            <div
                                style="
                                    margin-top:8px;
                                    font-size:18px;
                                    font-weight:bold;
                                    color:#146c43;
                                "
                            >

                                আরও

                                <?= bn_number($remainingMonths) ?>

                                মাস

                                <?= bn_number($remainingDays) ?>

                                দিন

                                বাকি

                            </div>

                        </div>



                    <?php else: ?>


                        <!-- =========================================
                             UNKNOWN
                        ========================================== -->

                        <div
                            style="
                                margin-top:15px;
                                padding:15px;
                                background:#f1f1f1;
                                border:1px solid #999;
                                border-radius:8px;
                                text-align:center;
                                color:#666;
                            "
                        >

                            কিস্তির সময়ের তথ্য সঠিকভাবে পাওয়া যায়নি।

                        </div>


                    <?php endif; ?>


                <?php else: ?>


                    <div
                        style="
                            text-align:center;
                            color:#6c757d;
                            padding:15px;
                        "
                    >

                        কিস্তির সময়ের তথ্য পাওয়া যায়নি।

                    </div>


                <?php endif; ?>

            </div>



            <!-- ==================================================
                 AMOUNT IN WORDS
            =================================================== -->

            <div
                style="
                    margin-top:15px;
                    padding:10px;
                    border:1px dashed #555;
                "
            >

                <b>মোট মূল্য:</b>

                <?= takaInWordsBn($totalPrice) ?>

            </div>



            <!-- ==================================================
                 SIGNATURE
            =================================================== -->

            <table
                style="
                    width:100%;
                    margin-top:70px;
                    font-size:14px;
                "
            >

                <tr>

                    <td style="text-align:left;">

                        _______________________

                        <br>

                        গ্রাহকের স্বাক্ষর

                    </td>


                    <td style="text-align:right;">

                        _______________________

                        <br>

                        কর্তৃপক্ষ

                    </td>

                </tr>

            </table>



            <!-- ==================================================
                 FOOTER
            =================================================== -->

            <div
                style="
                    text-align:center;
                    margin-top:30px;
                    padding-top:10px;
                    border-top:1px solid #000;
                    font-size:12px;
                "
            >

                ধন্যবাদ।
                আপনার সাথে ব্যবসা করতে পেরে আমরা আনন্দিত।

            </div>


        </div>

    </div>

</div>



<!-- ============================================================
     PRINT CSS
============================================================= -->

<style>

@media print {

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    body * {
        visibility: hidden;
    }

    #receiptArea,
    #receiptArea * {
        visibility: visible;
    }

    #receiptArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }

    button,
    .btn {
        display: none !important;
    }

    @page {
        size: A4;
        margin: 10mm;
    }

}

</style>