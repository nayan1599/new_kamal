<?php

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


$monthlyAmount = (float)$car['monthly_amount'];
$initialDeposit = (float)$car['initial_deposit'];

$currentMonth = date('Y-m');


/*
|--------------------------------------------------------------------------
| এই মাসে কত জমা হয়েছে
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


/*
|--------------------------------------------------------------------------
| মোট জমা
|--------------------------------------------------------------------------
*/

$totalPaidStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM metro_payments
    WHERE metro_car_id = ?
");

$totalPaidStmt->execute([
    $carId
]);

$totalPaid =
    (float)$totalPaidStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

$success = isset($_GET['success']) && $_GET['success'] == 1;

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-cash-stack text-success me-2"></i>

                কিস্তি / টাকা জমা

            </h1>

            <p class="text-muted mb-0">

                <?= htmlspecialchars($car['car_number']) ?>

                - এর পেমেন্ট গ্রহণ

            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="index.php?page=metro/view&id=<?= $carId ?>"
                class="btn btn-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                বিস্তারিত
            </a>

            <a
                href="index.php?page=metro/index"
                class="btn btn-outline-primary"
            >
                <i class="bi bi-car-front me-1"></i>
                গাড়ির তালিকা
            </a>

        </div>

    </div>


    <!-- =========================================================
         SUCCESS MESSAGE
    ========================================================== -->

    <?php if ($success): ?>

        <div
            class="alert alert-success alert-dismissible fade show shadow-sm"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            <strong>সফল!</strong>

            পেমেন্ট সফলভাবে জমা হয়েছে।

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- =====================================================
             LEFT SIDE - CAR INFO
        ====================================================== -->

        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-car-front-fill me-2"></i>

                        গাড়ির তথ্য

                    </h5>

                </div>


                <div class="card-body">

                    <div class="text-center mb-4">

                        <div class="car-icon mx-auto mb-3">

                            <i class="bi bi-car-front-fill"></i>

                        </div>

                        <h4 class="fw-bold mb-1">

                            <?= htmlspecialchars(
                                $car['car_number']
                            ) ?>

                        </h4>

                        <span class="badge bg-success">

                            <?= $car['status'] === 'active'
                                ? 'চলমান'
                                : htmlspecialchars($car['status'])
                            ?>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="text-muted">

                            <i class="bi bi-person me-2"></i>

                            চালকের নাম

                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $car['driver_name'] ?? '—'
                            ) ?>

                        </strong>

                    </div>


                    <div class="info-row">

                        <span class="text-muted">

                            <i class="bi bi-telephone me-2"></i>

                            মোবাইল

                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $car['mobile'] ?? '—'
                            ) ?>

                        </strong>

                    </div>


                    <div class="info-row">

                        <span class="text-muted">

                            <i class="bi bi-calendar3 me-2"></i>

                            গাড়ি নেওয়ার তারিখ

                        </span>

                        <strong>

                            <?= bn_number(
                                date(
                                    'd/m/Y',
                                    strtotime(
                                        $car['start_date']
                                    )
                                )
                            ) ?>

                        </strong>

                    </div>


                    <hr>


                    <div class="payment-info">

                        <div>

                            <small class="text-muted">

                                প্রাথমিক জমা

                            </small>

                            <h5 class="text-primary mb-0">

                                ৳ <?= bn_number(
                                    number_format(
                                        $initialDeposit,
                                        0
                                    )
                                ) ?>

                            </h5>

                        </div>


                        <div>

                            <small class="text-muted">

                                মাসিক কিস্তি

                            </small>

                            <h5 class="text-success mb-0">

                                ৳ <?= bn_number(
                                    number_format(
                                        $monthlyAmount,
                                        0
                                    )
                                ) ?>

                            </h5>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Current Month -->

            <div class="card border-0 shadow-sm mt-4">

                <div class="card-body">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-calendar-check text-warning me-2"></i>

                        <?= bn_number(
                            date('m/Y')
                        ) ?>

                        মাসের হিসাব

                    </h6>


                    <div class="d-flex justify-content-between mb-2">

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


                    <div class="d-flex justify-content-between mb-2">

                        <span class="text-muted">

                            জমা হয়েছে

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

                        <span class="fw-bold">

                            এই মাসে বাকি

                        </span>


                        <?php if ($currentMonthDue > 0): ?>

                            <strong class="text-danger">

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



        <!-- =====================================================
             RIGHT SIDE - PAYMENT FORM
        ====================================================== -->

        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-success text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-wallet2 me-2"></i>

                        নতুন পেমেন্ট জমা দিন

                    </h5>

                </div>


                <div class="card-body p-4">


                    <!-- IMPORTANT:
                         action রাখা হয়েছে
                    -->

                    <form
                        method="POST"
                        action="index.php?page=sql/payment_add"
                    >


                        <!-- গাড়ির ID -->

                        <input
                            type="hidden"
                            name="metro_car_id"
                            value="<?= $carId ?>"
                        >


                        <div class="row g-3">


                            <!-- Payment Type -->

                            <div class="col-12 col-md-6">

                                <label class="form-label fw-semibold">

                                    পেমেন্টের ধরন
                                    <span class="text-danger">*</span>

                                </label>


                                <select
                                    name="payment_type"
                                    id="payment_type"
                                    class="form-select form-select-lg"
                                    required
                                >

                                    <option value="monthly" selected>

                                        মাসিক কিস্তি

                                    </option>

                                    <option value="initial">

                                        প্রাথমিক জমা

                                    </option>

                                    <option value="other">

                                        অন্যান্য

                                    </option>

                                </select>

                            </div>



                            <!-- Payment Date -->

                            <div class="col-12 col-md-6">

                                <label class="form-label fw-semibold">

                                    পেমেন্টের তারিখ
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="date"
                                    name="payment_date"
                                    class="form-control form-control-lg"
                                    value="<?= date('Y-m-d') ?>"
                                    required
                                >

                            </div>



                            <!-- Month -->

                            <div
                                class="col-12 col-md-6"
                                id="monthBox"
                            >

                                <label class="form-label fw-semibold">

                                    কোন মাসের কিস্তি
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="month"
                                    name="month_year"
                                    id="month_year"
                                    class="form-control form-control-lg"
                                    value="<?= date('Y-m') ?>"
                                >

                                <div class="form-text">

                                    যে মাসের কিস্তি জমা দিচ্ছেন সেই মাস নির্বাচন করুন।

                                </div>

                            </div>



                            <!-- Amount -->

                            <div class="col-12 col-md-6">

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
                                        placeholder="টাকার পরিমাণ"
                                        required
                                    >

                                </div>


                                <div
                                    class="form-text"
                                    id="amountHelp"
                                >

                                    এই মাসের বাকি:

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



                            <!-- Note -->

                            <div class="col-12">

                                <label class="form-label fw-semibold">

                                    নোট

                                </label>

                                <textarea
                                    name="note"
                                    class="form-control"
                                    rows="3"
                                    placeholder="কোনো মন্তব্য বা নোট থাকলে লিখুন..."
                                ></textarea>

                            </div>



                            <!-- Summary -->

                            <div class="col-12">

                                <div class="payment-summary">

                                    <div>

                                        <small class="text-muted">

                                            গাড়ি

                                        </small>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $car['car_number']
                                            ) ?>

                                        </strong>

                                    </div>


                                    <div>

                                        <small class="text-muted">

                                            মাসিক কিস্তি

                                        </small>

                                        <strong class="text-success">

                                            ৳ <?= bn_number(
                                                number_format(
                                                    $monthlyAmount,
                                                    0
                                                )
                                            ) ?>

                                        </strong>

                                    </div>


                                    <div>

                                        <small class="text-muted">

                                            এই মাসে জমা

                                        </small>

                                        <strong class="text-primary">

                                            ৳ <?= bn_number(
                                                number_format(
                                                    $currentMonthPaid,
                                                    0
                                                )
                                            ) ?>

                                        </strong>

                                    </div>

                                </div>

                            </div>



                            <!-- Submit -->

                            <div class="col-12">

                                <hr class="my-2">

                                <div
                                    class="d-flex justify-content-end gap-2"
                                >

                                    <a
                                        href="index.php?page=metro/view&id=<?= $carId ?>"
                                        class="btn btn-light btn-lg px-4"
                                    >

                                        বাতিল

                                    </a>


                                    <button
                                        type="submit"
                                        class="btn btn-success btn-lg px-5"
                                    >

                                        <i class="bi bi-check-circle me-2"></i>

                                        টাকা জমা দিন

                                    </button>

                                </div>

                            </div>


                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>



<style>

.car-icon {

    width: 90px;

    height: 90px;

    border-radius: 50%;

    background: rgba(13, 110, 253, .10);

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 45px;

}


.card {

    border-radius: 14px;

}


.info-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 11px 0;

    border-bottom: 1px solid #eee;

    gap: 10px;

}


.info-row:last-child {

    border-bottom: 0;

}


.payment-info {

    display: flex;

    justify-content: space-between;

    gap: 20px;

}


.payment-summary {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    background: #f8f9fa;

    border: 1px solid #e9ecef;

    border-radius: 10px;

    padding: 18px;

}


.payment-summary div {

    display: flex;

    flex-direction: column;

    gap: 4px;

}


@media (max-width: 767px) {

    .payment-summary {

        grid-template-columns: 1fr;

    }

    .payment-info {

        flex-direction: column;

    }

}

@media print {

    .btn,
    form {

        display: none !important;

    }

}

</style>



<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const paymentType =
            document.getElementById(
                'payment_type'
            );

        const monthBox =
            document.getElementById(
                'monthBox'
            );

        const monthYear =
            document.getElementById(
                'month_year'
            );


        function toggleMonth() {

            if (
                paymentType.value === 'monthly'
            ) {

                monthBox.style.display =
                    '';

                monthYear.required =
                    true;

            } else {

                monthBox.style.display =
                    'none';

                monthYear.required =
                    false;

            }

        }


        paymentType.addEventListener(
            'change',
            toggleMonth
        );


        toggleMonth();

    }
);

</script>