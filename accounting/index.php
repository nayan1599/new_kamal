<?php
 
 
// সব রেকর্ড
$stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 100");
$transactions = $stmt->fetchAll();
 
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

        <!-- report button  -->
        
        <a href="index.php?page=accounting/add" class="btn btn-success"> <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি </a>
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
            <table class="table align-middle mb-0" id="dataTable">
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>IN</th>
                         <th>Out</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $row): ?>
                    <tr>
                        <td><?= bn_number (date('d-m-Y', strtotime($row['date']))) ?></td>
 
                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['taka_in'] ?? 0, 2)) ?>
                        </td>
                        <td class=" text-success fw-semibold">
                            ৳ <?= bn_number(number_format($row['taka_out'] ?? 0, 2)) ?>
                        </td>
                        <td class=" text-danger fw-semibold">
                            ৳ <?=  ($row['head_name'] ?? "") ?>
                        </td>

                        <td>
                                 <?= strtoupper($row['description'] ?? "") ?>
                         </td>

                        <td class="text-end">
    <a href="index.php?page=accounting/view&transactions_id=<?= urlencode($row['id'] ?? '') ?>"
        class="btn btn-info btn-sm text-white">দেখুন</a>
 
 
 

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