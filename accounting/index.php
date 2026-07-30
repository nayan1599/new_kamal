<?php


// ================== FETCH TRANSACTIONS ==================
$stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 100");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================== HEAD SUMMARY ==================
$summaryStmt = $pdo->query("
    SELECT 
        head_name,
        SUM(taka_in) as total_in,
        SUM(taka_out) as total_out
    FROM transactions
    GROUP BY head_name
");
$head_summaries = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);

// ================== GRAND TOTAL ==================
$grand_in = 0;
$grand_out = 0;

foreach ($head_summaries as $row) {
    $grand_in += $row['total_in'] ?? 0;
    $grand_out += $row['total_out'] ?? 0;
}

$grand_balance = $grand_in - $grand_out;


?>



<div class="container-fluid p-4">

    <!-- ================= HEADER ================= -->
    <div class="page-heading">
        <div>
            <h3>📊 কাস্টমার একাউন্টিং</h3>
            <small class="text-muted">সকল লেনদেনের হিসাব</small>
        </div>

        <a href="index.php?page=accounting/add" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি
        </a>
    </div>


    <!-- ================= GRAND SUMMARY ================= -->
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h6>মোট জমা (IN)</h6>
                    <h4>৳ <?= bn_number(number_format($grand_in, 2)) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <h6>মোট খরচ (OUT)</h6>
                    <h4>৳ <?= bn_number(number_format($grand_out, 2)) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card <?= $grand_balance >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white shadow">
                <div class="card-body">
                    <h6>মোট ব্যালেন্স</h6>
                    <h4>৳ <?= bn_number(number_format($grand_balance, 2)) ?></h4>
                </div>
            </div>
        </div>

    </div>


    <!-- ================= SEARCH ================= -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 সার্চ করুন (হেড)...">
    </div>


    <!-- ================= TABLE ================= -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">

                <thead class="table-dark text-white">
                    <tr>
                        <th>হেড</th>
                        <th>জমা (IN)</th>
                        <th>খরচ (OUT)</th>
                        <th>ব্যালেন্স</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach($head_summaries as $row): 
                    $balance = ($row['total_in'] ?? 0) - ($row['total_out'] ?? 0);
                ?>
                    <tr>
                        <td><strong><?= $row['head_name'] ?? 'N/A' ?></strong></td>

                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['total_in'] ?? 0, 2)) ?>
                        </td>

                        <td class="text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($row['total_out'] ?? 0, 2)) ?>
                        </td>

                        <td>
                            <span class="badge <?= $balance >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                ৳ <?= bn_number(number_format($balance, 2)) ?>
                            </span>
                        </td>

                        <td class="text-end">
                            <a href="index.php?page=accounting/sammery&head_name=<?= urlencode($row['head_name']) ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                Summary
                            </a>
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

