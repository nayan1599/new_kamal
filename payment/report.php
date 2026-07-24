<?php
 
// ================= FETCH DATA =================
$stmt = $pdo->query("SELECT * FROM kisti_payments ORDER BY payment_date DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= SUMMARY =================
$total_amount = 0;
$total_paid = 0;

foreach($records as $r){
    $total_amount += $r['amount'] ?? 0;
    $total_paid += $r['paid'] ?? 0;
}

$total_due = $total_amount - $total_paid;

 
?>

 

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>📊 কিস্তি রিপোর্ট</h4>
            <small class="text-muted">সকল কিস্তি লেনদেন</small>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn btn-dark btn-sm">
                🖨️ প্রিন্ট
            </button>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট টাকা</h6>
                    <h4 class="text-primary">
                        ৳ <?= bn_number(number_format($total_amount,2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট পরিশোধ</h6>
                    <h4 class="text-success">
                        ৳ <?= bn_number(number_format($total_paid,2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট বকেয়া</h6>
                    <h4 class="text-danger">
                        ৳ <?= bn_number(number_format($total_due,2)) ?>
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <!-- SEARCH -->
    <div class="mb-3 no-print">
        <input type="text" id="searchInput" class="form-control"
        placeholder="🔍 নাম / মোবাইল / গাড়ি নম্বর সার্চ করুন...">
    </div>

    <!-- TABLE -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead class="table-dark">
                    <tr>
                        <th>তারিখ</th>
                        <th>নাম</th>
                        <th>মোবাইল</th>
                        <th>গাড়ি</th>
                        <th>মোট টাকা</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($records as $row): 
                        $due = ($row['amount'] ?? 0) - ($row['paid'] ?? 0);
                    ?>
                    <tr>
                        <td><?= bn_number(date('d-m-Y', strtotime($row['payment_date']))) ?></td>

                        <td><?= $row['customer_name'] ?></td>

                        <td><?= bn_number($row['mobile']) ?></td>

                        <td><?= $row['car_number'] ?></td>

                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($row['amount'],2)) ?>
                        </td>

                        <td class="text-success fw-semibold">
                            ৳ <?= bn_number(number_format($row['paid'],2)) ?>
                        </td>

                        <td class="text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($due,2)) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<!-- SEARCH SCRIPT -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#dataTable tbody tr");

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

 