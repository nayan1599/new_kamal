<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// REDIRECT URL
// =====================================================

$redirectUrl = "index.php?page=cng/cng_rent";

// =====================================================
// REQUEST METHOD CHECK
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $redirectUrl);
    exit;
}

// =====================================================
// POST DATA
// =====================================================

$collection_date = trim(
    $_POST['collection_date'] ?? date('Y-m-d')
);

$vehicleIds     = $_POST['vehicle_id'] ?? [];
$carNumbers     = $_POST['car_number'] ?? [];
$amounts        = $_POST['amount'] ?? [];
$expenseAmounts = $_POST['expense_amount'] ?? [];
$expenseNotes   = $_POST['expense_note'] ?? [];

// =====================================================
// DATE VALIDATION
// =====================================================

$dateObject = DateTime::createFromFormat(
    '!Y-m-d',
    $collection_date
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $collection_date
) {
    $_SESSION['error'] = "সঠিক তারিখ নির্বাচন করুন।";

    header("Location: " . $redirectUrl);
    exit;
}

// =====================================================
// ARRAY VALIDATION
// =====================================================

if (
    !is_array($vehicleIds) ||
    !is_array($carNumbers) ||
    !is_array($amounts) ||
    !is_array($expenseAmounts) ||
    !is_array($expenseNotes) ||
    empty($vehicleIds)
) {
    $_SESSION['error'] = "গাড়ির সঠিক তথ্য পাওয়া যায়নি।";

    header("Location: " . $redirectUrl);
    exit;
}

// =====================================================
// PDO ERROR MODE
// =====================================================

try {

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

    // =================================================
    // PREPARED STATEMENTS
    // =================================================

    // গাড়ির তথ্য
    $vehicleStmt = $pdo->prepare("
        SELECT
            id,
            car_number,
            status
        FROM cng_vehicles
        WHERE id = ?
        LIMIT 1
    ");

    // একই তারিখে গাড়ির হিসাব আছে কি না
    $checkStmt = $pdo->prepare("
        SELECT id
        FROM cng_daily_collections
        WHERE vehicle_id = ?
          AND collection_date = ?
        LIMIT 1
    ");

    // পুরোনো হিসাব আপডেট
    $updateStmt = $pdo->prepare("
        UPDATE cng_daily_collections
        SET
            car_number = ?,
            amount = ?,
            expense_amount = ?,
            expense_note = ?
        WHERE id = ?
    ");

    // নতুন হিসাব সংরক্ষণ
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
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // =================================================
    // COUNTERS
    // =================================================

    $saved   = 0;
    $updated = 0;
    $skipped = 0;

    $pdo->beginTransaction();

    // =================================================
    // LOOP THROUGH VEHICLES
    // =================================================

    foreach ($vehicleIds as $vehicleId) {

        $vehicleId = filter_var(
            $vehicleId,
            FILTER_VALIDATE_INT
        );

        if (!$vehicleId || $vehicleId <= 0) {
            $skipped++;
            continue;
        }

        // ---------------------------------------------
        // GET FORM VALUES
        // ---------------------------------------------

        $amount = $amounts[$vehicleId] ?? '';

        $expenseAmount =
            $expenseAmounts[$vehicleId] ?? '';

        $expenseNote = trim(
            (string) ($expenseNotes[$vehicleId] ?? '')
        );

        // ---------------------------------------------
        // EMPTY AMOUNT = ZERO
        // ---------------------------------------------

        if ($amount === '' || $amount === null) {
            $amount = 0;
        }

        if (
            $expenseAmount === '' ||
            $expenseAmount === null
        ) {
            $expenseAmount = 0;
        }

        // ---------------------------------------------
        // NUMERIC VALIDATION
        // ---------------------------------------------

        if (
            !is_numeric($amount) ||
            !is_numeric($expenseAmount)
        ) {
            $skipped++;
            continue;
        }

        $amount = (float) $amount;

        $expenseAmount = (float) $expenseAmount;

        // ---------------------------------------------
        // NEGATIVE AMOUNT VALIDATION
        // ---------------------------------------------

        if ($amount < 0 || $expenseAmount < 0) {
            $skipped++;
            continue;
        }

        // ---------------------------------------------
        // NOTHING ENTERED: SKIP
        // ---------------------------------------------

        if (
            $amount == 0 &&
            $expenseAmount == 0 &&
            $expenseNote === ''
        ) {
            $skipped++;
            continue;
        }

        // ---------------------------------------------
        // GET VEHICLE FROM DATABASE
        // ---------------------------------------------

        $vehicleStmt->execute([$vehicleId]);

        $vehicle = $vehicleStmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$vehicle) {
            $skipped++;
            continue;
        }

        // ---------------------------------------------
        // ACTIVE VEHICLE CHECK
        // ---------------------------------------------

        if (
            strtolower(trim(
                (string) ($vehicle['status'] ?? '')
            )) !== 'active'
        ) {
            $skipped++;
            continue;
        }

        // ---------------------------------------------
        // CAR NUMBER
        // Use database value as the trusted value
        // ---------------------------------------------

        $carNumber = trim(
            (string) $vehicle['car_number']
        );

        if ($carNumber === '') {
            $carNumber = trim(
                (string) ($carNumbers[$vehicleId] ?? '')
            );
        }

        // ---------------------------------------------
        // CHECK EXISTING RECORD
        // ---------------------------------------------

        $checkStmt->execute([
            $vehicleId,
            $collection_date
        ]);

        $existing = $checkStmt->fetch(
            PDO::FETCH_ASSOC
        );

        // ---------------------------------------------
        // UPDATE EXISTING RECORD
        // ---------------------------------------------

        if ($existing) {


$updateStmt = $pdo->prepare("
    UPDATE cng_daily_collections
    SET
        car_number = ?,
        amount = ?,
        expense_amount = ?,
        expense_note = ?
    WHERE id = ?
");

            // $updateStmt->execute([
            //     $carNumber,
            //     $amount,
            //     $expenseAmount,
            //     $expenseNote !== '' ? $expenseNote : null,
            //     (int) $existing['id']
            // ]);

            $updated++;

        } else {

            // -----------------------------------------
            // INSERT NEW RECORD
            // -----------------------------------------

            $insertStmt->execute([
                $vehicleId,
                $carNumber,
                $collection_date,
                $amount,
                $expenseAmount,
                $expenseNote !== '' ? $expenseNote : null
            ]);

            $saved++;
        }
    }

    // =================================================
    // COMMIT TRANSACTION
    // =================================================

    $pdo->commit();

    // =================================================
    // SUCCESS / ERROR MESSAGES
    // =================================================

    if ($saved > 0 || $updated > 0) {

        $message = '';

        if ($saved > 0) {
            $message .=
                "{$saved} টি গাড়ির হিসাব নতুন করে সংরক্ষণ হয়েছে। ";
        }

        if ($updated > 0) {
            $message .=
                "{$updated} টি গাড়ির হিসাব আপডেট হয়েছে। ";
        }

        if ($skipped > 0) {
            $message .=
                "{$skipped} টি গাড়ি বাদ দেওয়া হয়েছে।";
        }

        $_SESSION['success'] = trim($message);

    } else {

        $_SESSION['error'] =
            "কোনো হিসাব সংরক্ষণ হয়নি। "
            . "গাড়ির স্ট্যাটাস ও টাকার পরিমাণ পরীক্ষা করুন।";
    }

} catch (Throwable $e) {

    // =================================================
    // ROLLBACK ON ERROR
    // =================================================

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // বিস্তারিত ত্রুটি সার্ভার লগে সংরক্ষণ
    error_log(
        "CNG Collection Error: " .
        $e->getMessage()
    );

    $_SESSION['error'] =
        "হিসাব সংরক্ষণ করা যায়নি। "
        . "ডাটাবেজের কলাম ও Error Log পরীক্ষা করুন।";
}

// =====================================================
// REDIRECT
// =====================================================

header("Location: " . $redirectUrl);
exit;