<?php 
$stmt = $pdo->query("SELECT * FROM rents ORDER BY created_at DESC LIMIT 100");
$rents = $stmt->fetchAll();
?>


<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Page Heading -->
    <div class="page-heading">
        <div class="page-heading-copy">
            <span class="page-icon"><i class="bi bi-car-front"></i></span>
            <div>
                <p class="eyebrow mb-1">রেকর্ডস</p>
                <h1 class="h3 mb-1">গাড়ি ভারা লিস্ট </h1>
                <p class="text-muted mb-0">সকল গাড়ি ভারা লিস্ট </p>
            </div>
        </div>

        <!-- report button  -->
        
        <a href="index.php?page=rent/collection" class="btn btn-success"> <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি </a>
    </div>



    <!-- সব রেকর্ড -->
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="h5 mb-1 section-title">
                    <i class="bi bi-car-front"></i>
                    <span>সকল রেকর্ড</span>
                </h2>
            </div>

            <div class="d-flex align-items-center gap-2">

                <input class="form-control form-control-sm table-search" type="search" id="searchInput"
                    placeholder="🔍 নাম, ফোন বা গাড়ির নম্বর সার্চ করুন..." aria-label="Search">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 border shadow-sm custom-table" id="dataTable">
    <thead class="text-center">
        <tr>
            <th>তারিখ</th>
            <th>কাস্টমার</th>
            <th>ফোন</th>
            <th>গাড়ি</th>
            <th class="text-end">মোট টাকা</th>
            <th>স্ট্যাটাস</th>
            <th>নোট</th>
            <th>একশন</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($rents as $row): ?>
        <tr>
            <td><?= bn_number(date('d-m-Y', strtotime($row['rent_date']))) ?></td>

            <td class="fw-semibold text-dark">
                <?= htmlspecialchars($row['customer_name']) ?>
            </td>

            <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>

            <td>
                <span class="badge bg-secondary">
                    <?= htmlspecialchars($row['car_number'] ?? '-') ?>
                </span>
            </td>

            <td class="text-end text-success fw-bold">
                ৳ <?= bn_number(number_format($row['rent_amount'] ?? 0, 2)) ?>
            </td>

            <td class="text-center">
                <?php 
                $status = strtolower($row['payment_status'] ?? '');
                if($status == 'paid'){
                    echo '<span class="badge bg-success">PAID</span>';
                } elseif($status == 'due'){
                    echo '<span class="badge bg-danger">DUE</span>';
                } else {
                    echo '<span class="badge bg-warning text-dark">PENDING</span>';
                }
                ?>
            </td>

            <td class="text-muted small">
                <?= htmlspecialchars($row['note'] ?? '-') ?>
            </td>

            <td class="text-center">
                 
<?php 
                $status = strtolower($row['payment_status'] ?? '');
                if($status == 'due'){?>

                <a href="index.php?page=rent/edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-pencil"></i>
                </a><?php } ?>
                

                 
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        </div>
    </section>
</div>

<!-- Search Script -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase().trim();
    let rows = document.querySelectorAll("#dataTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
});
</script>