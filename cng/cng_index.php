<?php
// cng/index.php

// ===============================
// Filters
// ===============================
$search = trim($_GET['search'] ?? '');
$garageId = (int)($_GET['garage_id'] ?? 0);
$status = trim($_GET['status'] ?? '');

// ===============================
// Active Garages
// ===============================
$garageStmt = $pdo->query("
    SELECT id, garage_name
    FROM garages
    WHERE status = 'active'
    ORDER BY garage_name ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


// ===============================
// Build Query
// ===============================
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "
        (
            v.car_number LIKE ?
            OR v.driver_name LIKE ?
            OR v.driver_mobile LIKE ?
        )
    ";

    $searchLike = '%' . $search . '%';

    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
}

if ($garageId > 0) {
    $where[] = "v.garage_id = ?";
    $params[] = $garageId;
}

if ($status !== '') {
    $allowedStatuses = [
        'active',
        'inactive',
        'maintenance',
        'sale'
    ];

    if (in_array($status, $allowedStatuses, true)) {
        $where[] = "v.status = ?";
        $params[] = $status;
    }
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}


// ===============================
// CNG List
// ===============================
$sql = "
    SELECT
        v.*,
        g.garage_name
    FROM cng_vehicles v
    LEFT JOIN garages g
        ON g.id = v.garage_id
    $whereSql
    ORDER BY v.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ===============================
// Dashboard Counts
// ===============================
$countStmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'active') AS active,
        SUM(status = 'inactive') AS inactive,
        SUM(status = 'maintenance') AS maintenance,
        SUM(status = 'sale') AS sale
    FROM cng_vehicles
");

$counts = $countStmt->fetch(PDO::FETCH_ASSOC);

$totalCng = (int)($counts['total'] ?? 0);
$activeCng = (int)($counts['active'] ?? 0);
$inactiveCng = (int)($counts['inactive'] ?? 0);
$maintenanceCng = (int)($counts['maintenance'] ?? 0);
$saleCng = (int)($counts['sale'] ?? 0);


// ===============================
// Status Helper
// ===============================
function cngStatusBadge($status)
{
    switch ($status) {

        case 'active':
            return '
                <span class="badge bg-success-subtle text-success px-3 py-2">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Active
                </span>
            ';

        case 'inactive':
            return '
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                    <i class="bi bi-pause-circle-fill me-1"></i>
                    Inactive
                </span>
            ';

        case 'maintenance':
            return '
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">
                    <i class="bi bi-tools me-1"></i>
                    Maintenance
                </span>
            ';

        case 'sale':
            return '
                <span class="badge bg-danger-subtle text-danger px-3 py-2">
                    <i class="bi bi-tag-fill me-1"></i>
                    Sale
                </span>
            ';

        default:
            return '
                <span class="badge bg-light text-dark px-3 py-2">
                    Unknown
                </span>
            ';
    }
}

?>

<!-- =========================================
     PAGE HEADER
========================================= -->

<div class="container-fluid px-3 px-lg-4 py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-car-front-fill text-primary me-2"></i>
                CNG গাড়ি ম্যানেজমেন্ট
            </h3>

            <p class="text-muted mb-0">
                আপনার সকল CNG গাড়ির তথ্য এখানে দেখুন ও পরিচালনা করুন।
            </p>
        </div>

        <div>
            <a href="index.php?page=cng/add"
               class="btn btn-primary px-4 shadow-sm">

                <i class="bi bi-plus-circle me-1"></i>
                নতুন CNG যোগ করুন

            </a>
        </div>

    </div>


    <!-- =========================================
         SUCCESS / ERROR MESSAGE
    ========================================== -->

    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>

            <?= htmlspecialchars($_SESSION['success']) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= htmlspecialchars($_SESSION['error']) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <!-- =========================================
         STAT CARDS
    ========================================== -->

    <div class="row g-3 mb-4">

        <!-- Total -->
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                মোট CNG
                            </small>

                            <h2 class="fw-bold mb-0 mt-1">
                                <?= number_format($totalCng) ?>
                            </h2>
                        </div>

                        <div class="rounded-circle bg-primary-subtle p-3">
                            <i class="bi bi-car-front-fill fs-4 text-primary"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Active -->
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Active
                            </small>

                            <h2 class="fw-bold text-success mb-0 mt-1">
                                <?= number_format($activeCng) ?>
                            </h2>
                        </div>

                        <div class="rounded-circle bg-success-subtle p-3">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Maintenance -->
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Maintenance
                            </small>

                            <h2 class="fw-bold text-warning mb-0 mt-1">
                                <?= number_format($maintenanceCng) ?>
                            </h2>
                        </div>

                        <div class="rounded-circle bg-warning-subtle p-3">
                            <i class="bi bi-tools fs-4 text-warning"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Sale -->
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Sale
                            </small>

                            <h2 class="fw-bold text-danger mb-0 mt-1">
                                <?= number_format($saleCng) ?>
                            </h2>
                        </div>

                        <div class="rounded-circle bg-danger-subtle p-3">
                            <i class="bi bi-tag-fill fs-4 text-danger"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================
         SEARCH / FILTER
    ========================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">

                <input type="hidden"
                       name="page"
                       value="cng/index">

                <div class="row g-3 align-items-end">

                    <!-- Search -->
                    <div class="col-lg-5">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-search me-1"></i>
                            খুঁজুন
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="গাড়ির নাম্বার / চালকের নাম / মোবাইল"
                                   value="<?= htmlspecialchars($search) ?>">

                        </div>

                    </div>


                    <!-- Garage -->
                    <div class="col-lg-3">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-building me-1"></i>
                            গ্যারেজ
                        </label>

                        <select name="garage_id"
                                class="form-select">

                            <option value="">
                                সকল গ্যারেজ
                            </option>

                            <?php foreach ($garages as $garage): ?>

                                <option value="<?= (int)$garage['id'] ?>"
                                    <?= $garageId == $garage['id'] ? 'selected' : '' ?>>

                                    <?= htmlspecialchars($garage['garage_name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Status -->
                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-funnel me-1"></i>
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="">
                                সকল Status
                            </option>

                            <option value="active"
                                <?= $status === 'active' ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="inactive"
                                <?= $status === 'inactive' ? 'selected' : '' ?>>
                                Inactive
                            </option>

                            <option value="maintenance"
                                <?= $status === 'maintenance' ? 'selected' : '' ?>>
                                Maintenance
                            </option>

                            <option value="sale"
                                <?= $status === 'sale' ? 'selected' : '' ?>>
                                Sale
                            </option>

                        </select>

                    </div>


                    <!-- Buttons -->
                    <div class="col-lg-2">

                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-primary w-100">

                                <i class="bi bi-search me-1"></i>
                                Search

                            </button>

                            <a href="index.php?page=cng/index"
                               class="btn btn-light border">

                                <i class="bi bi-arrow-clockwise"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================
         CNG LIST
    ========================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>

                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        CNG গাড়ির তালিকা
                    </h5>

                    <small class="text-muted">
                        মোট <?= number_format(count($vehicles)) ?> টি গাড়ি পাওয়া গেছে
                    </small>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <?php if (empty($vehicles)): ?>

                <!-- Empty State -->

                <div class="text-center py-5">

                    <div class="mb-3">

                        <i class="bi bi-car-front display-3 text-muted"></i>

                    </div>

                    <h5 class="fw-bold">
                        কোনো CNG গাড়ি পাওয়া যায়নি
                    </h5>

                    <p class="text-muted mb-3">
                        আপনার search/filter পরিবর্তন করুন অথবা নতুন CNG যোগ করুন।
                    </p>

                    <a href="index.php?page=cng/add"
                       class="btn btn-primary">

                        <i class="bi bi-plus-circle me-1"></i>
                        নতুন CNG যোগ করুন

                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-3">
                                    #
                                </th>

                                <th>
                                    গাড়ির নাম্বার
                                </th>

                                <th>
                                    গ্যারেজ
                                </th>

                                <th>
                                    চালক
                                </th>

                                <th>
                                    মোবাইল
                                </th>

                                <th>
                                    দৈনিক ভাড়া
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="text-center">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($vehicles as $index => $vehicle): ?>

                            <tr>

                                <!-- Number -->
                                <td class="px-3 text-muted fw-semibold">
                                    <?= $index + 1 ?>
                                </td>


                                <!-- Car Number -->
                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="rounded-circle bg-primary-subtle p-2">

                                            <i class="bi bi-car-front-fill text-primary"></i>

                                        </div>

                                        <div>

                                            <div class="fw-bold">
                                                <?= htmlspecialchars($vehicle['car_number']) ?>
                                            </div>

                                            <?php if (!empty($vehicle['note'])): ?>

                                                <small class="text-muted">

                                                    <i class="bi bi-info-circle me-1"></i>

                                                    <?= htmlspecialchars($vehicle['note']) ?>

                                                </small>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>


                                <!-- Garage -->
                                <td>

                                    <?php if (!empty($vehicle['garage_name'])): ?>

                                        <span class="text-dark">

                                            <i class="bi bi-building text-primary me-1"></i>

                                            <?= htmlspecialchars($vehicle['garage_name']) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            গ্যারেজ নেই
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Driver -->
                                <td>

                                    <?php if (!empty($vehicle['driver_name'])): ?>

                                        <span class="fw-semibold">

                                            <i class="bi bi-person-fill text-secondary me-1"></i>

                                            <?= htmlspecialchars($vehicle['driver_name']) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            চালক নেই
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Mobile -->
                                <td>

                                    <?php if (!empty($vehicle['driver_mobile'])): ?>

                                        <a href="tel:<?= htmlspecialchars($vehicle['driver_mobile']) ?>"
                                           class="text-decoration-none">

                                            <i class="bi bi-telephone-fill text-success me-1"></i>

                                            <?= htmlspecialchars($vehicle['driver_mobile']) ?>

                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Daily Rent -->
                                <td>

                                    <span class="fw-bold text-dark">

                                        ৳ <?= number_format((float)$vehicle['daily_rent'], 2) ?>

                                    </span>

                                    <small class="text-muted d-block">
                                        / দিন
                                    </small>

                                </td>


                                <!-- Status -->
                                <td>

                                    <?= cngStatusBadge($vehicle['status']) ?>

                                </td>


                                <!-- Actions -->
                                <td class="text-center">

                                    <div class="dropdown">

                                        <button class="btn btn-sm btn-light border"
                                                type="button"
                                                data-bs-toggle="dropdown">

                                            <i class="bi bi-three-dots-vertical"></i>

                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                            <li>

                                                <a class="dropdown-item"
                                                   href="index.php?page=cng/edit&id=<?= (int)$vehicle['id'] ?>">

                                                    <i class="bi bi-pencil-square text-primary me-2"></i>

                                                    Edit

                                                </a>

                                            </li>

                                            <li>

                                                <a class="dropdown-item"
                                                   href="index.php?page=cng/view&id=<?= (int)$vehicle['id'] ?>">

                                                    <i class="bi bi-eye text-success me-2"></i>

                                                    View

                                                </a>

                                            </li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            <li>

                                                <a class="dropdown-item text-danger"
                                                   href="index.php?page=sql/cng_delete&id=<?= (int)$vehicle['id'] ?>"
                                                   onclick="return confirm('আপনি কি এই CNG গাড়িটি মুছে ফেলতে চান?');">

                                                    <i class="bi bi-trash3 me-2"></i>

                                                    Delete

                                                </a>

                                            </li>

                                        </ul>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>