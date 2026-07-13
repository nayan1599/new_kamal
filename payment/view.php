<?php
$id = $_GET['id'] ?? '';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// kisti data
$stmt = $pdo->prepare("SELECT * FROM kisti_payments WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    echo "Data not found!";
    exit;
}

// customer data (car_number দিয়ে)
$stmt = $pdo->prepare("SELECT * FROM customer_records WHERE car_number = ? LIMIT 1");
$stmt->execute([$row['car_number']]);
$customer = $stmt->fetch();
?>

<div class="container-fluid py-4" >
<div class="text-end mb-3">
     <a href="index.php?page=payment/index" class="btn btn-info">Back</a>
 <button onclick="printDiv('receiptArea')" class="btn btn-success">🖨️ Print</button>
</div>
 <div id="receiptArea">
    <h1 class="title text-center font-weight-bold">জাহিরুল এন্টারপ্রাইজ</h1>
    <p class="text-center mb-1">প্রোঃ মোঃ জহিরুল ইসলাম (রাসেল)</p>
    <p class="text-center small">রূপগঞ্জ, নারায়ণগঞ্জ</p>

    <div class="line"></div>

    <h5 class="text-center">মানি রিসিট</h5>

    <div class="row mt-3">
        <div class="col-6">
            <strong>রিসিট নং:</strong> <?= $row['id'] ?>
        </div>
        <div class="col-6 text-end">
            <strong>তারিখ:</strong> <?= date('d/m/Y', strtotime($row['payment_date'])) ?>
        </div>
    </div>

    <div class="mt-3">
        <p><strong>গ্রাহকের নাম:</strong> <?= $customer['customer_name'] ?? 'N/A' ?></p>
        <p><strong>মোবাইল:</strong> <?= $customer['customer_phone'] ?? 'N/A' ?></p>
        <p><strong>কিস্তি নং:</strong> <?= $row['kisti_number'] ?></p>
    </div>

    <div class="line"></div>

    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>বিবরণ</th>
                <th>টাকা</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>মূল কিস্তি</td>
                <td>৳ <?= number_format($row['amount'], 2) ?></td>
            </tr>
            <tr>
                <td>জরিমানা</td>
                <td>৳ <?= number_format($row['fine_amount'], 2) ?></td>
            </tr>
            <tr>
                <th>মোট গ্রহণ</th>
                <th>৳ <?= number_format($row['total_received'], 2) ?></th>
            </tr>
        </tbody>
    </table>

    <div class="mt-3">
        <p><strong>পেমেন্ট মেথড:</strong> <?= $row['payment_method'] ?></p>
        <?php if (!empty($row['transaction_id'])): ?>
            <p><strong>ট্রানজেকশন আইডি:</strong> <?= $row['transaction_id'] ?></p>
        <?php endif; ?>
    </div>

    <div class="row mt-5">
        <div class="col-6 text-center">
            -------------------------<br>
            গ্রাহকের স্বাক্ষর
        </div>
        <div class="col-6 text-center">
            -------------------------<br>
            কর্তৃপক্ষ
        </div>
    </div>

    </div>

</div>
 


 


