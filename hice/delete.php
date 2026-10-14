<?php
 

// ID নেওয়া
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../index.php?page=hice/index&error=invalid_id");
    exit;
}

    // গাড়ি ডিলিট
    $stmt = $pdo->prepare("DELETE FROM hice_rents WHERE id = ?");
    $stmt->execute([$id]);

    // সফল হলে তালিকায় ফিরে যাবে
    header("Location: ../index.php?page=hice/index&success=deleted");
    exit;


    // Error হলে
    header("Location: ../index.php?page=hice/index&error=delete_failed");
    exit;
