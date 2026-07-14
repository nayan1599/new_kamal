<?php

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'sql/save_payment.php';
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="fas fa-hand-holding-dollar me-2"></i>কিস্তি আদায় ফর্ম</h3>
                </div>

                <div class="card-body p-4">
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $err): ?>
                                    <li><?= $err ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $_SESSION['success']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form class="needs-validation" novalidate method="POST" action="">
                        
                        <div class="row g-3">
                            <!-- গ্রাহকের তথ্য -->
                            <div class="col-12">
                                <h5 class="text-primary mb-3">👤 গ্রাহকের তথ্য</h5>
                            </div>

                            <!-- <div class="col-md-6">
                                <label class="form-label">গ্রাহকের নাম <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" placeholder="পূর্ণ নাম লিখুন" required>
                            </div> -->
                            <!-- <div class="col-md-6">
                                <label class="form-label">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="০১XXXXXXXXX" required>
                            </div> -->

                            <div class="col-md-6">
                                <label class="form-label">গাড়ির নাম্বার</label>
                                <input type="text" name="car_number" class="form-control" placeholder="ঢাকা মেট্রো-গ-১২৩৪">
                            </div>

                            <!-- কিস্তি নম্বর (শুধু কিস্তি হলে দেখাবে) -->
                            <div class="col-md-6" id="kistiNumberField">
                                <label class="form-label">কিস্তি নম্বর <span class="text-danger">*</span></label>
                                <input type="number" name="kisti_number" value="1" class="form-control" placeholder="১, ২, ৩..." required>
                            </div>

                

                            <div class="col-md-6">
                                <label class="form-label">টাকার পরিমাণ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="০.০০" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">জরিমানা</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="fine_amount" class="form-control" value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">পেমেন্ট তারিখ <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="cash">নগদ (Cash)</option>
                                    <option value="bkash">বিকাশ</option>
                                    <option value="nagad">নগদ</option>
                                    <option value="bank_transfer">ব্যাংক ট্রান্সফার</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">পেমেন্ট টাইপ <span class="text-danger">*</span></label>
                                <select name="payment_type" id="payment_type" class="form-select" required>
                                    <option value="kisti">কিস্তি</option>
                                    <option value="vara">অগ্রিম</option>
                                    <option value="other">অন্যান্য</option>
                                </select>
                            </div>

                            <!-- ট্রানজেকশন ফিল্ড -->
                            <div class="col-12 mt-3" id="transactionFields" style="display:none;">
                                <h6 class="text-muted">ট্রানজেকশন বিবরণ</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label>ট্রানজেকশন আইডি</label>
                                        <input type="text" name="transaction_id" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>ব্যাংকের নাম</label>
                                        <input type="text" name="bank_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>চেক নম্বর</label>
                                        <input type="text" name="cheque_no" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">স্ট্যাটাস</label>
                                <select name="status" class="form-select">
                                    <option value="paid" selected>পেইড</option>
                                    <option value="pending">পেন্ডিং</option>
                                    <option value="failed">ফেইল্ড</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">প্রাপক</label>
                                <input type="text" name="received_by" class="form-control" value="জহিরুল">
                            </div>

                            <div class="col-12">
                                <label class="form-label">নোট / মন্তব্য</label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>সংরক্ষণ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// পেমেন্ট টাইপ অনুযায়ী কিস্তি নম্বর শো/হাইড
document.getElementById('payment_type').addEventListener('change', function () {
    const kistiField = document.getElementById('kistiNumberField');
    if (this.value === 'kisti') {
        kistiField.style.display = 'block';
        kistiField.querySelector('input').required = true;
    } else {
        kistiField.style.display = 'none';
        kistiField.querySelector('input').required = false;
    }
});

// ট্রানজেকশন ফিল্ড শো/হাইড (আগের মতো)
document.getElementById('payment_method').addEventListener('change', function () {
    const fields = document.getElementById('transactionFields');
    fields.style.display = (this.value === 'bank_transfer') ? 'block' : 'none';
});

// প্রথমবার লোডে কিস্তি সিলেক্টেড থাকলে দেখাবে
window.onload = function() {
    const paymentType = document.getElementById('payment_type');
    const kistiField = document.getElementById('kistiNumberField');
    if (paymentType.value === 'kisti') {
        kistiField.style.display = 'block';
    } else {
        kistiField.style.display = 'none';
    }
};
</script>