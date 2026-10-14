
<?php

/*
|--------------------------------------------------------------------------
| Hice Rent Save / Update
|--------------------------------------------------------------------------
| Normal Mode:
| Rent - Expense = Net Income
| Driver = Net Income x 30%
| Owner  = Net Income x 70%
|
| Direct Mode:
| Owner amount is entered manually.
| Driver percentage is not calculated.
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=hice/add");
    exit;
}

/*
|--------------------------------------------------------------------------
| Redirect Helper
|--------------------------------------------------------------------------
*/

function hiceSaveRedirect(int $id = 0): void
{
    if ($id > 0) {
        header("Location: index.php?page=hice/add&edit=" . $id);
    } else {
        header("Location: index.php?page=hice/add");
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$id = (int)($_POST['id'] ?? 0);

$collectionDate = trim($_POST['collection_date'] ?? '');

$vehicleId = filter_var(
    $_POST['vehicle_id'] ?? '',
    FILTER_VALIDATE_INT
);

$entryMode = (
    ($_POST['entry_mode'] ?? 'normal') === 'direct'
) ? 'direct' : 'normal';

/*
|--------------------------------------------------------------------------
| Numeric Input Validation
|--------------------------------------------------------------------------
*/

$rentInput = trim((string)($_POST['total_rent'] ?? '0'));
$expenseInput = trim((string)($_POST['expense_amount'] ?? '0'));

$ownerInput = trim((string)($_POST['owner_amount'] ?? ''));

$expenseNote = trim($_POST['expense_note'] ?? '');

if (
    $rentInput === '' ||
    !is_numeric($rentInput) ||
    !is_finite((float)$rentInput)
) {
    $_SESSION['error'] = 'ভাড়ার পরিমাণ সঠিকভাবে লিখুন।';
    hiceSaveRedirect($id);
}

if (
    $expenseInput === '' ||
    !is_numeric($expenseInput) ||
    !is_finite((float)$expenseInput)
) {
    $_SESSION['error'] = 'খরচের পরিমাণ সঠিকভাবে লিখুন।';
    hiceSaveRedirect($id);
}

$totalRent = (float)$rentInput;
$expense = (float)$expenseInput;

/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

$dateObj = DateTime::createFromFormat('!Y-m-d', $collectionDate);

if (
    !$dateObj ||
    $dateObj->format('Y-m-d') !== $collectionDate
) {
    $_SESSION['error'] = 'সঠিক তারিখ পাওয়া যায়নি।';
    hiceSaveRedirect($id);
}

/*
|--------------------------------------------------------------------------
| Common Validation
|--------------------------------------------------------------------------
*/

if (!$vehicleId || $vehicleId <= 0) {
    $_SESSION['error'] = 'গাড়ি নির্বাচন করুন।';
    hiceSaveRedirect($id);
}

/*
|--------------------------------------------------------------------------
| Verify Vehicle
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM hice_vehicles
    WHERE id = ?
      AND status = 'active'
    LIMIT 1
");

$stmt->execute([$vehicleId]);

$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    $_SESSION['error'] = 'নির্বাচিত গাড়িটি পাওয়া যায়নি অথবা সক্রিয় নয়।';
    hiceSaveRedirect($id);
}

/*
|--------------------------------------------------------------------------
| Verify Existing Record for Update
|--------------------------------------------------------------------------
*/

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT id
        FROM hice_rents
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['error'] = 'আপডেট করার হিসাব পাওয়া যায়নি।';
        hiceSaveRedirect();
    }
}

/*
|--------------------------------------------------------------------------
| Initialize Calculation Variables
|--------------------------------------------------------------------------
*/

$netIncome = 0;
$driverPercent = 0;
$driverAmount = 0;
$ownerAmount = 0;

/*
|--------------------------------------------------------------------------
| Calculation by Mode
|--------------------------------------------------------------------------
*/

if ($entryMode === 'direct') {

    /*
    |--------------------------------------------------------------------------
    | Direct Owner Mode
    |--------------------------------------------------------------------------
    | মালিকের টাকা সরাসরি এন্ট্রি হবে।
    | ড্রাইভারের ৩০% স্বয়ংক্রিয়ভাবে হিসাব হবে না।
    |--------------------------------------------------------------------------
    */

    if (
        $ownerInput === '' ||
        !is_numeric($ownerInput) ||
        !is_finite((float)$ownerInput) ||
        (float)$ownerInput < 0
    ) {
        $_SESSION['error'] = 'মালিকের প্রাপ্য টাকার সঠিক পরিমাণ লিখুন।';
        hiceSaveRedirect($id);
    }

    $ownerAmount = round((float)$ownerInput, 2);

    // Direct mode-এ rent ও expense প্রযোজ্য নয়
    $totalRent = 0;
    $expense = 0;
    $expenseNote = '';

    $netIncome = 0;
    $driverPercent = 0;
    $driverAmount = 0;

} else {

    /*
    |--------------------------------------------------------------------------
    | Normal Calculation Mode
    |--------------------------------------------------------------------------
    */

    if ($totalRent < 0) {
        $_SESSION['error'] = 'ভাড়ার পরিমাণ ঋণাত্মক হতে পারবে না।';
        hiceSaveRedirect($id);
    }

    if ($expense < 0) {
        $_SESSION['error'] = 'খরচের পরিমাণ ঋণাত্মক হতে পারবে না।';
        hiceSaveRedirect($id);
    }

    $netIncome = max(0, $totalRent - $expense);

    $driverPercent = 30;

    $driverAmount = round($netIncome * 0.30, 2);

    $ownerAmount = round(
        $netIncome - $driverAmount,
        2
    );
}

/*
|--------------------------------------------------------------------------
| Database Insert / Update
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
            UPDATE hice_rents
            SET
                vehicle_id = ?,
                collection_date = ?,
                total_rent = ?,
                expense_amount = ?,
                expense_note = ?,
                net_income = ?,
                driver_percent = ?,
                driver_amount = ?,
                owner_amount = ?,
                entry_mode = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $vehicleId,
            $collectionDate,
            $totalRent,
            $expense,
            $expenseNote !== '' ? $expenseNote : null,
            $netIncome,
            $driverPercent,
            $driverAmount,
            $ownerAmount,
            $entryMode,
            $id
        ]);

        $_SESSION['success'] = 'হিসাব সফলভাবে আপডেট হয়েছে।';

    } else {

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO hice_rents (
                vehicle_id,
                collection_date,
                total_rent,
                expense_amount,
                expense_note,
                net_income,
                driver_percent,
                driver_amount,
                owner_amount,
                entry_mode
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $vehicleId,
            $collectionDate,
            $totalRent,
            $expense,
            $expenseNote !== '' ? $expenseNote : null,
            $netIncome,
            $driverPercent,
            $driverAmount,
            $ownerAmount,
            $entryMode
        ]);

        $_SESSION['success'] = 'নতুন হিসাব সফলভাবে সংরক্ষণ হয়েছে।';
    }

    header("Location: index.php?page=hice/index");
    exit;

} catch (PDOException $e) {

    // প্রকৃত error server log-এ রাখুন
    error_log('Hice rent save error: ' . $e->getMessage());

    $_SESSION['error'] = 'ডাটাবেজে তথ্য সংরক্ষণ করা যায়নি।';

    hiceSaveRedirect($id);
}
