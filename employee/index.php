<?php
 

/*
|--------------------------------------------------------------------------
| Employee List
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $stmt = $pdo->prepare("
        SELECT id, employee_name, mobile
        FROM employees
        WHERE name LIKE :search
           OR mobile LIKE :search
        ORDER BY id DESC
    ");

    $stmt->execute([
        ':search' => '%' . $search . '%'
    ]);

} else {

    $stmt = $pdo->query("
        SELECT id, employee_name, mobile
        FROM employees
        ORDER BY id DESC
    ");
}

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-1">
                <i class="bi bi-people-fill"></i>
                কর্মচারীর তালিকা
            </h4>

            <div class="text-muted">
                সকল কর্মচারীর তথ্য
            </div>
        </div>

        <div>
            <a
                href="index.php?page=employee/add"
                class="btn btn-primary"
            >
                <i class="bi bi-person-plus"></i>
                নতুন কর্মচারী
            </a>
        </div>

    </div>


    <!-- Search -->
    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body">

            <form method="GET" action="index.php">

                <input
                    type="hidden"
                    name="page"
                    value="employee/index"
                >

                <div class="row g-2">

                    <div class="col-md-10">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="নাম অথবা মোবাইল নম্বর দিয়ে খুঁজুন..."
                            value="<?= htmlspecialchars($search) ?>"
                        >

                    </div>

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-search"></i>
                            খুঁজুন
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Employee Table -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <strong>
                <i class="bi bi-list-ul"></i>
                কর্মচারী তালিকা
            </strong>

            <span class="badge bg-primary">
                মোট <?= count($employees) ?> জন
            </span>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered mb-0 align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="70" class="text-center">
                                #
                            </th>

                            <th>
                                কর্মচারীর নাম
                            </th>

                            <th>
                                মোবাইল নম্বর
                            </th>

                            <th width="300" class="text-center">
                                অ্যাকশন
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($employees)): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bi bi-person-x"
                                    style="font-size:40px;"
                                ></i>

                                <div class="mt-2">
                                    কোনো কর্মচারী পাওয়া যায়নি।
                                </div>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($employees as $key => $employee): ?>

                            <tr>

                                <!-- Serial -->
                                <td class="text-center">
                                    <?= $key + 1 ?>
                                </td>


                                <!-- Name -->
                                <td>

                                    <div class="fw-semibold">

                                        <i class="bi bi-person-circle text-primary"></i>

                                        <?= htmlspecialchars(
                                            $employee['employee_name'] ?? ''
                                        ) ?>

                                    </div>

                                    <small class="text-muted">
                                        ID: <?= (int)$employee['id'] ?>
                                    </small>

                                </td>


                                <!-- Phone -->
                                <td>

                                    <?php if (!empty($employee['mobile'])): ?>

                                        <i class="bi bi-telephone"></i>

                                        <?= htmlspecialchars(
                                            $employee['mobile']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            দেওয়া হয়নি
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Actions -->
                                <td class="text-center">

                                    <!-- View -->
                                    <a
                                        href="index.php?page=employee/view&id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-sm btn-info text-white"
                                        title="দেখুন"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>


                                    <!-- Edit -->
                                    <a
                                        href="index.php?page=employee/edit&id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>


                                    <!-- Attendance -->
                                    <a
                                        href="index.php?page=employee/attendance_add&employee_id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-sm btn-success"
                                        title="Attendance"
                                    >
                                        <i class="bi bi-calendar-check"></i>
                                    </a>


                                    <!-- Delete -->
                                    <a
                                        href="index.php?page=employee/delete&id=<?= (int)$employee['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        title="Delete"
                                        onclick="return confirm('আপনি কি এই কর্মচারীটি মুছে ফেলতে চান?');"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>