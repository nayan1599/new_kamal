 <?php

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


<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                <i class="bi bi-car-front-fill text-primary me-2"></i>
                নতুন গাড়ি যোগ করুন
            </h1>

            <p class="text-muted mb-0">
                নতুন মেট্রো গাড়ির তথ্য সংরক্ষণ করুন
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


    <!-- Error -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="row justify-content-center">

        <div class="col-12 col-lg-8 col-xl-7">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-pencil-square me-2"></i>

                        গাড়ির তথ্য

                    </h5>

                </div>


                <div class="card-body p-4">

                    <form method="POST">


                        <!-- গাড়ির নম্বর -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                গাড়ির নম্বর
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-car-front"></i>
                                </span>

                                <input
                                    type="text"
                                    name="car_number"
                                    class="form-control"
                                    placeholder="যেমন: ঢাকা মেট্রো-গ ১২-৩৪৫৬"
                                    value="<?= htmlspecialchars($_POST['car_number'] ?? '') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- চালকের নাম -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                চালকের নাম

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>

                                <input
                                    type="text"
                                    name="driver_name"
                                    class="form-control"
                                    placeholder="চালকের নাম"
                                    value="<?= htmlspecialchars($_POST['driver_name'] ?? '') ?>"
                                >

                            </div>

                        </div>


                        <!-- মোবাইল -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                মোবাইল নম্বর

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>

                                <input
                                    type="text"
                                    name="mobile"
                                    class="form-control"
                                    placeholder="01XXXXXXXXX"
                                    value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
                                >

                            </div>

                        </div>


                        <!-- গাড়ি নেওয়ার তারিখ -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                গাড়ি নেওয়ার তারিখ
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-calendar3"></i>
                                </span>

                                <input
                                    type="date"
                                    name="start_date"
                                    class="form-control"
                                    value="<?= $_POST['start_date'] ?? date('Y-m-d') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- টাকা -->

                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <div class="border rounded p-3 bg-light">

                                    <small class="text-muted">
                                        গাড়ি নেওয়ার সময় জমা
                                    </small>

                                    <h4 class="text-success mb-0 mt-1">

                                        ৳ ১,০০,০০০

                                    </h4>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="border rounded p-3 bg-light">

                                    <small class="text-muted">
                                        প্রতি মাসে জমা
                                    </small>

                                    <h4 class="text-primary mb-0 mt-1">

                                        ৳ ২৫,০০০

                                    </h4>

                                </div>

                            </div>

                        </div>


                        <!-- Buttons -->

                        <div class="d-flex justify-content-end gap-2">

                            <a
                                href="index.php?page=metro/index"
                                class="btn btn-light border"
                            >
                                বাতিল
                            </a>

                            <button
                                type="submit"
                                class="btn btn-success px-4"
                            >

                                <i class="bi bi-check-circle me-1"></i>

                                গাড়ি যোগ করুন

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>