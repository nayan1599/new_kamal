<?php

// =====================================================
// CAR NUMBER CHECK
// =====================================================

if (!isset($_GET['car_number']) || empty($_GET['car_number'])) {
    die("<h3 class='text-center mt-5 text-danger'>গাড়ির নম্বর দেয়া হয়নি!</h3>");
}

$car_number = trim($_GET['car_number']);


// =====================================================
// CUSTOMER / CONTRACT RECORD
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM customer_records
    WHERE car_number = ?
    ORDER BY created_at DESC
    LIMIT 1
");

$stmt->execute([$car_number]);

$record = $stmt->fetch(PDO::FETCH_ASSOC);


// =====================================================
// ALL PAYMENTS
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM kisti_payments
    WHERE car_number = ?
    ORDER BY kisti_number ASC, payment_date DESC
");

$stmt->execute([$car_number]);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// PAYMENT EXISTS CHECK
// =====================================================

$hasPayments = !empty($payments);


// =====================================================
// CUSTOMER INFORMATION
// =====================================================

$customer_name  = $record['customer_name'] ?? '';
$customer_phone = $record['customer_phone'] ?? '';
$customer_email = $record['customer_email'] ?? null;
$customer_nid   = $record['nid'] ?? null;
$customer_addr  = $record['address'] ?? null;
$note  = $record['note'] ?? null;

// যদি customer_records-এ তথ্য না থাকে,
// payment থেকে customer information নেওয়া হবে
if ($hasPayments) {

    if (empty($customer_name)) {
        $customer_name = $payments[0]['customer_name'] ?? '';
    }

    if (empty($customer_phone)) {
        $customer_phone = $payments[0]['customer_phone'] ?? '';
    }
}


// =====================================================
// CONTRACT INFORMATION
// =====================================================

$hasContractTotal = (
    !empty($record) &&
    isset($record['total_price']) &&
    floatval($record['total_price']) > 0
);

$totalPrice = $hasContractTotal
    ? floatval($record['total_price'])
    : 0;

$discountAmount = $hasContractTotal
    ? floatval($record['discount_amount'] ?? 0)
    : 0;

$netPayable = $hasContractTotal
    ? max(0, $totalPrice - $discountAmount)
    : 0;

$totalKistiPlanned = $hasContractTotal
    ? intval($record['total_kisti'] ?? 0)
    : 0;

$monthlyKisti = $hasContractTotal
    ? floatval($record['monthly_kisti'] ?? 0)
    : 0;

$paid_amount = $hasContractTotal
    ? floatval($record['paid_amount'] ?? 0)
    : 0;

$nextDueDate = $record['next_due_date'] ?? null;

$kistiStartDate = $record['kisti_start_date'] ?? null;


// =====================================================
// PAYMENT SUMMARY
// =====================================================

$totalPaid        = 0;
$paymentFineTotal = 0;
$totalKistiPaid   = 0;
$maxKisti         = 0;
$lastPaymentDate  = null;


// =====================================================
// PAYMENT DATA CALCULATION
// =====================================================

foreach ($payments as $p) {

    $amount = floatval($p['amount'] ?? 0);
    $fine   = floatval($p['fine_amount'] ?? 0);

    $kistiNumber = intval($p['kisti_number'] ?? 0);

    // মোট payment
    $totalPaid += $amount;

    // IMPORTANT:
    // Fine আর minus হবে না
    $paymentFineTotal += $fine;


    if ($kistiNumber > $maxKisti) {
        $maxKisti = $kistiNumber;
    }

    if (
        !empty($p['payment_date']) &&
        (
            $lastPaymentDate === null ||
            strtotime($p['payment_date']) > strtotime($lastPaymentDate)
        )
    ) {
        $lastPaymentDate = $p['payment_date'];
    }
}


// =====================================================
// UNIQUE PAID KISTI COUNT
// =====================================================

$paidKistiNumbers = [];

foreach ($payments as $p) {

    $kistiNo = intval($p['kisti_number'] ?? 0);

    if ($kistiNo > 0) {
        $paidKistiNumbers[$kistiNo] = true;
    }
}

$totalKistiPaid = count($paidKistiNumbers);


// =====================================================
// REMAINING AMOUNT
// =====================================================

$remainingAmount = 0;

if ($hasContractTotal) {

    $remainingAmount =
        $totalPrice
        - $discountAmount
        - $paid_amount
        - $totalPaid;

    $remainingAmount = max(0, $remainingAmount);
}


// =====================================================
// TOTAL DUE
// =====================================================

$total_due = $remainingAmount;


// =====================================================
// DAILY FINE SETTINGS
// =====================================================

$dailyFine = 100;
$today = new DateTime( date('Y-m-d') );
$paidKistiMap = [];
foreach ($payments as $p) {
    $kistiNo = intval($p['kisti_number'] ?? 0);
    if ($kistiNo <= 0) {
        continue;
    }

    if (!isset($paidKistiMap[$kistiNo])) {

        $paidKistiMap[$kistiNo] = $p;

    } else {

        // সর্বশেষ payment record রাখা হবে
        if (
            !empty($p['payment_date']) &&
            strtotime($p['payment_date']) >
            strtotime($paidKistiMap[$kistiNo]['payment_date'] ?? '1970-01-01')
        ) {
            $paidKistiMap[$kistiNo] = $p;
        }
    }
}


// =====================================================
// MONTHLY DUE DATE FUNCTION
//
// উদাহরণ:
//
// Start Date = 22/08/2026
//
// Kisti 1 = 22/08/2026
// Kisti 2 = 22/09/2026
// Kisti 3 = 22/10/2026
// Kisti 4 = 22/11/2026
//
// Due date-এর দিন পর্যন্ত Fine = 0
// পরের দিন থেকে Fine = 100/day
// =====================================================

function getKistiDueDate(DateTime $startDate, int $kistiNo): DateTime
{
    $monthsToAdd = max(0, $kistiNo - 1);

    $year  = (int)$startDate->format('Y');
    $month = (int)$startDate->format('n');
    $day   = (int)$startDate->format('j');

    // Target month বের করা
    $targetMonthIndex = ($year * 12 + ($month - 1)) + $monthsToAdd;

    $targetYear  = intdiv($targetMonthIndex, 12);
    $targetMonth = ($targetMonthIndex % 12) + 1;

    // ঐ মাসের শেষ দিন
    $lastDayOfMonth = cal_days_in_month(
        CAL_GREGORIAN,
        $targetMonth,
        $targetYear
    );

    // যেমন 31 তারিখ হলে ফেব্রুয়ারিতে 28/29 হবে
    $targetDay = min($day, $lastDayOfMonth);

    return new DateTime(
        sprintf(
            '%04d-%02d-%02d',
            $targetYear,
            $targetMonth,
            $targetDay
        )
    );
}


// =====================================================
// AUTOMATIC FINE CALCULATION
// =====================================================

$autoFineTotal = 0;

$kistiFineDetails = [];


// =====================================================
// UNPAID FINE LIST
//
// শুধু যেসব কিস্তি PAYMENT হয়নি,
// সেগুলোর Fine এখানে আসবে।
//
// PAYMENT হয়ে গেলে সেই কিস্তির Fine = 0
// এবং unpaid list-এ আসবে না।
// =====================================================

$unpaidFineList = [];


if (
    $hasContractTotal &&
    !empty($kistiStartDate) &&
    $totalKistiPlanned > 0
) {

    try {

        $startDateObj = new DateTime(
            date(
                'Y-m-d',
                strtotime($kistiStartDate)
            )
        );


        for (
            $kistiNo = 1;
            $kistiNo <= $totalKistiPlanned;
            $kistiNo++
        ) {


            // =================================================
            // DUE DATE
            // =================================================

            $dueDateObj = getKistiDueDate(
                $startDateObj,
                $kistiNo
            );


            // =================================================
            // PAYMENT CHECK
            // =================================================

            $isPaid = isset(
                $paidKistiMap[$kistiNo]
            );


            // =================================================
            // PAYMENT হয়ে গেলে
            //
            // এই কিস্তির automatic fine আর দেখানো হবে না
            // =================================================

            if ($isPaid) {

                $paymentDate = $paidKistiMap[$kistiNo]['payment_date'] ?? null;

                $kistiFineDetails[$kistiNo] = [
                    'due_date'     => $dueDateObj->format('Y-m-d'),
                    'payment_date' => $paymentDate,
                    'late_days'    => 0,
                    'fine'         => 0,
                    'status'       => 'paid'
                ];

                continue;
            }


            // =================================================
            // PAYMENT হয়নি
            // =================================================

            $fineAmount = 0;
            $lateDays   = 0;


            // -------------------------------------------------
            // Due Date-এর দিন পর্যন্ত কোনো Fine নেই
            // -------------------------------------------------

            if ($today > $dueDateObj) {

                /*
                 * IMPORTANT:
                 *
                 * Due Date = 22
                 *
                 * 22 তারিখ:
                 * fine = 0
                 *
                 * 23 তারিখ:
                 * lateDays = 1
                 * fine = 100
                 *
                 * 24 তারিখ:
                 * lateDays = 2
                 * fine = 200
                 */

                $lateDays = $dueDateObj->diff($today)->days;

                $fineAmount =
                    $lateDays * $dailyFine;

                $autoFineTotal += $fineAmount;


                // শুধুমাত্র unpaid + late হলে list-এ আসবে
                $unpaidFineList[] = [
                    'kisti_no'  => $kistiNo,
                    'due_date'  => $dueDateObj->format('Y-m-d'),
                    'late_days' => $lateDays,
                    'fine'      => $fineAmount
                ];


                $kistiFineDetails[$kistiNo] = [
                    'due_date'     => $dueDateObj->format('Y-m-d'),
                    'payment_date' => null,
                    'late_days'    => $lateDays,
                    'fine'         => $fineAmount,
                    'status'       => 'unpaid'
                ];

            } else {

                // এখনো Due হয়নি অথবা আজ Due Date
                $kistiFineDetails[$kistiNo] = [
                    'due_date'     => $dueDateObj->format('Y-m-d'),
                    'payment_date' => null,
                    'late_days'    => 0,
                    'fine'         => 0,
                    'status'       => 'not_due'
                ];
            }
        }


    } catch (Exception $e) {

        $autoFineTotal = 0;
        $kistiFineDetails = [];
        $unpaidFineList = [];
    }
}


// =====================================================
// TOTAL FINE
// =====================================================

$totalFine = $paymentFineTotal ;
// print_r($autoFineTotal);
 
// =====================================================
// TOTAL DUE KISTI AMOUNT
// =====================================================

$totalDue_amount = 0;

if ($monthlyKisti > 0) {

    foreach ($payments as $p) {

        $amount = floatval(
            $p['amount'] ?? 0
        );

        $due = max(
            0,
            $monthlyKisti - $amount
        );

        $totalDue_amount += $due;
    }
}


// =====================================================
// PROGRESS
// =====================================================

$progressPct = (
    $hasContractTotal &&
    $totalKistiPlanned > 0
)
    ? min(
        100,
        round(
            ($totalKistiPaid / $totalKistiPlanned) * 100
        )
    )
    : null;


// =====================================================
// DATE / TIME CALCULATION
// =====================================================

$startDate = $kistiStartDate;

$endDate = date('Y-m-d');

$monthlyAmount = $monthlyKisti;


$totalMonths = 0;
$totalDays = 0;
$remainingMonths = 0;
$remainingFullMonths = 0;
$remainingDays = 0;
$totalAmount = 0;


if (!empty($startDate)) {

    try {

        $start = new DateTime(
            date(
                'Y-m-d',
                strtotime($startDate)
            )
        );

        $end = new DateTime($endDate);

        $diff = $start->diff($end);


        // মোট মাস
        $totalMonths =
            ($diff->y * 12)
            + $diff->m;


        // অতিরিক্ত দিন
        $totalDays =
            $diff->d;


        // Daily rate
        $dailyRate =
            $monthlyAmount > 0
                ? ($monthlyAmount / 30)
                : 0;


        // মোট চলতি হিসাব
        $totalAmount =
            ($totalMonths * $monthlyAmount)
            + ($totalDays * $dailyRate)
            - $totalPaid;


        $totalAmount =
            max(
                0,
                $totalAmount
            );


        // Passed months
        $passedMonths =
            ($diff->y * 12)
            + $diff->m;

        $passedDays =
            $diff->d;


        $passedTotalMonths =
            $passedMonths
            + ($passedDays / 30);


        // Remaining months
        $remainingMonths =
            $totalKistiPlanned
            - $passedTotalMonths;


        $remainingMonths =
            max(
                0,
                $remainingMonths
            );


        // মাস
        $remainingFullMonths =
            floor($remainingMonths);


        // দিন
        $remainingDays =
            round(
                (
                    $remainingMonths
                    - $remainingFullMonths
                ) * 30
            );


        // 30 দিন হলে ১ মাসে convert
        if ($remainingDays >= 30) {

            $remainingFullMonths++;
            $remainingDays = 0;
        }


    } catch (Exception $e) {

        $totalMonths = 0;
        $totalDays = 0;
        $totalAmount = 0;
        $remainingMonths = 0;
        $remainingFullMonths = 0;
        $remainingDays = 0;
    }
}


// =====================================================
// RECEIPT NUMBER
// =====================================================

if (!empty($record['invoice_no'])) {

    $receiptSerial =
        $record['invoice_no'];

} else {

    $dateForReceipt =
        $lastPaymentDate
        ?? date('Y-m-d');


    $receiptSerial =
        'JE-'
        . preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            $car_number
        )
        . '-'
        . date(
            'ym',
            strtotime($dateForReceipt)
        );
}

?>

<style>

    .receipt-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .receipt {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
    }

    @media print {

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }

        .receipt {
            width: 100% !important;
            max-width: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }

        @page {
            size: A4 landscape;
            margin: 8mm;
        }

    }

</style>


<div class="container-fluid">


    <!-- =====================================================
         PRINT BUTTON
    ===================================================== -->

    <div
        class="no-print"
        style="
            text-align:right;
            padding:8px 0;
            margin:0 auto;
        "
    >

        <button
            onclick="printDiv('receiptArea')"
            style="
                background:#198754;
                color:#fff;
                border:none;
                border-radius:6px;
                padding:10px 18px;
                font-size:.9rem;
                font-weight:600;
                cursor:pointer;
            "
        >
            🖨️ Print
        </button>

    </div>


    <!-- =====================================================
         RECEIPT
    ===================================================== -->

    <div
        class="receipt"
        id="receiptArea"
        style="
            -webkit-print-color-adjust:exact !important;
            print-color-adjust:exact !important;
            position:relative;
            margin:0 auto;
            background:#faf6ec;
            border-radius:4px;
            box-shadow:
                0 25px 60px -15px rgba(8,21,39,0.35),
                0 0 0 1px rgba(13,35,64,0.06);
            overflow:hidden;
        "
    >


        <!-- =====================================================
             TOP DESIGN
        ===================================================== -->

        <div
            style="
                print-color-adjust:exact !important;
                height:14px;
                background-color:#0d2340;
                background-image:
                    repeating-linear-gradient(
                        115deg,
                        transparent 0 6px,
                        rgba(184,134,60,0.55) 6px 7px,
                        transparent 7px 13px
                    ),
                    repeating-linear-gradient(
                        65deg,
                        transparent 0 6px,
                        rgba(228,201,141,0.35) 6px 7px,
                        transparent 7px 13px
                    );
            "
        ></div>


        <!-- =====================================================
             HEADER
        ===================================================== -->

        <div
            style="
                print-color-adjust:exact !important;
                background:linear-gradient(
                    160deg,
                    #0d2340 0%,
                    #081527 100%
                );
                color:#f4efe1;
                padding:30px 40px 26px;
                position:relative;
                border-bottom:1px solid rgba(228,201,141,0.35);
            "
        >

            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:flex-start;
                    gap:20px;
                "
            >

                <div>

                    <p
                        style="
                            font-family:'Noto Serif Bengali',serif;
                            font-size:1.9rem;
                            font-weight:700;
                            letter-spacing:.5px;
                            margin:0;
                        "
                    >
                        জহিরুল এন্টারপ্রাইজ
                    </p>

                    <p
                        style="
                            margin:4px 0 0;
                            font-size:.82rem;
                            color:#e4c98d;
                            letter-spacing:1.5px;
                            text-transform:uppercase;
                        "
                    >
                        গাড়ি কিস্তি বিক্রয় ও পরিষেবা
                    </p>

                </div>


                <div
                    style="
                        text-align:right;
                        font-size:.85rem;
                        color:#d9d2bd;
                        line-height:1.7;
                    "
                >

                    রিসিট নং:

                    <b
                        style="
                            color:#fff;
                            font-family:'JetBrains Mono',monospace;
                        "
                    >
                        <?= htmlspecialchars($receiptSerial) ?>
                    </b>

                    <br>

                    ইস্যুর তারিখ:

                    <b style="color:#fff;">
                        <?= date('d/m/Y') ?>
                    </b>

                </div>

            </div>


            <div
                style="
                    margin-top:18px;
                    display:flex;
                    align-items:center;
                    gap:14px;
                "
            >

                <p style="margin:0;">
                    মানি রিসিট • কিস্তি হিসাব বিবরণী
                </p>

                <div
                    style="
                        flex:1;
                        height:1px;
                        background:rgba(228,201,141,0.35);
                    "
                ></div>

                <p
                    style="
                        margin:0;
                        font-family:'JetBrains Mono',monospace;
                    "
                >
                    <?= htmlspecialchars($car_number) ?>
                </p>

            </div>

        </div>


        <!-- =====================================================
             MAIN CONTENT
        ===================================================== -->

        <div style="padding:10px;position:relative;">


            <!-- =================================================
                 CUSTOMER / CAR / CONTRACT
            ================================================= -->

            <div
                style="
                    display:grid;
                    grid-template-columns:repeat(3,1fr);
                    gap:18px;
                    margin-bottom:30px;
                "
            >


                <!-- CUSTOMER -->

                <div
                    style="
                        border:1px solid #e7ddc7;
                        border-radius:6px;
                        padding:18px 20px;
                        background:#fffdf7;
                    "
                >

                    <h6
                        style="
                            margin:0 0 12px;
                            font-size:.78rem;
                            letter-spacing:1.5px;
                            text-transform:uppercase;
                            color:#b8863c;
                            font-weight:700;
                        "
                    >
                        গ্রাহকের তথ্য
                    </h6>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                        "
                    >
                        <span style="color:#5b6472;">
                            নাম
                        </span>

                        <span style="font-weight:600;">
                            <?= htmlspecialchars(
                                $customer_name ?: '—'
                            ) ?>
                        </span>
                    </div>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                            border-top:1px dashed #e7ddc7;
                        "
                    >

                        <span style="color:#5b6472;">
                            মোবাইল
                        </span>

                        <span
                            style="
                                font-weight:600;
                                font-family:'JetBrains Mono',monospace;
                            "
                        >
                            <?= !empty($customer_phone)
                                ? bn_number(
                                    htmlspecialchars($customer_phone)
                                )
                                : '—'
                            ?>
                        </span>

                    </div>


                    <?php if ($customer_email): ?>

                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                ইমেইল
                            </span>

                            <span style="font-weight:600;">
                                <?= htmlspecialchars($customer_email) ?>
                            </span>

                        </div>

                    <?php endif; ?>


                    <?php if ($customer_nid): ?>

                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                এনআইডি
                            </span>

                            <span
                                style="
                                    font-weight:600;
                                    font-family:'JetBrains Mono',monospace;
                                "
                            >
                                <?= bn_number(
                                    htmlspecialchars($customer_nid)
                                ) ?>
                            </span>

                        </div>

                    <?php endif; ?>


                    <?php if ($customer_addr): ?>

                        <div
                            style=" display:flex; justify-content:space-between;  padding:5px 0; font-size:.95rem;  border-top:1px dashed #e7ddc7; " >

                            <span style="color:#5b6472;">
                                ঠিকানা  
                            </span>

                            <span style="font-weight:600; margin-left:5px;">
                                 <?= htmlspecialchars($customer_addr) ?>
                            </span>

                        </div>
                        <div style=" display:flex; justify-content:space-between; padding:5px 0; font-size:.95rem; border-top:1px dashed #e7ddc7;" >
                            <span style="color:#5b6472;"> নোট </span>
                            <span style="font-weight:600; margin-left:5px;"> <?= htmlspecialchars($note) ?> </span>

                        </div>
                    <?php endif; ?>

                </div>


                <!-- =================================================
                     CAR INFORMATION
                ================================================= -->

                <div
                    style="
                        border:1px solid #e7ddc7;
                        border-radius:6px;
                        padding:18px 20px;
                        background:#fffdf7;
                    "
                >

                    <h6
                        style="
                            margin:0 0 12px;
                            font-size:.78rem;
                            letter-spacing:1.5px;
                            text-transform:uppercase;
                            color:#b8863c;
                            font-weight:700;
                        "
                    >
                        গাড়ির তথ্য
                    </h6>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                        "
                    >

                        <span style="color:#5b6472;">
                            গাড়ির নম্বর
                        </span>

                        <span
                            style="
                                font-weight:600;
                                font-family:'JetBrains Mono',monospace;
                            "
                        >
                            <?= bn_number(
                                htmlspecialchars($car_number)
                            ) ?>
                        </span>

                    </div>


                    <?php if (!empty($record)): ?>

                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                গাড়ির নাম
                            </span>

                            <span style="font-weight:600;">
                                <?= htmlspecialchars(
                                    $record['car_name'] ?? '—'
                                ) ?>
                            </span>

                        </div>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                মডেল / বছর
                            </span>

                            <span style="font-weight:600;">

                                <?= htmlspecialchars(
                                    $record['car_model'] ?? '—'
                                ) ?>

                                /

                                <?= htmlspecialchars(
                                    $record['car_year'] ?? '—'
                                ) ?>

                            </span>

                        </div>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                ক্রয়ের ধরন
                            </span>

                            <span style="font-weight:600;">

                                <?= ($record['type'] ?? '') === 'installment'
                                    ? 'কিস্তিতে'
                                    : htmlspecialchars(
                                        $record['type'] ?? '—'
                                    )
                                ?>

                            </span>

                        </div>


                        <?php if (!empty($record['kisti_start_date'])): ?>

                            <div
                                style="
                                    display:flex;
                                    justify-content:space-between;
                                    padding:5px 0;
                                    font-size:.95rem;
                                    border-top:1px dashed #e7ddc7;
                                "
                            >

                                <span style="color:#5b6472;">
                                    কিস্তি শুরুর তারিখ
                                </span>

                                <span style="font-weight:600;">

                                    <?= bn_number(
                                        date(
                                            'd/m/Y',
                                            strtotime(
                                                $record['kisti_start_date']
                                            )
                                        )
                                    ) ?>

                                </span>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                            border-top:1px dashed #e7ddc7;
                        "
                    >

                        <span style="color:#5b6472;">
                            সর্বশেষ পরিশোধ
                        </span>

                        <span style="font-weight:600;">

                            <?= $lastPaymentDate
                                ? bn_number(
                                    date(
                                        'd/m/Y',
                                        strtotime(
                                            $lastPaymentDate
                                        )
                                    )
                                )
                                : '—'
                            ?>

                        </span>

                    </div>

                </div>


                <!-- =================================================
                     CONTRACT INFORMATION
                ================================================= -->

                <div
                    style="
                        border:1px solid #e7ddc7;
                        border-radius:6px;
                        padding:18px 20px;
                        background:#fffdf7;
                    "
                >

                    <h6
                        style="
                            margin:0 0 12px;
                            font-size:.78rem;
                            letter-spacing:1.5px;
                            text-transform:uppercase;
                            color:#b8863c;
                            font-weight:700;
                        "
                    >
                        চুক্তির তথ্য
                    </h6>


                    <?php if ($hasContractTotal): ?>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                            "
                        >

                            <span style="color:#5b6472;">
                                মোট মূল্য
                            </span>

                            <span
                                style="
                                    font-weight:600;
                                    font-family:'JetBrains Mono',monospace;
                                "
                            >
                                ৳ <?= bn_number(
                                    number_format($totalPrice)
                                ) ?>
                            </span>

                        </div>


                        <?php if ($discountAmount > 0): ?>

                            <div
                                style="
                                    display:flex;
                                    justify-content:space-between;
                                    padding:5px 0;
                                    font-size:.95rem;
                                    border-top:1px dashed #e7ddc7;
                                "
                            >

                                <span style="color:#5b6472;">
                                    ডিসকাউন্ট
                                </span>

                                <span
                                    style="
                                        font-weight:600;
                                        font-family:'JetBrains Mono',monospace;
                                    "
                                >
                                    ৳ <?= bn_number(
                                        number_format($discountAmount)
                                    ) ?>
                                </span>

                            </div>

                        <?php endif; ?>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                জমাঃ
                            </span>

                            <span
                                style="
                                    font-weight:600;
                                    font-family:'JetBrains Mono',monospace;
                                "
                            >
                                ৳ <?= bn_number(
                                    number_format($paid_amount)
                                ) ?>
                            </span>

                        </div>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                মোট বাকিঃ
                            </span>

                            <span
                                style="
                                    font-weight:600;
                                    font-family:'JetBrains Mono',monospace;
                                "
                            >
                                ৳ <?= bn_number(
                                    number_format($total_due)
                                ) ?>
                            </span>

                        </div>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                মাসিক কিস্তি
                            </span>

                            <span
                                style="
                                    font-weight:600;
                                    font-family:'JetBrains Mono',monospace;
                                "
                            >
                                ৳ <?= bn_number(
                                    number_format($monthlyKisti)
                                ) ?>
                            </span>

                        </div>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                                border-top:1px dashed #e7ddc7;
                            "
                        >

                            <span style="color:#5b6472;">
                                মোট কিস্তি সংখ্যা
                            </span>

                            <span style="font-weight:600;">

                                <?= bn_number(
                                    $totalKistiPlanned
                                ) ?>

                                টি

                            </span>

                        </div>


                        <?php if ($nextDueDate): ?>

                            <div
                                style="
                                    display:flex;
                                    justify-content:space-between;
                                    padding:5px 0;
                                    font-size:.95rem;
                                    border-top:1px dashed #e7ddc7;
                                "
                            >

                                <span style="color:#5b6472;">
                                    পরবর্তী কিস্তির তারিখ
                                </span>

                                <span style="font-weight:600;">

                                    <?= bn_number(
                                        date(
                                            'd/m/Y',
                                            strtotime(
                                                $nextDueDate
                                            )
                                        )
                                    ) ?>

                                </span>

                            </div>

                        <?php endif; ?>


                    <?php else: ?>


                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                padding:5px 0;
                                font-size:.95rem;
                            "
                        >

                            <span style="color:#5b6472;">
                                চুক্তির তথ্য
                            </span>

                            <span style="font-weight:600;">
                                পাওয়া যায়নি
                            </span>

                        </div>


                    <?php endif; ?>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                            border-top:1px dashed #e7ddc7;
                        "
                    >

                        <span style="color:#5b6472;">
                            মোট সময়
                        </span>

                        <span style="font-weight:600;">

                            <?= bn_number($totalMonths) ?>
                            মাস
                            <?= bn_number($totalDays) ?>
                            দিন

                        </span>

                    </div>


                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            padding:5px 0;
                            font-size:.95rem;
                            border-top:1px dashed #e7ddc7;
                        "
                    >

                        <span style="color:#5b6472;">
                            বাকি সময়
                        </span>

                        <span style="font-weight:600;">

                            <?= bn_number($remainingFullMonths) ?>
                            মাস
                            <?= bn_number($remainingDays) ?>
                            দিন

                        </span>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 SUMMARY CARDS
            ===================================================== -->

            <div
                style="
                    position:relative;
                    margin-bottom:22px;
                "
            >

                <div
                    style="
                        display:grid;
                        grid-template-columns:repeat(4,1fr);
                        gap:14px;
                    "
                >


                    <!-- PAID KISTI -->

                    <div
                        style="
                            border:1px solid #e7ddc7;
                            border-radius:6px;
                            padding:16px 12px;
                            text-align:center;
                            background:#fffdf7;
                        "
                    >

                        <div
                            style="
                                font-size:.72rem;
                                color:#5b6472;
                                letter-spacing:.5px;
                            "
                        >
                            মোট কিস্তি পরিশোধ
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                            "
                        >
                            <?= bn_number(
                                $totalKistiPaid
                            ) ?>
                            টি
                        </div>


                        <div
                            style="
                                font-size:.72rem;
                                color:#5b6472;
                                margin-top:5px;
                            "
                        >
                            মোট কিস্তি বাকি
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                            "
                        >
                            <?= bn_number(
                                $hasContractTotal
                                    ? max(
                                        0,
                                        $totalKistiPlanned
                                        - $totalKistiPaid
                                    )
                                    : 0
                            ) ?>
                            টি
                        </div>

                    </div>


                    <!-- TOTAL COLLECTION -->

                    <div
                        style="
                            border:1px solid #e7ddc7;
                            border-radius:6px;
                            padding:16px 12px;
                            text-align:center;
                            background:#fffdf7;
                        "
                    >

                        <div
                            style="
                                font-size:.72rem;
                                color:#5b6472;
                            "
                        >
                            মোট আদায়
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                                color:#1f6f43;
                                font-family:'JetBrains Mono',monospace;
                            "
                        >
                            ৳ <?= bn_number(
                                number_format($totalPaid)
                            ) ?>
                        </div>

                    </div>


                    <!-- TOTAL FINE -->

                    <div
                        style="
                            border:1px solid #e7ddc7;
                            border-radius:6px;
                            padding:16px 12px;
                            text-align:center;
                            background:#fffdf7;
                        "
                    >

                        <div
                            style="
                                font-size:.72rem;
                                color:#5b6472;
                            "
                        >
                            মোট জরিমানা
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                                font-family:'JetBrains Mono',monospace;
                                color:<?= $totalFine > 0
                                    ? '#9b2226'
                                    : 'inherit'
                                ?>;
                            "
                        >
                            ৳ <?= bn_number(
                                number_format($totalFine)
                            ) ?>
                        </div>

                        <div
                            style="
                                font-size:.72rem;
                                color:#9b2226;
                                margin-top:5px;
                            "
                        >
                            Due Date-এর পর প্রতিদিন
                            <strong>১০০</strong> টাকা
                        </div>


 <div
                            style="
                             margin-top:6px;
                                font-size:.72rem;
                                color:#5b6472;
                            "
                        >
                            বাকি জরিমানা
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                                font-family:'JetBrains Mono',monospace;
                                color:<?= $totalFine > 0
                                    ? '#9b2226'
                                    : 'inherit'
                                ?>;
                            "
                        >
                            ৳ <?= bn_number(
                                number_format( $autoFineTotal - $totalFine)
                            ) ?>
                        </div>


                    </div>


                    <!-- DUE -->

                    <div
                        style="
                            border:1px solid #e7ddc7;
                            border-radius:6px;
                            padding:16px 12px;
                            text-align:center;
                            background:#fffdf7;
                        "
                    >

                        <div
                            style="
                                font-size:.72rem;
                                color:#5b6472;
                            "
                        >
                            বাকি টাকা
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                                color:#9b2226;
                                font-family:'JetBrains Mono',monospace;
                            "
                        >
                            ৳ <?= bn_number(
                                number_format($remainingAmount)
                            ) ?>
                        </div>

                        <div
                            style="
                                font-size:.72rem;
                                color:#9b2226;
                                margin-top:5px;
                            "
                        >
                            মোট বকেয়া
                        </div>

                        <div
                            style="
                                font-size:1.35rem;
                                font-weight:700;
                                margin-top:6px;
                                color:#ffc107;
                                font-family:'JetBrains Mono',monospace;
                            "
                        >
                            ৳ <?= bn_number(
                                number_format($totalAmount)
                            ) ?>
                        </div>

                    </div>

                </div>


                <!-- PROGRESS -->

                <?php if ($progressPct !== null): ?>

                    <div
                        style="
                            margin-top:16px;
                            height:8px;
                            border-radius:6px;
                            background:#e7ddc7;
                            overflow:hidden;
                        "
                    >

                        <div
                            style="
                                height:100%;
                                border-radius:6px;
                                background:linear-gradient(
                                    90deg,
                                    #b8863c,
                                    #e4c98d
                                );
                                width:<?= $progressPct ?>%;
                            "
                        ></div>

                    </div>


                    <div
                        style="
                            margin-top:6px;
                            font-size:.78rem;
                            color:#5b6472;
                            text-align:right;
                        "
                    >

                        <?= bn_number($progressPct) ?>%
                        কিস্তি পরিশোধিত

                        (
                        <?= bn_number($totalKistiPaid) ?>
                        /
                        <?= bn_number($totalKistiPlanned) ?>
                        কিস্তি
                        )

                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================================================
                 AUTOMATIC FINE NOTICE
                 
                 শুধু UNPAID কিস্তির জরিমানা থাকলে দেখাবে
            ===================================================== -->

            <?php if ($autoFineTotal > 0): ?>

                <div
                    style="
                        margin:0 0 22px;
                        padding:14px 18px;
                        border:1px solid #f1b0b7;
                        border-radius:6px;
                        background:#fff5f5;
                        color:#842029;
                        font-size:.9rem;
                    "
                >

                    <strong>
                        ⚠️ বিলম্ব জরিমানা:
                    </strong>

                    Payment না করা বকেয়া কিস্তির জন্য

                    <strong>
                        ৳ <?= bn_number(
                            number_format($autoFineTotal)
                        ) ?>
                    </strong>

                    টাকা স্বয়ংক্রিয় জরিমানা হিসাব হয়েছে।

                    <br>

                    <small>

                        নিয়ম:
                        <strong>Due Date-এর দিন জরিমানা ০ টাকা</strong>।
                        পরের দিন থেকে প্রতিদিন
                        <strong>১০০ টাকা</strong> জরিমানা।

                    </small>

                </div>

            <?php endif; ?>


            <!-- =====================================================
                 UNPAID FINE LIST
                 
                 PAYMENT হয়ে গেলে এখানে আসবে না
            ===================================================== -->

            <!-- <?php if (!empty($unpaidFineList)): ?>

                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        margin:0 0 14px;
                        font-size:.8rem;
                        letter-spacing:1.5px;
                        color:#9b2226;
                        font-weight:700;
                    "
                >

                    <span>
                        বকেয়া জরিমানার তালিকা
                    </span>

                    <div
                        style="
                            flex:1;
                            height:1px;
                            background:#e7ddc7;
                        "
                    ></div>

                </div>


                <div class="table-responsive">

                    <table
                        style="
                            width:100%;
                            border-collapse:collapse;
                            margin-bottom:25px;
                        "
                    >

                        <thead>

                            <tr>

                                <th
                                    style="
                                        background:#9b2226;
                                        color:#fff;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    কিস্তি নং
                                </th>

                                <th
                                    style="
                                        background:#9b2226;
                                        color:#fff;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    Due Date
                                </th>

                                <th
                                    style="
                                        background:#9b2226;
                                        color:#fff;
                                        padding:11px 12px;
                                        text-align:center;
                                    "
                                >
                                    বকেয়া দিন
                                </th>

                                <th
                                    style="
                                        background:#9b2226;
                                        color:#fff;
                                        padding:11px 12px;
                                        text-align:right;
                                    "
                                >
                                    জরিমানা
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $unpaidFineList
                                as $fineRow
                            ): ?>

                                <tr>

                                    <td
                                        style="
                                            padding:10px 12px;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <?= bn_number(
                                            $fineRow['kisti_no']
                                        ) ?>

                                    </td>


                                    <td
                                        style="
                                            padding:10px 12px;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <?= bn_number(
                                            date(
                                                'd/m/Y',
                                                strtotime(
                                                    $fineRow['due_date']
                                                )
                                            )
                                        ) ?>

                                    </td>


                                    <td
                                        style="
                                            padding:10px 12px;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:center;
                                        "
                                    >

                                        <?= bn_number(
                                            $fineRow['late_days']
                                        ) ?>

                                        দিন

                                    </td>


                                    <td
                                        style="
                                            padding:10px 12px;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:right;
                                            color:#9b2226;
                                            font-weight:700;
                                        "
                                    >

                                        ৳ <?= bn_number(
                                            number_format(
                                                $fineRow['fine']
                                            )
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>
 -->

            <!-- =====================================================
                 PAYMENT LIST
                 
                 Payment না থাকলে পুরো Payment List দেখাবে না
            ===================================================== -->

            <?php if ($hasPayments): ?>


                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        margin:0 0 14px;
                        font-size:.8rem;
                        letter-spacing:1.5px;
                        color:#b8863c;
                        font-weight:700;
                    "
                >

                    <span>
                        কিস্তির বিস্তারিত তথ্য
                    </span>

                    <div
                        style="
                            flex:1;
                            height:1px;
                            background:#e7ddc7;
                        "
                    ></div>

                </div>


                <div class="table-responsive">

                    <table
                        style="
                            width:100%;
                            border-collapse:collapse;
                            margin-bottom:6px;
                        "
                    >

                        <thead>

                            <tr>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    #
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    তারিখ
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    কিস্তি নং
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:right;
                                    "
                                >
                                    মাসিক কিস্তি
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:right;
                                    "
                                >
                                    জমা
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:right;
                                    "
                                >
                                    বাকি
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:right;
                                    "
                                >
                                    জরিমানা
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    মেথড
                                </th>

                                <th
                                    style="
                                        background:#0d2340;
                                        color:#f2ead3;
                                        padding:11px 12px;
                                        text-align:left;
                                    "
                                >
                                    প্রাপক
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php

                            $i = 0;

                            foreach (
                                $payments
                                as $row
                            ):

                                $i++;


                                // =================================
                                // PAYMENT AMOUNT
                                // =================================

                                $rowAmount =
                                    floatval(
                                        $row['amount'] ?? 0
                                    );


                                // =================================
                                // ROW DUE
                                // =================================

                                $due =
                                    max(
                                        0,
                                        $monthlyKisti
                                        - $rowAmount
                                    );


                                // =================================
                                // DATABASE FINE
                                // =================================

                                $paymentFine =
                                    floatval(
                                        $row['fine_amount'] ?? 0
                                    );


                                /*
                                 * IMPORTANT:
                                 *
                                 * Payment হয়ে গেলে automatic fine
                                 * আর row-তে যোগ হবে না।
                                 */

                                $rowKistiNumber =
                                    intval(
                                        $row['kisti_number'] ?? 0
                                    );


                                $rowAutoFine = 0;


                                // Payment already exists,
                                // তাই automatic fine = 0


                                $rowTotalFine =
                                    $paymentFine
                                    + $rowAutoFine;


                                // =================================
                                // BACKGROUND
                                // =================================

                                $rowBg =
                                    ($i % 2 === 0)
                                        ? '#f4efe1'
                                        : 'transparent';

                            ?>

                                <tr
                                    style="
                                        background:<?= $rowBg ?>;
                                    "
                                >

                                    <!-- # -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >
                                        <?= bn_number($i) ?>
                                    </td>


                                    <!-- DATE -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <?= bn_number(
                                            date(
                                                'd/m/Y',
                                                strtotime(
                                                    $row['payment_date']
                                                )
                                            )
                                        ) ?>

                                    </td>


                                    <!-- KISTI NUMBER -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <span
                                            style="
                                                display:inline-block;
                                                min-width:26px;
                                                padding:2px 6px;
                                                border-radius:4px;
                                                background:#0d2340;
                                                color:#f2ead3;
                                                font-family:'JetBrains Mono',monospace;
                                                font-size:.8rem;
                                                text-align:center;
                                            "
                                        >

                                            <?= bn_number(
                                                $rowKistiNumber
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- MONTHLY -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:right;
                                        "
                                    >

                                        <?= bn_number(
                                            number_format(
                                                $monthlyKisti
                                            )
                                        ) ?>

                                    </td>


                                    <!-- PAID -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:right;
                                        "
                                    >

                                        <?= bn_number(
                                            number_format(
                                                $rowAmount
                                            )
                                        ) ?>

                                    </td>


                                    <!-- DUE -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:right;
                                            font-family:'JetBrains Mono',monospace;
                                        "
                                    >

                                        <?= bn_number(
                                            number_format(
                                                $due
                                            )
                                        ) ?>

                                    </td>


                                    <!-- FINE -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                            text-align:right;
                                            font-family:'JetBrains Mono',monospace;
                                            color:<?= $rowTotalFine > 0
                                                ? '#9b2226'
                                                : 'inherit'
                                            ?>;
                                            font-weight:<?= $rowTotalFine > 0
                                                ? '700'
                                                : '400'
                                            ?>;
                                        "
                                    >

                                        <?= $rowTotalFine > 0

                                            ? bn_number(
                                                number_format(
                                                    $rowTotalFine
                                                )
                                            )

                                            : '—'
                                        ?>

                                    </td>


                                    <!-- METHOD -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <?= strtoupper(
                                            htmlspecialchars(
                                                $row['payment_method']
                                                ?? '—'
                                            )
                                        ) ?>

                                    </td>


                                    <!-- RECEIVED BY -->

                                    <td
                                        style="
                                            padding:10px 12px;
                                            font-size:.9rem;
                                            border-bottom:1px solid #e7ddc7;
                                        "
                                    >

                                        <?= htmlspecialchars(
                                            $row['received_by']
                                            ?? '—'
                                        ) ?>

                                    </td>

                                </tr>


                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     SIGNATURE
                ================================================= -->

                <div
                    style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:40px;
                        margin:38px 0 8px;
                    "
                >

                    <div
                        style="
                            border-top:1px solid #1f2a37;
                            padding-top:8px;
                            font-size:.85rem;
                            color:#5b6472;
                            text-align:center;
                        "
                    >
                        গ্রহণকারীর স্বাক্ষর
                    </div>


                    <div
                        style="
                            border-top:1px solid #1f2a37;
                            padding-top:8px;
                            font-size:.85rem;
                            color:#5b6472;
                            text-align:center;
                        "
                    >
                        কর্তৃপক্ষের স্বাক্ষর ও সিল
                    </div>

                </div>


            <?php else: ?>


                <!-- =================================================
                     NO PAYMENT MESSAGE
                ================================================= -->

                <div
                    style="
                        margin:20px 0 25px;
                        padding:25px;
                        text-align:center;
                        border:1px dashed #d6b56b;
                        border-radius:8px;
                        background:#fffdf7;
                    "
                >

                    <div
                        style="
                            font-size:2rem;
                            margin-bottom:8px;
                        "
                    >
                        📋
                    </div>


                    <div
                        style="
                            font-size:1.05rem;
                            font-weight:700;
                            color:#9b2226;
                        "
                    >
                        এখনো কোনো কিস্তি পরিশোধ করা হয়নি
                    </div>


                    <div
                        style="
                            margin-top:6px;
                            font-size:.85rem;
                            color:#5b6472;
                        "
                    >
                        Payment করার পর কিস্তির Payment List এখানে দেখা যাবে।
                    </div>


                    <?php if ($autoFineTotal > 0): ?>

                        <div
                            style="
                                margin-top:15px;
                                padding:12px;
                                border-radius:6px;
                                background:#fff5f5;
                                color:#842029;
                                font-size:.9rem;
                            "
                        >

                            ⚠️ বর্তমান পর্যন্ত বকেয়া জরিমানা:

                            <strong>
                                ৳ <?= bn_number(
                                    number_format(
                                        $autoFineTotal
                                    )
                                ) ?>
                            </strong>

                        </div>

                    <?php endif; ?>

                </div>


            <?php endif; ?>

        </div>


        <!-- =====================================================
             FOOTER
        ===================================================== -->

        <div
            style="
                padding:18px 40px 26px;
                text-align:center;
                color:#eee5c9;
                background:linear-gradient(
                    160deg,
                    #081527 0%,
                    #0d2340 100%
                );
            "
        >

            <p
                style="
                    margin:0;
                    font-size:.85rem;
                "
            >
                ধন্যবাদ! আপনার সাথে ব্যবসা করতে পেরে আমরা আনন্দিত।
            </p>


            <small
                style="
                    display:block;
                    margin-top:6px;
                    color:#a79f83;
                    font-size:.72rem;
                    letter-spacing:.4px;
                "
            >
                এই ডকুমেন্ট কম্পিউটার জেনারেটেড এবং অফিসিয়াল —
                রিসিট নং
                <?= htmlspecialchars($receiptSerial) ?>
            </small>

        </div>


        <!-- =====================================================
             BOTTOM DESIGN
        ===================================================== -->

        <div
            style="
                print-color-adjust:exact !important;
                height:14px;
                background-color:#0d2340;
                background-image:
                    repeating-linear-gradient(
                        115deg,
                        transparent 0 6px,
                        rgba(184,134,60,0.55) 6px 7px,
                        transparent 7px 13px
                    ),
                    repeating-linear-gradient(
                        65deg,
                        transparent 0 6px,
                        rgba(228,201,141,0.35) 6px 7px,
                        transparent 7px 13px
                    );
            "
        ></div>

    </div>


    <!-- =====================================================
         BACK BUTTON
    ===================================================== -->

    <div
        class="no-print"
        style="
            margin:18px auto 0;
            display:flex;
            justify-content:space-between;
            gap:12px;
        "
    >

        <a
            href="index.php?page=car/index"
            style="
                border:1px solid #e7ddc7;
                border-radius:6px;
                padding:12px 22px;
                font-size:.9rem;
                font-weight:600;
                cursor:pointer;
                text-decoration:none;
                display:inline-flex;
                align-items:center;
                gap:8px;
                background:#fff;
                color:#0d2340;
            "
        >
            ← লিস্টে ফিরুন
        </a>

    </div>

</div>


<script>

function printDiv(divId)
{
    const printContents =
        document.getElementById(divId).innerHTML;

    const originalContents =
        document.body.innerHTML;

    document.body.innerHTML =
        printContents;

    window.print();

    document.body.innerHTML =
        originalContents;

    location.reload();
}

</script>