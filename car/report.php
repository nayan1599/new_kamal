<?php
// সব রেকর্ড load (JS দিয়ে filter/limit হবে)
$stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC");
$records = $stmt->fetchAll();
?>

<div class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">📋 বকেয়া তালিকা</h4>
        <span class="badge bg-dark">মোট: <?= count($records) ?></span>
    </div>
    <!-- Page Heading -->
 


<div class="row">
    <div class="col-md-2 col-sm-12">
            <h2 class="h6 mb-0">
                <i class="bi bi-table"></i> সকল রেকর্ড
            </h2>
        </div>
  <div class="col-md-2 col-sm-12">
    
                <input type="search" id="searchInput"
                    class="form-control form-control-sm"
                    placeholder="🔍 সার্চ...">
  </div>
    <div class="col-md-2 col-sm-12">
           <input type="date" id="fromDate" class="form-control form-control-sm">
    </div>
      <div class="col-md-2 col-sm-12">
           <input type="date" id="toDate" class="form-control form-control-sm">
      </div>
        <div class="col-md-2 col-sm-12">
                


             
             

                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">সব স্ট্যাটাস</option>
                    <option value="running">চলমান</option>
                    <option value="completed">সম্পন্ন</option>
                </select>

             

            </div>
            
  <div class="col-md-2 col-sm-12">
    <div class="d-flex gap-2 flex-wrap">
       <button class="btn btn-primary btn-sm" onclick="applyFilter()">ফিল্টার</button>
                <button class="btn btn-secondary btn-sm" onclick="resetFilter()">রিসেট</button>
  </div>


        </div>
</div>







    <!-- Table Section -->
    <section class="panel mt-3">
       

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
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
                $i = 0;
                foreach($records as $row):

                    $startDate      = $row['kisti_start_date'];
                    $monthlyAmount  = $row['monthly_kisti'] ?? 0;
                    $totalPaid      = $row['paid_amount'] ?? 0;
                    $totalPlanMonth = $row['total_kisti'] ?? 0;

                    $today = date('Y-m-d');

                    $start = new DateTime($startDate);
                    $end   = new DateTime($today);
                    $diff  = $start->diff($end);

                    $passedMonths = ($diff->y * 12) + $diff->m;
                    $passedDays   = $diff->d;

                    $passedTotalMonths = $passedMonths + ($passedDays / 30);

                    // মোট সময়
                    $totalDuration = $totalPlanMonth . " মাস";

                    // বাকি সময়
                    $remainingMonths = max(0, $totalPlanMonth - $passedTotalMonths);
                    $remMonths = floor($remainingMonths);
                    $remDays   = round(($remainingMonths - $remMonths) * 30);
                    $remainingDuration = $remMonths . " মাস " . $remDays . " দিন";
                    $status = ($remainingMonths <= 0) ? 'completed' : 'running';
                ?>
                    <tr>
                        <td><?= ++$i ?></td>

                        <td data-date="<?= date('Y-m-d', strtotime($startDate)) ?>">
                            <?= bn_number(date('d-m-Y', strtotime($startDate))) ?>
                        </td>

                        <td><?= htmlspecialchars($row['customer_name']) ?></td>

                        <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>

                        <td><?= htmlspecialchars($row['car_number'] ?? '-') ?></td>

                        <td class="text-success fw-semibold">
                            <?= bn_number($totalDuration) ?>
                        </td>

                        <td class="text-danger fw-semibold">
                            <?= bn_number($remainingDuration) ?>
                        </td>

                        <td data-status="<?= $status ?>">
                            <span class="badge bg-<?= ($status == 'completed') ? 'success' : 'warning' ?>">
                                <?= ($status == 'completed') ? 'সম্পন্ন' : 'চলমান' ?>
                            </span>
                        </td>

                        <td class="text-end">
                            <a href="index.php?page=car/view&car_number=<?= urlencode($row['car_number']) ?>"
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

<!-- 🔥 FILTER SCRIPT -->
<script>
const rows = document.querySelectorAll("#tableBody tr");
const searchInput = document.getElementById("searchInput");

function showDefault() {
    rows.forEach((row, index) => {
        row.style.display = (index < 10) ? "" : "none";
    });
}

function applyFilter() {
    let search = searchInput.value.toLowerCase().trim();
    let from   = document.getElementById("fromDate").value;
    let to     = document.getElementById("toDate").value;
    let status = document.getElementById("statusFilter").value;

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        let date = row.querySelector("[data-date]").getAttribute("data-date");
        let rowStatus = row.querySelector("[data-status]").getAttribute("data-status");

        let matchSearch = text.includes(search);

        let matchDate = true;
        if (from && date < from) matchDate = false;
        if (to && date > to) matchDate = false;

        let matchStatus = true;
        if (status && rowStatus !== status) matchStatus = false;

        row.style.display = (matchSearch && matchDate && matchStatus) ? "" : "none";
    });
}

function resetFilter() {
    searchInput.value = "";
    document.getElementById("fromDate").value = "";
    document.getElementById("toDate").value = "";
    document.getElementById("statusFilter").value = "";
    showDefault();
}

// events
searchInput.addEventListener("keyup", applyFilter);
document.getElementById("fromDate").addEventListener("change", applyFilter);
document.getElementById("toDate").addEventListener("change", applyFilter);
document.getElementById("statusFilter").addEventListener("change", applyFilter);

// load
showDefault();
</script>