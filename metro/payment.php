<?php

/*
|--------------------------------------------------------------------------
| Metro Payment Form
|--------------------------------------------------------------------------
*/

$carId = (int)($_GET['id'] ?? 0);

if ($carId <= 0) {
    die("
        <div class='container py-5'>
            <div class='alert alert-danger'>
                গাড়ির ID পাওয়া যায়নি!
            </div>
        </div>
    ");
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
    die("
        <div class='container py-5'>
            <div class='alert alert-danger'>
                গাড়ির তথ্য পাওয়া যায়নি!
            </div>
        </div>
    ");
}


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$monthlyAmount = (float)$car['monthly_amount'];
$initialDeposit = (float)$car['initial_deposit'];

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| Total Monthly Paid
|--------------------------------------------------------------------------
*/

$totalMonthlyStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
    AND payment_type = 'monthly'
");

$totalMonthlyStmt->execute([$carId]);

$totalMonthlyPaid =
    (float)$totalMonthlyStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Current Month
|--------------------------------------------------------------------------
*/

$currentMonth = date('Y-m');


$currentMonthStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
    AND payment_type = 'monthly'
    AND month_year = ?
");

$currentMonthStmt->execute([
    $carId,
    $currentMonth
]);

$currentMonthPaid =
    (float)$currentMonthStmt->fetchColumn();


$currentMonthDue = max(
    0,
    $monthlyAmount - $currentMonthPaid
);


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $paymentType =
        $_POST['payment_type'] ?? 'monthly';

    $paymentDate =
        $_POST['payment_date']
        ?? date('Y-m-d');

    $monthYear =
        $_POST['month_year']
        ?? date('Y-m');

    $amount =
        (float)($_POST['amount'] ?? 0);

    $note =
        trim($_POST['note'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($amount <= 0) {

        $error = 'সঠিক টাকার পরিমাণ দিন!';

    } elseif ($paymentDate === '') {

        $error = 'পেমেন্টের তারিখ নির্বাচন করুন!';

    } elseif (
        $paymentType === 'monthly'
        && $monthYear === ''
    ) {

        $error = 'কোন মাসের কিস্তি তা নির্বাচন করুন!';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Duplicate / Over Payment Check
            |--------------------------------------------------------------------------
            */

            if ($paymentType === 'monthly') {

                $alreadyPaidStmt = $pdo->prepare("
                    SELECT COALESCE(SUM(amount), 0)
                    FROM metro_payments
                    WHERE metro_car_id = ?
                    AND payment_type = 'monthly'
                    AND month_year = ?
                ");

                $alreadyPaidStmt->execute([
                    $carId,
                    $monthYear
                ]);

                $alreadyPaid =
                    (float)$alreadyPaidStmt->fetchColumn();


                $remaining =
                    max(
                        0,
                        $monthlyAmount - $alreadyPaid
                    );


                if ($remaining <= 0) {

                    $error =
                        'এই মাসের ৳'
                        . number_format($monthlyAmount, 0)
                        . ' ইতোমধ্যে সম্পূর্ণ জমা হয়েছে!';

                } elseif ($amount > $remaining) {

                    $error =
                        'এই মাসে সর্বোচ্চ ৳'
                        . number_format($remaining, 0)
                        . ' জমা নেওয়া যাবে!';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Insert Payment
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                $pdo->beginTransaction();


                $stmtPayment = $pdo->prepare("
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
                        ?, ?, ?, ?, ?, ?
                    )
                ");


                $stmtPayment->execute([
                    $carId,
                    $paymentDate,
                    $monthYear,
                    $amount,
                    $paymentType,
                    $note
                ]);


                $pdo->commit();


                /*
                |--------------------------------------------------------------------------
                | Redirect
                |--------------------------------------------------------------------------
                */

            

                exit;
            }

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'পেমেন্ট সংরক্ষণ করা যায়নি: '
                . $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {
    $success = 'পেমেন্ট সফলভাবে জমা হয়েছে!';
}


/*
|--------------------------------------------------------------------------
| Updated Current Month Payment
|--------------------------------------------------------------------------
*/

$currentMonthStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
    AND payment_type = 'monthly'
    AND month_year = ?
");

$currentMonthStmt->execute([
    $carId,
    $currentMonth
]);

$currentMonthPaid =
    (float)$currentMonthStmt->fetchColumn();


$currentMonthDue = max(
    0,
    $monthlyAmount - $currentMonthPaid
);

?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-cash-stack text-success me-2"></i>

                পেমেন্ট জমা

            </h1>

            <p class="text-muted mb-0">

                <?= htmlspecialchars($car['car_number']) ?>

                - এর পেমেন্ট জমা করুন

            </p>

        </div>


        <a
            href="index.php?page=metro/index"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            গাড়ির তালিকা

        </a>

    </div>



    <!-- =========================================================
         ALERT
    ========================================================== -->

    <?php if ($error): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
        >

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
        >

            <i class="bi bi-check-circle me-2"></i>

            <?= htmlspecialchars($success) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <div class="row g-4">


        <!-- =====================================================
             LEFT : PAYMENT FORM
        ====================================================== -->

        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm">


                <div class="card-header bg-success text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-wallet2 me-2"></i>

                        টাকা জমা দিন

                    </h5>

                </div>


                <div class="card-body p-4">


                    <form method="POST">


                        <!-- Payment Type -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                পেমেন্টের ধরন

                            </label>


                            <div class="row g-2">


                                <div class="col-6">

                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="payment_type"
                                        id="monthlyPayment"
                                        value="monthly"
                                        checked
                                    >

                                    <label
                                        class="btn btn-outline-success w-100 py-3"
                                        for="monthlyPayment"
                                    >

                                        <i class="bi bi-calendar-check fs-5"></i>

                                        <br>

                                        মাসিক জমা

                                    </label>

                                </div>


                                <div class="col-6">

                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="payment_type"
                                        id="otherPayment"
                                        value="other"
                                    >

                                    <label
                                        class="btn btn-outline-primary w-100 py-3"
                                        for="otherPayment"
                                    >

                                        <i class="bi bi-cash fs-5"></i>

                                        <br>

                                        অন্যান্য

                                    </label>

                                </div>

                            </div>

                        </div>



                        <!-- Month -->

                        <div
                            class="mb-3"
                            id="monthBox"
                        >

                            <label class="form-label fw-semibold">

                                কোন মাসের কিস্তি?

                                <span class="text-danger">*</span>

                            </label>


                            <input
                                type="month"
                                name="month_year"
                                id="month_year"
                                class="form-control"
                                value="<?= date('Y-m') ?>"
                            >

                            <div class="form-text">

                                এই মাসের বকেয়া:
                                <strong class="text-danger">

                                    ৳ <?= bn_number(
                                        number_format(
                                            $currentMonthDue,
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </div>

                        </div>



                        <!-- Payment Date -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                পেমেন্টের তারিখ

                                <span class="text-danger">*</span>

                            </label>


                            <input
                                type="date"
                                name="payment_date"
                                class="form-control"
                                value="<?= date('Y-m-d') ?>"
                                required
                            >

                        </div>



                        <!-- Amount -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                জমার পরিমাণ

                                <span class="text-danger">*</span>

                            </label>


                            <div class="input-group input-group-lg">

                                <span class="input-group-text">

                                    ৳

                                </span>


                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    class="form-control"
                                    min="1"
                                    step="0.01"
                                    value="<?= $monthlyAmount ?>"
                                    required
                                >

                            </div>


                            <div
                                class="form-text"
                                id="amountHelp"
                            >

                                মাসিক কিস্তি:
                                <strong>

                                    ৳ <?= bn_number(
                                        number_format(
                                            $monthlyAmount,
                                            0
                                        )
                                    ) ?>

                                </strong>

                            </div>

                        </div>



                        <!-- Note -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                নোট

                            </label>


                            <textarea
                                name="note"
                                class="form-control"
                                rows="3"
                                placeholder="প্রয়োজনে কোনো তথ্য লিখুন..."
                            ></textarea>

                        </div>



                        <!-- Submit -->

                        <div class="d-flex justify-content-end gap-2">


                            <a
                                href="index.php?page=metro/view&id=<?= $carId ?>"
                                class="btn btn-light border"
                            >

                                বাতিল

                            </a>


                            <button
                                type="submit"
                                class="btn btn-success px-4"
                            >

                                <i class="bi bi-check-circle me-1"></i>

                                টাকা জমা দিন

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>



        <!-- =====================================================
             RIGHT : CAR INFO
        ====================================================== -->

        <div class="col-12 col-lg-4">


            <!-- Car Information -->

            <div class="card border-0 shadow-sm mb-4">


                <div class="card-header bg-primary text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-car-front me-2"></i>

                        গাড়ির তথ্য

                    </h5>

                </div>


                <div class="card-body">


                    <div class="text-center mb-4">

                        <div class="car-icon mx-auto mb-3">

                            <i class="bi bi-car-front-fill"></i>

                        </div>


                        <h4 class="mb-1">

                            <?= htmlspecialchars(
                                $car['car_number']
                            ) ?>

                        </h4>


                        <span class="badge bg-success">

                            চলমান

                        </span>

                    </div>



                    <div
                        class="d-flex justify-content-between border-bottom py-2"
                    >

                        <span class="text-muted">

                            চালক

                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $car['driver_name'] ?? '—'
                            ) ?>

                        </strong>

                    </div>



                    <div
                        class="d-flex justify-content-between border-bottom py-2"
                    >

                        <span class="text-muted">

                            মোবাইল

                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $car['mobile'] ?? '—'
                            ) ?>

                        </strong>

                    </div>



                    <div
                        class="d-flex justify-content-between border-bottom py-2"
                    >

                        <span class="text-muted">

                            প্রাথমিক জমা

                        </span>

                        <strong class="text-success">

                            ৳ <?= bn_number(
                                number_format(
                                    $initialDeposit,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>



                    <div
                        class="d-flex justify-content-between py-2"
                    >

                        <span class="text-muted">

                            মাসিক জমা

                        </span>

                        <strong class="text-primary">

                            ৳ <?= bn_number(
                                number_format(
                                    $monthlyAmount,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>

                </div>

            </div>



            <!-- Current Month -->

            <div class="card border-0 shadow-sm">


                <div class="card-header bg-warning py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-calendar3 me-2"></i>

                        এই মাসের হিসাব

                    </h5>

                </div>


                <div class="card-body">


                    <div class="d-flex justify-content-between mb-3">

                        <span class="text-muted">

                            মাসিক কিস্তি

                        </span>

                        <strong>

                            ৳ <?= bn_number(
                                number_format(
                                    $monthlyAmount,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-3">

                        <span class="text-muted">

                            এই মাসে জমা

                        </span>

                        <strong class="text-success">

                            ৳ <?= bn_number(
                                number_format(
                                    $currentMonthPaid,
                                    0
                                )
                            ) ?>

                        </strong>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <span class="fw-semibold">

                            বাকি

                        </span>


                        <?php if ($currentMonthDue > 0): ?>

                            <strong class="text-danger fs-5">

                                ৳ <?= bn_number(
                                    number_format(
                                        $currentMonthDue,
                                        0
                                    )
                                ) ?>

                            </strong>

                        <?php else: ?>

                            <span class="badge bg-success">

                                সম্পূর্ণ জমা

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| Payment Type
|--------------------------------------------------------------------------
*/

const monthlyPayment =
    document.getElementById('monthlyPayment');

const otherPayment =
    document.getElementById('otherPayment');

const monthBox =
    document.getElementById('monthBox');

const amount =
    document.getElementById('amount');


monthlyPayment.addEventListener(
    'change',
    function () {

        monthBox.style.display = 'block';

        amount.value =
            <?= $monthlyAmount ?>;

    }
);


otherPayment.addEventListener(
    'change',
    function () {

        monthBox.style.display = 'none';

        amount.value = '';

    }
);

</script>



<style>

.car-icon {

    width: 75px;
    height: 75px;

    border-radius: 50%;

    background: rgba(13,110,253,.10);

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 35px;

}


.form-control:focus {

    border-color: #86b7fe;

    box-shadow:
        0 0 0 .2rem
        rgba(13,110,253,.12);

}


.btn-check:checked + .btn {

    font-weight: 600;

}


@media (max-width: 768px) {

    .card-body {
        padding: 20px !important;
    }

}

</style>