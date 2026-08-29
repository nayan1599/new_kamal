<?php


$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id     = (int)($_POST['employee_id'] ?? 0);
    $attendance_date = $_POST['attendance_date'] ?? date('Y-m-d');
    $status          = $_POST['status'] ?? 'present';
    $check_in        = !empty($_POST['check_in']) ? $_POST['check_in'] : null;
    $check_out       = !empty($_POST['check_out']) ? $_POST['check_out'] : null;
    $note            = trim($_POST['note'] ?? '');

    if ($employee_id <= 0) {
        $message = 'Employee নির্বাচন করুন।';
        $message_type = 'danger';
    } elseif (empty($attendance_date)) {
        $message = 'Attendance date নির্বাচন করুন।';
        $message_type = 'danger';
    } else {

        try {

            // আগে থেকে attendance আছে কিনা check
            $check = $pdo->prepare("
                SELECT id 
                FROM employee_attendance
                WHERE employee_id = ?
                AND attendance_date = ?
                LIMIT 1
            ");

            $check->execute([
                $employee_id,
                $attendance_date
            ]);

            if ($check->fetch()) {

                $message = 'এই Employee-এর এই তারিখের Attendance আগে থেকেই দেওয়া আছে।';
                $message_type = 'warning';

            } else {

                $stmt = $pdo->prepare("
                    INSERT INTO employee_attendance
                    (
                        employee_id,
                        attendance_date,
                        status,
                        check_in,
                        check_out,
                        note
                    )
                    VALUES
                    (
                        :employee_id,
                        :attendance_date,
                        :status,
                        :check_in,
                        :check_out,
                        :note
                    )
                ");

                $stmt->execute([
                    ':employee_id'     => $employee_id,
                    ':attendance_date' => $attendance_date,
                    ':status'          => $status,
                    ':check_in'        => $check_in,
                    ':check_out'       => $check_out,
                    ':note'            => $note ?: null
                ]);

                $message = 'Attendance সফলভাবে সংরক্ষণ হয়েছে।';
                $message_type = 'success';

            }

        } catch (PDOException $e) {

            $message = 'Attendance সংরক্ষণ করতে সমস্যা হয়েছে: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}


// Employee list
$employees = $pdo->query("
    SELECT id, employee_name, mobile
    FROM employees
    ORDER BY employee_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">

    <title>Employee Attendance</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fb;
        }

        .attendance-card {
            max-width: 750px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }

        .card-header-custom {
            background: #0d6efd;
            color: #fff;
            padding: 18px 22px;
        }

        .card-header-custom h4 {
            margin: 0;
            font-weight: 600;
        }

        .form-area {
            padding: 25px;
        }

        .form-label {
            font-weight: 600;
        }
    </style>

</head>

<body>

<div class="container">

    <div class="attendance-card">

        <div class="card-header-custom">
            <h4>👤 Employee Attendance</h4>
        </div>

        <div class="form-area">

            <?php if ($message): ?>

                <div class="alert alert-<?= htmlspecialchars($message_type) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <!-- Employee -->
                <div class="mb-3">

                    <label class="form-label">
                        Employee <span class="text-danger">*</span>
                    </label>

                    <select
                        name="employee_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Employee নির্বাচন করুন --
                        </option>

                        <?php foreach ($employees as $employee): ?>

                            <option
                                value="<?= (int)$employee['id'] ?>"
                                <?= (
                                    isset($_POST['employee_id']) &&
                                    $_POST['employee_id'] == $employee['id']
                                ) ? 'selected' : '' ?>
                            >

                                <?= htmlspecialchars($employee['employee_name']) ?>

                                <?php if (!empty($employee['mobile'])): ?>
                                    - <?= htmlspecialchars($employee['mobile']) ?>
                                <?php endif; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Date -->
                <div class="mb-3">

                    <label class="form-label">
                        Attendance Date <span class="text-danger">*</span>
                    </label>

                    <input
                        type="date"
                        name="attendance_date"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $_POST['attendance_date'] ?? date('Y-m-d')
                        ) ?>"
                        required
                    >

                </div>


                <!-- Status -->
                <div class="mb-3">

                    <label class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                        required
                    >

                        <option value="present"
                            <?= ($_POST['status'] ?? '') === 'present' ? 'selected' : '' ?>>
                            উপস্থিত
                        </option>

                        <option value="absent"
                            <?= ($_POST['status'] ?? '') === 'absent' ? 'selected' : '' ?>>
                            অনুপস্থিত
                        </option>

                        <option value="late"
                            <?= ($_POST['status'] ?? '') === 'late' ? 'selected' : '' ?>>
                            দেরিতে এসেছে
                        </option>

                        <option value="leave"
                            <?= ($_POST['status'] ?? '') === 'leave' ? 'selected' : '' ?>>
                            ছুটি
                        </option>

                        <option value="half_day"
                            <?= ($_POST['status'] ?? '') === 'half_day' ? 'selected' : '' ?>>
                            অর্ধদিবস
                        </option>

                    </select>

                </div>


                <div class="row">

                    <!-- Check In -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Check In
                        </label>

                        <input
                            type="time"
                            name="check_in"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $_POST['check_in'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <!-- Check Out -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Check Out
                        </label>

                        <input
                            type="time"
                            name="check_out"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $_POST['check_out'] ?? ''
                            ) ?>"
                        >

                    </div>

                </div>


                <!-- Note -->
                <div class="mb-4">

                    <label class="form-label">
                        Note
                    </label>

                    <textarea
                        name="note"
                        class="form-control"
                        rows="3"
                        placeholder="কোনো মন্তব্য থাকলে লিখুন..."
                    ><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>

                </div>


                <!-- Buttons -->
                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary px-4"
                    >
                        💾 Attendance Save
                    </button>

                    <a
                        href="index.php?page=employee/index"
                        class="btn btn-secondary px-4"
                    >
                        বাতিল
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>