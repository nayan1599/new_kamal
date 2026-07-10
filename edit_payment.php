<?php
// edit_payment.php
session_start();
require_once 'db.php';   // আপনার PDO কানেকশন ফাইল

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "আইডি পাওয়া যায়নি!";
    header("Location: kisti_payment_list.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM kisti_payments WHERE id = ?");
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

 
if (!$row) {
    $_SESSION['error'] = "এই আইডির কোনো রেকর্ড পাওয়া যায়নি!";
    header("Location: kisti_payment_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কিস্তি এডিট - <?= htmlspecialchars($row['customer_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .form-control, .form-select { border-radius: 8px; padding: 12px 15px; }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-header text-dark text-center">
                        <h3><i class="fas fa-edit me-2"></i>কিস্তি এডিট করুন</h3>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="update_payment.php">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">

                            <div class="row g-3">
                                <div class="col-12"><h5 class="text-primary mb-3">👤 গ্রাহকের তথ্য</h5></div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">গ্রাহকের নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" 
                                           value="<?= htmlspecialchars($row['customer_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_phone" class="form-control" 
                                           value="<?= htmlspecialchars($row['customer_phone']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">গাড়ির নাম্বার</label>
                                    <input type="text" name="car_number" class="form-control" 
                                           value="<?= htmlspecialchars($row['car_number'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">কিস্তি নম্বর <span class="text-danger">*</span></label>
                                    <input type="number" name="kisti_number" class="form-control" 
                                           value="<?= $row['kisti_number'] ?>" required>
                                </div>

                                <div class="col-12 mt-4"><h5 class="text-primary mb-3">💰 পেমেন্ট তথ্য</h5></div>

                                <div class="col-md-6">
                                    <label class="form-label">টাকার পরিমাণ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="amount" class="form-control" 
                                               value="<?= $row['amount'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">জরিমানা</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="fine_amount" class="form-control" 
                                               value="<?= $row['fine_amount'] ?? 0 ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">পেমেন্ট তারিখ <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" 
                                           value="<?= $row['payment_date'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পেমেন্ট মেথড</label>
                                    <select name="payment_method" id="payment_method" class="form-select">
                                        <option value="cash" <?= ($row['payment_method']=='cash') ? 'selected' : '' ?>>নগদ (Cash)</option>
                                        <option value="bkash" <?= ($row['payment_method']=='bkash') ? 'selected' : '' ?>>বিকাশ</option>
                                        <option value="nagad" <?= ($row['payment_method']=='nagad') ? 'selected' : '' ?>>নগদ</option>
                                        <option value="bank_transfer" <?= ($row['payment_method']=='bank_transfer') ? 'selected' : '' ?>>ব্যাংক ট্রান্সফার</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-3" id="transactionFields">
                                    <h6 class="text-muted">ট্রানজেকশন বিবরণ</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">ট্রানজেকশন আইডি</label>
                                            <input type="text" name="transaction_id" class="form-control" 
                                                   value="<?= htmlspecialchars($row['transaction_id'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ব্যাংকের নাম</label>
                                            <input type="text" name="bank_name" class="form-control" 
                                                   value="<?= htmlspecialchars($row['bank_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">চেক নম্বর</label>
                                            <input type="text" name="cheque_no" class="form-control" 
                                                   value="<?= htmlspecialchars($row['cheque_no'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">প্রাপক</label>
                                    <input type="text" name="received_by" class="form-control" 
                                           value="<?= htmlspecialchars($row['received_by'] ?? '') ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">নোট / মন্তব্য</label>
                                    <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($row['note'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-warning btn-lg">আপডেট করুন</button>
                                <a href="kisti_payment_list.php" class="btn btn-secondary btn-lg">বাতিল</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTransaction() {
            document.getElementById('transactionFields').style.display = 
                (document.getElementById('payment_method').value === 'bank_transfer') ? 'block' : 'none';
        }
        document.getElementById('payment_method').addEventListener('change', toggleTransaction);
        window.onload = toggleTransaction;
    </script>
</body>
</html>