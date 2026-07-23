<?php
// সব রেকর্ড (সব load হবে, কিন্তু JS দিয়ে 10টা দেখাবো)
$stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC");
$records = $stmt->fetchAll();
?>

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

    <!-- Table Section -->
    <section class="panel">
        <div class="panel-header">
            <h2 class="h5 mb-1 section-title">
                <i class="bi bi-car-front"></i> সকল রেকর্ড
            </h2>

            <input class="form-control form-control-sm table-search" type="search"
                id="searchInput"
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
                            <?= bn_number($remainingDuration) ?>
                        </td>

                        <td>
                            <span class="badge bg-<?= ($remainingMonths <= 0) ? 'success' : 'warning' ?>">
                                <?= ($remainingMonths <= 0) ? 'সম্পন্ন' : 'চলমান' ?>
                            </span>
                        </td>

                        <td class="text-end">
                            <a href="index.php?page=car/view&car_number=<?= urlencode($row['car_number'] ?? '') ?>"
                                class="btn btn-info btn-sm text-white">দেখুন</a>

                            <a href="index.php?page=car/edit&id=<?= $row['id'] ?>"
                                class="btn btn-warning btn-sm">সম্পাদনা</a>

                            <a href="index.php?page=car/receipt&id=<?= $row['id'] ?>"
                                class="btn btn-success btn-sm">রসিদ</a>
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

// 👉 default: last 10 show
function showDefault() {
    rows.forEach((row, index) => {
        row.style.display = (index < 30) ? "" : "none";
    });
}

// 👉 search
searchInput.addEventListener("keyup", function () {
    let value = this.value.toLowerCase().trim();

    if (value === "") {
        showDefault();
        return;
    }

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
});

// 👉 page load
showDefault();
</script>