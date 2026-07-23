

<?php
 
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================== INPUT ==================
    $invoice_no = 'INV-' . date('Ymd') . '-' . rand(100, 999);

    $customer_name   = strtolower(trim($_POST['customer_name']));
    $customer_phone  = trim($_POST['customer_phone']);
    $customer_email  = trim($_POST['customer_email']);
    $nid             = trim($_POST['nid']);
    $address         = trim($_POST['address']);

    $car_name        = trim($_POST['car_name']);
    $car_number      = strtoupper(trim($_POST['car_number'])); // normalize
    $car_model       = trim($_POST['car_model']);
    $car_year        = $_POST['car_year'];

    $type            = $_POST['type'];
    $quantity        = (int)$_POST['quantity'];

    $total_price     = (float)$_POST['total_price'];
    $paid_amount     = (float)$_POST['paid_amount'];
    $discount_amount = (float)$_POST['discount_amount'];
    $fine_amount     = (float)$_POST['fine_amount'];

    $total_kisti     = (int)$_POST['total_kisti'];
    $monthly_kisti   = (float)$_POST['monthly_kisti'];
    $kisti_start_date= $_POST['kisti_start_date'];
    $next_due_date   = $_POST['next_due_date'];

    $note            = trim($_POST['note']);

    $errors = [];

    // ================== VALIDATION ==================
    if (empty($customer_name))  $errors[] = "কাস্টমারের নাম দিতে হবে";
    if (empty($customer_phone)) $errors[] = "ফোন নম্বর দিতে হবে";
    if (empty($total_price))    $errors[] = "মোট টাকা দিতে হবে";
    if (empty($car_number))     $errors[] = "গাড়ির নম্বর দিতে হবে";

    // ================== DUPLICATE CHECK ==================
    if (empty($errors)) {

        $check = $pdo->prepare("
            SELECT id FROM customer_records 
            WHERE car_number = ?
            AND customer_name = ?
            AND customer_phone = ?
        ");

        $check->execute([$car_number, $customer_name, $customer_phone]);

        if ($check->rowCount() > 0) {
            $errors[] = "❌ এই কাস্টমারের এই গাড়ির রেকর্ড আগে থেকেই আছে!";
        }
    }

    // ================== INSERT ==================
    if (empty($errors)) {
        try {

            // Invoice duplicate avoid
            $checkInv = $pdo->prepare("SELECT id FROM customer_records WHERE invoice_no = ?");
            $checkInv->execute([$invoice_no]);

            if ($checkInv->rowCount() > 0) {
                $invoice_no = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
            }

            $sql = "INSERT INTO customer_records 
                (invoice_no, customer_name, customer_phone, customer_email, nid, address, 
                 car_name, car_number, car_model, car_year, quantity, type, 
                 total_price, paid_amount, discount_amount, fine_amount, 
                 total_kisti, monthly_kisti, kisti_start_date, next_due_date, note, created_by) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $invoice_no, $customer_name, $customer_phone, $customer_email, $nid, $address,
                $car_name, $car_number, $car_model, $car_year, $quantity, $type,
                $total_price, $paid_amount, $discount_amount, $fine_amount,
                $total_kisti, $monthly_kisti, $kisti_start_date, $next_due_date, $note,
                $_SESSION['user_id'] ?? 1
            ]);

            $_SESSION['success'] = "✅ নতুন রেকর্ড সফলভাবে সংরক্ষিত হয়েছে! ইনভয়েস নং: " . $invoice_no;
            header("Location:index.php?page=car/index");
            exit();

        } catch(PDOException $e) {
            $errors[] = "❌ Database Error: " . $e->getMessage();
        }
    }

    // ================== ERROR SHOW ==================
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header("Location:index.php?page=car/add");
        exit();
    }
}
?>

