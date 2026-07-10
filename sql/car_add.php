<?php
 
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $invoice_no      = 'INV-' . date('Ymd') . '-' . rand(100, 999); // অটো ইনভয়েস নাম্বার
    $customer_name   = trim($_POST['customer_name']);
    $customer_phone  = trim($_POST['customer_phone']);
    $customer_email  = trim($_POST['customer_email']);
    $nid             = trim($_POST['nid']);
    $address         = trim($_POST['address']);
    
    $car_name        = trim($_POST['car_name']);
    $car_number      = trim($_POST['car_number']);
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

    // Basic Validation
    if (empty($customer_name)) $errors[] = "কাস্টমারের নাম দিতে হবে";
    if (empty($customer_phone)) $errors[] = "ফোন নম্বর দিতে হবে";
    if (empty($total_price)) $errors[] = "মোট টাকা দিতে হবে";

    if (empty($errors)) {
        try {
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
                $_SESSION['user_id'] ?? 1   // লগইন ইউজারের আইডি
            ]);

            $_SESSION['success'] = "✅ নতুন রেকর্ড সফলভাবে সংরক্ষিত হয়েছে! ইনভয়েস নং: " . $invoice_no;
            header("Location: car/index");
            exit();

        } catch(PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>