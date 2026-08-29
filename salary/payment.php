<?php

/*
|--------------------------------------------------------------------------
| Salary Payment
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

$selectedEmployeeId = (int)($_GET['employee_id'] ?? $_POST['employee_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Employee List
|--------------------------------------------------------------------------
*/

$employeeStmt = $pdo->query("
    SELECT
        e.id,
        e.employee_name,
        e.mobile,
        e.designation,
        e.salary,
        e.garage_id,
        g.garage_name

    FROM employees e

    LEFT JOIN garages g
        ON g.id = e.garage_id

    WHERE e.status = 'active'

    ORDER BY e.employee_name ASC
");

$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Selected Employee
|--------------------------------------------------------------------------
*/

$selectedEmployee = null;

if ($selectedEmployeeId > 0) {

    $stmt = $pdo->prepare("
        SELECT
            e.*,
            g.garage_name

        FROM employees e

        LEFT JOIN garages g
            ON g.id = e.garage_id

        WHERE e.id = ?
        LIMIT 1
    ");

    $stmt->execute([$selectedEmployeeId]);

    $selectedEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Default Form Values
|--------------------------------------------------------------------------
*/

$salary_month  = $_POST['salary_month'] ?? date('Y-m-01');
$payment_date  = $_POST['payment_date'] ?? date('Y-m-d');

$basic_salary  = $_POST['basic_salary'] ?? '';
$bonus         = $_POST['bonus'] ?? '0';
$overtime      = $_POST['overtime'] ?? '0';
$advance       = $_POST['advance'] ?? '0';
$deduction     = $_POST['deduction'] ?? '0';
$paid_amount   = $_POST['paid_amount'] ?? '';

$payment_method = $_POST['payment_method'] ?? 'cash';
$note           = $_POST['note'] ?? '';


/*
|--------------------------------------------------------------------------
| Auto Basic Salary
|--------------------------------------------------------------------------
*/

if ($selectedEmployee && $basic_salary === '') {

    $basic_salary = $selectedEmployee['salary'];

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id = (int)($_POST['employee_id'] ?? 0);

    $basic_salary = (float)($_POST['basic_salary'] ?? 0);
    $bonus        = (float)($_POST['bonus'] ?? 0);
    $overtime     = (float)($_POST['overtime'] ?? 0);
    $advance      = (float)($_POST['advance'] ?? 0);
    $deduction    = (float)($_POST['deduction'] ?? 0);
    $paid_amount  = (float)($_POST['paid_amount'] ?? 0);

    $salary_month = $_POST['salary_month'] ?? '';
    $payment_date = $_POST['payment_date'] ?? '';

    $payment_method = $_POST['payment_method'] ?? 'cash';

    $note = trim($_POST['note'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($employee_id <= 0) {

        $error = 'কর্মচারী নির্বাচন করুন।';

    } elseif ($basic_salary <= 0) {

        $error = 'মূল বেতন সঠিকভাবে দিন।';

    } elseif ($salary_month === '') {

        $error = 'বেতনের মাস নির্বাচন করুন।';

    } elseif ($payment_date === '') {

        $error = 'পেমেন্টের তারিখ দিন।';

    } elseif ($paid_amount < 0) {

        $error = 'পরিশোধের টাকা সঠিকভাবে দিন।';

    } elseif (!in_array(
        $payment_method,
        ['cash', 'bank', 'mobile_banking'],
        true
    )) {

        $error = 'সঠিক Payment Method নির্বাচন করুন।';

    } else {


        /*
        |--------------------------------------------------------------------------
        | Total Salary
        |--------------------------------------------------------------------------
        */

        $total_salary =
            $basic_salary
            + $bonus
            + $overtime
            - $deduction;


        /*
        |--------------------------------------------------------------------------
        | Due
        |--------------------------------------------------------------------------
        */

        $due_amount =
            max(
                0,
                $total_salary - $paid_amount
            );


        /*
        |--------------------------------------------------------------------------
        | Duplicate Month Check
        |--------------------------------------------------------------------------
        */

        $checkStmt = $pdo->prepare("
            SELECT id
            FROM salary_payments
            WHERE employee_id = ?
            AND salary_month = ?
            LIMIT 1
        ");

        $checkStmt->execute([
            $employee_id,
            $salary_month
        ]);

        $existingPayment = $checkStmt->fetchColumn();


        if ($existingPayment) {

            $error =
                'এই কর্মচারীর এই মাসের বেতন ইতোমধ্যে এন্ট্রি করা হয়েছে।';

        } else {


            try {

                /*
                |--------------------------------------------------------------------------
                | Garage ID
                |--------------------------------------------------------------------------
                */

                $garageStmt = $pdo->prepare("
                    SELECT garage_id
                    FROM employees
                    WHERE id = ?
                    LIMIT 1
                ");

                $garageStmt->execute([
                    $employee_id
                ]);

                $garage_id =
                    $garageStmt->fetchColumn();


                /*
                |--------------------------------------------------------------------------
                | Insert
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    INSERT INTO salary_payments
                    (
                        employee_id,
                        garage_id,
                        salary_month,
                        basic_salary,
                        bonus,
                        overtime,
                        advance,
                        deduction,
                        total_salary,
                        paid_amount,
                        due_amount,
                        payment_date,
                        payment_method,
                        note
                    )
                    VALUES
                    (
                        :employee_id,
                        :garage_id,
                        :salary_month,
                        :basic_salary,
                        :bonus,
                        :overtime,
                        :advance,
                        :deduction,
                        :total_salary,
                        :paid_amount,
                        :due_amount,
                        :payment_date,
                        :payment_method,
                        :note
                    )
                ");

                $stmt->execute([

                    ':employee_id' =>
                        $employee_id,

                    ':garage_id' =>
                        $garage_id ?: null,

                    ':salary_month' =>
                        $salary_month,

                    ':basic_salary' =>
                        $basic_salary,

                    ':bonus' =>
                        $bonus,

                    ':overtime' =>
                        $overtime,

                    ':advance' =>
                        $advance,

                    ':deduction' =>
                        $deduction,

                    ':total_salary' =>
                        $total_salary,

                    ':paid_amount' =>
                        $paid_amount,

                    ':due_amount' =>
                        $due_amount,

                    ':payment_date' =>
                        $payment_date,

                    ':payment_method' =>
                        $payment_method,

                    ':note' =>
                        $note !== ''
                            ? $note
                            : null

                ]);


                /*
                |--------------------------------------------------------------------------
                | Success
                |--------------------------------------------------------------------------
                */

                $newPaymentId =
                    $pdo->lastInsertId();

                $success =
                    'বেতন সফলভাবে সংরক্ষণ করা হয়েছে।';


                /*
                |--------------------------------------------------------------------------
                | Reset
                |--------------------------------------------------------------------------
                */

                $selectedEmployeeId = 0;

                $selectedEmployee = null;

                $basic_salary = '';

                $bonus = '0';

                $overtime = '0';

                $advance = '0';

                $deduction = '0';

                $paid_amount = '';

                $note = '';


            } catch (PDOException $e) {

                $error =
                    'বেতন সংরক্ষণ করতে সমস্যা হয়েছে: '
                    . $e->getMessage();

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Recalculate Preview
|--------------------------------------------------------------------------
*/

$previewBasic =
    (float)($basic_salary ?: 0);

$previewBonus =
    (float)($bonus ?: 0);

$previewOvertime =
    (float)($overtime ?: 0);

$previewDeduction =
    (float)($deduction ?: 0);

$previewPaid =
    (float)($paid_amount ?: 0);


$previewTotal =
    $previewBasic
    + $previewBonus
    + $previewOvertime
    - $previewDeduction;


$previewDue =
    max(
        0,
        $previewTotal - $previewPaid
    );

?>



<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-cash-stack text-success me-2"></i>

                বেতন প্রদান

            </h1>

            <p class="text-muted mb-0">

                কর্মচারীর মাসিক বেতন প্রদান ও হিসাব সংরক্ষণ করুন

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

            <?php if (!empty($newPaymentId)): ?>

                <a
                    href="index.php?page=salary/receipt&id=<?= (int)$newPaymentId ?>"
                    class="alert-link ms-2"
                >
                    রসিদ দেখুন
                </a>

            <?php endif; ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- =========================================================
         MAIN FORM
    ========================================================== -->

    <div class="row g-4">


        <!-- =====================================================
             FORM
        ====================================================== -->

        <div class="col-lg-8">

            <div class="card shadow-sm border-0">


                <div
                    class="card-header bg-success text-white py-3"
                >

                    <h5 class="mb-0">

                        <i class="bi bi-wallet2 me-2"></i>

                        বেতন প্রদানের তথ্য

                    </h5>

                </div>



                <div class="card-body p-4">


                    <form
                        method="POST"
                        action=""
                        id="salaryForm"
                        autocomplete="off" action=""
                    >


                        <!-- Employee -->

                        <div class="mb-4">

                            <label class="form-label">

                                কর্মচারী

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <select
                                name="employee_id"
                                id="employee_id"
                                class="form-select form-select-lg"
                                required
                            >

                                <option value="">

                                    কর্মচারী নির্বাচন করুন

                                </option>


                                <?php foreach ($employees as $employee): ?>

                                    <option
                                        value="<?= (int)$employee['id'] ?>"
                                        data-salary="<?= htmlspecialchars($employee['salary']) ?>"
                                        data-garage="<?= htmlspecialchars($employee['garage_name'] ?? '') ?>"
                                        data-designation="<?= htmlspecialchars($employee['designation'] ?? '') ?>"
                                        <?= $selectedEmployeeId == $employee['id']
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        <?= htmlspecialchars(
                                            $employee['employee_name']
                                        ) ?>

                                        <?php if (!empty($employee['designation'])): ?>

                                            —
                                            <?= htmlspecialchars(
                                                $employee['designation']
                                            ) ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>



                        <!-- Employee Info -->

                        <div
                            id="employeeInfo"
                            class="employee-info mb-4"
                            style="<?= $selectedEmployee ? '' : 'display:none;' ?>"
                        >

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <div class="info-label">
                                        কর্মচারী
                                    </div>

                                    <div
                                        id="infoName"
                                        class="info-value"
                                    >

                                        <?= $selectedEmployee
                                            ? htmlspecialchars(
                                                $selectedEmployee['employee_name']
                                            )
                                            : ''
                                        ?>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="info-label">
                                        পদ
                                    </div>

                                    <div
                                        id="infoDesignation"
                                        class="info-value"
                                    >

                                        <?= $selectedEmployee
                                            ? htmlspecialchars(
                                                $selectedEmployee['designation'] ?? '—'
                                            )
                                            : ''
                                        ?>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="info-label">
                                        গ্যারেজ
                                    </div>

                                    <div
                                        id="infoGarage"
                                        class="info-value"
                                    >

                                        <?= $selectedEmployee
                                            ? htmlspecialchars(
                                                $selectedEmployee['garage_name'] ?? '—'
                                            )
                                            : ''
                                        ?>

                                    </div>

                                </div>

                            </div>

                        </div>



                        <div class="row g-3">


                            <!-- Salary Month -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    বেতনের মাস

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="month"
                                    name="salary_month"
                                    id="salary_month"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        date(
                                            'Y-m',
                                            strtotime($salary_month)
                                        )
                                    ) ?>"
                                    required
                                >

                            </div>



                            <!-- Payment Date -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    পেমেন্টের তারিখ

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="date"
                                    name="payment_date"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $payment_date
                                    ) ?>"
                                    required
                                >

                            </div>



                            <!-- Basic Salary -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    মূল বেতন

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
                                        id="basic_salary"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $basic_salary
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                        required
                                    >

                                </div>

                            </div>



                            <!-- Bonus -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Bonus

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        ৳
                                    </span>

                                    <input
                                        type="number"
                                        name="bonus"
                                        id="bonus"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $bonus
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>



                            <!-- Overtime -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Overtime

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        ৳
                                    </span>

                                    <input
                                        type="number"
                                        name="overtime"
                                        id="overtime"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $overtime
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>



                            <!-- Advance -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Advance

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        ৳
                                    </span>

                                    <input
                                        type="number"
                                        name="advance"
                                        id="advance"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $advance
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>



                            <!-- Deduction -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Deduction

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        ৳
                                    </span>

                                    <input
                                        type="number"
                                        name="deduction"
                                        id="deduction"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $deduction
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>



                            <!-- Paid -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    পরিশোধের টাকা

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
                                        name="paid_amount"
                                        id="paid_amount"
                                        class="form-control money-input"
                                        value="<?= htmlspecialchars(
                                            $paid_amount
                                        ) ?>"
                                        min="0"
                                        step="0.01"
                                        required
                                    >

                                </div>

                            </div>



                            <!-- Payment Method -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Payment Method

                                </label>

                                <select
                                    name="payment_method"
                                    class="form-select"
                                >

                                    <option
                                        value="cash"
                                        <?= $payment_method === 'cash'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        নগদ
                                    </option>

                                    <option
                                        value="bank"
                                        <?= $payment_method === 'bank'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        ব্যাংক
                                    </option>

                                    <option
                                        value="mobile_banking"
                                        <?= $payment_method === 'mobile_banking'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        মোবাইল ব্যাংকিং
                                    </option>

                                </select>

                            </div>



                            <!-- Note -->

                            <div class="col-12">

                                <label class="form-label">

                                    নোট

                                </label>

                                <textarea
                                    name="note"
                                    class="form-control"
                                    rows="3"
                                    placeholder="প্রয়োজন হলে নোট লিখুন"
                                ><?= htmlspecialchars(
                                    $note
                                ) ?></textarea>

                            </div>

                        </div>



                        <!-- Buttons -->

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
                                id="resetBtn"
                            >

                                <i class="bi bi-arrow-counterclockwise me-1"></i>

                                Reset

                            </button>


                            <button
                                type="submit"
                                class="btn btn-success"
                            >

                                <i class="bi bi-check-circle me-1"></i>

                                বেতন সংরক্ষণ

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>



        <!-- =====================================================
             SUMMARY
        ====================================================== -->

        <div class="col-lg-4">

            <div class="card shadow-sm border-0 sticky-summary">

                <div
                    class="card-header bg-primary text-white py-3"
                >

                    <h5 class="mb-0">

                        <i class="bi bi-calculator me-2"></i>

                        বেতন হিসাব

                    </h5>

                </div>


                <div class="card-body">


                    <div class="summary-row">

                        <span>
                            মূল বেতন
                        </span>

                        <strong>
                            ৳ <span id="viewBasic">
                                <?= number_format(
                                    $previewBasic,
                                    2
                                ) ?>
                            </span>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Bonus
                        </span>

                        <strong class="text-success">

                            + ৳ <span id="viewBonus">
                                <?= number_format(
                                    $previewBonus,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Overtime
                        </span>

                        <strong class="text-success">

                            + ৳ <span id="viewOvertime">
                                <?= number_format(
                                    $previewOvertime,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Deduction
                        </span>

                        <strong class="text-danger">

                            - ৳ <span id="viewDeduction">
                                <?= number_format(
                                    $previewDeduction,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                    <hr>


                    <div class="summary-total">

                        <span>
                            মোট বেতন
                        </span>

                        <strong>

                            ৳ <span id="viewTotal">
                                <?= number_format(
                                    $previewTotal,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                    <div class="summary-paid mt-3">

                        <span>
                            পরিশোধ
                        </span>

                        <strong>

                            ৳ <span id="viewPaid">
                                <?= number_format(
                                    $previewPaid,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                    <div class="summary-due mt-2">

                        <span>
                            বাকি
                        </span>

                        <strong>

                            ৳ <span id="viewDue">
                                <?= number_format(
                                    $previewDue,
                                    2
                                ) ?>
                            </span>

                        </strong>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| Employee Select
|--------------------------------------------------------------------------
*/

const employeeSelect =
    document.getElementById('employee_id');

const employeeInfo =
    document.getElementById('employeeInfo');

const infoName =
    document.getElementById('infoName');

const infoDesignation =
    document.getElementById('infoDesignation');

const infoGarage =
    document.getElementById('infoGarage');

const basicSalary =
    document.getElementById('basic_salary');


employeeSelect.addEventListener(
    'change',
    function () {

        const option =
            this.options[this.selectedIndex];


        if (!this.value) {

            employeeInfo.style.display =
                'none';

            return;

        }


        const salary =
            option.dataset.salary || 0;

        const garage =
            option.dataset.garage || '—';

        const designation =
            option.dataset.designation || '—';


        infoName.textContent =
            option.textContent.trim();

        infoDesignation.textContent =
            designation;

        infoGarage.textContent =
            garage;


        employeeInfo.style.display =
            'block';


        /*
        |--------------------------------------------------------------------------
        | Auto Basic Salary
        |--------------------------------------------------------------------------
        */

        basicSalary.value =
            salary;


        calculateSalary();

    }
);


/*
|--------------------------------------------------------------------------
| Calculate Salary
|--------------------------------------------------------------------------
*/

function moneyValue(id) {

    const element =
        document.getElementById(id);

    if (!element) {
        return 0;
    }

    return parseFloat(element.value) || 0;

}


function formatMoney(amount) {

    return amount.toLocaleString(
        'en-US',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}


function calculateSalary() {

    const basic =
        moneyValue('basic_salary');

    const bonus =
        moneyValue('bonus');

    const overtime =
        moneyValue('overtime');

    const deduction =
        moneyValue('deduction');

    const paid =
        moneyValue('paid_amount');


    const total =
        basic
        + bonus
        + overtime
        - deduction;


    const due =
        Math.max(
            0,
            total - paid
        );


    document.getElementById(
        'viewBasic'
    ).textContent =
        formatMoney(basic);


    document.getElementById(
        'viewBonus'
    ).textContent =
        formatMoney(bonus);


    document.getElementById(
        'viewOvertime'
    ).textContent =
        formatMoney(overtime);


    document.getElementById(
        'viewDeduction'
    ).textContent =
        formatMoney(deduction);


    document.getElementById(
        'viewTotal'
    ).textContent =
        formatMoney(total);


    document.getElementById(
        'viewPaid'
    ).textContent =
        formatMoney(paid);


    document.getElementById(
        'viewDue'
    ).textContent =
        formatMoney(due);

}


/*
|--------------------------------------------------------------------------
| Input Change
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.money-input')
    .forEach(function (input) {

        input.addEventListener(
            'input',
            calculateSalary
        );

    });


/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

document
    .getElementById('resetBtn')
    .addEventListener(
        'click',
        function () {

            setTimeout(
                calculateSalary,
                50
            );

        }
    );


/*
|--------------------------------------------------------------------------
| Initial Calculation
|--------------------------------------------------------------------------
*/

calculateSalary();

</script>



<style>

/*
|--------------------------------------------------------------------------
| Employee Info
|--------------------------------------------------------------------------
*/

.employee-info {

    background:
        rgba(13, 110, 253, .05);

    border:
        1px solid rgba(13, 110, 253, .12);

    border-radius: 10px;

    padding: 15px;

}


.info-label {

    color: #6b7280;

    font-size: 12px;

    margin-bottom: 4px;

}


.info-value {

    font-weight: 700;

    color: #111827;

}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

.form-label {

    font-weight: 600;

    color: #374151;

}


.form-control,
.form-select {

    min-height: 43px;

    border-radius: 7px;

}


textarea.form-control {

    min-height: 90px;

    resize: vertical;

}


.form-control:focus,
.form-select:focus {

    border-color: #86b7fe;

    box-shadow:
        0 0 0 .20rem rgba(13, 110, 253, .10);

}


/*
|--------------------------------------------------------------------------
| Salary Summary
|--------------------------------------------------------------------------
*/

.sticky-summary {

    position: sticky;

    top: 20px;

}


.summary-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 10px 0;

    border-bottom:
        1px dashed #e5e7eb;

}


.summary-row span {

    color: #6b7280;

}


.summary-total {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 13px;

    border-radius: 8px;

    background: #e8f5e9;

    color: #198754;

    font-size: 17px;

}


.summary-paid {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 11px;

    border-radius: 8px;

    background: #e0f2fe;

    color: #0369a1;

}


.summary-due {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 11px;

    border-radius: 8px;

    background: #fee2e2;

    color: #dc2626;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 991px) {

    .sticky-summary {

        position: static;

    }

}


@media (max-width: 768px) {

    .container-fluid {

        padding-left: 10px !important;

        padding-right: 10px !important;

    }

    .card-body {

        padding: 18px !important;

    }

}

</style>