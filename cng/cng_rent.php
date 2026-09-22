 
<?php

// =====================================================
// SELECTED DATE
// =====================================================

// GET থেকে তারিখ নেবে
$selectedDate = $_GET['collection_date'] ?? date('Y-m-d');

// সঠিক date format কিনা যাচাই
$dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);

if (
    !$dateObj ||
    $dateObj->format('Y-m-d') !== $selectedDate
) {
    $selectedDate = date('Y-m-d');
}

// =====================================================
// TODAY
// =====================================================

$today = date('Y-m-d');

// =====================================================
// SELECTED GARAGE
// =====================================================

$selectedGarage = (int)($_GET['garage_id'] ?? 0);

// =====================================================
// ACTIVE GARAGES
// =====================================================

$garageStmt = $pdo->query("
    SELECT
        id,
        garage_name
    FROM garages
    WHERE status = 'active'
    ORDER BY garage_name ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// ACTIVE CNG VEHICLES
// =====================================================

$vehicleSql = "
    SELECT
        v.id,
        v.car_number,
        v.garage_id,
        v.driver_name,
        v.driver_mobile,
        v.daily_rent,
        g.garage_name
    FROM cng_vehicles v
    LEFT JOIN garages g
        ON g.id = v.garage_id
    WHERE v.status = 'active'
";

$vehicleParams = [];

// =====================================================
// GARAGE FILTER
// =====================================================

if ($selectedGarage > 0) {

    $vehicleSql .= "
        AND v.garage_id = ?
    ";

    $vehicleParams[] = $selectedGarage;
}

$vehicleSql .= "
    ORDER BY v.id ASC
";

$vehicleStmt = $pdo->prepare($vehicleSql);
$vehicleStmt->execute($vehicleParams);

$vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// SELECTED DATE'S COLLECTION
// =====================================================

$collectionStmt = $pdo->prepare("
    SELECT
        id,
        vehicle_id,
        car_number,
        amount,
        expense_amount,
        expense_note
    FROM cng_daily_collections
    WHERE collection_date = ?
");

$collectionStmt->execute([
    $selectedDate
]);

$todayCollections = [];

foreach (
    $collectionStmt->fetchAll(PDO::FETCH_ASSOC)
    as $row
) {

    $todayCollections[(int)$row['vehicle_id']] = $row;
}

// =====================================================
// TOTAL
// =====================================================

$totalToday = 0;
$totalExpense = 0;
$paidCount = 0;
$dueCount = 0;

foreach ($vehicles as $vehicle) {

    $vehicleId = (int)$vehicle['id'];

    if (isset($todayCollections[$vehicleId])) {

        $amount =
            (float)$todayCollections[$vehicleId]['amount'];

        $expense =
            (float)$todayCollections[$vehicleId]['expense_amount'];

        $totalToday += $amount;
        $totalExpense += $expense;

        if ($amount > 0) {
            $paidCount++;
        }

    } else {

        $dueCount++;
    }
}

$netToday =
    $totalToday - $totalExpense;

// =====================================================
// SELECTED GARAGE NAME
// =====================================================

$selectedGarageName = 'সকল গ্যারেজ';

if ($selectedGarage > 0) {

    foreach ($garages as $garage) {

        if (
            (int)$garage['id'] === $selectedGarage
        ) {

            $selectedGarageName =
                $garage['garage_name'];

            break;
        }
    }
}

// =====================================================
// DATE DISPLAY
// =====================================================

$selectedDateDisplay =
    date('d-m-Y', strtotime($selectedDate));

if ($selectedDate === $today) {

    $dateLabel = 'আজকের হিসাব';

} elseif ($selectedDate < $today) {

    $dateLabel = 'পুরাতন হিসাব';

} else {

    $dateLabel = 'ভবিষ্যৎ তারিখ';
}

?>

<!-- =====================================================
     PAGE
====================================================== -->

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                <i class="bi bi-cash-stack text-primary me-2"></i>

                CNG দৈনিক ভাড়া জমা

            </h3>

            <div class="text-muted">

                <i class="bi bi-calendar3 me-1"></i>

                <?= htmlspecialchars($dateLabel) ?>

                :

                <strong>
                    <?= htmlspecialchars($selectedDateDisplay) ?>
                </strong>

            </div>

        </div>


        <!-- NET -->

        <div class="bg-success text-white rounded-4 px-4 py-3 shadow-sm">

            <div class="small opacity-75">

                নির্বাচিত দিনের নেট আয়

            </div>

            <div class="fs-3 fw-bold">

                ৳

                <span id="topNet">

                    <?= number_format(
                        $netToday,
                        2
                    ) ?>

                </span>

            </div>

        </div>

    </div>


    <!-- =================================================
         DATE + GARAGE FILTER
    ================================================== -->

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-end">


                <!-- DATE -->

                <div class="col-md-6 col-lg-4">

                    <label
                        for="dateSelect"
                        class="form-label fw-bold"
                    >

                        <i class="bi bi-calendar-date text-primary me-1"></i>

                        তারিখ নির্বাচন করুন

                    </label>

                    <div class="input-group input-group-lg">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-calendar3 text-primary"></i>

                        </span>

                        <input
                            type="date"
                            id="dateSelect"
                            class="form-control"
                            value="<?= htmlspecialchars($selectedDate) ?>"
                        >

                    </div>

                </div>


                <!-- GARAGE -->

                <div class="col-md-6 col-lg-4">

                    <label
                        for="garageSelect"
                        class="form-label fw-bold"
                    >

                        <i class="bi bi-building text-primary me-1"></i>

                        গ্যারেজ নির্বাচন করুন

                    </label>

                    <select
                        id="garageSelect"
                        class="form-select form-select-lg"
                    >

                        <option value="0">

                            🚗 সকল গ্যারেজ

                        </option>

                        <?php foreach ($garages as $garage): ?>

                            <option
                                value="<?= (int)$garage['id'] ?>"
                                <?= $selectedGarage == $garage['id']
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


                <!-- SELECTED GARAGE -->

                <div class="col-md-6 col-lg-2">

                    <div class="garage-info-box">

                        <div class="small text-muted">

                            নির্বাচিত গ্যারেজ

                        </div>

                        <div class="fw-bold">

                            <i class="bi bi-building-fill text-primary me-1"></i>

                            <span id="selectedGarageName">

                                <?= htmlspecialchars(
                                    $selectedGarageName
                                ) ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- RESET -->

                <div class="col-md-6 col-lg-2">

                    <a
                        href="index.php?page=cng/cng_rent"
                        class="btn btn-outline-secondary btn-lg w-100"
                    >

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        রিসেট

                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         SELECTED DATE INFO
    ================================================== -->

    <div class="alert alert-primary border-0 rounded-4 shadow-sm mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="date-info-icon">

                <i class="bi bi-calendar-check-fill"></i>

            </div>

            <div>

                <div class="fw-bold">

                    <?= htmlspecialchars($dateLabel) ?>

                </div>

                <div>

                    📅

                    <strong>
                        <?= htmlspecialchars($selectedDateDisplay) ?>
                    </strong>

                    —

                    <?= htmlspecialchars($selectedGarageName) ?>

                    অনুযায়ী হিসাব দেখানো হচ্ছে।

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         SUMMARY
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL CAR -->

        <div class="col-md-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                Active CNG

                            </div>

                            <div
                                class="fs-3 fw-bold text-primary"
                                id="carCount"
                            >

                                <?= count($vehicles) ?>

                            </div>

                        </div>

                        <div class="rounded-circle bg-primary-subtle p-3">

                            <i class="bi bi-car-front-fill text-primary fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- PAID -->

        <div class="col-md-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                <?= htmlspecialchars($dateLabel) ?> জমা

                            </div>

                            <div
                                class="fs-3 fw-bold text-success"
                                id="paidCount"
                            >

                                <?= $paidCount ?>

                            </div>

                        </div>

                        <div class="rounded-circle bg-success-subtle p-3">

                            <i class="bi bi-check-circle-fill text-success fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- DUE -->

        <div class="col-md-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                জমা বাকি

                            </div>

                            <div
                                class="fs-3 fw-bold text-warning"
                                id="dueCount"
                            >

                                <?= $dueCount ?>

                            </div>

                        </div>

                        <div class="rounded-circle bg-warning-subtle p-3">

                            <i class="bi bi-clock-history text-warning fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- EXPENSE -->

        <div class="col-md-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">

                                <?= htmlspecialchars($dateLabel) ?> খরচ

                            </div>

                            <div
                                class="fs-3 fw-bold text-danger"
                                id="expenseTotal"
                            >

                                ৳ <?= number_format(
                                    $totalExpense,
                                    2
                                ) ?>

                            </div>

                        </div>

                        <div class="rounded-circle bg-danger-subtle p-3">

                            <i class="bi bi-wallet2 text-danger fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         FORM
    ================================================== -->

    <form
        method="POST"
        action="index.php?page=sql/cng_collection"
        id="collectionForm"
    >

        <!-- SELECTED DATE -->

        <input
            type="hidden"
            name="collection_date"
            value="<?= htmlspecialchars($selectedDate) ?>"
            id="collectionDateInput"
        >


        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">


            <!-- HEADER -->

            <div class="card-header bg-white border-0 py-3">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div>

                        <h5 class="fw-bold mb-1">

                            <i class="bi bi-list-check text-primary me-2"></i>

                            গাড়ি অনুযায়ী হিসাব

                        </h5>

                        <small class="text-muted">

                            📅
                            <?= htmlspecialchars($selectedDateDisplay) ?>

                            —

                            জমা এবং খরচ একই সাথে লিখতে পারবেন।

                        </small>

                    </div>


                    <span class="badge bg-primary-subtle text-primary px-3 py-2">

                        🚗 <?= count($vehicles) ?> টি গাড়ি

                    </span>

                </div>

            </div>


            <!-- TABLE -->

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th
                                    class="text-center"
                                    style="width:60px;"
                                >
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

                                <th class="text-center">
                                    নির্ধারিত ভাড়া
                                </th>

                                <th style="min-width:180px;">
                                    জমা
                                </th>

                                <th style="min-width:180px;">
                                    খরচ
                                </th>

                                <th style="min-width:220px;">
                                    খরচের বিবরণ
                                </th>

                                <th
                                    class="text-center"
                                    style="width:130px;"
                                >
                                    অবস্থা
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (empty($vehicles)): ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center py-5"
                                >

                                    <i class="bi bi-car-front display-4 text-muted"></i>

                                    <h5 class="fw-bold mt-3">

                                        কোনো Active CNG পাওয়া যায়নি

                                    </h5>

                                    <p class="text-muted mb-0">

                                        এই গ্যারেজে বর্তমানে কোনো Active CNG নেই।

                                    </p>

                                </td>

                            </tr>


                        <?php else: ?>


                            <?php foreach (
                                $vehicles as $index => $vehicle
                            ): ?>


                                <?php

                                $vehicleId =
                                    (int)$vehicle['id'];

                                $existing =
                                    $todayCollections[$vehicleId]
                                    ?? null;

                                $existingAmount =
                                    $existing['amount']
                                    ?? '';

                                $existingExpense =
                                    $existing['expense_amount']
                                    ?? '';

                                $existingNote =
                                    $existing['expense_note']
                                    ?? '';

                                $isPaid =
                                    $existing !== null
                                    &&
                                    (float)$existingAmount > 0;

                                ?>


                                <tr
                                    class="<?= $existing
                                        ? 'table-success-subtle'
                                        : ''
                                    ?>"
                                >


                                    <!-- NUMBER -->

                                    <td class="text-center fw-bold text-muted">

                                        <?= $index + 1 ?>

                                    </td>


                                    <!-- CAR -->

                                    <td>

                                        <div class="d-flex align-items-center gap-2">

                                            <div class="car-icon">

                                                <i class="bi bi-car-front-fill"></i>

                                            </div>

                                            <div>

                                                <div class="fw-bold text-primary">

                                                    <?= htmlspecialchars(
                                                        $vehicle['car_number']
                                                    ) ?>

                                                </div>

                                                <small class="text-muted">

                                                    ID #<?= $vehicleId ?>

                                                </small>

                                            </div>

                                        </div>


                                        <input
                                            type="hidden"
                                            name="vehicle_id[]"
                                            value="<?= $vehicleId ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="car_number[<?= $vehicleId ?>]"
                                            value="<?= htmlspecialchars(
                                                $vehicle['car_number']
                                            ) ?>"
                                        >

                                    </td>


                                    <!-- GARAGE -->

                                    <td>

                                        <span class="badge bg-light text-dark border">

                                            <i class="bi bi-building me-1"></i>

                                            <?= htmlspecialchars(
                                                $vehicle['garage_name']
                                                ?? '-'
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- DRIVER -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $vehicle['driver_name']
                                            )
                                        ): ?>

                                            <div class="fw-semibold">

                                                <i class="bi bi-person-fill text-secondary me-1"></i>

                                                <?= htmlspecialchars(
                                                    $vehicle['driver_name']
                                                ) ?>

                                            </div>


                                            <?php if (
                                                !empty(
                                                    $vehicle['driver_mobile']
                                                )
                                            ): ?>

                                                <small class="text-muted">

                                                    <i class="bi bi-telephone me-1"></i>

                                                    <?= htmlspecialchars(
                                                        $vehicle['driver_mobile']
                                                    ) ?>

                                                </small>

                                            <?php endif; ?>


                                        <?php else: ?>

                                            <span class="text-muted">

                                                চালক নেই

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DAILY RENT -->

                                    <td class="text-center">

                                        <span class="badge bg-primary-subtle text-primary px-3 py-2">

                                            ৳

                                            <?= number_format(
                                                (float)$vehicle['daily_rent'],
                                                2
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- COLLECTION -->

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
                                                class="form-control collection-input"
                                                value="<?= htmlspecialchars(
                                                    $existingAmount
                                                ) ?>"
                                                placeholder="জমার টাকা"
                                            >

                                        </div>

                                    </td>


                                    <!-- EXPENSE -->

                                    <td>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                ৳

                                            </span>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="expense_amount[<?= $vehicleId ?>]"
                                                class="form-control expense-input"
                                                value="<?= htmlspecialchars(
                                                    $existingExpense
                                                ) ?>"
                                                placeholder="খরচ"
                                            >

                                        </div>

                                    </td>


                                    <!-- EXPENSE NOTE -->

                                    <td>

                                        <input
                                            type="text"
                                            name="expense_note[<?= $vehicleId ?>]"
                                            class="form-control"
                                            value="<?= htmlspecialchars(
                                                $existingNote
                                            ) ?>"
                                            placeholder="যেমন: গ্যাস, মেরামত..."
                                        >

                                    </td>


                                    <!-- STATUS -->

                                    <td
                                        class="text-center status-cell"
                                    >

                                        <?php if ($isPaid): ?>

                                            <span class="badge bg-success px-3 py-2">

                                                <i class="bi bi-check-circle-fill me-1"></i>

                                                জমা হয়েছে

                                            </span>


                                        <?php elseif (
                                            $existing
                                            &&
                                            (float)$existingExpense > 0
                                        ): ?>

                                            <span class="badge bg-danger-subtle text-danger px-3 py-2">

                                                <i class="bi bi-wallet2 me-1"></i>

                                                খরচ আছে

                                            </span>


                                        <?php else: ?>

                                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">

                                                <i class="bi bi-clock-fill me-1"></i>

                                                বাকি

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


            <!-- =================================================
                 FOOTER
            ================================================== -->

            <?php if (!empty($vehicles)): ?>

                <div class="card-footer bg-white border-0 p-3">

                    <div class="row align-items-center g-3">


                        <div class="col-lg-4">

                            <div class="text-muted">

                                <i class="bi bi-info-circle text-primary me-1"></i>

                                যেই গাড়ির কোনো হিসাব নেই,

                                সেই ঘরগুলো খালি রাখুন।

                            </div>

                        </div>


                        <div class="col-lg-8">

                            <div class="d-flex flex-wrap justify-content-lg-end align-items-center gap-4">


                                <!-- TOTAL COLLECTION -->

                                <div class="text-end">

                                    <small class="text-muted">

                                        মোট জমা

                                    </small>

                                    <div class="fw-bold text-success">

                                        ৳

                                        <span id="footerTotal">

                                            <?= number_format(
                                                $totalToday,
                                                2
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                                <!-- TOTAL EXPENSE -->

                                <div class="text-end">

                                    <small class="text-muted">

                                        মোট খরচ

                                    </small>

                                    <div class="fw-bold text-danger">

                                        ৳

                                        <span id="footerExpense">

                                            <?= number_format(
                                                $totalExpense,
                                                2
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                                <!-- NET -->

                                <div class="text-end">

                                    <small class="text-muted">

                                        নেট আয়

                                    </small>

                                    <div class="fw-bold text-primary fs-5">

                                        ৳

                                        <span id="footerNet">

                                            <?= number_format(
                                                $netToday,
                                                2
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                                <!-- SAVE -->

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg px-4"
                                    id="saveButton"
                                >

                                    <i class="bi bi-save2 me-1"></i>

                                    হিসাব সংরক্ষণ করুন

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </form>

</div>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function () {


    // =================================================
    // DATE + GARAGE FILTER
    // =================================================

    const garageSelect =
        document.getElementById('garageSelect');

    const dateSelect =
        document.getElementById('dateSelect');


    function applyFilters() {

        const garageId =
            garageSelect
                ? garageSelect.value
                : '0';

        const selectedDate =
            dateSelect
                ? dateSelect.value
                : '<?= htmlspecialchars($selectedDate) ?>';


        let url =
            'index.php?page=cng/cng_rent';


        const params = [];


        // DATE

        if (selectedDate) {

            params.push(
                'collection_date=' +
                encodeURIComponent(selectedDate)
            );
        }


        // GARAGE

        if (garageId !== '0') {

            params.push(
                'garage_id=' +
                encodeURIComponent(garageId)
            );
        }


        if (params.length > 0) {

            url += '&' + params.join('&');

        }


        window.location.href = url;

    }


    // GARAGE CHANGE

    if (garageSelect) {

        garageSelect.addEventListener(
            'change',
            applyFilters
        );

    }


    // DATE CHANGE

    if (dateSelect) {

        dateSelect.addEventListener(
            'change',
            applyFilters
        );

    }


    // =================================================
    // LIVE TOTAL
    // =================================================

    const collectionInputs =
        document.querySelectorAll(
            '.collection-input'
        );


    const expenseInputs =
        document.querySelectorAll(
            '.expense-input'
        );


    function updateTotals() {

        let total = 0;

        let expense = 0;

        let paid = 0;

        let due = 0;


        // COLLECTION

        collectionInputs.forEach(
            function (input) {

                const value =
                    parseFloat(input.value) || 0;


                if (value > 0) {

                    paid++;

                    total += value;

                }

            }
        );


        // EXPENSE

        expenseInputs.forEach(
            function (input) {

                const value =
                    parseFloat(input.value) || 0;


                if (value > 0) {

                    expense += value;

                }

            }
        );


        // DUE

        due =
            collectionInputs.length - paid;


        // NET

        const net =
            total - expense;


        const formattedTotal =
            total.toLocaleString(
                'en-US',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        const formattedExpense =
            expense.toLocaleString(
                'en-US',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        const formattedNet =
            net.toLocaleString(
                'en-US',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        // =================================================
        // TOTAL
        // =================================================

        const footerTotal =
            document.getElementById(
                'footerTotal'
            );


        if (footerTotal) {

            footerTotal.textContent =
                formattedTotal;

        }


        // =================================================
        // EXPENSE
        // =================================================

        const footerExpense =
            document.getElementById(
                'footerExpense'
            );


        const expenseTotal =
            document.getElementById(
                'expenseTotal'
            );


        if (footerExpense) {

            footerExpense.textContent =
                formattedExpense;

        }


        if (expenseTotal) {

            expenseTotal.textContent =
                '৳ ' +
                formattedExpense;

        }


        // =================================================
        // NET
        // =================================================

        const footerNet =
            document.getElementById(
                'footerNet'
            );


        const topNet =
            document.getElementById(
                'topNet'
            );


        if (footerNet) {

            footerNet.textContent =
                formattedNet;

        }


        if (topNet) {

            topNet.textContent =
                formattedNet;

        }


        // =================================================
        // PAID / DUE
        // =================================================

        const paidCount =
            document.getElementById(
                'paidCount'
            );


        const dueCount =
            document.getElementById(
                'dueCount'
            );


        if (paidCount) {

            paidCount.textContent =
                paid;

        }


        if (dueCount) {

            dueCount.textContent =
                due;

        }

    }


    // =================================================
    // INPUT EVENTS
    // =================================================

    collectionInputs.forEach(
        function (input) {

            input.addEventListener(
                'input',
                updateTotals
            );

        }
    );


    expenseInputs.forEach(
        function (input) {

            input.addEventListener(
                'input',
                updateTotals
            );

        }
    );


    // INITIAL TOTAL

    updateTotals();


    // =================================================
    // FORM SUBMIT
    // =================================================

    const form =
        document.getElementById(
            'collectionForm'
        );


    if (form) {

        form.addEventListener(
            'submit',
            function (e) {

                let hasData = false;


                // COLLECTION CHECK

                collectionInputs.forEach(
                    function (input) {

                        const amount =
                            parseFloat(
                                input.value
                            ) || 0;


                        if (amount > 0) {

                            hasData = true;

                        }

                    }
                );


                // EXPENSE CHECK

                expenseInputs.forEach(
                    function (input) {

                        const expense =
                            parseFloat(
                                input.value
                            ) || 0;


                        if (expense > 0) {

                            hasData = true;

                        }

                    }
                );


                // NO DATA

                if (!hasData) {

                    e.preventDefault();

                    alert(
                        'কোনো জমা বা খরচের টাকা দেওয়া হয়নি।'
                    );

                    return false;

                }


                // CONFIRM

                const selectedDate =
                    document.getElementById(
                        'collectionDateInput'
                    )?.value;


                const formattedDate =
                    selectedDate
                        ? selectedDate
                        : 'নির্বাচিত তারিখ';


                const confirmed =
                    confirm(
                        '📅 তারিখ: ' +
                        formattedDate +
                        '\n\n' +
                        'আপনি কি এই দিনের CNG হিসাব সংরক্ষণ করতে চান?'
                    );


                if (!confirmed) {

                    e.preventDefault();

                    return false;

                }

            }
        );

    }

});

</script>


<!-- =====================================================
     CSS
====================================================== -->

<style>

.garage-info-box {

    background: #f8f9fa;

    border: 1px solid #e9ecef;

    border-radius: 12px;

    padding: 10px 15px;

    min-height: 58px;

    display: flex;

    flex-direction: column;

    justify-content: center;

}


.date-info-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: rgba(13, 110, 253, .10);

    color: #0d6efd;

    font-size: 22px;

    flex-shrink: 0;

}


.car-icon {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: rgba(13, 110, 253, .10);

    color: #0d6efd;

    font-size: 19px;

}


.collection-input,

.expense-input {

    min-width: 130px;

    font-weight: 600;

}


.table > :not(caption) > * > * {

    padding-top: 14px;

    padding-bottom: 14px;

}


.table thead th {

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

}


.table tbody td {

    font-size: 14px;

}


.collection-input:focus,

.expense-input:focus,

.form-control:focus,

.form-select:focus {

    border-color: #0d6efd;

    box-shadow:

        0 0 0 .2rem

        rgba(13, 110, 253, .10);

}


input[type="date"] {

    cursor: pointer;

}


@media (max-width: 768px) {

    .table {

        min-width: 1250px;

    }


    .card-footer .btn {

        width: 100%;

    }


    .date-info-icon {

        width: 42px;

        height: 42px;

        font-size: 18px;

    }

}

</style>
 
