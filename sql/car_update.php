<?php
 
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ID অবশ্যই লাগবে
    $id = $_POST['id'] ?? '';

    if (!$id) {
        die("Invalid ID");
    }

    // ইনপুট ডাটা
    $customer_name     = trim($_POST['customer_name']);
    $customer_phone    = trim($_POST['customer_phone']);
    $car_number        = trim($_POST['car_number']);
    $type              = $_POST['type'] ?? '';
    $total_price       = (float)($_POST['total_price'] ?? 0);
    $paid_amount       = (float)($_POST['paid_amount'] ?? 0);
    $total_kisti       = (int)($_POST['total_kisti'] ?? 0);
    $monthly_kisti     = (float)($_POST['monthly_kisti'] ?? 0);
    $kisti_start_date  = $_POST['kisti_start_date'] ?? null;
    $note              = trim($_POST['note'] ?? '');

    try {

        $stmt = $pdo->prepare("
            UPDATE customer_records SET
                customer_name = ?,
                customer_phone = ?,
                car_number = ?,
                type = ?,
                total_price = ?,
                paid_amount = ?,
                total_kisti = ?,
                monthly_kisti = ?,
                kisti_start_date = ?,
                note = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $customer_name,
            $customer_phone,
            $car_number,
            $type,
            $total_price,
            $paid_amount,
            $total_kisti,
            $monthly_kisti,
            $kisti_start_date,
            $note,
            $id
        ]);

        // সফল হলে redirect
      $_SESSION['success'] = "✅ রেকর্ড সফলভাবে আপডেট হয়েছে!";

         header("Location:index.php?page=car/index");
            exit();

    } catch (PDOException $e) {
        echo "Update Failed: " . $e->getMessage();
    }

} else {
    echo "Invalid Request";
}
?>
