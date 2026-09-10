<?php
 

// ID নেওয়া
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../index.php?page=rent/index&error=invalid_id");
    exit;
}

try {

    // আগে গাড়িটি আছে কিনা চেক
    $stmt = $pdo->prepare("SELECT id, car_number FROM rents WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        header("Location: ../index.php?page=rent/index&error=not_found");
        exit;
    }

    // গাড়ি ডিলিট
    $stmt = $pdo->prepare("DELETE FROM rents WHERE id = ?");
    $stmt->execute([$id]);

    // সফল হলে তালিকায় ফিরে যাবে
    header("Location: ../index.php?page=rent/index&success=deleted");
    exit;

} catch (PDOException $e) {

    // Error হলে
    header("Location: ../index.php?page=rent/index&error=delete_failed");
    exit;
}