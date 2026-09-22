<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=cng/cng_rent");
    exit;
}


// =====================================================
// POST DATA
// =====================================================

$collection_date = trim($_POST['collection_date'] ?? date('Y-m-d'));

$vehicleIds      = $_POST['vehicle_id'] ?? [];
$carNumbers      = $_POST['car_number'] ?? [];
$amounts         = $_POST['amount'] ?? [];
$expenseAmounts  = $_POST['expense_amount'] ?? [];
$expenseNotes    = $_POST['expense_note'] ?? [];


// =====================================================
// DATE VALIDATION
// =====================================================

$dateObject = DateTime::createFromFormat('Y-m-d', $collection_date);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $collection_date
) {
    $_SESSION['error'] = "সঠিক তারিখ পাওয়া যায়নি।";

    header("Location: index.php?page=cng/cng_rent");
    exit;
}


// =====================================================
// VEHICLE VALIDATION
// =====================================================

if (!is_array($vehicleIds) || empty($vehicleIds)) {

    $_SESSION['error'] = "কোনো গাড়ির তথ্য পাওয়া যায়নি।";

    header("Location: index.php?page=cng/cng_rent");
    exit;
}


try {

    $pdo->beginTransaction();


    // =================================================
    // VEHICLE
    // =================================================

    $vehicleStmt = $pdo->prepare("
        SELECT
            id,
            car_number,
            daily_rent,
            status
        FROM cng_vehicles
        WHERE id = ?
        LIMIT 1
    ");


    // =================================================
    // EXISTING COLLECTION
    // =================================================

    $checkStmt = $pdo->prepare("
        SELECT id
        FROM cng_daily_collections
        WHERE vehicle_id = ?
          AND collection_date = ?
        LIMIT 1
    ");


    // =================================================
    // UPDATE
    // =================================================

    $updateStmt = $pdo->prepare("
        UPDATE cng_daily_collections
        SET
            car_number = ?,
            amount = ?,
            expense_amount = ?,
            expense_note = ?
        WHERE id = ?
    ");


    // =================================================
    // INSERT
    // =================================================

    $insertStmt = $pdo->prepare("
        INSERT INTO cng_daily_collections
        (
            vehicle_id,
            car_number,
            collection_date,
            amount,
            expense_amount,
            expense_note
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");


    $saved   = 0;
    $updated = 0;
    $skipped = 0;


    // =================================================
    // LOOP
    // =================================================

    foreach ($vehicleIds as $vehicleId) {

        $vehicleId = (int)$vehicleId;

        if ($vehicleId <= 0) {
            $skipped++;
            continue;
        }


        // =================================================
        // GET VALUES
        // =================================================

        $amount = $amounts[$vehicleId] ?? 0;

        $expenseAmount = $expenseAmounts[$vehicleId] ?? 0;

        $expenseNote = trim(
            $expenseNotes[$vehicleId] ?? ''
        );


        // =================================================
        // EMPTY VALUES
        // =================================================

        if ($amount === '' || $amount === null) {
            $amount = 0;
        }

        if (
            $expenseAmount === '' ||
            $expenseAmount === null
        ) {
            $expenseAmount = 0;
        }


        // =================================================
        // NUMERIC CHECK
        // =================================================

        if (!is_numeric($amount)) {
            $skipped++;
            continue;
        }

        if (!is_numeric($expenseAmount)) {
            $skipped++;
            continue;
        }


        $amount = (float)$amount;

        $expenseAmount = (float)$expenseAmount;


        // =================================================
        // NEGATIVE VALUE
        // =================================================

        if ($amount < 0) {
            $amount = 0;
        }

        if ($expenseAmount < 0) {
            $expenseAmount = 0;
        }


        // =================================================
        // NOTHING ENTERED
        // =================================================

        if (
            $amount <= 0 &&
            $expenseAmount <= 0 &&
            $expenseNote === ''
        ) {
            $skipped++;
            continue;
        }


        // =================================================
        // GET VEHICLE
        // =================================================

        $vehicleStmt->execute([$vehicleId]);

        $vehicle = $vehicleStmt->fetch(PDO::FETCH_ASSOC);


        if (!$vehicle) {
            $skipped++;
            continue;
        }


        // =================================================
        // ACTIVE CHECK
        // =================================================

        if (
            strtolower(trim($vehicle['status'])) !== 'active'
        ) {
            $skipped++;
            continue;
        }


        // =================================================
        // CAR NUMBER
        // =================================================

        // POST থেকে car number থাকলে সেটি ব্যবহার করবে
        $carNumber = trim(
            $carNumbers[$vehicleId]
            ?? $vehicle['car_number']
            ?? ''
        );


        // =================================================
        // CHECK EXISTING
        // =================================================

        $checkStmt->execute([
            $vehicleId,
            $collection_date
        ]);

        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);


        // =================================================
        // UPDATE
        // =================================================

        if ($existing) {

            $updateStmt->execute([

                $carNumber,

                $amount,

                $expenseAmount,

                $expenseNote !== ''
                    ? $expenseNote
                    : null,

                (int)$existing['id']
            ]);

            $updated++;

        }


        // =================================================
        // INSERT
        // =================================================

        else {

            $insertStmt->execute([

                $vehicleId,

                $carNumber,

                $collection_date,

                $amount,

                $expenseAmount,

                $expenseNote !== ''
                    ? $expenseNote
                    : null
            ]);

            $saved++;
        }
    }


    // =================================================
    // COMMIT
    // =================================================

    $pdo->commit();


    // =================================================
    // SUCCESS MESSAGE
    // =================================================

    if ($saved > 0 && $updated > 0) {

        $_SESSION['success'] =
            "{$saved} টি গাড়ির হিসাব সংরক্ষণ হয়েছে এবং "
            . "{$updated} টি গাড়ির হিসাব আপডেট হয়েছে।";

    } elseif ($saved > 0) {

        $_SESSION['success'] =
            "সফলভাবে {$saved} টি গাড়ির হিসাব সংরক্ষণ হয়েছে।";

    } elseif ($updated > 0) {

        $_SESSION['success'] =
            "সফলভাবে {$updated} টি গাড়ির হিসাব আপডেট হয়েছে।";

    } else {

        $_SESSION['error'] =
            "কোনো জমা বা খরচের তথ্য দেওয়া হয়নি।";
    }


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "CNG Collection Error: " .
        $e->getMessage()
    );

    $_SESSION['error'] =
        "Database Error: " . $e->getMessage();
}


// =====================================================
// REDIRECT
// =====================================================

header("Location: index.php?page=cng/cng_rent");
exit;