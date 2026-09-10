
<?php

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $stmt = $pdo->prepare("
        SELECT *
        FROM rent_drivers
        WHERE driver_name LIKE ?
           OR phone LIKE ?
           OR driving_license LIKE ?
        ORDER BY id DESC
    ");

    $keyword = "%{$search}%";

    $stmt->execute([
        $keyword,
        $keyword,
        $keyword
    ]);

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM rent_drivers
        ORDER BY id DESC
    ");
}

$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Summary
$totalDrivers = count($drivers);

$activeDrivers = 0;
$inactiveDrivers = 0;

foreach ($drivers as $driver) {

    if ($driver['status'] === 'active') {
        $activeDrivers++;
    } else {
        $inactiveDrivers++;
    }
}

?>

<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary"></i>
                ভাড়া চালকদের তালিকা
            </h4>

            <small class="text-muted">
                গাড়ি ভাড়া চালানোর জন্য নিবন্ধিত সকল চালক
            </small>
        </div>

        <a href="index.php?page=rent/driver_add"
           class="btn btn-primary">

            <i class="bi bi-person-plus-fill"></i>
            নতুন চালক যোগ করুন

        </a>

    </div>


    <!-- Summary -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                মোট চালক
                            </small>

                            <h3 class="fw-bold mb-0">
                                <?= $totalDrivers ?>
                            </h3>
                        </div>

                        <i class="bi bi-people-fill fs-1 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                সক্রিয় চালক
                            </small>

                            <h3 class="fw-bold text-success mb-0">
                                <?= $activeDrivers ?>
                            </h3>
                        </div>

                        <i class="bi bi-person-check-fill fs-1 text-success"></i>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card summary-card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                নিষ্ক্রিয় চালক
                            </small>

                            <h3 class="fw-bold text-danger mb-0">
                                <?= $inactiveDrivers ?>
                            </h3>
                        </div>

                        <i class="bi bi-person-x-fill fs-1 text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Search -->
    <div class="card border-0 shadow-sm mb-3">

        <div class="card-body">

            <form method="GET">

                <input type="hidden"
                       name="page"
                       value="rent/driver">

                <div class="input-group">

                    <input type="text"
                           name="search"
                           class="form-control"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="নাম, মোবাইল অথবা লাইসেন্স নম্বর দিয়ে খুঁজুন...">

                    <button class="btn btn-primary">
                        <i class="bi bi-search"></i>
                        খুঁজুন
                    </button>

                    <?php if ($search !== ''): ?>

                        <a href="index.php?page=rent/driver"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                            পরিষ্কার
                        </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>

    </div>


    <!-- Table -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ul"></i>
                চালকের তথ্য
            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>#</th>
                            <th>চালকের নাম</th>
                            <th>মোবাইল</th>
                            <th>NID</th>
                            <th>ড্রাইভিং লাইসেন্স</th>
                            <th>লাইসেন্স মেয়াদ</th>
                            <th>স্ট্যাটাস</th>
                            <th class="text-center">অ্যাকশন</th>
                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!$drivers): ?>

                        <tr>

                            <td colspan="8"
                                class="text-center py-5 text-muted">

                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>

                                কোনো চালকের তথ্য পাওয়া যায়নি।

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($drivers as $key => $driver): ?>

                            <tr>

                                <td>
                                    <?= $key + 1 ?>
                                </td>


                                <td>

                                    <div class="fw-bold">
                                        <?= htmlspecialchars($driver['driver_name']) ?>
                                    </div>

                                    <?php if (!empty($driver['address'])): ?>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($driver['address']) ?>
                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= htmlspecialchars($driver['phone'] ?? '-') ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars($driver['nid'] ?? '-') ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars($driver['driving_license'] ?? '-') ?>
                                </td>


                                <td>

                                    <?php if (!empty($driver['license_expiry'])): ?>

                                        <?= date('d-m-Y', strtotime($driver['license_expiry'])) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($driver['status'] === 'active'): ?>

                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i>
                                            সক্রিয়
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i>
                                            নিষ্ক্রিয়
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td class="text-center">

                                    <a href="index.php?page=rent/driver_edit&id=<?= $driver['id'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <a href="index.php?page=rent/driver_delete&id=<?= $driver['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('এই চালককে মুছে ফেলতে চান?');"
                                       title="Delete">

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


<style>

.summary-card {
    border-radius: 12px;
}

.table th {
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

</style>

