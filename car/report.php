<?php

// ---------- মাস অনুযায়ী ও স্ট্যাটাস অনুযায়ী ফিল্টার ----------
$selected_month = trim($_GET['month'] ?? '');
$is_filtered    = $selected_month !== '' && preg_match('/^\d{4}-\d{2}$/', $selected_month);

$status_filter  = $_GET['status'] ?? '';
$status_filter  = in_array($status_filter, ['completed', 'pending'], true) ? $status_filter : '';

$where  = [];
$params = [];

if ($is_filtered) {
    $where[]  = "DATE_FORMAT(created_at, '%Y-%m') = ?";
    $params[] = $selected_month;
}
if ($status_filter === 'completed') {
    $where[] = "status = 'completed'";
} elseif ($status_filter === 'pending') {
    $where[] = "(status IS NULL OR status <> 'completed')";
}

$sql = "SELECT * FROM customer_records";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

// আজকের রেকর্ড
$today = date('Y-m-d');
$stmt_today = $pdo->prepare("SELECT * FROM customer_records WHERE DATE(created_at) = ?");
$stmt_today->execute([$today]);
$today_records = $stmt_today->fetchAll();

// মাস অনুযায়ী সব মাসের লিস্ট (ড্রপডাউন/কুইক চিপের জন্য)
$stmt_months = $pdo->query("SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') AS ym FROM customer_records ORDER BY ym DESC");
$available_months = $stmt_months->fetchAll(PDO::FETCH_COLUMN);

// Summary Calculation (বাছাই করা মাস/স্ট্যাটাস অথবা সব রেকর্ডের উপর ভিত্তি করে)
$total_records = count($records);
$total_paid    = 0;
$total_due     = 0;
$total_amount  = 0;
$completed     = 0;
$pending       = 0;
$unique_phones = [];

foreach ($records as $r) {
    $total_amount += $r['total_price']  ?? 0;
    $total_paid   += $r['paid_amount']  ?? 0;
    $total_due    += $r['due_amount']   ?? 0;

    if (($r['status'] ?? '') == 'completed') {
        $completed++;
    } else {
        $pending++;
    }

    if (!empty($r['customer_phone'])) {
        $unique_phones[$r['customer_phone']] = true;
    }
}

$completion_rate = $total_records > 0 ? round(($completed / $total_records) * 100) : 0;
$collection_rate = $total_amount > 0 ? round(($total_paid / $total_amount) * 100) : 0;
$avg_ticket       = $total_records > 0 ? round($total_amount / $total_records) : 0;
$unique_customers = count($unique_phones);

// আগের মাসের সাথে তুলনা (শুধু মাস ফিল্টার করা থাকলে)
$prev_stats = null;
if ($is_filtered) {
    $prev_ym = date('Y-m', strtotime($selected_month . '-01 -1 month'));
    $prev_sql = "SELECT
            COALESCE(SUM(total_price), 0) AS total_amount,
            COALESCE(SUM(paid_amount), 0) AS total_paid,
            COALESCE(SUM(due_amount), 0)  AS total_due,
            COUNT(*) AS cnt
        FROM customer_records WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
    $stmt_prev = $pdo->prepare($prev_sql);
    $stmt_prev->execute([$prev_ym]);
    $prev_stats = $stmt_prev->fetch();
}

function pct_change($current, $previous)
{
    if ($previous == 0) {
        return $current > 0 ? ['val' => 100, 'dir' => 'up'] : ['val' => 0, 'dir' => 'flat'];
    }
    $val = round((($current - $previous) / $previous) * 100);
    return ['val' => abs($val), 'dir' => $val > 0 ? 'up' : ($val < 0 ? 'down' : 'flat')];
}

function trend_badge($current, $previous)
{
    $c = pct_change($current, $previous);
    $icon  = $c['dir'] === 'up' ? 'bi-arrow-up-short' : ($c['dir'] === 'down' ? 'bi-arrow-down-short' : 'bi-dash');
    $cls   = $c['dir'] === 'up' ? 'trend-up' : ($c['dir'] === 'down' ? 'trend-down' : 'trend-flat');
    return "<span class=\"trend-badge {$cls}\"><i class=\"bi {$icon}\"></i>{$c['val']}%</span>";
}

// মাসের বাংলা লেবেল বানানোর জন্য
$bn_months = [
    '01' => 'জানুয়ারি', '02' => 'ফেব্রুয়ারি', '03' => 'মার্চ', '04' => 'এপ্রিল', '05' => 'মে', '06' => 'জুন',
    '07' => 'জুলাই', '08' => 'আগস্ট', '09' => 'সেপ্টেম্বর', '10' => 'অক্টোবর', '11' => 'নভেম্বর', '12' => 'ডিসেম্বর'
];
function bn_month_label($ym, $bn_months)
{
    [$y, $m] = explode('-', $ym);
    return ($bn_months[$m] ?? $m) . ' ' . $y;
}

// URL বিল্ডার - বর্তমান GET প্যারামিটার ধরে রেখে নির্দিষ্ট কিছু ওভাররাইড করার জন্য
function rp_url($overrides)
{
    $q = array_merge($_GET, $overrides);
    foreach ($q as $k => $v) {
        if ($v === '' || $v === null) {
            unset($q[$k]);
        }
    }
    return '?' . http_build_query($q);
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
                    <?php if ($status_filter !== ''): ?>
                        &middot; ফিল্টার: <?= $status_filter === 'completed' ? 'সম্পন্ন' : 'পেন্ডিং' ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-success" onclick="exportCSV()">
                <i class="bi bi-file-earmark-spreadsheet"></i> CSV এক্সপোর্ট
            </button>
            <a href="index.php?page=car/index" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> ফিরে যান
            </a>
        </div>
    </div>

    <!-- মাস ও স্ট্যাটাস অনুযায়ী ফিল্টার -->
    <div class="panel mb-4">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> মাস ও স্ট্যাটাস অনুযায়ী দেখুন</h5>
        </div>
        <form method="GET" class="month-filter-body">
            <?php if (!empty($_GET['page'])): ?>
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
            <?php endif; ?>
            <?php if ($status_filter !== ''): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <?php endif; ?>

            <div class="month-filter-field">
                <label for="monthInput">মাস বাছাই করুন</label>
                <input type="month" id="monthInput" name="month" value="<?= htmlspecialchars($selected_month) ?>"
                    max="<?= date('Y-m') ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> দেখুন
            </button>
            <?php if ($is_filtered || $status_filter !== ''): ?>
            <a href="<?= rp_url(['month' => '', 'status' => '']) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> সব ফিল্টার মুছুন
            </a>
            <?php endif; ?>

            <div class="status-tabs">
                <a href="<?= rp_url(['status' => '']) ?>" class="status-tab <?= $status_filter === '' ? 'active' : '' ?>">
                    <i class="bi bi-collection"></i> সব
                </a>
                <a href="<?= rp_url(['status' => 'completed']) ?>" class="status-tab tab-success <?= $status_filter === 'completed' ? 'active' : '' ?>">
                    <i class="bi bi-check-circle"></i> সম্পন্ন
                </a>
                <a href="<?= rp_url(['status' => 'pending']) ?>" class="status-tab tab-warning <?= $status_filter === 'pending' ? 'active' : '' ?>">
                    <i class="bi bi-hourglass-split"></i> পেন্ডিং
                </a>
            </div>

            <?php if (!empty($available_months)): ?>
            <div class="month-quick-links">
                <?php foreach (array_slice($available_months, 0, 6) as $ym): ?>
                <a href="<?= rp_url(['month' => $ym]) ?>"
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
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-primary">
                <div class="stat-icon"><i class="bi bi-clipboard-data"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের রেকর্ড' : 'মোট রেকর্ড' ?></span>
                    <span class="stat-value"><?= $total_records ?></span>
                    <?php if ($is_filtered && $prev_stats): ?>
                        <?= trend_badge($total_records, $prev_stats['cnt']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-success">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের আয়' : 'মোট আয়' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_paid) ?></span>
                    <?php if ($is_filtered && $prev_stats): ?>
                        <?= trend_badge($total_paid, $prev_stats['total_paid']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-danger">
                <div class="stat-icon"><i class="bi bi-exclamation-circle"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের বাকি' : 'মোট বাকি' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_due) ?></span>
                    <?php if ($is_filtered && $prev_stats): ?>
                        <?= trend_badge($total_due, $prev_stats['total_due']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-info">
                <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-body">
                    <span class="stat-label"><?= $is_filtered ? 'এই মাসের লেনদেন' : 'মোট লেনদেন' ?></span>
                    <span class="stat-value">৳ <?= number_format($total_amount) ?></span>
                    <?php if ($is_filtered && $prev_stats): ?>
                        <?= trend_badge($total_amount, $prev_stats['total_amount']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="stat-body">
                    <span class="stat-label">গড় বিল</span>
                    <span class="stat-value">৳ <?= number_format($avg_ticket) ?></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card stat-teal">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-body">
                    <span class="stat-label">ইউনিক কাস্টমার</span>
                    <span class="stat-value"><?= $unique_customers ?></span>
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

    <!-- বিস্তারিত রিপোর্ট টেবিল -->
    <div class="panel">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-table"></i> বিস্তারিত রেকর্ডস</h5>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-wrap">
                        <i class="bi bi-search search-wrap-icon"></i>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="নাম, ফোন, গাড়ি, তারিখ বা স্ট্যাটাস দিয়ে সার্চ করুন...">
                        <i class="bi bi-x-circle search-clear-icon" id="searchClear" title="মুছুন"></i>
                    </div>
                    <select id="perPageSelect" class="form-select form-select-sm per-page-select">
                        <option value="10">১০ / পেজ</option>
                        <option value="25" selected>২৫ / পেজ</option>
                        <option value="50">৫০ / পেজ</option>
                        <option value="all">সব দেখান</option>
                    </select>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-printer"></i> প্রিন্ট
                    </button>
                </div>
            </div>
        </div>

        <div class="table-info-bar">
            <span id="rangeInfo"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th data-sort="date">তারিখ <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th data-sort="text">কাস্টমার <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th data-sort="text">ফোন <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th data-sort="text">গাড়ি <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="text-end" data-sort="number">মোট টাকা <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="text-end" data-sort="number">পেইড <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="text-end" data-sort="number">বাকি <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th data-sort="text">স্ট্যাটাস <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_records === 0): ?>
                    <tr class="empty-row">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i> কোনো রেকর্ড পাওয়া যায়নি
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td class="text-nowrap" data-value="<?= strtotime($row['created_at']) ?>">
                            <?= date('d-m-Y', strtotime($row['created_at'])) ?>
                        </td>
                        <td>
                            <div class="cust-cell">
                                <span
                                    class="cust-avatar"><?= htmlspecialchars(mb_substr($row['customer_name'] ?? '?', 0, 1)) ?></span>
                                <span><?= htmlspecialchars($row['customer_name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                        <td><?= htmlspecialchars($row['car_name'] ?? '-') ?></td>
                        <td class="text-end fw-semibold" data-value="<?= (float)($row['total_price'] ?? 0) ?>">৳
                            <?= number_format($row['total_price'] ?? 0) ?></td>
                        <td class="text-end text-success fw-semibold" data-value="<?= (float)($row['paid_amount'] ?? 0) ?>">৳
                            <?= number_format($row['paid_amount'] ?? 0) ?></td>
                        <td class="text-end text-danger fw-semibold" data-value="<?= (float)($row['due_amount'] ?? 0) ?>">৳
                            <?= number_format($row['due_amount'] ?? 0) ?>
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
            <div id="noResults" class="no-results" style="display:none;">
                <i class="bi bi-search fs-4 d-block mb-2"></i> সার্চের সাথে মিলে এমন কোনো রেকর্ড পাওয়া যায়নি
            </div>
        </div>

        <div class="table-footer" id="pagerBar">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPageBtn">
                <i class="bi bi-chevron-left"></i> আগে
            </button>
            <span id="pageIndicator" class="page-indicator"></span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPageBtn">
                পরে <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Report Interactivity Script -->
<script>
(function () {
    const table = document.getElementById('reportTable');
    const tbody = table.querySelector('tbody');
    let allRows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));
    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    const perPageSelect = document.getElementById('perPageSelect');
    const rangeInfo = document.getElementById('rangeInfo');
    const noResults = document.getElementById('noResults');
    const pageIndicator = document.getElementById('pageIndicator');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');

    let currentPage = 1;
    let perPage = 25;
    let sortState = { col: null, dir: 1 };

    function getMatchingRows() {
        return allRows.filter(r => !r.classList.contains('search-hide'));
    }

    function renderPage() {
        const matched = getMatchingRows();
        const total = matched.length;
        const totalPages = (perPage === 0) ? 1 : Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        allRows.forEach(r => r.classList.remove('page-hide'));

        if (perPage !== 0) {
            matched.forEach((r, i) => {
                if (i < (currentPage - 1) * perPage || i >= currentPage * perPage) {
                    r.classList.add('page-hide');
                }
            });
        }

        allRows.forEach(r => {
            r.style.display = (r.classList.contains('search-hide') || r.classList.contains('page-hide')) ? 'none' : '';
        });

        if (total === 0) {
            rangeInfo.textContent = '';
            noResults.style.display = allRows.length > 0 ? '' : 'none';
        } else {
            const start = (currentPage - 1) * (perPage || total) + 1;
            const end = Math.min(currentPage * (perPage || total), total);
            rangeInfo.textContent = `দেখানো হচ্ছে ${start}-${end} এর মধ্যে ${total} টি রেকর্ড`;
            noResults.style.display = 'none';
        }

        pageIndicator.textContent = `পেজ ${currentPage} / ${totalPages}`;
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;
        document.getElementById('pagerBar').style.display = (perPage === 0 || total <= perPage) ? 'none' : '';
    }

    searchInput.addEventListener('input', function () {
        const value = this.value.toLowerCase().trim();
        allRows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.classList.toggle('search-hide', !(value === '' || text.includes(value)));
        });
        currentPage = 1;
        renderPage();
    });

    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });

    perPageSelect.addEventListener('change', function () {
        perPage = this.value === 'all' ? 0 : parseInt(this.value, 10);
        currentPage = 1;
        renderPage();
    });

    prevBtn.addEventListener('click', function () {
        currentPage--;
        renderPage();
    });
    nextBtn.addEventListener('click', function () {
        currentPage++;
        renderPage();
    });

    table.querySelectorAll('th[data-sort]').forEach((th, colIndex) => {
        th.addEventListener('click', function () {
            const type = th.dataset.sort;
            const dir = (sortState.col === colIndex) ? -sortState.dir : 1;
            sortState = { col: colIndex, dir };

            const rows = allRows.slice();
            rows.sort((a, b) => {
                const aCell = a.children[colIndex];
                const bCell = b.children[colIndex];
                let av = aCell.dataset.value !== undefined ? aCell.dataset.value : aCell.innerText.trim();
                let bv = bCell.dataset.value !== undefined ? bCell.dataset.value : bCell.innerText.trim();

                if (type === 'number' || type === 'date') {
                    av = parseFloat(av) || 0;
                    bv = parseFloat(bv) || 0;
                    return (av - bv) * dir;
                }
                return av.localeCompare(bv, 'bn') * dir;
            });

            rows.forEach(r => tbody.appendChild(r));
            allRows = rows;

            table.querySelectorAll('th[data-sort]').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            th.classList.add(dir === 1 ? 'sort-asc' : 'sort-desc');

            renderPage();
        });
    });

    perPage = parseInt(perPageSelect.value, 10) || 25;
    renderPage();

    window.exportCSV = function () {
        const header = ['তারিখ', 'কাস্টমার', 'ফোন', 'গাড়ি', 'মোট টাকা', 'পেইড', 'বাকি', 'স্ট্যাটাস'];
        const csvRows = [header.join(',')];

        getMatchingRows().forEach(row => {
            const cells = row.querySelectorAll('td');
            const vals = Array.from(cells).slice(0, 8).map(c => {
                const text = c.innerText.trim().replace(/\s+/g, ' ');
                return '"' + text.replace(/"/g, '""') + '"';
            });
            csvRows.push(vals.join(','));
        });

        const blob = new Blob(["\ufeff" + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'report_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
})();
</script>

<style>
.report-page {
    --rp-primary: #1e3a8a;
    --rp-primary-light: #2563eb;
    --rp-success: #16a34a;
    --rp-danger: #dc2626;
    --rp-info: #0891b2;
    --rp-purple: #7c3aed;
    --rp-teal: #0d9488;
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
    box-shadow: 0 4px 12px rgba(30, 58, 138, .25);
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
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
    transition: transform .15s, box-shadow .15s;
}

.report-page .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
}

.report-page .stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    flex-shrink: 0;
}

.report-page .stat-primary .stat-icon { background: var(--rp-primary-light); }
.report-page .stat-success .stat-icon { background: var(--rp-success); }
.report-page .stat-danger  .stat-icon { background: var(--rp-danger); }
.report-page .stat-info    .stat-icon { background: var(--rp-info); }
.report-page .stat-purple  .stat-icon { background: var(--rp-purple); }
.report-page .stat-teal    .stat-icon { background: var(--rp-teal); }

.report-page .stat-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.report-page .stat-label {
    font-size: 12px;
    color: var(--rp-muted);
    font-weight: 600;
    white-space: nowrap;
}

.report-page .stat-value {
    font-size: 19px;
    font-weight: 700;
    color: #1e293b;
}

.report-page .trend-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 700;
    margin-top: 2px;
    width: fit-content;
    padding: 1px 6px 1px 2px;
    border-radius: 6px;
}

.report-page .trend-up { color: var(--rp-success); background: #dcfce7; }
.report-page .trend-down { color: var(--rp-danger); background: #fee2e2; }
.report-page .trend-flat { color: var(--rp-muted); background: #f1f5f9; }

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

/* ---------- মাস ও স্ট্যাটাস ফিল্টার ---------- */
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

.report-page .status-tabs {
    display: flex;
    gap: 6px;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 10px;
}

.report-page .status-tab {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all .15s;
}

.report-page .status-tab:hover { background: #e2e8f0; }
.report-page .status-tab.active { background: var(--rp-primary-light); color: #fff; }
.report-page .status-tab.tab-success.active { background: var(--rp-success); }
.report-page .status-tab.tab-warning.active { background: #d97706; }

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

.report-page .month-chip:hover { background: #e2e8f0; }
.report-page .month-chip.active { background: var(--rp-primary-light); color: #fff; }

/* ---------- সার্চ ইনপুট ---------- */
.report-page .search-wrap {
    position: relative;
    width: 100%;
    max-width: 320px;
}

.report-page .search-wrap-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--rp-muted);
    font-size: 14px;
}

.report-page .search-clear-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--rp-muted);
    font-size: 14px;
    cursor: pointer;
    opacity: .6;
}

.report-page .search-clear-icon:hover { opacity: 1; }

.report-page .search-wrap input {
    padding-left: 34px;
    padding-right: 30px;
    border-radius: 9px;
    border: 1px solid var(--rp-border);
}

.report-page .search-wrap input:focus {
    border-color: var(--rp-primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.report-page .per-page-select {
    width: auto;
    border-radius: 9px;
    font-size: 13px;
}

/* ---------- টেবিল ---------- */
.report-page .table-info-bar {
    padding: 10px 20px 0;
    font-size: 12.5px;
    color: var(--rp-muted);
}

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
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}

.report-page table thead th:hover { background: #1e293b; }

.report-page .sort-icon {
    font-size: 11px;
    opacity: .5;
    margin-left: 4px;
}

.report-page table thead th.sort-asc .sort-icon,
.report-page table thead th.sort-desc .sort-icon {
    opacity: 1;
    color: #60a5fa;
}

.report-page table tbody td {
    padding: 12px 14px;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
    border-top: none;
}

.report-page table tbody tr:hover { background: #f8fafc; }

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

.report-page .badge-success { background: #dcfce7; color: #15803d; }
.report-page .badge-warning { background: #fef3c7; color: #b45309; }

.report-page .no-results {
    text-align: center;
    color: var(--rp-muted);
    padding: 36px 0;
}

.report-page .table-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 14px 20px;
    border-top: 1px solid var(--rp-border);
}

.report-page .page-indicator {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

@media (max-width:767px) {
    .report-page .page-heading { align-items: flex-start; }
    .report-page .panel-header { flex-direction: column; align-items: flex-start; }
    .report-page .search-wrap { max-width: 100%; }
    .report-page .month-filter-body { align-items: stretch; }
    .report-page .status-tabs { width: 100%; justify-content: space-between; }
}

@media print {
    .report-page .page-heading,
    .report-page .panel-header input,
    .report-page .panel-header select,
    .report-page .panel-header button,
    .report-page .btn,
    .report-page .progress-card,
    .report-page .month-filter-body,
    .report-page .search-wrap,
    .report-page .table-footer,
    .report-page .table-info-bar,
    .report-page .sort-icon {
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

    .report-page tr { display: table-row !important; }
}
</style>