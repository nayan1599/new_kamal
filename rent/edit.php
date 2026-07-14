<?php
 

$id = $_GET['id'] ?? '';

if (!$id) {
    die("Invalid ID");
}

// data load
$stmt = $pdo->prepare("SELECT * FROM rents WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    die("Data not found");
}
?>

<div class="container mt-4">
    <h4 class="mb-3">✏️ ভাড়া Edit</h4>

    <form method="POST" action="index.php?page=sql/rent_update">
        
        <input type="hidden" name="id" value="<?= $row['id'] ?>">

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">গাড়ি নাম্বার</label>
                <input type="text" name="car_number" class="form-control"
                       value="<?= htmlspecialchars($row['car_number']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">কাস্টমার নাম</label>
                <input type="text" name="customer_name" class="form-control"
                       value="<?= htmlspecialchars($row['customer_name']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">ফোন</label>
                <input type="text" name="customer_phone" class="form-control"
                       value="<?= htmlspecialchars($row['customer_phone']) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">মোট ভাড়া</label>
                <input type="number" step="0.01" name="rent_amount" class="form-control"
                       value="<?= $row['rent_amount'] ?>" required>
            </div>

             
 

            <div class="col-md-3">
                <label class="form-label">তারিখ</label>
                <input type="date" name="rent_date" class="form-control"
                       value="<?= $row['rent_date'] ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">স্ট্যাটাস</label>
                <select name="payment_status" class="form-select">
                    <option value="paid" <?= $row['payment_status']=='paid'?'selected':'' ?>>PAID</option>
                    <option value="due" <?= $row['payment_status']=='due'?'selected':'' ?>>DUE</option>
                    <option value="pending" <?= $row['payment_status']=='pending'?'selected':'' ?>>PENDING</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">পেমেন্ট মেথড</label>
                <select name="payment_method" class="form-select">
                    <option value="cash" <?= $row['payment_method']=='cash'?'selected':'' ?>>Cash</option>
                    <option value="bkash" <?= $row['payment_method']=='bkash'?'selected':'' ?>>bKash</option>
                    <option value="nagad" <?= $row['payment_method']=='nagad'?'selected':'' ?>>Nagad</option>
                    <option value="bank" <?= $row['payment_method']=='bank'?'selected':'' ?>>Bank</option>
                </select>
            </div>
          
            <div class="col-md-12">
                <label class="form-label">নোট</label>
                <textarea name="note" class="form-control"><?= htmlspecialchars($row['note']) ?></textarea>
            </div>

        </div>

        <button class="btn btn-success mt-3">💾 Update</button>
        <a href="index.php?page=rent/index" class="btn btn-secondary mt-3">⬅ Back</a>

    </form>
</div>