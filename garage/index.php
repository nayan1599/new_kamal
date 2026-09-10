
<?php

// =========================
// DATE & FILTER
// =========================

$today = date('Y-m-d');

$from_date = $_GET['from_date'] ?? $today;
$to_date   = $_GET['to_date'] ?? $today;

$garage_id = $_GET['garage_id'] ?? '';
$type      = $_GET['type'] ?? '';
$search    = trim($_GET['search'] ?? '');


// =========================
// GARAGE LIST
// =========================

$garageStmt = $pdo->query("
    SELECT *
    FROM garages
    WHERE status = 'active'
    ORDER BY id ASC
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// PAGINATION
// =========================

$limit = 10;

$page = isset($_GET['p'])
    ? max(1, (int)$_GET['p'])
    : 1;

$offset = ($page - 1) * $limit;


// =========================
// WHERE
// =========================

$where = "
    gt.transaction_date BETWEEN :from_date AND :to_date
";

$params = [
    ':from_date' => $from_date,
    ':to_date'   => $to_date
];


// =========================
// GARAGE FILTER
// =========================

if ($garage_id !== '') {

    $where .= " AND gt.garage_id = :garage_id";

    $params[':garage_id'] = $garage_id;
}


// =========================
// TYPE FILTER
// =========================

if ($type !== '') {

    $where .= " AND gt.type = :type";

    $params[':type'] = $type;
}


// =========================
// SEARCH
// =========================

if ($search !== '') {

    $where .= "
        AND (
            gt.category LIKE :search
            OR gt.description LIKE :search
            OR g.garage_name LIKE :search
        )
    ";

    $params[':search'] = "%{$search}%";
}


// =========================
// TOTAL ROW COUNT
// =========================

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM garage_transactions gt

    LEFT JOIN garages g
        ON g.id = gt.garage_id

    WHERE $where
");

$countStmt->execute($params);

$totalRows = (int)$countStmt->fetchColumn();

$totalPages = max(
    1,
    ceil($totalRows / $limit)
);


// =========================
// TRANSACTIONS
// =========================

$sql = "
    SELECT
        gt.*,
        g.garage_name

    FROM garage_transactions gt

    LEFT JOIN garages g
        ON g.id = gt.garage_id

    WHERE $where

    ORDER BY
        gt.transaction_date DESC,
        gt.id DESC

    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="container-fluid p-4">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                📋 গ্যারেজ লেনদেন
            </h3>

            <div class="text-muted">
                আয় ও ব্যয়ের লেনদেনের তালিকা
            </div>

        </div>


        <a
            href="index.php?page=garage/add"
            class="btn btn-primary"
        >

            <i class="bi bi-plus-circle"></i>

            নতুন হিসাব

        </a>

    </div>


    <!-- SEARCH / FILTER -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <input
                    type="hidden"
                    name="page"
                    value="garage/index"
                >


                <div class="row g-3 align-items-end">


                    <!-- SEARCH -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            🔍 সার্চ
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            class="form-control"
                            placeholder="খাত / বিবরণ / গ্যারেজ"
                        >

                    </div>


                    <!-- GARAGE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            গ্যারেজ
                        </label>

                        <select
                            name="garage_id"
                            class="form-select"
                        >

                            <option value="">
                                সব গ্যারেজ
                            </option>

                            <?php foreach ($garages as $garage): ?>

                                <option
                                    value="<?= $garage['id'] ?>"
                                    <?= $garage_id == $garage['id']
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


                    <!-- TYPE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            ধরন
                        </label>

                        <select
                            name="type"
                            class="form-select"
                        >

                            <option value="">
                                সব
                            </option>

                            <option
                                value="income"
                                <?= $type === 'income'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                আয়
                            </option>

                            <option
                                value="expense"
                                <?= $type === 'expense'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                ব্যয়
                            </option>

                        </select>

                    </div>


                    <!-- FROM DATE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            শুরু
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="<?= htmlspecialchars($from_date) ?>"
                            class="form-control"
                        >

                    </div>


                    <!-- TO DATE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            শেষ
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="<?= htmlspecialchars($to_date) ?>"
                            class="form-control"
                        >

                    </div>


                    <!-- BUTTON -->

                    <div class="col-md-1">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                            title="সার্চ"
                        >

                            🔍

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- LIST -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0 fw-bold">
                📋 লেনদেনের তালিকা
            </h5>


            <span class="badge bg-primary">

                মোট
                <?= bn_number(number_format($totalRows)) ?>
                টি

            </span>

        </div>


        <div class="table-responsive">

            <table class="table table-hover table-bordered mb-0">

                <thead class="table-dark">

                    <tr>

                        <th width="60">
                            #
                        </th>

                        <th>
                            তারিখ
                        </th>

                        <th>
                            গ্যারেজ
                        </th>

                        <th>
                            ধরন
                        </th>

                        <th>
                            খাত
                        </th>

                        <th>
                            পরিমাণ
                        </th>

                        <th>
                            বিবরণ
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($transactions): ?>


                    <?php foreach ($transactions as $key => $row): ?>


                        <tr>


                            <!-- SERIAL -->

                            <td>

                                <?= bn_number(
                                    $offset + $key + 1
                                ) ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?= bn_number(
                                    date(
                                        'd-m-Y',
                                        strtotime(
                                            $row['transaction_date']
                                        )
                                    )
                                ) ?>

                            </td>


                            <!-- GARAGE -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['garage_name']
                                        ?? '—'
                                    ) ?>

                                </strong>

                            </td>


                            <!-- TYPE -->

                            <td>

                                <?php if (
                                    $row['type'] === 'income'
                                ): ?>

                                    <span class="badge bg-success">
                                        আয়
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-danger">
                                        ব্যয়
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['category']
                                    ?? ''
                                ) ?>

                            </td>


                            <!-- AMOUNT -->

                            <td class="fw-bold">

                                ৳
                                <?= bn_number(
                                    number_format(
                                        (float)$row['amount'],
                                        2
                                    )
                                ) ?>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['description']
                                    ?? ''
                                ) ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            class="text-center py-5 text-muted"
                        >

                            <div class="fs-1">
                                📭
                            </div>

                            কোনো লেনদেন পাওয়া যায়নি।

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


        <!-- PAGINATION -->

        <?php if ($totalPages > 1): ?>


            <div class="card-footer bg-white">

                <div class="d-flex justify-content-between align-items-center">


                    <!-- INFO -->

                    <div class="text-muted">

                        পেজ

                        <?= bn_number($page) ?>

                        /

                        <?= bn_number($totalPages) ?>

                    </div>


                    <!-- PAGINATION -->

                    <nav>

                        <ul class="pagination mb-0">


                            <!-- PREVIOUS -->

                            <?php if ($page > 1): ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="?page=garage/index
                                        &p=<?= $page - 1 ?>
                                        &search=<?= urlencode($search) ?>
                                        &garage_id=<?= urlencode($garage_id) ?>
                                        &type=<?= urlencode($type) ?>
                                        &from_date=<?= urlencode($from_date) ?>
                                        &to_date=<?= urlencode($to_date) ?>"
                                    >

                                        ‹ পূর্বের

                                    </a>

                                </li>

                            <?php endif; ?>


                            <!-- PAGE NUMBERS -->

                            <?php

                            $startPage = max(
                                1,
                                $page - 2
                            );

                            $endPage = min(
                                $totalPages,
                                $page + 2
                            );

                            ?>


                            <?php for (
                                $i = $startPage;
                                $i <= $endPage;
                                $i++
                            ): ?>


                                <li
                                    class="page-item
                                    <?= $i == $page
                                        ? 'active'
                                        : ''
                                    ?>"
                                >

                                    <a
                                        class="page-link"
                                        href="?page=garage/index
                                        &p=<?= $i ?>
                                        &search=<?= urlencode($search) ?>
                                        &garage_id=<?= urlencode($garage_id) ?>
                                        &type=<?= urlencode($type) ?>
                                        &from_date=<?= urlencode($from_date) ?>
                                        &to_date=<?= urlencode($to_date) ?>"
                                    >

                                        <?= bn_number($i) ?>

                                    </a>

                                </li>


                            <?php endfor; ?>


                            <!-- NEXT -->

                            <?php if ($page < $totalPages): ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="?page=garage/index
                                        &p=<?= $page + 1 ?>
                                        &search=<?= urlencode($search) ?>
                                        &garage_id=<?= urlencode($garage_id) ?>
                                        &type=<?= urlencode($type) ?>
                                        &from_date=<?= urlencode($from_date) ?>
                                        &to_date=<?= urlencode($to_date) ?>"
                                    >

                                        পরের ›

                                    </a>

                                </li>

                            <?php endif; ?>


                        </ul>

                    </nav>


                </div>

            </div>


        <?php endif; ?>


    </div>


</div>

