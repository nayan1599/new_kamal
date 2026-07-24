<?php
 

// ================== FETCH TRANSACTIONS ==================
$stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 100");
$transactions = $stmt->fetchAll();

// ================== HEAD SUMMARY ==================
$summaryStmt = $pdo->query("
    SELECT 
        head_name,
        SUM(taka_in) as total_in,
        SUM(taka_out) as total_out
    FROM transactions
    GROUP BY head_name
");
$head_summaries = $summaryStmt->fetchAll();

 

?>

<div class="container-fluid p-4">

    <!-- ================= HEADER ================= -->
    <div class="page-heading">
        <div>
            <h1>📊 কাস্টমার একাউন্টিং</h1>
            <p class="text-muted">সকল লেনদেনের হিসাব</p>
        </div>

        <a href="index.php?page=accounting/add" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি
        </a>
    </div>



    <!-- ================= SEARCH ================= -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 সার্চ করুন (নাম / ফোন / হেড)...">
    </div>

    <!-- ================= TABLE ================= -->
    <div class="card">
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
                        <!-- Head Name -->
                        <td>
                            <strong><?= $row['head_name'] ?? 'N/A' ?></strong>
                        </td>

                        <!-- IN -->
                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['total_in'] ?? 0, 2)) ?>
                        </td>

                        <!-- OUT -->
                        <td class="text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($row['total_out'] ?? 0, 2)) ?>
                        </td>

                        <!-- Balance -->
                        <td>
                            <span class="badge <?= $balance >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                ৳ <?= bn_number(number_format($balance, 2)) ?>
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="text-end">
                            <a href="index.php?page=accounting/sammery&head_name=<?= $row['head_name'] ?? 'N/A' ?>" class="btn btn-sm btn-outline-secondary" >
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