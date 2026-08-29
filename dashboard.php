<?php

$today = date('Y-m-d');

// =====================================================
// DASHBOARD DATA
// =====================================================
// আজকে মোট গাড়ি
$stmt = $pdo->prepare(" SELECT COUNT(*) AS total FROM customer_records");
$stmt->execute();
$totalCars = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
// =====================================================
// আজকের Active গাড়ি
// =====================================================
$stmt = $pdo->prepare(" SELECT COUNT(*) AS total FROM customer_records WHERE status = 'active' ");
$stmt->execute();
$activeCars = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
// =====================================================
// আজকের Completed গাড়ি
// =====================================================
$stmt = $pdo->prepare(" SELECT COUNT(*) AS total FROM customer_records WHERE  status = 'completed' ");
$stmt->execute();
$completedCars = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
// =====================================================
// আজকের Returned গাড়ি
// =====================================================
$stmt = $pdo->prepare("  SELECT COUNT(*) AS total FROM customer_records WHERE DATE(created_at) = ? AND status = 'returned' ");
$stmt->execute([$today]);
$returnedCars = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
 

 

// =====================================================
// KISTI PAYMENT DATA
// =====================================================

// সর্বশেষ ১০টি কিস্তি
$stmt = $pdo->query("
    SELECT *
    FROM kisti_payments
    ORDER BY payment_date DESC
    LIMIT 10
");

$kistiPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// মোট কিস্তি জমা
// =====================================================

$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM kisti_payments
");

$totalKisti = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);


// =====================================================
// আজকের কিস্তি
// =====================================================

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM kisti_payments
    WHERE DATE(payment_date) = ?
");

$stmt->execute([$today]);

$todayKisti = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);


// =====================================================
// বকেয়া কিস্তি
// =====================================================

 
$dueKisti =   0;

$totalRent        = $totalRent ?? 0;
$todayRent        = $todayRent ?? 0;
$dueRent          = $dueRent ?? 0;

$totalIncome      = $totalIncome ?? 0;
$totalExpense     = $totalExpense ?? 0;
$balance          = $balance ?? 0;

$todayFollowup    = $todayFollowup ?? 0;
$promiseCount     = $promiseCount ?? 0;

$totalMetroCars   = $totalMetroCars ?? 0;
$metroCollection  = $metroCollection ?? 0;
$metroDue         = $metroDue ?? 0;

$totalEmployees   = $totalEmployees ?? 0;
$presentEmployees = $presentEmployees ?? 0;
$absentEmployees  = $absentEmployees ?? 0;

$salaryPaid       = $salaryPaid ?? 0;
$salaryDue        = $salaryDue ?? 0;
?>


<style>

/* =====================================================
   DASHBOARD
===================================================== */

.dashboard-page {
    padding: 10px;
    color: #1f2937;
}


/* =====================================================
   HEADER
===================================================== */

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 22px;
}

.dashboard-title h2 {
    margin: 0;
    font-size: 25px;
    font-weight: 700;
}

.dashboard-title p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 14px;
}

.dashboard-date {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}


/* =====================================================
   MAIN STAT CARDS
===================================================== */

.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 18px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 5px 18px rgba(15,23,42,.05);
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: "";
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    right: -30px;
    top: -30px;
    background: rgba(255,255,255,.20);
}

.stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.stat-label {
    font-size: 14px;
    margin-top: 12px;
    opacity: .9;
}

.stat-value {
    font-size: 26px;
    font-weight: 800;
    margin-top: 3px;
}

.stat-link {
    display: inline-block;
    margin-top: 12px;
    color: inherit;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.stat-link:hover {
    text-decoration: underline;
}


/* COLORS */

.stat-blue {
    background: linear-gradient(135deg,#2563eb,#3b82f6);
    color: #fff;
}

.stat-green {
    background: linear-gradient(135deg,#059669,#10b981);
    color: #fff;
}

.stat-orange {
    background: linear-gradient(135deg,#ea580c,#f97316);
    color: #fff;
}

.stat-red {
    background: linear-gradient(135deg,#dc2626,#ef4444);
    color: #fff;
}


/* =====================================================
   MODULE CARDS
===================================================== */

.module-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.module-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 5px 18px rgba(15,23,42,.05);
    transition: .2s;
}

.module-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15,23,42,.10);
}

.module-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.module-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 16px;
}

.module-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.module-menu {
    color: #64748b;
    text-decoration: none;
    font-size: 13px;
}

.module-number {
    font-size: 27px;
    font-weight: 800;
}

.module-sub {
    color: #64748b;
    font-size: 13px;
    margin-top: 4px;
}


/* =====================================================
   TWO COLUMN
===================================================== */

.dashboard-columns {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 18px;
    margin-bottom: 20px;
}

.dashboard-box {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15,23,42,.05);
}

.box-header {
    padding: 15px 18px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.box-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.box-header a {
    font-size: 12px;
    text-decoration: none;
}

.box-body {
    padding: 18px;
}


/* =====================================================
   QUICK ACTION
===================================================== */

.quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.quick-action {
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    padding: 15px 10px;
    text-align: center;
    text-decoration: none;
    color: #334155;
    background: #f8fafc;
    transition: .2s;
}

.quick-action:hover {
    background: #eff6ff;
    color: #2563eb;
    border-color: #bfdbfe;
}

.quick-action i {
    display: block;
    font-size: 23px;
    margin-bottom: 7px;
}

.quick-action span {
    font-size: 12px;
    font-weight: 600;
}


/* =====================================================
   MONEY SUMMARY
===================================================== */

.money-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 0;
    border-bottom: 1px solid #eef2f7;
}

.money-row:last-child {
    border-bottom: 0;
}

.money-title {
    color: #64748b;
    font-size: 13px;
}

.money-value {
    font-weight: 700;
    font-size: 15px;
}

.income {
    color: #059669;
}

.expense {
    color: #dc2626;
}

.balance {
    color: #2563eb;
}


/* =====================================================
   FOLLOWUP
===================================================== */

.followup-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 9px;
    margin-bottom: 9px;
}

.followup-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.followup-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #2563eb;
}

.followup-text strong {
    display: block;
    font-size: 13px;
}

.followup-text small {
    color: #64748b;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:1200px) {

    .stat-grid,
    .module-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media(max-width:768px) {

    .dashboard-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .stat-grid,
    .module-grid,
    .dashboard-columns {
        grid-template-columns: 1fr;
    }

}


@media(max-width:480px) {

    .stat-grid,
    .module-grid {
        grid-template-columns: 1fr;
    }

    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

</style>


<div class="dashboard-page">

    <!-- =================================================
         HEADER
    ================================================= -->

    <div class="dashboard-header">

        <div class="dashboard-title">

            <h2>
                📊 ড্যাশবোর্ড
            </h2>

            <p>
                জহিরুল এন্টারপ্রাইজের সকল কার্যক্রমের সংক্ষিপ্ত বিবরণ
            </p>

        </div>

        <div class="dashboard-date">

            📅 <?= bn_number(date('d-m-Y')) ?>

        </div>

    </div>


    <!-- =================================================
         MAIN STAT
    ================================================= -->

    <div class="stat-grid">


        <!-- গাড়ি -->

        <div class="stat-card stat-blue">

            <div class="stat-top">

                <div class="stat-icon">
                    🚗
                </div>

                <span>
                    গাড়ি
                </span>

            </div>

            <div class="stat-label">
                মোট গাড়ি
            </div>

            <div class="stat-value">
                <?= bn_number($totalCars) ?>
            </div>

            <a
                href="index.php?page=car/index"
                class="stat-link"
            >
                গাড়ির তালিকা →
            </a>

        </div>


        <!-- কিস্তি -->

        <div class="stat-card stat-green">

            <div class="stat-top">

                <div class="stat-icon">
                    💰
                </div>

                <span>
                    কিস্তি
                </span>

            </div>

            <div class="stat-label">
                আজকের কিস্তি
            </div>

            <div class="stat-value">
                ৳ <?= number_format($todayKisti,2) ?>
            </div>

            <a
                href="index.php?page=payment/index"
                class="stat-link"
            >
                কিস্তি দেখুন →
            </a>

        </div>


        <!-- আয় -->

        <div class="stat-card stat-orange">

            <div class="stat-top">

                <div class="stat-icon">
                    📈
                </div>

                <span>
                    হিসাব
                </span>

            </div>

            <div class="stat-label">
                মোট আয়
            </div>

            <div class="stat-value">
                ৳ <?= number_format($totalIncome,2) ?>
            </div>

            <a
                href="index.php?page=garage/report"
                class="stat-link"
            >
                হিসাব দেখুন →
            </a>

        </div>


        <!-- খরচ -->

        <div class="stat-card stat-red">

            <div class="stat-top">

                <div class="stat-icon">
                    📉
                </div>

                <span>
                    খরচ
                </span>

            </div>

            <div class="stat-label">
                মোট খরচ
            </div>

            <div class="stat-value">
                ৳ <?= number_format($totalExpense,2) ?>
            </div>

            <a
                href="index.php?page=garage/report"
                class="stat-link"
            >
                রিপোর্ট দেখুন →
            </a>

        </div>

    </div>


    <!-- =================================================
         MODULE CARDS
    ================================================= -->

    <div class="module-grid">


        <!-- গাড়ি -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#dbeafe;color:#2563eb;"
                    >
                        🚗
                    </div>

                    গাড়ি ও গ্রাহক

                </div>

                <a
                    href="index.php?page=car/index"
                    class="module-menu"
                >
                    দেখুন
                </a>

            </div>

            <div class="module-number">

                <?= bn_number($totalCars) ?>

            </div>

            <div class="module-sub">

                চলমান:
                <?= bn_number($activeCars) ?>

                |
                
                সম্পন্ন:
                <?= bn_number($completedCars) ?>

            </div>

        </div>


        <!-- কিস্তি -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#dcfce7;color:#059669;"
                    >
                        💳
                    </div>

                    কিস্তি ও পেমেন্ট

                </div>

                <a
                    href="index.php?page=payment/index"
                    class="module-menu"
                >
                    দেখুন
                </a>

            </div>

            <div class="module-number">

                ৳ <?= number_format($totalKisti,2) ?>

            </div>

            <div class="module-sub">

                বকেয়া:
                <strong class="expense">
                    ৳ <?= number_format($dueKisti,2) ?>
                </strong>

            </div>

        </div>


        <!-- ভাড়া -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#fef3c7;color:#d97706;"
                    >
                        🏠
                    </div>

                    ভাড়া ব্যবস্থাপনা

                </div>

                <a
                    href="index.php?page=rent/index"
                    class="module-menu"
                >
                    দেখুন
                </a>

            </div>

            <div class="module-number">

                ৳ <?= number_format($totalRent,2) ?>

            </div>

            <div class="module-sub">

                আজ:
                ৳ <?= number_format($todayRent,2) ?>

                |
                
                বকেয়া:
                ৳ <?= number_format($dueRent,2) ?>

            </div>

        </div>


        <!-- কল স্টোরি -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#ede9fe;color:#7c3aed;"
                    >
                        📞
                    </div>

                    কল স্টোরি

                </div>

                <a
                    href="index.php?page=call_story/call_report"
                    class="module-menu"
                >
                    রিপোর্ট
                </a>

            </div>

            <div class="module-number">

                <?= bn_number($todayFollowup) ?>

            </div>

            <div class="module-sub">

                আজকের Follow-up

                |

                Promise:
                <?= bn_number($promiseCount) ?>

            </div>

        </div>


        <!-- মেট্রো -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#cffafe;color:#0891b2;"
                    >
                        🚕
                    </div>

                    মেট্রো গাড়ি

                </div>

                <a
                    href="index.php?page=metro/index"
                    class="module-menu"
                >
                    দেখুন
                </a>

            </div>

            <div class="module-number">

                <?= bn_number($totalMetroCars) ?>

            </div>

            <div class="module-sub">

                জমা:
                ৳ <?= number_format($metroCollection,2) ?>

                |

                বকেয়া:
                ৳ <?= number_format($metroDue,2) ?>

            </div>

        </div>


        <!-- কর্মচারী -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#fce7f3;color:#db2777;"
                    >
                        👥
                    </div>

                    কর্মচারী

                </div>

                <a
                    href="index.php?page=employee/index"
                    class="module-menu"
                >
                    দেখুন
                </a>

            </div>

            <div class="module-number">

                <?= bn_number($totalEmployees) ?>

            </div>

            <div class="module-sub">

                উপস্থিত:
                <strong class="income">
                    <?= bn_number($presentEmployees) ?>
                </strong>

                |

                অনুপস্থিত:
                <strong class="expense">
                    <?= bn_number($absentEmployees) ?>
                </strong>

            </div>

        </div>


        <!-- বেতন -->

        <div class="module-card">

            <div class="module-head">

                <div class="module-title">

                    <div
                        class="module-icon"
                        style="background:#e0f2fe;color:#0284c7;"
                    >
                        💵
                    </div>

                    বেতন ব্যবস্থাপনা

                </div>

                <a
                    href="index.php?page=salary/report"
                    class="module-menu"
                >
                    রিপোর্ট
                </a>

            </div>

            <div class="module-number">

                ৳ <?= number_format($salaryPaid,2) ?>

            </div>

            <div class="module-sub">

                বেতন বকেয়া:
                <strong class="expense">
                    ৳ <?= number_format($salaryDue,2) ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =================================================
         LOWER SECTION
    ================================================= -->

    <div class="dashboard-columns">


        <!-- =================================================
             QUICK ACTION
        ================================================= -->

        <div class="dashboard-box">

            <div class="box-header">

                <h5>
                    ⚡ দ্রুত কাজ
                </h5>

            </div>

            <div class="box-body">

                <div class="quick-grid">


                    <a
                        href="index.php?page=car/add"
                        class="quick-action"
                    >
                        <i>🚗</i>
                        <span>নতুন গাড়ি</span>
                    </a>


                    <a
                        href="index.php?page=payment/add"
                        class="quick-action"
                    >
                        <i>💰</i>
                        <span>কিস্তি গ্রহণ</span>
                    </a>


                    <a
                        href="index.php?page=rent/collection"
                        class="quick-action"
                    >
                        <i>🏠</i>
                        <span>ভাড়া গ্রহণ</span>
                    </a>


                    <a
                        href="index.php?page=garage/add"
                        class="quick-action"
                    >
                        <i>➕</i>
                        <span>নতুন আয়/ব্যয়</span>
                    </a>


                    <a
                        href="index.php?page=metro/add"
                        class="quick-action"
                    >
                        <i>🚕</i>
                        <span>মেট্রো গাড়ি</span>
                    </a>


                    <a
                        href="index.php?page=employee/add"
                        class="quick-action"
                    >
                        <i>👤</i>
                        <span>নতুন কর্মচারী</span>
                    </a>


                    <a
                        href="index.php?page=salary/payment"
                        class="quick-action"
                    >
                        <i>💵</i>
                        <span>বেতন প্রদান</span>
                    </a>


                    <a
                        href="index.php?page=accounting/add"
                        class="quick-action"
                    >
                        <i>🧾</i>
                        <span>নতুন হিসাব</span>
                    </a>

                </div>

            </div>

        </div>


        <!-- =================================================
             MONEY SUMMARY
        ================================================= -->

        <div class="dashboard-box">

            <div class="box-header">

                <h5>
                    💰 আজকের হিসাব
                </h5>

                <a
                    href="index.php?page=garage/report"
                >
                    বিস্তারিত
                </a>

            </div>

            <div class="box-body">


                <div class="money-row">

                    <span class="money-title">
                        মোট আয়
                    </span>

                    <span class="money-value income">
                        ৳ <?= number_format($totalIncome,2) ?>
                    </span>

                </div>


                <div class="money-row">

                    <span class="money-title">
                        মোট খরচ
                    </span>

                    <span class="money-value expense">
                        ৳ <?= number_format($totalExpense,2) ?>
                    </span>

                </div>


                <div class="money-row">

                    <span class="money-title">
                        বর্তমান ব্যালেন্স
                    </span>

                    <span class="money-value balance">
                        ৳ <?= number_format($balance,2) ?>
                    </span>

                </div>


                <div class="money-row">

                    <span class="money-title">
                        আজকের কিস্তি
                    </span>

                    <span class="money-value income">
                        ৳ <?= number_format($todayKisti,2) ?>
                    </span>

                </div>


            </div>

        </div>

    </div>


    <!-- =================================================
         FOLLOW UP + REPORT
    ================================================= -->

    <div class="dashboard-columns">


        <!-- Followup -->

        <div class="dashboard-box">

            <div class="box-header">

                <h5>
                    📞 আজকের Follow-up
                </h5>

                <a
                    href="index.php?page=call_story/today_followup"
                >
                    সব দেখুন
                </a>

            </div>

            <div class="box-body">


                <div class="followup-item">

                    <div class="followup-left">

                        <div class="followup-icon">
                            📅
                        </div>

                        <div class="followup-text">

                            <strong>
                                আজকের Follow-up
                            </strong>

                            <small>
                                মোট <?= bn_number($todayFollowup) ?> টি
                            </small>

                        </div>

                    </div>

                    <span class="badge bg-primary">
                        <?= bn_number($todayFollowup) ?>
                    </span>

                </div>


                <div class="followup-item">

                    <div class="followup-left">

                        <div class="followup-icon">
                            👍
                        </div>

                        <div class="followup-text">

                            <strong>
                                Promise তালিকা
                            </strong>

                            <small>
                                কাস্টমারের প্রতিশ্রুতি
                            </small>

                        </div>

                    </div>

                    <span class="badge bg-success">
                        <?= bn_number($promiseCount) ?>
                    </span>

                </div>


                <div class="followup-item">

                    <div class="followup-left">

                        <div class="followup-icon">
                            ⚠️
                        </div>

                        <div class="followup-text">

                            <strong>
                                কিস্তি বকেয়া
                            </strong>

                            <small>
                                যাদের পেমেন্ট বাকি
                            </small>

                        </div>

                    </div>

                    <span class="badge bg-danger">
                        ৳ <?= number_format($dueKisti,2) ?>
                    </span>

                </div>


            </div>

        </div>


        <!-- Reports -->

        <div class="dashboard-box">

            <div class="box-header">

                <h5>
                    📊 গুরুত্বপূর্ণ রিপোর্ট
                </h5>

            </div>

            <div class="box-body">


                <a
                    href="index.php?page=car/report"
                    class="quick-action d-flex align-items-center gap-2 mb-2"
                >
                    🚗
                    <span>গাড়ি রিপোর্ট</span>
                </a>


                <a
                    href="index.php?page=payment/report"
                    class="quick-action d-flex align-items-center gap-2 mb-2"
                >
                    💰
                    <span>কিস্তি রিপোর্ট</span>
                </a>


                <a
                    href="index.php?page=garage/report"
                    class="quick-action d-flex align-items-center gap-2 mb-2"
                >
                    🏢
                    <span>গ্যারেজ রিপোর্ট</span>
                </a>


                <a
                    href="index.php?page=salary/report"
                    class="quick-action d-flex align-items-center gap-2"
                >
                    💵
                    <span>বেতন রিপোর্ট</span>
                </a>


            </div>

        </div>

    </div>

</div>