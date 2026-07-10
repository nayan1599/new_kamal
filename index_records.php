<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC");
$records = $stmt->fetchAll();

// Summary হিসাব
$total_records = count($records);
$total_paid = 0;
$total_due = 0;
$completed = 0;

foreach($records as $r){
    $total_paid += $r['paid_amount'];
    $total_due += $r['due_amount'];
    if($r['status'] == 'completed'){
        $completed++;
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>রেকর্ড ড্যাশবোর্ড</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background: #f4f6f9; }

.header {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: white;
    padding: 20px;
    text-align: center;
}

.card-box {
    border-radius: 12px;
    color: white;
    padding: 20px;
}

.search-box {
    max-width: 300px;
}
</style>
</head>

<body>

<div class="header">
    <h3>জাহিরুল এন্টারপ্রাইজ</h3>
    <p>কাস্টমার রেকর্ড ড্যাশবোর্ড</p>
</div>

<div class="container mt-4">

    <!-- 🔥 Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-box bg-primary">
                <h5>মোট রেকর্ড</h5>
                <h3><?= $total_records ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box bg-success">
                <h5>মোট পেইড</h5>
                <h3><?= number_format($total_paid,2) ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box bg-danger">
                <h5>মোট বাকি</h5>
                <h3><?= number_format($total_due,2) ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box bg-dark">
                <h5>Completed</h5>
                <h3><?= $completed ?></h3>
            </div>
        </div>
    </div>

    <!-- 🔍 Search + Add -->
    <div class="d-flex justify-content-between mb-3">
        <a href="add_record.php" class="btn btn-success">+ নতুন এন্ট্রি</a>

        <input type="text" id="searchInput" class="form-control search-box" placeholder="🔍 সার্চ করুন...">
    </div>

    <!-- 📋 Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0" id="dataTable">
                <thead class="table-dark text-center">
                    <tr>
                        <th>তারিখ</th>
                        <th>ইনভয়েস</th>
                        <th>নাম</th>
                        <th>ফোন</th>
                        <th>গাড়ি</th>
                        <th>মোট</th>
                        <th>পেইড</th>
                        <th>বাকি</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach($records as $row): ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                    <td><?= $row['invoice_no'] ?></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= $row['customer_phone'] ?></td>
                    <td><?= $row['car_name'] ?? '-' ?></td>

                    <td class="text-primary"><?= number_format($row['total_price'],2) ?></td>
                    <td class="text-success"><?= number_format($row['paid_amount'],2) ?></td>
                    <td class="text-danger"><?= number_format($row['due_amount'],2) ?></td>

                    <td class="text-center">
                        <span class="badge bg-<?= $row['status']=='completed' ? 'success':'warning' ?>">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>

                    <td class="text-center">
                        <a href="view_records.php?car_number=<?= $row['car_number'] ?>" 
                           class="btn btn-info btn-sm text-white">View</a>

                        <a href="edit_record.php?id=<?= $row['id'] ?>" 
                           class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<!-- 🔍 Search Script -->
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

</body>
</html>