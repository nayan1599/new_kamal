<?php
require_once 'config/db.php'; // db connection

$id = $_GET['id'] ?? '';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// ডাটা লোড
$stmt = $pdo->prepare("SELECT * FROM customer_records WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    echo "Data not found!";
    exit;
}
?>

<div class="container-fluid px-3 px-lg-4 py-4">


<h3 class="mb-3">✏️ কাস্টমার এডিট করুন</h3>

<form method="POST" action="index.php?page=sql/car_update">

    <input type="hidden" name="id" value="<?= $data['id'] ?>">

    <!-- কাস্টমার -->
    <div class="row g-3">
        <div class="col-md-6">
            <label>নাম</label>
            <input type="text" name="customer_name" class="form-control"
                   value="<?= htmlspecialchars($data['customer_name']) ?>" required>
        </div>

        <div class="col-md-6">
            <label>ফোন</label>
            <input type="text" name="customer_phone" class="form-control"
                   value="<?= htmlspecialchars($data['customer_phone']) ?>" required>
        </div>

        <div class="col-md-6">
            <label>গাড়ির নম্বর</label>
            <input type="text" name="car_number" class="form-control"
                   value="<?= htmlspecialchars($data['car_number']) ?>">
        </div>

        <div class="col-md-6">
            <label>ধরন</label>
            <select name="type" class="form-select">
                <option value="service" <?= $data['type']=='service'?'selected':'' ?>>সার্ভিস</option>
                <option value="sale" <?= $data['type']=='sale'?'selected':'' ?>>বিক্রি</option>
                <option value="installment" <?= $data['type']=='installment'?'selected':'' ?>>কিস্তি</option>
                <option value="repair" <?= $data['type']=='repair'?'selected':'' ?>>মেরামত</option>
                <option value="parts" <?= $data['type']=='parts'?'selected':'' ?>>পার্টস</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>মোট টাকা</label>
            <input type="number" name="total_price" class="form-control"
                   value="<?= $data['total_price'] ?>">
        </div>

        <div class="col-md-6">
            <label>পেইড</label>
            <input type="number" name="paid_amount" class="form-control"
                   value="<?= $data['paid_amount'] ?>">
        </div>

        <div class="col-md-6">
            <label>মোট কিস্তি</label>
            <input type="number" name="total_kisti" class="form-control"
                   value="<?= $data['total_kisti'] ?>">
        </div>

        <div class="col-md-6">
            <label>মাসিক কিস্তি</label>
            <input type="number" name="monthly_kisti" class="form-control"
                   value="<?= $data['monthly_kisti'] ?>">
        </div>

        <div class="col-md-6">
            <label>শুরুর তারিখ</label>
            <input type="date" name="kisti_start_date" class="form-control"
                   value="<?= $data['kisti_start_date'] ?>">
        </div>

        <div class="col-12">
            <label>নোট</label>
            <textarea name="note" class="form-control"><?= htmlspecialchars($data['note']) ?></textarea>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-success">💾 আপডেট করুন</button>
        <a href="index.php?page=car/index" class="btn btn-secondary">⬅️ ফিরে যান</a>
    </div>

</form>


</div>
