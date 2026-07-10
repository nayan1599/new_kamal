<?php
 
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? '';

    if (!$id) {
        $_SESSION['error'] = "Invalid ID!";
        header("Location: index.php?page=payment/index");
        exit();
    }

    $car_number     = $_POST['car_number'] ?? null;
    $kisti_number   = (int) $_POST['kisti_number'];
    $amount         = (float) $_POST['amount'];
    $fine_amount    = (float) ($_POST['fine_amount'] ?? 0);
    $payment_date   = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $received_by    = $_POST['received_by'];
    $note           = $_POST['note'] ?? null;

    $total_received = $amount + $fine_amount;

    try {

        $stmt = $pdo->prepare("
            UPDATE kisti_payments SET
                car_number = ?,
                kisti_number = ?,
                amount = ?,
                fine_amount = ?,
                total_received = ?,
                payment_date = ?,
                payment_method = ?,
                received_by = ?,
                note = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $car_number,
            $kisti_number,
            $amount,
            $fine_amount,
            $total_received,
            $payment_date,
            $payment_method,
            $received_by,
            $note,
            $id
        ]);

        $_SESSION['success'] = "✅ আপডেট সফল হয়েছে!";
        header("Location: index.php?page=payment/index");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Update Failed!";
        header("Location: index.php?page=payment/index");
        exit();
    }

} else {
    echo "Invalid Request";
}
?>
