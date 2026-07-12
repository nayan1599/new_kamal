<?php
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_number      = trim($_POST['car_number']);
    $customer_name   = trim($_POST['customer_name']);
    $customer_phone  = trim($_POST['customer_phone']);

    $rent_amount     = (float)$_POST['rent_amount'];
    $advance_amount  = (float)($_POST['advance_amount'] ?? 0);
    $rent_month      = $_POST['rent_month'];
    $rent_date       = $_POST['rent_date'];
    $payment_status       = $_POST['payment_status'];
    $payment_method  = $_POST['payment_method'];
    $transaction_id  = trim($_POST['transaction_id'] ?? '');
    $note            = trim($_POST['note'] ?? '');

    $due_amount = 0;

    $stmt = $pdo->prepare("INSERT INTO rents 
        (car_number, customer_name, customer_phone, rent_amount, advance_amount, due_amount, rent_month, rent_date, payment_status, payment_method, transaction_id, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $car_number,
        $customer_name,
        $customer_phone,
        $rent_amount,
        $advance_amount,
        $due_amount,
        $rent_month,
        $rent_date,
        $payment_status,
        $payment_method,
        $transaction_id,
        $note
    ]);

    header("Location: index.php?page=rent/index");
    exit();
}
?>