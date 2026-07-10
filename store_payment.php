<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sql = "INSERT INTO kisti_payments (
        customer_record_id, invoice_no, customer_name, customer_phone,
        kisti_number, amount, fine_amount, total_received,
        payment_date, payment_method,
        transaction_id, bank_name, cheque_no,
        note, status
    ) VALUES (
        :customer_record_id, :invoice_no, :customer_name, :customer_phone,
        :kisti_number, :amount, :fine_amount, :total_received,
        :payment_date, :payment_method,
        :transaction_id, :bank_name, :cheque_no,
        :note, :status
    )";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':customer_record_id' => $_POST['customer_record_id'],
        ':invoice_no' => $_POST['invoice_no'],
        ':customer_name' => $_POST['customer_name'],
        ':customer_phone' => $_POST['customer_phone'],
        ':kisti_number' => $_POST['kisti_number'],
        ':amount' => $_POST['amount'],
        ':fine_amount' => $_POST['fine_amount'],
        ':total_received' => $_POST['total_received'],
        ':payment_date' => $_POST['payment_date'],
        ':payment_method' => $_POST['payment_method'],
        ':transaction_id' => $_POST['transaction_id'],
        ':bank_name' => $_POST['bank_name'],
        ':cheque_no' => $_POST['cheque_no'],
        ':note' => $_POST['note'],
        ':status' => $_POST['status']
    ]);

    echo "<script>alert('Payment সফলভাবে সংরক্ষণ হয়েছে'); window.location='create_payment.php';</script>";
}
?>