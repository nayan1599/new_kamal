<?php
 
$id = $_GET['id'] ?? '';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// ডাটা লোড
$stmt = $pdo->prepare("SELECT * FROM kisti_payments WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    echo "Data not found!";
    exit;
}
?>

<div class="container mt-5">
 
<!-- ✅ Success / Error Message -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header text-center">
                <h3>কিস্তি এডিট করুন</h3>
            </div>

            <div class="card-body">
                <form method="POST" action="index.php?page=sql/update_payment">

                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
                        <label>গাড়ির নাম্বার</label>
                        <input type="text" name="car_number" class="form-control"
                               value="<?= htmlspecialchars($row['car_number'] ?? '') ?>">
                    </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
                        <label>কিস্তি নম্বর</label>
                        <input type="number" name="kisti_number" class="form-control"
                               value="<?= $row['kisti_number'] ?>" required>
                    </div>
    </div>
    <div class="col-md-6">
         <div class="mb-3">
                        <label>টাকা</label>
                        <input type="number" step="0.01" name="amount" class="form-control"
                               value="<?= $row['amount'] ?>" required>
                    </div>
    </div>
    <div class="col-md-6">
         <div class="mb-3">
                        <label>জরিমানা</label>
                        <input type="number" step="0.01" name="fine_amount" class="form-control"
                               value="<?= $row['fine_amount'] ?? 0 ?>">
                    </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
                        <label>তারিখ</label>
                        <input type="date" name="payment_date" class="form-control"
                               value="<?= $row['payment_date'] ?>" required>
                    </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
                        <label>পেমেন্ট মেথড</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash" <?= ($row['payment_method']=='cash')?'selected':'' ?>>Cash</option>
                            <option value="bkash" <?= ($row['payment_method']=='bkash')?'selected':'' ?>>Bkash</option>
                            <option value="nagad" <?= ($row['payment_method']=='nagad')?'selected':'' ?>>Nagad</option>
                            <option value="bank_transfer" <?= ($row['payment_method']=='bank_transfer')?'selected':'' ?>>Bank</option>
                        </select>
                    </div>
                </div>
    <div class="col-md-6">
        <div class="mb-3">
                        <label>প্রাপক</label>
                        <input type="text" name="received_by" class="form-control"
                               value="<?= $row['received_by'] ?? '' ?>">
                    </div>
    </div>
    <div class="col-md-12">
         <div class="mb-3">
                        <label>নোট</label>
                        <textarea name="note" class="form-control"><?= $row['note'] ?? '' ?></textarea>
                    </div>
    </div>
</div>

                    

                    

                   

                   

                    

                    

                    

                   
<div class="text-end">
                    <button class="btn btn-warning">আপডেট করুন</button>
</div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
