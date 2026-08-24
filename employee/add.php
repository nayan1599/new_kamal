<?php

/*
|--------------------------------------------------------------------------
| Add Employee
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| Garage List
|--------------------------------------------------------------------------
*/

$garageStmt = $pdo->query("
    SELECT id, garage_name
    FROM garages
    ORDER BY garage_name ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Form Submit
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_name = trim($_POST['employee_name'] ?? '');
    $mobile        = trim($_POST['mobile'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $designation   = trim($_POST['designation'] ?? '');
    $garage_id     = (int)($_POST['garage_id'] ?? 0);
    $joining_date  = $_POST['joining_date'] ?? '';
    $salary        = (float)($_POST['salary'] ?? 0);
    $status        = $_POST['status'] ?? 'active';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($employee_name === '') {

        $error = 'কর্মচারীর নাম লিখুন।';

    } elseif ($salary <= 0) {

        $error = 'মাসিক বেতন সঠিকভাবে লিখুন।';

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $error = 'সঠিক Status নির্বাচন করুন।';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Insert Employee
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO employees
                (
                    garage_id,
                    employee_name,
                    mobile,
                    address,
                    designation,
                    joining_date,
                    salary,
                    status
                )
                VALUES
                (
                    :garage_id,
                    :employee_name,
                    :mobile,
                    :address,
                    :designation,
                    :joining_date,
                    :salary,
                    :status
                )
            ");


            $stmt->execute([

                ':garage_id' =>
                    $garage_id > 0
                        ? $garage_id
                        : null,

                ':employee_name' =>
                    $employee_name,

                ':mobile' =>
                    $mobile !== ''
                        ? $mobile
                        : null,

                ':address' =>
                    $address !== ''
                        ? $address
                        : null,

                ':designation' =>
                    $designation !== ''
                        ? $designation
                        : null,

                ':joining_date' =>
                    $joining_date !== ''
                        ? $joining_date
                        : null,

                ':salary' =>
                    $salary,

                ':status' =>
                    $status

            ]);


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            $success = 'কর্মচারী সফলভাবে যোগ করা হয়েছে।';


            /*
            |--------------------------------------------------------------------------
            | Clear Form
            |--------------------------------------------------------------------------
            */

            $employee_name = '';
            $mobile        = '';
            $address       = '';
            $designation   = '';
            $garage_id     = 0;
            $joining_date  = date('Y-m-d');
            $salary        = '';
            $status        = 'active';


        } catch (PDOException $e) {

            $error = 'কর্মচারী যোগ করতে সমস্যা হয়েছে: ' . $e->getMessage();

        }

    }

}

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-person-plus-fill text-primary me-2"></i>

                নতুন কর্মচারী

            </h1>

            <p class="text-muted mb-0">

                নতুন কর্মচারীর তথ্য ও মাসিক বেতন যোগ করুন

            </p>

        </div>


        <a
            href="index.php?page=employee/list"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left me-2"></i>

            কর্মচারী তালিকা

        </a>

    </div>



    <!-- =========================================================
         ALERT
    ========================================================== -->

    <?php if ($error !== ''): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <?php if ($success !== ''): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            <?= htmlspecialchars($success) ?>

            <a
                href="index.php?page=employee/list"
                class="alert-link ms-2"
            >
                কর্মচারী তালিকা দেখুন
            </a>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- =========================================================
         FORM CARD
    ========================================================== -->

    <div class="card shadow-sm border-0">

        <div
            class="card-header bg-primary text-white py-3"
        >

            <h5 class="mb-0">

                <i class="bi bi-person-vcard me-2"></i>

                কর্মচারীর তথ্য

            </h5>

        </div>



        <div class="card-body p-4">


            <form
                method="POST"
                action=""
                autocomplete="off"
            >


                <!-- =================================================
                     PERSONAL INFORMATION
                ================================================== -->

                <div class="section-title mb-3">

                    <i class="bi bi-person-fill me-2"></i>

                    ব্যক্তিগত তথ্য

                </div>


                <div class="row g-3">


                    <!-- Employee Name -->

                    <div class="col-md-6">

                        <label class="form-label">

                            কর্মচারীর নাম

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            name="employee_name"
                            class="form-control"
                            value="<?= htmlspecialchars($employee_name ?? '') ?>"
                            placeholder="কর্মচারীর নাম লিখুন"
                            required
                        >

                    </div>



                    <!-- Mobile -->

                    <div class="col-md-6">

                        <label class="form-label">

                            মোবাইল নম্বর

                        </label>

                        <input
                            type="text"
                            name="mobile"
                            class="form-control"
                            value="<?= htmlspecialchars($mobile ?? '') ?>"
                            placeholder="01XXXXXXXXX"
                        >

                    </div>



                    <!-- Designation -->

                    <div class="col-md-6">

                        <label class="form-label">

                            পদ

                        </label>

                        <input
                            type="text"
                            name="designation"
                            class="form-control"
                            value="<?= htmlspecialchars($designation ?? '') ?>"
                            placeholder="যেমন: ড্রাইভার / হেলপার / ম্যানেজার"
                        >

                    </div>



                    <!-- Garage -->

                    <div class="col-md-6">

                        <label class="form-label">

                            গ্যারেজ

                        </label>

                        <select
                            name="garage_id"
                            class="form-select"
                        >

                            <option value="0">

                                গ্যারেজ নির্বাচন করুন

                            </option>


                            <?php foreach ($garages as $garage): ?>

                                <option
                                    value="<?= (int)$garage['id'] ?>"
                                    <?= (int)($garage_id ?? 0) === (int)$garage['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $garage['garage_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>



                    <!-- Address -->

                    <div class="col-12">

                        <label class="form-label">

                            ঠিকানা

                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="কর্মচারীর ঠিকানা লিখুন"
                        ><?= htmlspecialchars($address ?? '') ?></textarea>

                    </div>

                </div>



                <hr class="my-4">



                <!-- =================================================
                     SALARY INFORMATION
                ================================================== -->

                <div class="section-title mb-3">

                    <i class="bi bi-cash-stack me-2"></i>

                    বেতন সংক্রান্ত তথ্য

                </div>


                <div class="row g-3">


                    <!-- Joining Date -->

                    <div class="col-md-4">

                        <label class="form-label">

                            যোগদানের তারিখ

                        </label>

                        <input
                            type="date"
                            name="joining_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $joining_date ?? date('Y-m-d')
                            ) ?>"
                        >

                    </div>



                    <!-- Salary -->

                    <div class="col-md-4">

                        <label class="form-label">

                            মাসিক বেতন

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                ৳
                            </span>

                            <input
                                type="number"
                                name="salary"
                                class="form-control"
                                value="<?= htmlspecialchars($salary ?? '') ?>"
                                placeholder="18000"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>



                    <!-- Status -->

                    <div class="col-md-4">

                        <label class="form-label">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="active"
                                <?= ($status ?? 'active') === 'active'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                সক্রিয়
                            </option>

                            <option
                                value="inactive"
                                <?= ($status ?? '') === 'inactive'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                নিষ্ক্রিয়
                            </option>

                        </select>

                    </div>

                </div>



                <!-- =================================================
                     FORM BUTTONS
                ================================================== -->

                <div
                    class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top"
                >

                    <a
                        href="index.php?page=employee/list"
                        class="btn btn-secondary"
                    >

                        <i class="bi bi-x-circle me-1"></i>

                        বাতিল

                    </a>


                    <button
                        type="reset"
                        class="btn btn-outline-warning"
                    >

                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                        Reset

                    </button>


                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        <i class="bi bi-check-circle me-1"></i>

                        কর্মচারী সংরক্ষণ

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>



<style>

/*
|--------------------------------------------------------------------------
| Section Title
|--------------------------------------------------------------------------
*/

.section-title {

    font-size: 16px;

    font-weight: 700;

    color: #0d6efd;

    padding-bottom: 8px;

    border-bottom: 2px solid rgba(13, 110, 253, .10);

}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

.form-label {

    font-weight: 600;

    color: #374151;

    margin-bottom: 6px;

}

.form-control,
.form-select {

    border-radius: 7px;

    min-height: 43px;

}

textarea.form-control {

    min-height: 100px;

    resize: vertical;

}

.input-group .form-control {

    min-height: 43px;

}


/*
|--------------------------------------------------------------------------
| Focus
|--------------------------------------------------------------------------
*/

.form-control:focus,
.form-select:focus {

    border-color: #86b7fe;

    box-shadow:
        0 0 0 .20rem rgba(13, 110, 253, .10);

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .container-fluid {

        padding-left: 10px !important;

        padding-right: 10px !important;

    }

    .card-body {

        padding: 18px !important;

    }

    .d-flex.justify-content-between {

        align-items: flex-start !important;

        gap: 12px;

    }

}

</style>