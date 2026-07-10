<?php
// sql/head_add.php
session_start();
include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     
    $head_name = trim($_POST['head_name'] ?? '');
    $head_type = trim($_POST['head_type'] ?? '');
    $head_code = 'cod-' . rand(100, 999);

    $errors = [];

    // Validation
    if (empty($head_name)) $errors[] = "হেডের নাম দিতে হবে";
    if (empty($head_type)) $errors[] = "হেডের টাইপ দিতে হবে";
    if (empty($head_code)) $errors[] = "হেডের কোড দিতে হবে";
     
 
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO account_head
                    (head_name, head_type, head_code, status) 
                    VALUES (?, ?, ?, 'active')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$head_name, $head_type, $head_code]);

            $_SESSION['success'] = "✅ নতুন অ্যাকাউন্টিং হেড সফলভাবে যোগ করা হয়েছে!";
            header("Location: index.php?page=head/index");
            exit();

        } catch(PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>