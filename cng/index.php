<?php

// =====================================================
// TODAY
// =====================================================
$today = date('Y-m-d');


// =====================================================
// ACTIVE CNG VEHICLES
// =====================================================
$stmt = $pdo->query("
    SELECT *
    FROM cng_vehicles
    WHERE status = 'active'
    ORDER BY id ASC
");

$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// TODAY'S COLLECTION
// =====================================================
$stmt = $pdo->prepare("
    SELECT *
    FROM cng_daily_collections
    WHERE collection_date = ?
");

$stmt->execute([$today]);

$todayCollections = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $todayCollections[$row['vehicle_id']] = $row;
}


// =====================================================
// TOTAL TODAY
// =====================================================
$totalToday = 0;

foreach ($todayCollections as $row) {
    $totalToday += (float)$row['amount'];
}

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- ================= HEADER ================= -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                🚖 CNG দৈনিক ভাড়া জমা
            </h3>

            <div class="text-muted">
                📅 <?= date('d-m-Y') ?>
            </div>
        </div>

        <div class="mt-2 mt-md-0">
            <div class="bg-success text-white rounded-3 px-4 py-3 shadow-sm">

                <div class="small">
                    আজকের মোট জমা
                </div>

                <div class="fs-4 fw-bold">
                    ৳ <?= number_format($totalToday, 2) ?>
                </div>

            </div>
        </div>

    </div>


    <!-- ================= FORM ================= -->

    <form method="POST" action="index.php?page=sql/cng_collection">

        <input type="hidden"
               name="collection_date"
               value="<?= htmlspecialchars($today) ?>">


        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-header bg-white border-0 py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">
                        🚕 গাড়ি অনুযায়ী জমা
                    </h5>

                    <span class="badge bg-primary">
                        মোট গাড়ি: <?= count($vehicles) ?> টি
                    </span>

                </div>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="text-center" style="width:70px;">
                                    #
                                </th>

                                <th>
                                    🚕 গাড়ির নাম্বার
                                </th>

                                <th>
                                    👤 চালক
                                </th>

                                <th class="text-center">
                                    💰 নির্ধারিত ভাড়া
                                </th>

                                <th style="width:220px;">
                                    💵 জমার টাকা
                                </th>

                                <th class="text-center">
                                    📌 অবস্থা
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (empty($vehicles)): ?>

                            <tr>

                                <td colspan="6"
                                    class="text-center py-5 text-muted">

                                    🚕 কোন CNG গাড়ি পাওয়া যায়নি।

                                </td>

                            </tr>

                        <?php else: ?>


                            <?php foreach ($vehicles as $index => $vehicle): ?>

                                <?php

                                $vehicleId = $vehicle['id'];

                                $existing =
                                    $todayCollections[$vehicleId]['amount']
                                    ?? '';

                                ?>


                                <tr>

                                    <!-- NUMBER -->
                                    <td class="text-center fw-bold">

                                        <?= $index + 1 ?>

                                    </td>


                                    <!-- CAR NUMBER -->
                                    <td>

                                        <div class="fw-bold text-primary">

                                            🚕
                                            <?= htmlspecialchars(
                                                $vehicle['car_number']
                                            ) ?>

                                        </div>

                                        <input type="hidden"
                                               name="vehicle_id[]"
                                               value="<?= $vehicleId ?>">

                                        <input type="hidden"
                                               name="car_number[<?= $vehicleId ?>]"
                                               value="<?= htmlspecialchars(
                                                   $vehicle['car_number']
                                               ) ?>">

                                    </td>


                                    <!-- DRIVER -->
                                    <td>

                                        <?= !empty($vehicle['driver_name'])
                                            ? htmlspecialchars(
                                                $vehicle['driver_name']
                                            )
                                            : '<span class="text-muted">-</span>'
                                        ?>

                                    </td>


                                    <!-- DAILY RENT -->
                                    <td class="text-center">

                                        <span class="badge bg-light text-dark">

                                            ৳
                                            <?= number_format(
                                                (float)$vehicle['daily_rent'],
                                                2
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- AMOUNT -->
                                    <td>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                ৳
                                            </span>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="amount[<?= $vehicleId ?>]"
                                                class="form-control form-control-lg"
                                                placeholder="জমার টাকা"
                                                value="<?= htmlspecialchars(
                                                    $existing
                                                ) ?>"
                                            >

                                        </div>

                                    </td>


                                    <!-- STATUS -->
                                    <td class="text-center">

                                        <?php if ($existing !== ''): ?>

                                            <span class="badge bg-success">

                                                ✓ জমা হয়েছে

                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">

                                                ⏳ বাকি

                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- ================= FOOTER ================= -->

            <?php if (!empty($vehicles)): ?>

                <div class="card-footer bg-white border-0 p-3">

                    <div class="row align-items-center">

                        <div class="col-md-6">

                            <div class="text-muted">

                                💡 যেই গাড়ির টাকা জমা হয়নি,
                                সেই ঘরটি খালি রাখুন।

                            </div>

                        </div>


                        <div class="col-md-6 text-md-end mt-3 mt-md-0">

                            <button type="submit"
                                    class="btn btn-primary btn-lg px-4">

                                <i class="bi bi-save"></i>

                                সব টাকা জমা করুন

                            </button>

                        </div>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </form>

</div>