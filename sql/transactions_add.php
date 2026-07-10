<?php
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $taka_in        = (float)$_POST['taka_in'];
    $taka_out       = (float)($_POST['taka_out'] ?? 0);
    $head_name      = $_POST['head_name'];
    $date           = $_POST['date'];
     $description   = trim($_POST['description'] ?? '');
    $status         = 1;

    $stmt = $pdo->prepare("INSERT INTO transactions 
        (taka_in, taka_out, status, head_name, date, description)
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        
        $taka_in,
        $taka_out,
        $status,
        $head_name,
        $date,
        $description
    ]);

    header("Location: index.php?page=rent/index");
    exit();
}
?>