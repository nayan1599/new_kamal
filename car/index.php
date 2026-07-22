<?php
 
 
// সব রেকর্ড
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

        <!-- report button  -->
        
        <a href="index.php?page=car/add" class="btn btn-success"> <i class="bi bi-plus-circle"></i> নতুন এন্ট্রি </a>
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
                        <th>কাস্টমার</th>
                        <th>ফোন</th>
                        <th>গাড়ি</th>
                        <th class="text-end">মোট টাকা</th>
                        <th class="text-end">পেইড</th>
                        <th class="text-end">বাকি</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($records as $row): ?>
                    <tr>
                        <td><?= bn_number (date('d-m-Y', strtotime($row['kisti_start_date']))) ?></td>

                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= bn_number(htmlspecialchars($row['customer_phone'])) ?></td>
                        <td><?= htmlspecialchars($row['car_number'] ?? '-') ?></td>

                        <td class="text-end text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['total_price'] ?? 0, 2)) ?>
                        </td>
                        <td class="text-end text-success fw-semibold">
                            ৳ <?= bn_number(number_format($row['paid_amount'] ?? 0, 2)) ?>
                        </td>
                        <td class="text-end text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($row['due_amount'] ?? 0, 2)) ?>
                        </td>

                        <td>
                            <span class="badge bg-<?= ($row['status'] ?? '') == 'completed' ? 'success' : 'warning' ?>">
                                <?= strtoupper($row['status'] ?? 'Pending') ?>
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