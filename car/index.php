<?php
$stmt = $pdo->query("SELECT * FROM customer_records WHERE status != 'completed' ORDER BY created_at DESC ");
$records = $stmt->fetchAll();
 $totalRecords      = count($records);
$activeCount       =  $pdo->query("SELECT COUNT(*) FROM customer_records WHERE status='active'")->fetchColumn();
$holdCount         =  $pdo->query("SELECT COUNT(*) FROM customer_records WHERE status='hold'")->fetchColumn();
$returnedCount     = $pdo->query("SELECT COUNT(*) FROM customer_records WHERE status='returned'")->fetchColumn();
$cancelledCount    = $pdo->query("SELECT COUNT(*) FROM customer_records WHERE status='cancelled'")->fetchColumn();
$repossessedCount  = 0;
 


?>

<style>
/* =====================================================
   PAGE WRAPPER
===================================================== */

.container-fluid {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, sans-serif;
}

/* =====================================================
   PAGE HEADING
===================================================== */

.page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    background: linear-gradient(120deg, #1e3a8a 0%, #2563eb 45%, #3b82f6 100%);
    border-radius: 18px;
    padding: 22px 26px;
    margin-bottom: 20px;
    box-shadow: 0 12px 30px rgba(37, 99, 235, .22);
    position: relative;
    overflow: hidden;
}

.page-heading::before {
    content: "";
    position: absolute;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, .08);
    border-radius: 50%;
    top: -90px;
    right: -60px;
}

.page-heading-copy {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 2;
}

.page-icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: rgba(255, 255, 255, .16);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, .25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    flex-shrink: 0;
}

.page-heading .eyebrow {
    color: rgba(255, 255, 255, .8);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
}

.page-heading h1 {
    color: #fff;
    font-weight: 800;
}

.page-heading .text-muted {
    color: rgba(255, 255, 255, .85) !important;
}

.page-heading .btn {
    position: relative;
    z-index: 2;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
    white-space: nowrap;
}

.page-heading .col-md-2 {
    position: relative;
    z-index: 2;
    width: auto;
}


/* =====================================================
   QUICK STAT STRIP
===================================================== */

.mini-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

.mini-stat {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: 0 5px 16px rgba(15, 23, 42, .05);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform .2s ease, box-shadow .2s ease;
}

.mini-stat:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
}

.mini-stat .mini-icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}

.mini-stat .mini-value {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.1;
}

.mini-stat .mini-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}

.mi-total .mini-icon {
    background: #dbeafe;
    color: #2563eb;
}

.mi-active .mini-icon {
    background: #dcfce7;
    color: #059669;
}

.mi-hold .mini-icon {
    background: #fef3c7;
    color: #d97706;
}

.mi-due .mini-icon {
    background: #fee2e2;
    color: #dc2626;
}

.mi-ret .mini-icon {
    background: #cffafe;
    color: #0891b2;
}


/* =====================================================
   PANEL / TABLE CARD
===================================================== */

.panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(15, 23, 42, .06);
}

.panel-header {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.panel-header .section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.table-search {
    max-width: 300px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 8px 14px;
}

.table-search:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
}


/* =====================================================
   TABLE STYLE
===================================================== */

.table-responsive {
    max-height: 640px;
    overflow-y: auto;
}

#dataTable {
    margin: 0;
}

#dataTable thead th {
    position: sticky;
    top: 0;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 14px;
    white-space: nowrap;
    z-index: 1;
}

#dataTable tbody td {
    padding: 12px 14px;
    font-size: 13.5px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

#dataTable tbody tr {
    transition: background .15s ease;
}

#dataTable tbody tr:hover {
    background: #f8fafc;
}

#dataTable .badge {
    font-size: 11.5px;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 20px;
}

#dataTable .btn-sm {
    border-radius: 8px;
    padding: 4px 9px;
    margin-left: 3px;
}
</style>
<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Page Heading -->
    <div class="page-heading">
        <div class="page-heading-copy">
            <span class="page-icon"><i class="bi bi-car-front"></i></span>
            <div>
                <p class="eyebrow mb-1">রেকর্ডস</p>
                <h1 class="h3 mb-1">কাস্টমার রেকর্ডস</h1>
                <p class="text-muted mb-0">সকল লেনদেন ও কাস্টমার তথ্য</p>
            </div>
        </div>

        <a href="index.php?page=car/add" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি
        </a>
    </div>
    <!-- Quick Stat Strip -->
    <div class="mini-stat-grid">

        <div class="mini-stat mi-total">
            <div class="mini-icon"><i class="bi bi-list-ul"></i></div>
            <div>
                <div class="mini-value"><?= bn_number($totalRecords) ?></div>
                <div class="mini-label">মোট রেকর্ড</div>
            </div>
        </div>

        <div class="mini-stat mi-active">
            <div class="mini-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="mini-value"><?= bn_number($activeCount) ?></div>
                <div class="mini-label">চলমান</div>
            </div>
        </div>

        <div class="mini-stat mi-hold">
            <div class="mini-icon"><i class="bi bi-pause-circle"></i></div>
            <div>
                <div class="mini-value"><?= bn_number($holdCount) ?></div>
                <div class="mini-label">গাড়ি ধরে রাখা</div>
            </div>
        </div>

        <div class="mini-stat mi-due">
            <div class="mini-icon"><i class="bi bi-exclamation-circle"></i></div>
            <div>
                <div class="mini-value"><?= bn_number($cancelledCount) ?></div>
                <div class="mini-label">চুক্তি বাতিল</div>
            </div>
        </div>

        <div class="mini-stat mi-ret">
            <div class="mini-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div>
                <div class="mini-value"><?= bn_number($returnedCount) ?></div>
                <div class="mini-label">গাড়ি ফেরত</div>
            </div>
        </div>

    </div>
    <!-- Table Section -->
    <section class="panel">
        <div class="panel-header">
            <h2 class="h5 mb-1 section-title">
                <i class="bi bi-car-front"></i> সকল রেকর্ড
            </h2>

            <input class="form-control form-control-sm table-search" type="search" id="searchInput"
                placeholder="🔍 নাম, ফোন বা গাড়ির নম্বর সার্চ করুন...">
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" id="dataTable">
                <thead>
                    <tr>
                        <th style="min-width:40px">#</th>
                        <th>তারিখ</th>
                        <th>কাস্টমার</th>
                        <th>ফোন</th>
                        <th>গাড়ি</th>
                        <th>মোট সময়</th>
                        <th>বাকি সময়</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    <?php
                $i=0;
                foreach($records as $row):

                    $startDate      = $row['kisti_start_date'];
                    $monthlyAmount  = $row['monthly_kisti'] ?? 0;
                    $totalPaid      = $row['paid_amount'] ?? 0;
                    $totalPlanMonth = $row['total_kisti'] ?? 0;

                    $today = date('Y-m-d');

                    $start = new DateTime($startDate);
                    $end   = new DateTime($today);

                    $diff = $start->diff($end);

                    $passedMonths = ($diff->y * 12) + $diff->m;
                    $passedDays   = $diff->d;

                    $passedTotalMonths = $passedMonths + ($passedDays / 30);

                    // মোট সময়
                    $totalDuration = $totalPlanMonth . " মাস";

                    // বাকি সময়
                    $remainingMonths = $totalPlanMonth - $passedTotalMonths;
                    $remainingMonths = max(0, $remainingMonths);

                    $remMonths = floor($remainingMonths);
                    $remDays   = round(($remainingMonths - $remMonths) * 30);


                    
                    $remainingDuration = $remMonths . " মাস " . $remDays . " দিন";
                ?>
                    <tr>
                        <td><?= ++$i; ?></td>

                        <td><?= bn_number(date('d-m-Y', strtotime($startDate))) ?></td>

                        <td><?= htmlspecialchars($row['customer_name']) ?></td>

                        <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>

                        <td><?= htmlspecialchars($row['car_number'] ?? '-') ?></td>

                        <td class="text-success fw-semibold">
                            <?= bn_number($totalDuration) ?>
                        </td>

                        <td class="text-danger fw-semibold">

                            <?php if (($row['status'] ?? '') === 'cancelled'): ?>

                            <span class="text-secondary">
                                - -- -- -
                            </span>

                            <?php elseif ($remainingMonths <= 0): ?>

                            <span class="badge bg-danger">
                                সময় শেষ
                            </span>

                            <?php else: ?>

                            <span class="text-danger fw-semibold">
                                <?= bn_number($remainingDuration) ?>
                            </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?php
$status = $row['status'] ?? '';

$statusData = [
    'active' => [
        'text' => 'চলমান',
        'class' => 'success'
    ],
    'hold' => [
        'text' => 'গাড়ি ধরে রাখা',
        'class' => 'warning'
    ],
    'default' => [
        'text' => 'কিস্তি বকেয়া',
        'class' => 'danger'
    ],
    'returned' => [
        'text' => 'গাড়ি ফেরত',
        'class' => 'info'
    ],
    'completed' => [
        'text' => 'কিস্তি সম্পন্ন',
        'class' => 'primary'
    ],
    'cancelled' => [
        'text' => 'চুক্তি বাতিল',
        'class' => 'secondary'
    ],
    'repossessed' => [
        'text' => 'গাড়ি পুনরুদ্ধার',
        'class' => 'danger'
    ]
];

$data = $statusData[$status] ?? [
    'text' => $status ?: 'অজানা',
    'class' => 'secondary'
];
?>

                            <span class="badge bg-<?= $data['class'] ?>">
                                <?= htmlspecialchars($data['text']) ?>
                            </span>

                        </td>

                        <td class="text-end">
                            <a href="index.php?page=car/view&car_number=<?= urlencode($row['car_number'] ?? '') ?>"
                                class="btn btn-info btn-sm text-white" title="দেখুন">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="index.php?page=car/edit&id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm"
                                title="সম্পাদনা">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <a href="index.php?page=car/receipt&id=<?= (int)$row['id'] ?>"
                                class="btn btn-success btn-sm" title="রসিদ">
                                <i class="bi bi-receipt"></i>
                            </a>

                            <a href="index.php?page=car/delete&id=<?= (int)$row['id'] ?>" class="btn btn-danger btn-sm"
                                title="ডিলিট"
                                onclick="return confirm('আপনি কি নিশ্চিতভাবে এই গাড়িটি ডিলিট করতে চান?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </section>
</div>

<!-- Search + Default 10 Script -->
<script>
const rows = document.querySelectorAll("#tableBody tr");
const searchInput = document.getElementById("searchInput");

let searchTimer;

function showDefault() {
    rows.forEach((row, index) => {
        row.style.display = index < 30 ? "" : "none";
    });
}

searchInput.addEventListener("input", function() {

    clearTimeout(searchTimer);

    const value = this.value.toLowerCase().trim();

    searchTimer = setTimeout(() => {

        if (value === "") {
            showDefault();
            return;
        }

        rows.forEach(row => {

            const text = row.innerText.toLowerCase();

            row.style.display =
                text.includes(value) ? "" : "none";

        });

    }, 200);

});

showDefault();
</script>