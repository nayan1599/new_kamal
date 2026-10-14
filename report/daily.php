<!-- =====================================================
     DAY BY DAY REPORT
===================================================== -->

<?php
// বাংলাদেশ সময়
date_default_timezone_set('Asia/Dhaka');

// আজকের তারিখ
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM kisti_payments
    WHERE DATE(payment_date) = ?
");

$stmt->execute([$today]);
$todayKisti = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// -----------------------------------------------------
// আপনার Database থেকে এই ৪টি Variable-এ আজকের হিসাব দিন
// -----------------------------------------------------

$todayKisti     = $todayKisti ?? 0;       // আজকের মোট কিস্তি

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(rent_amount), 0) AS total
    FROM rents
    WHERE DATE(rent_date) = ?
");
$stmt->execute([$today]);
$todayJoma = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
$todayJoma      = $todayJoma ?? 0;        // আজকের মোট জমা

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(owner_amount), 0) AS total
    FROM hice_rents
    WHERE DATE(collection_date) = ?
");
$stmt->execute([$today]);
$todayHiceJoma = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
$todayHiceJoma  = $todayHiceJoma ?? 0;     // আজকের Hice জমা

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        v.vehicle_number,
        v.driver_name,
        v.driver_mobile
    FROM hice_rents r
    LEFT JOIN hice_vehicles v ON v.id = r.vehicle_id
    WHERE r.collection_date = ?
    ORDER BY r.id DESC
");
$stmt->execute([$today]);
$records = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM garage_transactions
    WHERE garage_id = 3
      AND type = 'expense'
      AND DATE(transaction_date) = ?
");

$stmt->execute([$today]);

$todayKhoroch = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
$todayKhoroch   = $todayKhoroch ?? 0;      // আজকের মোট খরচ

$stmt = $pdo->prepare("
    SELECT *
    FROM garage_transactions
    WHERE garage_id = 3
      AND type = 'expense'
      AND DATE(transaction_date) = ?
    ORDER BY transaction_date DESC, id DESC
");
$stmt->execute([$today]);
$khoroch_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

 
 


// টাকা Format
function money_format_bn($amount)
{
    return '৳ ' . number_format((float)$amount, 2);
}

// বাংলা তারিখ
$months_bn = [
    'January'   => 'জানুয়ারি',
    'February'  => 'ফেব্রুয়ারি',
    'March'     => 'মার্চ',
    'April'     => 'এপ্রিল',
    'May'       => 'মে',
    'June'      => 'জুন',
    'July'      => 'জুলাই',
    'August'    => 'আগস্ট',
    'September' => 'সেপ্টেম্বর',
    'October'   => 'অক্টোবর',
    'November'  => 'নভেম্বর',
    'December'  => 'ডিসেম্বর'
];

$days_bn = [
    'Saturday'  => 'শনিবার',
    'Sunday'    => 'রবিবার',
    'Monday'    => 'সোমবার',
    'Tuesday'   => 'মঙ্গলবার',
    'Wednesday' => 'বুধবার',
    'Thursday'  => 'বৃহস্পতিবার',
    'Friday'    => 'শুক্রবার'
];

$englishMonth = date('F');
$englishDay   = date('l');

$todayDateBn = date('d') . ' '
    . ($months_bn[$englishMonth] ?? $englishMonth)
    . ' ' . date('Y');

$todayDayBn = $days_bn[$englishDay] ?? $englishDay;

// -----------------------------------------------------
// Net Balance
// -----------------------------------------------------
$todaytotal = ($todayKisti + $todayJoma + $todayHiceJoma) - $todayKhoroch;
 
?>

<style>

/* =====================================================
   DAY REPORT
===================================================== */

.day-report-wrapper {
    width: 100%;
    padding: 20px 0 10px;
}

/* Main Box */
.day-report-box {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    background: #f5f8fc;
    border: 1px solid #e3eaf3;
    box-shadow: 0 12px 35px rgba(15, 35, 64, 0.10);
}

/* =====================================================
   HEADER
===================================================== */

 

.day-report-header::before {
    content: "";
    position: absolute;
    width: 230px;
    height: 230px;
    right: -80px;
    top: -130px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
}

.day-report-header::after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    left: -100px;
    bottom: -120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}

.day-report-header-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

/* Title */

.day-report-title {
    display: flex;
    align-items: center;
    gap: 15px;
}

.day-report-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: inset 0 0 20px rgba(255,255,255,0.05);
}

.day-report-title h2 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: .3px;
}

.day-report-title p {
    margin: 5px 0 0;
    font-size: 14px;
    color: rgba(8, 8, 8, 0.78);
}

/* Date */

.day-report-date {
    min-width: 235px;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 16px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
}

.day-report-date-icon {
    width: 43px;
    height: 43px;
    border-radius: 12px;
    background: #fff;
    color: #0b5fc0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.day-report-date small {
    display: block;
    color: rgba(0, 0, 0, 0.7);
    font-size: 12px;
    margin-bottom: 3px;
}

.day-report-date strong {
    font-size: 15px;
    color: #000;
}


/* =====================================================
   CARDS
===================================================== */

.day-report-cards {
    padding: 25px;
}

.day-report-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
}

.day-card {
    position: relative;
    overflow: hidden;
    min-height: 185px;
    padding: 20px;
    border-radius: 20px;
    background: #fff;
    border: 1px solid #e8edf4;
    box-shadow: 0 8px 25px rgba(20,40,70,.07);
    transition: all .25s ease;
}

.day-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(20,40,70,.13);
}

.day-card::after {
    content: "";
    position: absolute;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    right: -55px;
    bottom: -65px;
    opacity: .08;
}

/* Card Icon */

.day-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.day-card-icon {
    width: 55px;
    height: 55px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 27px;
}

.day-card-label {
    margin-top: 15px;
    font-size: 14px;
    font-weight: 700;
    color: #68778b;
}

.day-card-value {
    margin-top: 5px;
    font-size: 27px;
    font-weight: 900;
    letter-spacing: .3px;
}


/* =====================================================
   CARD COLORS
===================================================== */

/* Kisti */

.card-kisti {
    border-top: 4px solid #16a36a;
}

.card-kisti .day-card-icon {
    color: #0d9b60;
    background: #e7f8ef;
}

.card-kisti .day-card-value {
    color: #087c4c;
}

.card-kisti::after {
    background: #16a36a;
}


/* Joma */

.card-joma {
    border-top: 4px solid #1677d2;
}

.card-joma .day-card-icon {
    color: #126bc0;
    background: #e8f3ff;
}

.card-joma .day-card-value {
    color: #075aa8;
}

.card-joma::after {
    background: #1677d2;
}


/* Hice */

.card-hice {
    border-top: 4px solid #7145d6;
}

.card-hice .day-card-icon {
    color: #6b3fc5;
    background: #f0eaff;
}

.card-hice .day-card-value {
    color: #5a31ae;
}

.card-hice::after {
    background: #7145d6;
}


/* Khoroch */

.card-khoroch {
    border-top: 4px solid #e33d4e;
}

.card-khoroch .day-card-icon {
    color: #d92f42;
    background: #ffeaed;
}

.card-khoroch .day-card-value {
    color: #c72638;
}

.card-khoroch::after {
    background: #e33d4e;
}
/* total */

.card-total {
    border-top: 4px solid #3dd92f;
}

.card-total .day-card-icon {
    color: #3dd92f;
    background: #ffeaed;
}

.card-total .day-card-value {
    color: #3dd92f;
}

.card-total::after {
    background: #3dd92f;
}

/* =====================================================
   BOTTOM SUMMARY
===================================================== */

.day-report-summary {
    margin: 0 25px 25px;
    padding: 18px 22px;
    border-radius: 18px;
    background: linear-gradient(
        135deg,
        #edf7ff,
        #f7fbff
    );
    border: 1px solid #cfe5fa;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    align-items: center;
}

.summary-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 55px;
}

.summary-item:not(:last-child) {
    border-right: 1px solid #d7e5f2;
}

.summary-icon {
    width: 45px;
    height: 45px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,.06);
}

.summary-text small {
    display: block;
    color: #718096;
    font-size: 12px;
    font-weight: 600;
}

.summary-text strong {
    display: block;
    margin-top: 3px;
    font-size: 20px;
    font-weight: 900;
    color: #12355b;
}

.summary-income .summary-icon {
    color: #07945b;
}

.summary-expense .summary-icon {
    color: #d72d42;
}

.summary-balance .summary-icon {
    color: #126bc0;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1100px) {

    .day-report-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 700px) {

    .day-report-header-content {
        flex-direction: column;
        align-items: stretch;
    }

    .day-report-date {
        width: 100%;
        min-width: 0;
    }

    .day-report-title h2 {
        font-size: 21px;
    }

    .day-report-cards {
        padding: 15px;
    }

    .day-report-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .day-card {
        min-height: 165px;
        padding: 15px;
    }

    .day-card-value {
        font-size: 21px;
    }

    .day-report-summary {
        margin: 0 15px 15px;
        padding: 10px;
        grid-template-columns: 1fr;
    }

    .summary-item {
        justify-content: flex-start;
        padding: 10px;
    }

    .summary-item:not(:last-child) {
        border-right: 0;
        border-bottom: 1px solid #d7e5f2;
    }

}

@media (max-width: 430px) {

    .day-report-grid {
        grid-template-columns: 1fr;
    }

    .day-report-title-icon {
        width: 48px;
        height: 48px;
        font-size: 23px;
    }

    .day-report-title h2 {
        font-size: 19px;
    }

}

</style>


<section class="day_raport">

    <div class="container-fluid px-3 px-lg-4 py-4">

        <div class="day-report-wrapper">

            <div class="day-report-box">

                <!-- =========================
                     HEADER
                ========================== -->
                <div class="day-report-header">

                    <div class="day-report-header-content">

                        <div class="day-report-title">

                            <div class="day-report-title-icon">
                                📊
                            </div>

                            <div>
                                <h2>Day by Day রিপোর্ট</h2>
                                <p>আজকের দৈনিক হিসাবের সারসংক্ষেপ</p>
                            </div>

                        </div>


                        <!-- DATE -->
                        <div class="day-report-date">

                            <div class="day-report-date-icon">
                                📅
                            </div>

                            <div>
                                <small>আজকের তারিখ</small>
                                <strong>
                                    <?= htmlspecialchars($todayDateBn) ?>
                                    <br>
                                    <span style="font-size:12px;color:#000;">
                                        <?= htmlspecialchars($todayDayBn) ?>
                                    </span>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =========================
                     MAIN CARDS
                ========================== -->

                <div class="day-report-cards">

                    <div class="day-report-grid">


                        <!-- মোট কিস্তি -->
                        <div class="day-card card-kisti">

                            <div class="day-card-top">

                                <div class="day-card-icon">
                                    🧾
                                </div>

                                <span style="font-size:22px;">
                                    📅
                                </span>

                            </div>

                            <div class="day-card-label">
                                আজকের মোট কিস্তি
                            </div>

                            <div class="day-card-value">
                                <?=bn_number( money_format_bn($todayKisti)) ?>
                            </div>

                        </div>


                        <!-- মোট জমা -->
                        <div class="day-card card-joma">

                            <div class="day-card-top">

                                <div class="day-card-icon">
                                    💰
                                </div>

                                <span style="font-size:22px;">
                                    📈
                                </span>

                            </div>

                            <div class="day-card-label">
                                আজকের মোট জমা
                            </div>

                            <div class="day-card-value">
                                <?= bn_number(money_format_bn($todayJoma)) ?>
                            </div>

                        </div>


                        <!-- Hice জমা -->
                        <div class="day-card card-hice">

                            <div class="day-card-top">

                                <div class="day-card-icon">
                                    🚐
                                </div>

                                <span style="font-size:22px;">
                                    💵
                                </span>

                            </div>

                            <div class="day-card-label">
                                আজকের Hice জমা
                            </div>

                            <div class="day-card-value">
                                <?=bn_number( money_format_bn($todayHiceJoma)) ?>
                            </div>

                        </div>


                        <!-- মোট খরচ -->
                        <div class="day-card card-khoroch">

                            <div class="day-card-top">

                                <div class="day-card-icon">
                                    💸
                                </div>

                                <span style="font-size:22px;">
                                    📉
                                </span>

                            </div>

                            <div class="day-card-label">
                                আজকের মোট খরচ
                            </div>

                            <div class="day-card-value">
                                <?=bn_number( money_format_bn($todayKhoroch)) ?>
                            </div>

                        </div>

                             <!-- মোট খরচ -->
                        <div class="day-card card-total">

                            <div class="day-card-top">

                                <div class="day-card-icon">
                                    💸
                                </div>

                                <span style="font-size:22px;">
                                    📉
                                </span>

                            </div>

                            <div class="day-card-label">
                                আজকের মোট আমদানি
                            </div>

                            <div class="day-card-value">
                                <?=bn_number( money_format_bn($todaytotal)) ?>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =========================
                     SUMMARY
                ========================== -->
        <div class="table-responsive">


            <table
                class="table align-middle mb-0"
                id="dataTable"
            >


                <thead>

                    <tr>

                        <th>#</th>

                    

                        <th>গাড়ি</th>

                        <th>ড্রাইভার</th>

                        <th>মোট ভাড়া</th>

                        <th>খরচ</th>

                        <th>নিট আয়</th>

                        <th>ড্রাইভার ৩০%</th>

                        <th>মালিক</th>

                     

                    </tr>

                </thead>



                <tbody id="tableBody">


                <?php

                $i = 0;

                foreach ($records as $row):


                    $rent =
                        (float)($row['total_rent'] ?? 0);


                    $expense =
                        (float)($row['expense_amount'] ?? 0);


                    $net =
                        (float)($row['net_income'] ?? ($rent - $expense));


                    $driver =
                        (float)($row['driver_amount'] ?? ($net * .30));


                    $owner =
                        (float)($row['owner_amount'] ?? ($net - $driver));


                ?>


                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?= bn_number(++$i) ?>

                        </td>



                        <!-- DATE -->

                       


                        <!-- VEHICLE -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $row['vehicle_number'] ?? '-'
                                ) ?>

                            </strong>

                        </td>



                        <!-- DRIVER -->

                        <td>

                            <?= htmlspecialchars(
                                $row['driver_name'] ?? '-'
                            ) ?>


                            <?php if (!empty($row['driver_mobile'])): ?>

                                <br>

                                <small class="text-muted">

                                    <?= bn_number(
                                        htmlspecialchars(
                                            $row['driver_mobile']
                                        )
                                    ) ?>

                                </small>

                            <?php endif; ?>

                        </td>



                        <!-- RENT -->

                        <td>

                            <span class="text-primary fw-bold">

                                <?= bn_number(
                                    number_format($rent, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- EXPENSE -->

                        <td>

                            <span class="text-danger fw-semibold">

                                <?= bn_number(
                                    number_format($expense, 2)
                                ) ?>

                            </span>

                            <?php if (!empty($row['expense_note'])): ?>

                                <br>

                                <small class="text-muted">

                                    <?= htmlspecialchars(
                                        $row['expense_note']
                                    ) ?>

                                </small>

                            <?php endif; ?>

                        </td>



                        <!-- NET -->

                        <td>

                            <span class="badge bg-info text-dark">

                                <?= bn_number(
                                    number_format($net, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- DRIVER -->

                        <td>

                            <span class="badge bg-warning text-dark">

                                <?= bn_number(
                                    number_format($driver, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- OWNER -->

                        <td>

                            <span class="badge bg-success">

                                <?= bn_number(
                                    number_format($owner, 2)
                                ) ?>

                            </span>

                        </td>



                        <!-- ACTION -->


                    </tr>


                <?php endforeach; ?>


                <?php if (empty($records)): ?>


                    <tr>

                        <td
                            colspan="10"
                            class="text-center py-5"
                        >

                            <div class="text-muted">

                                <i
                                    class="bi bi-inbox fs-1 d-block mb-2"
                                ></i>

                                এখনো কোনো Hice ভাড়া হিসাব নেই।

                            </div>


                            <a
                                href="index.php?page=hice/add"
                                class="btn btn-success mt-3"
                            >

                                <i class="bi bi-plus-circle"></i>

                                প্রথম হিসাব যোগ করুন

                            </a>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>


        </div>

 <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>তারিখ</th>
            <th>ক্যাটাগরি</th>
            <th>পরিমাণ</th>
            <th>বিবরণ</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($khoroch_list)): ?>

            <?php foreach ($khoroch_list as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>

                    <td>
                        <?=bn_number( htmlspecialchars($row['transaction_date'])) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['category']) ?>
                    </td>

                    <td class="text-end fw-bold">
                        <?=bn_number( number_format((float)$row['amount'], 2)) ?> ৳
                    </td>

                    <td>
                        <?=bn_number( htmlspecialchars($row['description'] ?? '')) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="5" class="text-center text-muted py-3">
                    আজকের কোনো খরচ পাওয়া যায়নি।
                </td>
            </tr>

        <?php endif; ?>
    </tbody>
</table>


            </div>

        </div>

    </div>

</section>