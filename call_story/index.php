 
<?php

/* =========================================================
   CALL STORY CRM DASHBOARD
   ========================================================= */

// Total Calls
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM call_stories
");
$totalCalls = (int)$stmt->fetchColumn();


// Connected Calls
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM call_stories
    WHERE call_status = 'Connected'
");
$connectedCalls = (int)$stmt->fetchColumn();


// Pending / Not Connected
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM call_stories
    WHERE call_status != 'Connected'
       OR call_status IS NULL
       OR call_status = ''
");
$pendingCalls = (int)$stmt->fetchColumn();


// Total Due
$stmt = $pdo->query("
    SELECT COALESCE(SUM(due_amount), 0)
    FROM call_stories
");
$totalDue = (float)$stmt->fetchColumn();


// Today's Followup
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM call_stories
    WHERE next_followup_date = CURDATE()
");
$todayFollowup = (int)$stmt->fetchColumn();


// Upcoming Followup
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM call_stories
    WHERE next_followup_date > CURDATE()
");
$upcomingFollowup = (int)$stmt->fetchColumn();


// Overdue Followup
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM call_stories
    WHERE next_followup_date < CURDATE()
");
$overdueFollowup = (int)$stmt->fetchColumn();


// Connected Percentage
$connectedPercent = $totalCalls > 0
    ? round(($connectedCalls / $totalCalls) * 100)
    : 0;


// Today's connected calls
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM call_stories
    WHERE call_status = 'Connected'
      AND DATE(created_at) = CURDATE()
");
$todayConnected = (int)$stmt->fetchColumn();


// Today's calls
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM call_stories
    WHERE DATE(created_at) = CURDATE()
");
$todayCalls = (int)$stmt->fetchColumn();


// Recent 5 Calls
$stmt = $pdo->query("
    SELECT *
    FROM call_stories
    ORDER BY id DESC
    LIMIT 5
");

$recentCalls = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<style>

    /* =====================================================
       CRM DASHBOARD
       ===================================================== */

    .crm-wrapper {
        background: #f5f7fb;
        min-height: calc(100vh - 70px);
        padding: 25px;
    }

    /* Header */

    .crm-header {
        background: linear-gradient(135deg, #0d6efd, #084298);
        border-radius: 20px;
        padding: 28px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(13, 110, 253, .18);
        margin-bottom: 25px;
    }

    .crm-header h3 {
        font-weight: 700;
        margin: 0;
    }

    .crm-header p {
        margin: 7px 0 0;
        opacity: .85;
        font-size: 14px;
    }

    .crm-add-btn {
        background: #fff;
        color: #0d6efd;
        border: 0;
        border-radius: 11px;
        padding: 11px 18px;
        font-weight: 600;
        transition: .2s;
    }

    .crm-add-btn:hover {
        transform: translateY(-2px);
        background: #f4f7ff;
    }


    /* Main Cards */

    .crm-card {
        background: #fff;
        border: 0;
        border-radius: 17px;
        padding: 20px;
        box-shadow: 0 5px 22px rgba(0,0,0,.055);
        height: 100%;
        transition: .2s;
    }

    .crm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(0,0,0,.08);
    }

    .crm-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
    }

    .crm-blue {
        background: #e9f1ff;
        color: #0d6efd;
    }

    .crm-green {
        background: #e8f8ef;
        color: #198754;
    }

    .crm-orange {
        background: #fff3df;
        color: #fd7e14;
    }

    .crm-red {
        background: #ffe9eb;
        color: #dc3545;
    }

    .crm-purple {
        background: #f0eaff;
        color: #6f42c1;
    }

    .crm-cyan {
        background: #e4f8fb;
        color: #0dcaf0;
    }

    .crm-label {
        font-size: 13px;
        color: #8a94a6;
        margin-bottom: 5px;
    }

    .crm-number {
        font-size: 27px;
        font-weight: 750;
        color: #202633;
    }

    .crm-small {
        font-size: 12px;
        color: #8a94a6;
    }


    /* Section */

    .crm-section {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 5px 22px rgba(0,0,0,.05);
        height: 100%;
    }

    .crm-section-title {
        font-weight: 700;
        color: #202633;
        margin-bottom: 20px;
    }


    /* Followup */

    .follow-card {
        padding: 16px;
        border-radius: 13px;
        background: #f8f9fc;
        margin-bottom: 10px;
        border: 1px solid #edf0f5;
    }

    .follow-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #e9f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    /* Progress */

    .progress {
        height: 10px;
        border-radius: 20px;
        background: #edf0f4;
    }

    .progress-bar {
        border-radius: 20px;
    }


    /* Quick Actions */

    .quick-action {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px;
        border-radius: 12px;
        background: #f8f9fc;
        color: #333;
        text-decoration: none;
        margin-bottom: 10px;
        transition: .2s;
        border: 1px solid #edf0f4;
    }

    .quick-action:hover {
        background: #eef5ff;
        color: #0d6efd;
        transform: translateX(3px);
    }

    .quick-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    /* Recent Activity */

    .activity {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 13px 0;
        border-bottom: 1px solid #edf0f4;
    }

    .activity:last-child {
        border-bottom: 0;
    }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e9f8ef;
        color: #198754;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .activity-name {
        font-weight: 600;
        font-size: 14px;
    }

    .activity-info {
        font-size: 12px;
        color: #8a94a6;
    }


    /* Date */

    .today-date {
        font-size: 13px;
        opacity: .85;
    }


    @media(max-width: 768px) {

        .crm-wrapper {
            padding: 15px;
        }

        .crm-header {
            padding: 22px;
        }

        .crm-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }

        .crm-add-btn {
            width: 100%;
        }

    }

</style>


<div class="crm-wrapper">

    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="crm-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h3>
                    <i class="bi bi-grid-1x2-fill me-2"></i>
                    CRM Dashboard
                </h3>

                <p>
                    কল, ফলোআপ এবং গ্রাহকের বকেয়া ব্যবস্থাপনার সারসংক্ষেপ
                </p>

                <div class="today-date mt-2">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= date('d F Y') ?>
                </div>

            </div>

            <a
                href="index.php?page=call_story/add"
                class="crm-add-btn"
            >
                <i class="bi bi-telephone-plus me-1"></i>
                নতুন কল যোগ করুন
            </a>

        </div>

    </div>


    <!-- =====================================================
         MAIN STATISTICS
         ===================================================== -->

    <div class="row g-3 mb-4">

        <!-- Total Calls -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="crm-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="crm-label">
                            মোট কল
                        </div>

                        <div class="crm-number">
                            <?= number_format($totalCalls) ?>
                        </div>

                        <div class="crm-small mt-1">
                            আজ <?= number_format($todayCalls) ?> টি কল
                        </div>

                    </div>

                    <div class="crm-icon crm-blue">
                        <i class="bi bi-telephone"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- Connected -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="crm-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="crm-label">
                            Connected
                        </div>

                        <div class="crm-number">
                            <?= number_format($connectedCalls) ?>
                        </div>

                        <div class="crm-small mt-1">
                            <?= $connectedPercent ?>% Success Rate
                        </div>

                    </div>

                    <div class="crm-icon crm-green">
                        <i class="bi bi-telephone-check"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- Pending -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="crm-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="crm-label">
                            Pending Call
                        </div>

                        <div class="crm-number">
                            <?= number_format($pendingCalls) ?>
                        </div>

                        <div class="crm-small mt-1">
                            যোগাযোগ প্রয়োজন
                        </div>

                    </div>

                    <div class="crm-icon crm-orange">
                        <i class="bi bi-clock-history"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- Due -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="crm-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="crm-label">
                            মোট বকেয়া
                        </div>

                        <div class="crm-number">
                            ৳ <?= number_format($totalDue, 0) ?>
                        </div>

                        <div class="crm-small mt-1">
                            সংগ্রহ করা প্রয়োজন
                        </div>

                    </div>

                    <div class="crm-icon crm-red">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         FOLLOWUP STATISTICS
         ===================================================== -->

    <div class="row g-3 mb-4">

        <!-- Today -->

        <div class="col-md-4">

            <div class="crm-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="crm-icon crm-blue">
                        <i class="bi bi-calendar-day"></i>
                    </div>

                    <div>

                        <div class="crm-label">
                            আজকের Follow-up
                        </div>

                        <div class="crm-number">
                            <?= number_format($todayFollowup) ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Upcoming -->

        <div class="col-md-4">

            <div class="crm-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="crm-icon crm-purple">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>

                        <div class="crm-label">
                            Upcoming Follow-up
                        </div>

                        <div class="crm-number">
                            <?= number_format($upcomingFollowup) ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Overdue -->

        <div class="col-md-4">

            <div class="crm-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="crm-icon crm-red">
                        <i class="bi bi-calendar-x"></i>
                    </div>

                    <div>

                        <div class="crm-label">
                            Overdue Follow-up
                        </div>

                        <div class="crm-number">
                            <?= number_format($overdueFollowup) ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         LOWER DASHBOARD
         ===================================================== -->

    <div class="row g-4">

        <!-- Call Performance -->

        <div class="col-lg-5">

            <div class="crm-section">

                <div class="crm-section-title">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Call Performance
                </div>


                <div class="mb-3">

                    <div class="d-flex justify-content-between mb-2">

                        <span class="crm-small">
                            Connected Calls
                        </span>

                        <strong>
                            <?= $connectedPercent ?>%
                        </strong>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-bar bg-success"
                            style="width: <?= $connectedPercent ?>%"
                        ></div>

                    </div>

                </div>


                <div class="row g-3 mt-3">

                    <div class="col-6">

                        <div class="p-3 rounded-3 bg-light">

                            <div class="crm-small">
                                আজকের কল
                            </div>

                            <div class="fs-4 fw-bold mt-1">
                                <?= number_format($todayCalls) ?>
                            </div>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="p-3 rounded-3 bg-light">

                            <div class="crm-small">
                                আজ Connected
                            </div>

                            <div class="fs-4 fw-bold mt-1 text-success">
                                <?= number_format($todayConnected) ?>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="mt-4">

                    <div class="d-flex justify-content-between mb-2">

                        <span class="crm-small">
                            Pending / Not Connected
                        </span>

                        <strong>
                            <?= $totalCalls > 0
                                ? round(($pendingCalls / $totalCalls) * 100)
                                : 0
                            ?>%
                        </strong>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-bar bg-warning"
                            style="width: <?= $totalCalls > 0
                                ? round(($pendingCalls / $totalCalls) * 100)
                                : 0
                            ?>%"
                        ></div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Follow-up Overview -->

        <div class="col-lg-4">

            <div class="crm-section">

                <div class="crm-section-title">
                    <i class="bi bi-calendar-event text-primary me-2"></i>
                    Follow-up Overview
                </div>


                <div class="follow-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="follow-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>

                        <div class="flex-grow-1">

                            <div class="fw-semibold">
                                আজকের Follow-up
                            </div>

                            <div class="crm-small">
                                আজ গ্রাহকদের কল করুন
                            </div>

                        </div>

                        <strong class="text-primary">
                            <?= $todayFollowup ?>
                        </strong>

                    </div>

                </div>


                <div class="follow-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="follow-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>

                        <div class="flex-grow-1">

                            <div class="fw-semibold">
                                Upcoming
                            </div>

                            <div class="crm-small">
                                পরবর্তী Follow-up
                            </div>

                        </div>

                        <strong class="text-primary">
                            <?= $upcomingFollowup ?>
                        </strong>

                    </div>

                </div>


                <div class="follow-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="follow-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>

                        <div class="flex-grow-1">

                            <div class="fw-semibold">
                                Overdue
                            </div>

                            <div class="crm-small">
                                Follow-up বাকি আছে
                            </div>

                        </div>

                        <strong class="text-danger">
                            <?= $overdueFollowup ?>
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        <!-- Quick Actions -->

        <div class="col-lg-3">

            <div class="crm-section">

                <div class="crm-section-title">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                    Quick Actions
                </div>


                <a
                    href="index.php?page=call_story/add"
                    class="quick-action"
                >

                    <div class="quick-action-icon bg-primary-subtle text-primary">
                        <i class="bi bi-telephone-plus"></i>
                    </div>

                    <div>
                        <div class="fw-semibold">
                            নতুন কল
                        </div>

                        <small class="text-muted">
                            কল রেকর্ড করুন
                        </small>
                    </div>

                </a>


                <a
                    href="index.php?page=call_story/today_followup"
                    class="quick-action"
                >

                    <div class="quick-action-icon bg-success-subtle text-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>
                        <div class="fw-semibold">
                            আজকের Follow-up
                        </div>

                        <small class="text-muted">
                            আজকের কল দেখুন
                        </small>
                    </div>

                </a>


                <a
                    href="index.php?page=call_story/index"
                    class="quick-action"
                >

                    <div class="quick-action-icon bg-warning-subtle text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div>
                        <div class="fw-semibold">
                            Call History
                        </div>

                        <small class="text-muted">
                            পুরাতন কল দেখুন
                        </small>
                    </div>

                </a>


                <a
                    href="index.php?page=call_story/call_report"
                    class="quick-action"
                >

                    <div class="quick-action-icon bg-info-subtle text-info">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>

                    <div>
                        <div class="fw-semibold">
                            CRM Report
                        </div>

                        <small class="text-muted">
                            রিপোর্ট দেখুন
                        </small>
                    </div>

                </a>

            </div>

        </div>

    </div>


    <!-- =====================================================
         RECENT ACTIVITY
         ===================================================== -->

    <?php if (!empty($recentCalls)) { ?>

    <div class="row mt-4">

        <div class="col-12">

            <div class="crm-section">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div class="crm-section-title mb-0">
                        <i class="bi bi-activity text-primary me-2"></i>
                        Recent Activity
                    </div>

                    

                </div>


                <?php foreach ($recentCalls as $call) { ?>

                    <div class="activity">

                        <div class="activity-icon">

                            <?php if (($call['call_status'] ?? '') == 'Connected') { ?>

                                <i class="bi bi-telephone-check"></i>

                            <?php } else { ?>

                                <i class="bi bi-telephone"></i>

                            <?php } ?>

                        </div>


                        <div class="flex-grow-1">

                            <div class="activity-name">

                                <?= htmlspecialchars(
                                    $call['name'] ?? 'Unknown Customer'
                                ) ?>

                            </div>

                            <div class="activity-info">

                                <?= htmlspecialchars(
                                    $call['phone'] ?? ''
                                ) ?>

                                <?php if (!empty($call['car_number'])) { ?>

                                    · <?= htmlspecialchars(
                                        $call['car_number']
                                    ) ?>

                                <?php } ?>

                            </div>

                        </div>


                        <div class="text-end">

                            <?php if (($call['call_status'] ?? '') == 'Connected') { ?>

                                <span class="badge bg-success-subtle text-success">
                                    Connected
                                </span>

                            <?php } else { ?>

                                <span class="badge bg-warning-subtle text-warning">
                                    <?= htmlspecialchars(
                                        $call['call_status'] ?: 'Pending'
                                    ) ?>
                                </span>

                            <?php } ?>

                        </div>

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

    <?php } ?>

</div>
 
