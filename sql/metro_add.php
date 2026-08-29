 <?php
 
include './config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_number  = trim($_POST['car_number'] ?? '');
    $driver_name = trim($_POST['driver_name'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $start_date  = $_POST['start_date'] ?? date('Y-m-d');

    // আপনার নির্ধারিত টাকা
    $initial_deposit = 100000;
    $monthly_amount  = 25000;

    if ($car_number == '') {

        $error = "গাড়ির নম্বর লিখুন!";

    } else {

        // আগে গাড়ি আছে কিনা চেক
        $check = $pdo->prepare("
            SELECT id
            FROM metro_cars
            WHERE car_number = ?
            LIMIT 1
        ");

        $check->execute([$car_number]);

        if ($check->fetch()) {

            $error = "এই গাড়ির নম্বর আগে থেকেই আছে!";

        } else {

            try {

                $pdo->beginTransaction();

                // গাড়ি যোগ
                $stmt = $pdo->prepare("
                    INSERT INTO metro_cars
                    (
                        car_number,
                        driver_name,
                        mobile,
                        initial_deposit,
                        monthly_amount,
                        start_date,
                        status
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, 'active'
                    )
                ");

                $stmt->execute([
                    $car_number,
                    $driver_name,
                    $mobile,
                    $initial_deposit,
                    $monthly_amount,
                    $start_date
                ]);

                $car_id = $pdo->lastInsertId();


                // ১ লাখ প্রাথমিক জমার রেকর্ড
                $payment = $pdo->prepare("
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
                        ?, ?, ?, ?, 'initial', ?
                    )
                ");

                $payment->execute([
                    $car_id,
                    $start_date,
                    date('Y-m', strtotime($start_date)),
                    $initial_deposit,
                    'গাড়ি নেওয়ার সময় প্রাথমিক জমা'
                ]);


                $pdo->commit();


 

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "গাড়ি যোগ করা যায়নি! " . $e->getMessage();
            }
        }
    }
}

?>
