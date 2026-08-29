<?php
include './config/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=metro/index');
    exit;
}

$carId = (int)($_POST['metro_car_id'] ?? 0);

$paymentType = $_POST['payment_type'] ?? 'monthly';

$paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

$monthYear = $_POST['month_year'] ?? date('Y-m');

$amount = (float)($_POST['amount'] ?? 0);

$note = trim($_POST['note'] ?? '');


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($carId <= 0) {

    die('গাড়ি নির্বাচন করা হয়নি!');

}


if ($amount <= 0) {

    die('সঠিক টাকার পরিমাণ দিন!');

}


/*
|--------------------------------------------------------------------------
| গাড়ির তথ্য
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM metro_cars
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$carId]);

$car = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$car) {

    die('গাড়ির তথ্য পাওয়া যায়নি!');

}


$monthlyAmount =
    (float)$car['monthly_amount'];


/*
|--------------------------------------------------------------------------
| Monthly Payment
|--------------------------------------------------------------------------
*/

if ($paymentType === 'monthly') {

    $check = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0)
        FROM metro_payments

        WHERE metro_car_id = ?

        AND payment_type = 'monthly'

        AND month_year = ?
    ");

    $check->execute([
        $carId,
        $monthYear
    ]);

    $alreadyPaid =
        (float)$check->fetchColumn();


    $remaining =
        $monthlyAmount - $alreadyPaid;


    if ($remaining <= 0) {

        die("
            <div style='
                max-width:600px;
                margin:50px auto;
                font-family:Arial;
            '>

                <div style='
                    background:#f8d7da;
                    color:#842029;
                    padding:20px;
                    border-radius:10px;
                '>

                    এই মাসের কিস্তি ইতোমধ্যে সম্পূর্ণ জমা হয়েছে।

                </div>

                <a
                    href='index.php?page=metro/payment&id={$carId}'
                    style='
                        display:inline-block;
                        margin-top:15px;
                        padding:10px 20px;
                        background:#0d6efd;
                        color:white;
                        text-decoration:none;
                        border-radius:6px;
                    '
                >
                    ফিরে যান
                </a>

            </div>
        ");

    }


    if ($amount > $remaining) {

        die("
            <div style='
                max-width:600px;
                margin:50px auto;
                font-family:Arial;
            '>

                <div style='
                    background:#fff3cd;
                    color:#664d03;
                    padding:20px;
                    border-radius:10px;
                '>

                    এই মাসে সর্বোচ্চ
                    <strong>
                        ৳ " . number_format($remaining, 0) . "
                    </strong>
                    জমা নেওয়া যাবে।

                </div>

                <a
                    href='index.php?page=metro/payment&id={$carId}'
                    style='
                        display:inline-block;
                        margin-top:15px;
                        padding:10px 20px;
                        background:#0d6efd;
                        color:white;
                        text-decoration:none;
                        border-radius:6px;
                    '
                >
                    ফিরে যান
                </a>

            </div>
        ");

    }

}


/*
|--------------------------------------------------------------------------
| INSERT PAYMENT
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    $insert = $pdo->prepare("
        INSERT INTO metro_payments
        (
            metro_car_id,
            payment_date,
            month_year,
            amount,
            payment_type,
            note
        )

        VALUES
        (
            :metro_car_id,
            :payment_date,
            :month_year,
            :amount,
            :payment_type,
            :note
        )
    ");


    $insert->execute([

        ':metro_car_id' =>
            $carId,

        ':payment_date' =>
            $paymentDate,

        ':month_year' =>
            $paymentType === 'monthly'
                ? $monthYear
                : null,

        ':amount' =>
            $amount,

        ':payment_type' =>
            $paymentType,

        ':note' =>
            $note

    ]);


    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Success Redirect
    |--------------------------------------------------------------------------
    */

    header(
        "Location: index.php?page=metro/payment&id="
        . $carId
        . "&success=1"
    );

    exit;


} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    die("
        <div style='
            max-width:700px;
            margin:50px auto;
            font-family:Arial;
        '>

            <div style='
                background:#f8d7da;
                color:#842029;
                padding:20px;
                border-radius:10px;
            '>

                <h4>পেমেন্ট Save হয়নি</h4>

                <p>
                    " . htmlspecialchars(
                        $e->getMessage()
                    ) . "
                </p>

            </div>

        </div>
    ");

}