<?php
 $stmt = $pdo->query("SELECT * FROM customer_records ORDER BY created_at DESC ");
$rows = $stmt->fetchAll();
 

?>

<div class="container-fluid mt-4">
    <h4 class="mb-3">📋 বকেয়া তালিকা</h4>


<div class="card shadow">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table">
                    <tr>
                        <th>গাড়ি নম্বর</th>
                        <th>গ্রাহকের নাম</th>
                        <th>মোবাইল</th>
                        <th>মোট টাকা</th>
                        <th>পরিশোধিত</th>
                        <th class="text-danger">বকেয়া</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (count($rows) > 0): ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['car_number']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['customer_phone']) ?></td>

                        <td>৳ <?= number_format($row['total_amount'], 2) ?></td>
                        <td class="text-success">৳ <?= number_format($row['paid_amount'], 2) ?></td>
                        <td class="text-danger fw-bold">৳ <?= number_format($row['due_amount'], 2) ?></td>

                        <td>
                            <a href="index.php?page=car/view&car_number=<?= urlencode($row['car_number']) ?>" 
                               class="btn btn-info btn-sm text-white">দেখুন</a>

                            <a href="index.php?page=payment/create&car_number=<?= urlencode($row['car_number']) ?>" 
                               class="btn btn-success btn-sm">কিস্তি নিন</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">কোন বকেয়া নেই ✅</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>
</div>

</div>
