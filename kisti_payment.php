<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কিস্তি আদায়</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            padding: 1.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 12px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .btn-submit {
            padding: 12px 40px;
            font-size: 1.1rem;
            border-radius: 8px;
        }

        #transactionFields {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow">
                    <div class="card-header text-white text-center">
                        <h3><i class="fas fa-hand-holding-dollar me-2"></i>কিস্তি আদায় ফর্ম</h3>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $_SESSION['success'];
                                unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="save_payment.php">
                            <div class="row g-3">
                                <!-- গ্রাহকের তথ্য -->
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">👤 গ্রাহকের তথ্য</h5>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">গ্রাহকের নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control"
                                        placeholder="পূর্ণ নাম লিখুন" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_phone" class="form-control"
                                        placeholder="০১XXXXXXXXX" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">গাড়ির নাম্বার</label>
                                    <input type="text" name="car_number" class="form-control"
                                        placeholder="ঢাকা মেট্রো-গ-১২৩৪">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">কিস্তি নম্বর <span class="text-danger">*</span></label>
                                    <input type="number" name="kisti_number" class="form-control"
                                        placeholder="১, ২, ৩..." required>
                                </div>

                                <!-- পেমেন্ট তথ্য -->
                                <div class="col-12 mt-4">
                                    <h5 class="text-primary mb-3">💰 পেমেন্ট তথ্য</h5>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">টাকার পরিমাণ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="amount" class="form-control"
                                            placeholder="০.০০" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">জরিমানা</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="fine_amount" class="form-control"
                                            placeholder="০.০০">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">পেমেন্ট তারিখ <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-select" required>
                                        <option value="">পেমেন্ট মেথড নির্বাচন করুন</option>
                                        <option value="cash">নগদ (Cash)</option>
                                        <option value="bkash">বিকাশ (Bkash)</option>
                                        <option value="nagad">নগদ (Nagad)</option>
                                        <option value="bank_transfer">ব্যাংক ট্রান্সফার</option>
                                    </select>
                                </div>
                                <!-- payment_type  -->
                                <div class="col-md-6">
                                    <label class="form-label">পেমেন্ট টাইপ <span class="text-danger">*</span></label>
                                    <select name="payment_type" class="form-select" required>
                                        <option value="kisti">কিস্তি</option>
                                        <option value="vara">অগ্রিম</option>
                                        <option value="other">অন্যান্য</option>
                                    </select>
                                </div>


                                <!-- ট্রানজেকশন তথ্য (শুধু ব্যাংক ট্রান্সফারের জন্য) -->
                                <div class="col-12 mt-3" id="transactionFields">
                                    <h6 class="text-muted">ট্রানজেকশন বিবরণ</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">ট্রানজেকশন আইডি</label>
                                            <input type="text" name="transaction_id" class="form-control"
                                                placeholder="TRX123456789">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ব্যাংকের নাম</label>
                                            <input type="text" name="bank_name" class="form-control"
                                                placeholder="যেমন: ইসলামী ব্যাংক">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">চেক নম্বর</label>
                                            <input type="text" name="cheque_no" class="form-control"
                                                placeholder="চেক নম্বর">
                                        </div>
                                    </div>
                                </div>

                                <!-- status  -->
                                <div class="col-md-6">
                                    <label class="form-label">স্ট্যাটাস</label>
                                    <select name="status" class="form-select">
                                        <option value="paid" selected>পেইড</option>
                                        <option value="pending">পেন্ডিং</option>
                                        <option value="failed">ফেইল্ড</option>
                                    </select>
                                </div>
                                <!-- প্রাপক -->
                                <div class="col-md-6">
                                    <label class="form-label">প্রাপক</label>
                                    <input type="text" name="received_by" class="form-control"
                                        placeholder="প্রাপকের নাম">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">নোট / মন্তব্য</label>
                                    <textarea name="note" class="form-control" rows="3"
                                        placeholder="অতিরিক্ত তথ্য বা মন্তব্য..."></textarea>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-primary btn-lg btn-submit">
                                    <i class="fas fa-save me-2"></i>সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    
</body>

</html>