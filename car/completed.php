<?php 

$stmt =$pdo->query("SELECT * FROM customer_records WHERE status = 'completed' ORDER BY created_at DESC ");
$records = $stmt->fetchAll();

?>
<div class="container-fluid p-4">

    <!-- ================= HEADER ================= -->
    <div class="page-heading">
        <div>
            <h1>📊 কাস্টমার সম্পন্ন</h1>
            <p class="text-muted">সকল লেনদেনের হিসাব</p>
        </div>

    </div>



    <!-- ================= SEARCH ================= -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 সার্চ করুন (নাম / ফোন / হেড)...">
    </div>

    <!-- ================= TABLE ================= -->
    <div class="card">
        <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:40px">#</th>
                        <th>তারিখ</th>
                        <th>কাস্টমার</th>
                        <th>ফোন</th>
                        <th>গাড়ি</th>
                        <th>পেপার নেওয়ার তারিখ </th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody>
                <tbody id="tableBody">
                    <?php
                $i=0;
                foreach($records as $row): ?>
                    <tr>
                        <td><?= ++$i; ?></td>

                        <td><?= bn_number(date('d-m-Y', strtotime($row['kisti_start_date']))) ?></td>

                        <td><?= htmlspecialchars($row['customer_name']) ?></td>

                        <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>

                        <td><?= htmlspecialchars($row['car_number'] ?? '-') ?></td>
                        <td>
                            <?= bn_number(date('d-m-Y h:i A', strtotime($row['updated_at']))) ?>
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
    </div>

</div>

<!-- ================= SEARCH SCRIPT ================= -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#dataTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
});
</script>