<?php

// =====================================================
// ONLY POST REQUEST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php?page=cng/add");

    exit;
}


// =====================================================
// GET FORM DATA
// =====================================================

$carNumber = trim(
    $_POST['car_number'] ?? ''
);

$garageId = (int)(
    $_POST['garage_id'] ?? 0
);

$driverName = trim(
    $_POST['driver_name'] ?? ''
);

$driverMobile = trim(
    $_POST['driver_mobile'] ?? ''
);

$dailyRent = (float)(
    $_POST['daily_rent'] ?? 0
);

$status = trim(
    $_POST['status'] ?? 'active'
);

$note = trim(
    $_POST['note'] ?? ''
);


// =====================================================
// CAR NUMBER VALIDATION
// =====================================================

if ($carNumber === '') {

    $_SESSION['error'] =
        'গাড়ির নাম্বার দিন।';

    header("Location: index.php?page=cng/add");

    exit;
}


// =====================================================
// GARAGE VALIDATION
// =====================================================

if ($garageId <= 0) {

    $_SESSION['error'] =
        'গ্যারেজ নির্বাচন করুন।';

    header("Location: index.php?page=cng/add");

    exit;
}


// =====================================================
// DAILY RENT VALIDATION
// =====================================================

if ($dailyRent < 0) {

    $_SESSION['error'] =
        'সঠিক দৈনিক ভাড়া দিন।';

    header("Location: index.php?page=cng/add");

    exit;
}


// =====================================================
// STATUS VALIDATION
// =====================================================

$allowedStatus = [

    'active',

    'inactive',

    'maintenance',

    'sale'

];


if (!in_array(
    $status,
    $allowedStatus,
    true
)) {

    $_SESSION['error'] =
        'সঠিক Status নির্বাচন করুন।';

    header("Location: index.php?page=cng/add");

    exit;
}


// =====================================================
// CHECK GARAGE EXISTS
// =====================================================

try {

    $garageCheck = $pdo->prepare("
        SELECT id
        FROM garages
        WHERE id = ?
        AND status = 'active'
        LIMIT 1
    ");

    $garageCheck->execute([
        $garageId
    ]);


    if (!$garageCheck->fetch()) {

        $_SESSION['error'] =
            'নির্বাচিত গ্যারেজ পাওয়া যায়নি।';

        header(
            "Location: index.php?page=cng/add"
        );

        exit;
    }


} catch (PDOException $e) {

    $_SESSION['error'] =
        'গ্যারেজ যাচাই করতে সমস্যা হয়েছে: '
        . $e->getMessage();

    header(
        "Location: index.php?page=cng/add"
    );

    exit;
}


// =====================================================
// CHECK DUPLICATE CAR NUMBER
// =====================================================

try {

    $check = $pdo->prepare("
        SELECT id
        FROM cng_vehicles
        WHERE car_number = ?
        LIMIT 1
    ");

    $check->execute([
        $carNumber
    ]);


    if ($check->fetch()) {

        $_SESSION['error'] =
            'এই গাড়ির নাম্বার ইতিমধ্যে সিস্টেমে আছে।';

        header(
            "Location: index.php?page=cng/cng_add"
        );

        exit;
    }


} catch (PDOException $e) {

    $_SESSION['error'] =
        'গাড়ি যাচাই করতে সমস্যা হয়েছে: '
        . $e->getMessage();

    header(
        "Location: index.php?page=cng/cng_add"
    );

    exit;
}


// =====================================================
// INSERT CNG
// =====================================================

try {

    $stmt = $pdo->prepare("

        INSERT INTO cng_vehicles
        (
            car_number,
            garage_id,
            driver_name,
            driver_mobile,
            daily_rent,
            status,
            note
        )

        VALUES
        (
            :car_number,
            :garage_id,
            :driver_name,
            :driver_mobile,
            :daily_rent,
            :status,
            :note
        )

    ");


    $stmt->execute([

        ':car_number' =>
            $carNumber,

        ':garage_id' =>
            $garageId,

        ':driver_name' =>
            ($driverName !== ''
                ? $driverName
                : null),

        ':driver_mobile' =>
            ($driverMobile !== ''
                ? $driverMobile
                : null),

        ':daily_rent' =>
            $dailyRent,

        ':status' =>
            $status,

        ':note' =>
            ($note !== ''
                ? $note
                : null)

    ]);


    // =================================================
    // SUCCESS
    // =================================================

    $_SESSION['success'] =
        'CNG গাড়ি সফলভাবে সংরক্ষণ করা হয়েছে।';


    header(
        "Location: index.php?page=cng/index"
    );

    exit;


} catch (PDOException $e) {


    // =================================================
    // DATABASE ERROR
    // =================================================

    $_SESSION['error'] =
        'CNG সংরক্ষণ করতে সমস্যা হয়েছে: '
        . $e->getMessage();


    header(
        "Location: index.php?page=cng/add"
    );

    exit;
}