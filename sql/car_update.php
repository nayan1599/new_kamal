
<?php

include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ID
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        die("Invalid ID");
    }

    // Input data
    $customer_name    = trim($_POST['customer_name'] ?? '');
    $customer_phone   = trim($_POST['customer_phone'] ?? '');
    $car_number       = trim($_POST['car_number'] ?? '');
    $type             = trim($_POST['type'] ?? '');

    $total_price      = (float)($_POST['total_price'] ?? 0);
    $paid_amount      = (float)($_POST['paid_amount'] ?? 0);
    $total_kisti      = (int)($_POST['total_kisti'] ?? 0);
    $monthly_kisti    = (float)($_POST['monthly_kisti'] ?? 0);

    // Date
    $kisti_start_date = trim($_POST['kisti_start_date'] ?? '');

    // Empty হলে NULL
    if ($kisti_start_date === '') {
        $kisti_start_date = null;
    } else {

        // Date format YYYY-MM-DD কিনা যাচাই
        $dateObj = DateTime::createFromFormat('Y-m-d', $kisti_start_date);

        if (!$dateObj || $dateObj->format('Y-m-d') !== $kisti_start_date) {
            die("Invalid date format. Date must be YYYY-MM-DD");
        }

        $kisti_start_date = $dateObj->format('Y-m-d');
    }

    $note   = trim($_POST['note'] ?? '');
    $status = trim($_POST['status'] ?? '');

    try {

        $sql = "
            UPDATE customer_records SET
                customer_name = :customer_name,
                customer_phone = :customer_phone,
                car_number = :car_number,
                type = :type,
                total_price = :total_price,
                paid_amount = :paid_amount,
                total_kisti = :total_kisti,
                monthly_kisti = :monthly_kisti,
                kisti_start_date = :kisti_start_date,
                note = :note,
                status = :status
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':customer_name'    => $customer_name,
            ':customer_phone'   => $customer_phone,
            ':car_number'       => $car_number,
            ':type'             => $type,
            ':total_price'      => $total_price,
            ':paid_amount'      => $paid_amount,
            ':total_kisti'      => $total_kisti,
            ':monthly_kisti'    => $monthly_kisti,
            ':kisti_start_date' => $kisti_start_date,
            ':note'             => $note,
            ':status'           => $status,
            ':id'               => $id
        ]);

$_SESSION['success'] = "✅ রেকর্ড সফলভাবে আপডেট হয়েছে!";

echo "<script>
    window.location.href = 'index.php';
</script>";
exit;

    } catch (PDOException $e) {

        echo "Update Failed: " . htmlspecialchars($e->getMessage());

    }

} else {

    echo "Invalid Request";

}
?>
```
