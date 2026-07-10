<?php

include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


//print_r($_POST); // Debugging line to check the POST data

    // Data collect
    $customer_name = $_POST['customer_name'];
    $car_number = $_POST['car_number'] ?? null;
    $customer_phone = $_POST['customer_phone'];
    $kisti_number = (int) $_POST['kisti_number'];
    $amount = (float) $_POST['amount'];
    $fine_amount = (float) ($_POST['fine_amount'] ?? 0);
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $payment_type = $_POST['payment_type'] ?? 'kisti'; // default to 'kisti' if not provided
    $received_by = $_POST['received_by'];
    $transaction_id = $_POST['transaction_id'] ?? null;
    $bank_name = $_POST['bank_name'] ?? null;
    $cheque_no = $_POST['cheque_no'] ?? null;
    $note = $_POST['note'] ?? null;
    // Auto calculate
    $total_received = $amount + $fine_amount;
    // Optional fields
    $status = $_POST['status'] ?? 'paid'; // default to 'active' if not provided
    $created_by = $_POST['created_by'] ?? null; // login user id দিলে ভালো

    try {

        $sql = "INSERT INTO kisti_payments (
            customer_name, car_number, customer_phone,
            kisti_number, amount, fine_amount, total_received,
            payment_date, payment_method,payment_type,received_by, 
            transaction_id, bank_name, cheque_no,
            note, status, created_by
        ) VALUES (
            :customer_name, :car_number, :customer_phone,
            :kisti_number, :amount, :fine_amount, :total_received,
            :payment_date, :payment_method, :payment_type, :received_by,
            :transaction_id, :bank_name, :cheque_no,
            :note, :status, :created_by
        )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':customer_name' => $customer_name,
            ':car_number' => $car_number,
            ':customer_phone' => $customer_phone,
            ':kisti_number' => $kisti_number,
            ':amount' => $amount,
            ':fine_amount' => $fine_amount,
            ':total_received' => $total_received,
            ':payment_date' => $payment_date,
            ':payment_method' => $payment_method,
            ':payment_type' => $payment_type,
            ':received_by' => $received_by,
            ':transaction_id' => $transaction_id,
            ':bank_name' => $bank_name,
            ':cheque_no' => $cheque_no,
            ':note' => $note,
            ':status' => $status,
            ':created_by' => $created_by
        ]);

    //    header('Location:kisti_payment_list.php?success=1'); // Redirect to the list page with success message

    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}