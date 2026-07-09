<?php

// ---------- মাস অনুযায়ী ফিল্টার (YYYY-MM) ----------
$selected_month = trim($_GET['month'] ?? '');
$is_filtered    = $selected_month !== '' && preg_match('/^\d{4}-\d{2}$/', $selected_month);

if ($is_filtered) {
    $stmt = $pdo->prepare("SELECT * FROM customer_records WHERE DATE_FORMAT(created_at, '%Y-%m') = ? ORDER BY created_at DESC");
    $stmt->execute([$selected_month]);
} else {
    $stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC");
}
$records = $stmt->fetchAll();

// আজকের রেকর্ড
$today = date('Y-m-d');
$stmt_today = $pdo->prepare("SELECT * FROM customer_records WHERE DATE(created_at) = ?");
$stmt_today->execute([$today]);
$today_records = $stmt_today->fetchAll();

// মাস অনুযায়ী সব মাসের লিস্ট (ড্রপডাউনের জন্য, অপশনাল কিন্তু কাজে লাগবে)
$stmt_months = $pdo->query("SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') AS ym FROM customer_records ORDER BY ym DESC");
$available_months = $stmt_months->fetchAll(PDO::FETCH_COLUMN);

// Summary Calculation (বাছাই করা মাস অথবা সব রেকর্ডের উপর ভিত্তি করে)
$total_records = count($records);
$total_paid    = 0;
$total_due     = 0;
$total_amount  = 0;
$completed     = 0;
$pending       = 0;

foreach ($records as $r) {
    $total_amount += $r['total_price']  ?? 0;
    $total_paid   += $r['paid_amount']  ?? 0;
    $total_due    += $r['due_amount']   ?? 0;

    if (($r['status'] ?? '') == 'completed') {
        $completed++;
    } else {
        $pending++;
    }
}

$completion_rate = $total_records > 0 ? round(($completed / $total_records) * 100) : 0;
$collection_rate = $total_amount > 0 ? round(($total_paid / $total_amount) * 100) : 0;

// মাসের বাংলা লেবেল বানানোর জন্য
$bn_months = [
    '01'=>'জানুয়ারি','02'=>'ফেব্রুয়ারি','03'=>'মার্চ','04'=>'এপ্রিল','05'=>'মে','06'=>'জুন',
    '07'=>'জুলাই','08'=>'আগস্ট','09'=>'সেপ্টেম্বর','10'=>'অক্টোবর','11'=>'নভেম্বর','12'=>'ডিসেম্বর'
];
function bn_month_label($ym, $bn_months){
    [$y, $m] = explode('-', $ym);
    return ($bn_months[$m] ?? $m) . ' ' . $y;
}
?>

<div class="container-fluid px-3 px-lg-4 py-4 report-page">

    <div class="page-heading">
        <div class="page-heading-copy">
            <span class="page-icon"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
            <div>
                <p class="eyebrow mb-1">রিপোর্ট</p>
                <h1 class="h3 mb-1">
                    <?= $is_filtered ? bn_month_label($selected_month, $bn_months) . ' এর রিপোর্ট' : 'সার্বিক রিপোর্ট' ?>
                </h1>
                <p class="text-muted mb-0 small">
                    <?= $is_filtered ? 'এই মাসে' : 'সর্বমোট' ?> <?= $total_records ?> টি রেকর্ড
                    <?php if (!$is_filtered): ?> &middot; আজকের নতুন এন্ট্রি: <?= count($today_records) ?>
                    টি<?php endif; ?>
                </p>
            </div>
        </div>

        <a href="index.php?page=car/index" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> ফিরে যান
        </a>
    </div>

    <!-- মাস অনুযায়ী সার্চ -->
    <div class="panel mb-4">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> মাস অনুযায়ী আয়/হিসাব দেখুন</h5>
        </div>
        <form method="GET" class="month-filter-body">
            <?php if (!empty($_GET['page'])): ?>
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
            <?php endif; ?>
            <div class="month-filter-field">
                <label for="monthInput">মাস বাছাই করুন</label>
                <input type="month" id="monthInput" name="month" value="<?= htmlspecialchars($selected_month) ?>"
                    max="<?= date('Y-m') ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> দেখুন
            </button>
            <?php if ($is_filtered): ?>
            <a href="?<?= http_build_query(array_diff_key($_GET, ['month' => ''])) ?>"
                class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> ফিল্টার মুছুন
            </a>
            <?php endif; ?>

            <?php if (!empty($available_months)): ?>
            <div class="month-quick-links">
                <?php foreach (array_slice($available_months, 0, 6) as $ym): ?>
                <a href="?month=<?= urlencode($ym) ?>"
                    class="month-chip <?= $ym === $selected_month ? 'active' : '' ?>">
                    <?= bn_month_label($ym, $bn_months) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-primary">
                <div class="stat-icon"><i class="bi bi-clipboard-data"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের রেকর্ড' : 'মোট রেকর্ড' ?></span>
                    <span class="stat-value"><?= $total_records ?></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-success">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের আয়' : 'মোট আয়' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_paid, 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-danger">
                <div class="stat-icon"><i class="bi bi-exclamation-circle"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের বাকি' : 'মোট বাকি' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_due, 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-info">
                <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের লেনদেন' : 'মোট লেনদেন' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_amount, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="progress-card">
                <div class="progress-card-head">
                    <span><i class="bi bi-check-circle text-success"></i> কমপ্লিটেড রেকর্ড</span>
                    <strong><?= $completed ?> / <?= $total_records ?></strong>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: <?= $completion_rate ?>%"></div>
                </div>
                <span class="progress-note"><?= $completion_rate ?>% সম্পন্ন &middot; <?= $pending ?> টি পেন্ডিং</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="progress-card">
                <div class="progress-card-head">
                    <span><i class="bi bi-wallet2 text-primary"></i> কালেকশন রেট</span>
                    <strong>৳ <?= number_format($total_paid, 0) ?> / ৳ <?= number_format($total_amount, 0) ?></strong>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width: <?= $collection_rate ?>%"></div>
                </div>
                <span class="progress-note"><?= $collection_rate ?>% আদায় হয়েছে &middot; বাকি ৳
                    <?= number_format($total_due, 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="panel mb-4">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-search"></i> রিপোর্ট সার্চ করুন</h5>
                <div class="search-wrap">
                    <i class="bi bi-search search-wrap-icon"></i>
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="কাস্টমার নাম, ফোন, গাড়ি বা তারিখ দিয়ে সার্চ করুন...">
                </div>
            </div>
        </div>
    </div>

    <!-- বিস্তারিত রিপোর্ট টেবিল -->
    <div class="panel">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-table"></i> বিস্তারিত রেকর্ডস</h5>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer"></i> প্রিন্ট
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>কাস্টমার</th>
                        <th>ফোন</th>
                        <th>গাড়ি</th>
                        <th class="text-end">মোট টাকা</th>
                        <th class="text-end">পেইড</th>
                        <th class="text-end">বাকি</th>
                        <th>স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_records === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i> কোনো রেকর্ড পাওয়া যায়নি
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <div class="cust-cell">
                                <span
                                    class="cust-avatar"><?= htmlspecialchars(mb_substr($row['customer_name'] ?? '?', 0, 1)) ?></span>
                                <span><?= htmlspecialchars($row['customer_name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                        <td><?= htmlspecialchars($row['car_name'] ?? '-') ?></td>
                        <td class="text-end fw-semibold">৳ <?= number_format($row['total_price'] ?? 0, 2) ?></td>
                        <td class="text-end text-success fw-semibold">৳
                            <?= number_format($row['paid_amount'] ?? 0, 2) ?></td>
                        <td class="text-end text-danger fw-semibold">৳ <?= number_format($row['due_amount'] ?? 0, 2) ?>
                        </td>
                        <td>
                            <span
                                class="badge-status badge-<?= ($row['status'] ?? '') == 'completed' ? 'success' : 'warning' ?>">
                                <?= strtoupper($row['status'] ?? 'Pending') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Search Script -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase().trim();
    let rows = document.querySelectorAll("#reportTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
});
</script>

<style>
.report-page {
    --rp-primary: #1e3a8a;
    --rp-primary-light: #2563eb;
    --rp-success: #16a34a;
    --rp-danger: #dc2626;
    --rp-info: #0891b2;
    --rp-border: #e5e7eb;
    --rp-muted: #64748b;
    font-family: 'Hind Siliguri', 'Noto Sans Bengali', Arial, sans-serif;
}

/* ---------- পেইজ হেডিং ---------- */
.report-page .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 22px;
}

.report-page .page-heading-copy {
    display: flex;
    align-items: center;
    gap: 14px;
}

.report-page .page-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--rp-primary), var(--rp-primary-light));
    color: #fff;
    border-radius: 14px;
    font-size: 22px;
    flex-shrink: 0;
}

.report-page .eyebrow {
    text-transform: uppercase;
    letter-spacing: .6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--rp-primary-light);
}

/* ---------- স্ট্যাট কার্ড ---------- */
.report-page .stat-card {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 14px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
    transition: transform .15s, box-shadow .15s;
}

.report-page .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
}

.report-page .stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
    flex-shrink: 0;
}

.report-page .stat-primary .stat-icon {
    background: var(--rp-primary-light);
}

.report-page .stat-success .stat-icon {
    background: var(--rp-success);
}

.report-page .stat-danger .stat-icon {
    background: var(--rp-danger);
}

.report-page .stat-info .stat-icon {
    background: var(--rp-info);
}

.report-page .stat-body {
    display: flex;
    flex-direction: column;
}

.report-page .stat-label {
    font-size: 12.5px;
    color: var(--rp-muted);
    font-weight: 600;
}

.report-page .stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

/* ---------- প্রগ্রেস কার্ড ---------- */
.report-page .progress-card {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 14px;
    padding: 18px 20px;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.report-page .progress-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #1e293b;
}

.report-page .progress {
    height: 9px;
    border-radius: 6px;
    background: #f1f5f9;
    overflow: hidden;
}

.report-page .progress-bar {
    border-radius: 6px;
}

.report-page .progress-note {
    display: block;
    margin-top: 8px;
    font-size: 12.5px;
    color: var(--rp-muted);
}

/* ---------- প্যানেল ---------- */
.report-page .panel {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.report-page .panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--rp-border);
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: space-between;
    background: #f8fafc;
}

.report-page .panel-header h5 {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
}

/* ---------- মাস ফিল্টার ---------- */
.report-page .month-filter-body {
    padding: 18px 20px;
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.report-page .month-filter-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.report-page .month-filter-field label {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--rp-muted);
}

.report-page .month-filter-field input[type=month] {
    padding: 10px 12px;
    border: 1px solid var(--rp-border);
    border-radius: 9px;
    font-family: inherit;
    font-size: 14px;
    min-width: 180px;
}

.report-page .month-filter-field input[type=month]:focus {
    outline: none;
    border-color: var(--rp-primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.report-page .month-quick-links {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}

.report-page .month-chip {
    padding: 6px 14px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #334155;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all .15s;
}

.report-page .month-chip:hover {
    background: #e2e8f0;
}

.report-page .month-chip.active {
    background: var(--rp-primary-light);
    color: #fff;
}

/* ---------- সার্চ ইনপুট ---------- */
.report-page .search-wrap {
    position: relative;
    width: 100%;
    max-width: 360px;
}

.report-page .search-wrap-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--rp-muted);
    font-size: 14px;
}

.report-page .search-wrap input {
    padding-left: 34px;
    border-radius: 9px;
    border: 1px solid var(--rp-border);
}

.report-page .search-wrap input:focus {
    border-color: var(--rp-primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

/* ---------- টেবিল ---------- */
.report-page table thead {
    background: #0f172a;
}

.report-page table thead th {
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding: 13px 14px;
    border: none;
}

.report-page table tbody td {
    padding: 12px 14px;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
    border-top: none;
}

.report-page table tbody tr:hover {
    background: #f8fafc;
}

.report-page .cust-cell {
    display: flex;
    align-items: center;
    gap: 9px;
}

.report-page .cust-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--rp-primary), var(--rp-primary-light));
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.report-page .badge-status {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .3px;
}

.report-page .badge-success {
    background: #dcfce7;
    color: #15803d;
}

.report-page .badge-warning {
    background: #fef3c7;
    color: #b45309;
}

@media (max-width:767px) {
    .report-page .page-heading {
        align-items: flex-start;
    }

    .report-page .panel-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .report-page .search-wrap {
        max-width: 100%;
    }
}

@media print {

    .report-page .page-heading,
    .report-page .panel-header input,
    .report-page .btn,
    .report-page .progress-card,
    .report-page .month-filter-body,
    .report-page .search-wrap {
        display: none !important;
    }

    .report-page .panel {
        border: 1px solid #ccc;
        box-shadow: none;
    }

    .report-page table thead {
        background: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>