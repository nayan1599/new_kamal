<?php

/*
|--------------------------------------------------------------------------
| Employee Edit
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            কর্মচারীর ID পাওয়া যায়নি।
        </div>
    ';

    return;
}


$employeeId = (int)$_GET['id'];


/*
|--------------------------------------------------------------------------
| Employee Data
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM employees
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$employeeId]);

$employee = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$employee) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-person-x me-2"></i>
            কর্মচারীর তথ্য পাওয়া যায়নি।
        </div>
    ';

    return;
}


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
| Update
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employeeName = trim(
        $_POST['employee_name'] ?? ''
    );

    $mobile = trim(
        $_POST['mobile'] ?? ''
    );

    $designation = trim(
        $_POST['designation'] ?? ''
    );

    $garageId = !empty($_POST['garage_id'])
        ? (int)$_POST['garage_id']
        : null;

    $basicSalary = (float)(
        $_POST['basic_salary'] ?? 0
    );

    $joiningDate =
        $_POST['joining_date'] ?? null;

    $address = trim(
        $_POST['address'] ?? ''
    );

    $status =
        $_POST['status'] ?? 'active';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($employeeName === '') {

        $error = 'কর্মচারীর নাম লিখুন।';

    } elseif ($basicSalary < 0) {

        $error = 'বেতন সঠিকভাবে লিখুন।';

    } else {

        try {

            $update = $pdo->prepare("
                UPDATE employees
                SET
                    employee_name = :employee_name,
                    mobile = :mobile,
                    designation = :designation,
                    garage_id = :garage_id,
                    basic_salary = :basic_salary,
                    joining_date = :joining_date,
                    address = :address,
                    status = :status
                WHERE id = :id
            ");


            $update->execute([

                ':employee_name'
                    => $employeeName,

                ':mobile'
                    => $mobile ?: null,

                ':designation'
                    => $designation ?: null,

                ':garage_id'
                    => $garageId,

                ':basic_salary'
                    => $basicSalary,

                ':joining_date'
                    => $joiningDate ?: null,

                ':address'
                    => $address ?: null,

                ':status'
                    => $status,

                ':id'
                    => $employeeId

            ]);


            $success =
                'কর্মচারীর তথ্য সফলভাবে আপডেট হয়েছে।';


            /*
            |--------------------------------------------------------------------------
            | Reload Data
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT *
                FROM employees
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $employeeId
            ]);

            $employee =
                $stmt->fetch(PDO::FETCH_ASSOC);


        } catch (PDOException $e) {

            $error =
                'তথ্য আপডেট করা যায়নি: '
                . $e->getMessage();

        }

    }

}

?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i
                    class="bi bi-person-gear text-primary me-2"
                ></i>

                কর্মচারীর তথ্য সম্পাদনা

            </h1>

            <p class="text-muted mb-0">

                কর্মচারীর তথ্য পরিবর্তন ও আপডেট করুন

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=employee/index"
                class="btn btn-outline-secondary"
            >

                <i
                    class="bi bi-arrow-left me-1"
                ></i>

                কর্মচারী তালিকা

            </a>


            <a
                href="index.php?page=employee/add"
                class="btn btn-success"
            >

                <i
                    class="bi bi-person-plus me-1"
                ></i>

                নতুন কর্মচারী

            </a>

        </div>

    </div>



    <!-- =========================================================
         SUCCESS
    ========================================================== -->

    <?php if ($success): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
        >

            <i
                class="bi bi-check-circle-fill me-2"
            ></i>

            <?= htmlspecialchars($success) ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- =========================================================
         ERROR
    ========================================================== -->

    <?php if ($error): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
        >

            <i
                class="bi bi-exclamation-triangle-fill me-2"
            ></i>

            <?= htmlspecialchars($error) ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- =========================================================
         FORM
    ========================================================== -->

    <div class="card border-0 shadow-sm">


        <div
            class="card-header bg-primary text-white py-3"
        >

            <h5 class="mb-0">

                <i
                    class="bi bi-pencil-square me-2"
                ></i>

                কর্মচারীর তথ্য

            </h5>

        </div>



        <div class="card-body p-4">


            <form
                method="POST"
                action="index.php?page=employee/edit&id=<?= $employeeId ?>"
            >


                <div class="row g-4">


                    <!-- =================================================
                         NAME
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            কর্মচারীর নাম

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i
                                    class="bi bi-person"
                                ></i>

                            </span>


                            <input
                                type="text"
                                name="employee_name"
                                class="form-control"
                                placeholder="কর্মচারীর নাম"
                                value="<?= htmlspecialchars(
                                    $employee['employee_name']
                                    ?? ''
                                ) ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- =================================================
                         MOBILE
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            মোবাইল নম্বর

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i
                                    class="bi bi-telephone"
                                ></i>

                            </span>


                            <input
                                type="text"
                                name="mobile"
                                class="form-control"
                                placeholder="01XXXXXXXXX"
                                value="<?= htmlspecialchars(
                                    $employee['mobile']
                                    ?? ''
                                ) ?>"
                            >

                        </div>

                    </div>



                    <!-- =================================================
                         DESIGNATION
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            পদ / দায়িত্ব

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i
                                    class="bi bi-briefcase"
                                ></i>

                            </span>


                            <input
                                type="text"
                                name="designation"
                                class="form-control"
                                placeholder="যেমন: ড্রাইভার, ম্যানেজার"
                                value="<?= htmlspecialchars(
                                    $employee['designation']
                                    ?? ''
                                ) ?>"
                            >

                        </div>

                    </div>



                    <!-- =================================================
                         GARAGE
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            গ্যারেজ

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i
                                    class="bi bi-building"
                                ></i>

                            </span>


                            <select
                                name="garage_id"
                                class="form-select"
                            >

                                <option value="">

                                    গ্যারেজ নির্বাচন করুন

                                </option>


                                <?php foreach (
                                    $garages
                                    as $garage
                                ): ?>

                                    <option
                                        value="<?= (int)$garage['id'] ?>"
                                        <?= (
                                            (int)(
                                                $employee['garage_id']
                                                ?? 0
                                            )
                                            ===
                                            (int)$garage['id']
                                        )
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

                    </div>



                    <!-- =================================================
                         SALARY
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

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
                                name="basic_salary"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="মাসিক বেতন"
                                value="<?= htmlspecialchars(
                                    $employee['basic_salary']
                                    ?? 0
                                ) ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- =================================================
                         JOINING DATE
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            যোগদানের তারিখ

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i
                                    class="bi bi-calendar3"
                                ></i>

                            </span>


                            <input
                                type="date"
                                name="joining_date"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    $employee['joining_date']
                                    ?? ''
                                ) ?>"
                            >

                        </div>

                    </div>



                    <!-- =================================================
                         STATUS
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            স্ট্যাটাস

                        </label>


                        <select
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="active"
                                <?= (
                                    ($employee['status']
                                        ?? 'active')
                                    === 'active'
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                সক্রিয়

                            </option>


                            <option
                                value="inactive"
                                <?= (
                                    ($employee['status']
                                        ?? '')
                                    === 'inactive'
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                নিষ্ক্রিয়

                            </option>

                        </select>

                    </div>



                    <!-- =================================================
                         ADDRESS
                    ================================================== -->

                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            ঠিকানা

                        </label>


                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="কর্মচারীর ঠিকানা লিখুন..."
                        ><?= htmlspecialchars(
                            $employee['address']
                            ?? ''
                        ) ?></textarea>

                    </div>


                </div>



                <!-- =================================================
                     BUTTONS
                ================================================== -->

                <hr class="my-4">


                <div
                    class="d-flex justify-content-end gap-2"
                >

                    <a
                        href="index.php?page=employee/index"
                        class="btn btn-light border"
                    >

                        <i
                            class="bi bi-x-lg me-1"
                        ></i>

                        বাতিল

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary px-4"
                    >

                        <i
                            class="bi bi-check-lg me-1"
                        ></i>

                        তথ্য আপডেট করুন

                    </button>

                </div>


            </form>

        </div>

    </div>



    <!-- =========================================================
         EMPLOYEE INFO
    ========================================================== -->

    <div class="card border-0 shadow-sm mt-4">


        <div class="card-body">

            <div class="row align-items-center">


                <div class="col-md-8">

                    <div class="d-flex align-items-center">

                        <div class="employee-avatar me-3">

                            <i
                                class="bi bi-person-fill"
                            ></i>

                        </div>


                        <div>

                            <h5 class="mb-1">

                                <?= htmlspecialchars(
                                    $employee['employee_name']
                                ) ?>

                            </h5>


                            <div class="text-muted">

                                Employee ID:

                                <strong>

                                    #<?= (int)$employeeId ?>

                                </strong>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    <?php if (
                        ($employee['status']
                            ?? 'active')
                        === 'active'
                    ): ?>

                        <span
                            class="badge bg-success px-3 py-2"
                        >

                            <i
                                class="bi bi-check-circle me-1"
                            ></i>

                            সক্রিয়

                        </span>

                    <?php else: ?>

                        <span
                            class="badge bg-secondary px-3 py-2"
                        >

                            <i
                                class="bi bi-dash-circle me-1"
                            ></i>

                            নিষ্ক্রিয়

                        </span>

                    <?php endif; ?>

                </div>


            </div>

        </div>

    </div>


</div>



<style>

/*
|--------------------------------------------------------------------------
| Card
|--------------------------------------------------------------------------
*/

.card {

    border-radius: 10px;

}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

.form-label {

    color: #374151;

}


.form-control,
.form-select {

    min-height: 44px;

}


.input-group-text {

    background: #f8f9fa;

    min-width: 44px;

    justify-content: center;

}


/*
|--------------------------------------------------------------------------
| Avatar
|--------------------------------------------------------------------------
*/

.employee-avatar {

    width: 55px;

    height: 55px;

    border-radius: 50%;

    background:
        rgba(13, 110, 253, .1);

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

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

}

</style>