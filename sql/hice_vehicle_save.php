<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=hice/vehicles");
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$id = (int)($_POST['id'] ?? 0);

$vehicleNumber = trim($_POST['vehicle_number'] ?? '');
$driverName    = trim($_POST['driver_name'] ?? '');
$driverMobile  = trim($_POST['driver_mobile'] ?? '');
$status        = trim($_POST['status'] ?? 'active');


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($vehicleNumber === '') {

    $_SESSION['error'] = 'গাড়ির নম্বর লিখুন।';

    if ($id > 0) {
        header("Location: index.php?page=hice/vehicle_add&edit=" . $id);
    } else {
        header("Location: index.php?page=hice/vehicle_add");
    }

    exit;
}


if ($driverName === '') {

    $_SESSION['error'] = 'ড্রাইভারের নাম লিখুন।';

    if ($id > 0) {
        header("Location: index.php?page=hice/vehicle_add&edit=" . $id);
    } else {
        header("Location: index.php?page=hice/vehicle_add");
    }

    exit;
}


/*
|--------------------------------------------------------------------------
| Status Validation
|--------------------------------------------------------------------------
*/

if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'active';
}


/*
|--------------------------------------------------------------------------
| Duplicate Vehicle Number Check
|--------------------------------------------------------------------------
*/

if ($id > 0) {

    // Edit করার সময় নিজের ID বাদ দিয়ে চেক
    $stmt = $pdo->prepare("
        SELECT id
        FROM hice_vehicles
        WHERE vehicle_number = ?
        AND id != ?
        LIMIT 1
    ");

    $stmt->execute([
        $vehicleNumber,
        $id
    ]);

} else {

    // New vehicle
    $stmt = $pdo->prepare("
        SELECT id
        FROM hice_vehicles
        WHERE vehicle_number = ?
        LIMIT 1
    ");

    $stmt->execute([
        $vehicleNumber
    ]);
}


$duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

if ($duplicate) {

    $_SESSION['error'] = 'এই গাড়ির নম্বর ইতিমধ্যে আছে।';

    if ($id > 0) {
        header("Location: index.php?page=hice/vehicle_add&edit=" . $id);
    } else {
        header("Location: index.php?page=hice/vehicle_add");
    }

    exit;
}


/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

try {

    if ($id > 0) {

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE hice_vehicles
            SET
                vehicle_number = ?,
                driver_name = ?,
                driver_mobile = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $vehicleNumber,
            $driverName,
            $driverMobile !== '' ? $driverMobile : null,
            $status,
            $id
        ]);

        $_SESSION['success'] = 'গাড়ির তথ্য সফলভাবে আপডেট হয়েছে।';

    } else {

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO hice_vehicles (
                vehicle_number,
                driver_name,
                driver_mobile,
                status
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $vehicleNumber,
            $driverName,
            $driverMobile !== '' ? $driverMobile : null,
            $status
        ]);

        $_SESSION['success'] = 'নতুন Hice গাড়ি সফলভাবে যোগ হয়েছে।';
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header("Location: index.php?page=hice/vehicles");
    exit;


} catch (PDOException $e) {

    $_SESSION['error'] = 'গাড়ির তথ্য সংরক্ষণ করা যায়নি।';

    if ($id > 0) {
        header("Location: index.php?page=hice/vehicle_add&edit=" . $id);
    } else {
        header("Location: index.php?page=hice/vehicle_add");
    }

    exit;
}