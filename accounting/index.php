<?php
 

$stmt = $pdo->query("
    SELECT 
        at.*,
        ah.head_name
    FROM accounting_transactions at
    LEFT JOIN account_heads ah ON ah.id = at.head_id
    ORDER BY at.transaction_date DESC, at.id DESC
");

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $row) {
    if ($row['transaction_type'] === 'income') {
        $totalIncome += (float)$row['amount'];
    }

    if ($row['transaction_type'] === 'expense') {
        $totalExpense += (float)$row['amount'];
    }
}

$balance = $totalIncome - $totalExpense;
?>



<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">হিসাবের তালিকা</h3>
            <p class="text-muted mb-0">সকল আয় ও ব্যয়ের হিসাব</p>
        </div>

        <a href="index.php?page=accounting/add" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            নতুন হিসাব
        </a>
    </div>


    <!-- Summary -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">মোট আয়</div>

                    <h3 class="text-success mb-0">
                        ৳ <?= number_format($totalIncome, 2) ?>
                    </h3>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">মোট খরচ</div>

                    <h3 class="text-danger mb-0">
                        ৳ <?= number_format($totalExpense, 2) ?>
                    </h3>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">বর্তমান ব্যালেন্স</div>

                    <h3 class="<?= $balance >= 0 ? 'text-primary' : 'text-danger' ?> mb-0">
                        ৳ <?= number_format($balance, 2) ?>
                    </h3>
                </div>
            </div>
        </div>

    </div>


    <!-- Table -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                    <tr>
                        <th>#</th>
                        <th>তারিখ</th>
                        <th>হিসাবের Head</th>
                        <th>ধরন</th>
                        <th>পরিমাণ</th>
                        <th>পেমেন্ট</th>
                        <th>বিবরণ</th>
                        <th class="text-center">Action</th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php if (!$transactions): ?>

                        <tr>
                            <td colspan="8"
                                class="text-center text-muted py-4">
                                কোনো হিসাব পাওয়া যায়নি
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($transactions as $key => $row): ?>

                            <tr>

                                <td>
                                    <?= $key + 1 ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['transaction_date']) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['head_name'] ?? 'N/A') ?>
                                    </strong>
                                </td>


                                <td>

                                    <?php if ($row['transaction_type'] === 'income'): ?>

                                        <span class="badge bg-success-subtle text-success">
                                            আয়
                                        </span>

                                    <?php elseif ($row['transaction_type'] === 'expense'): ?>

                                        <span class="badge bg-danger-subtle text-danger">
                                            খরচ
                                        </span>

                                    <?php elseif ($row['transaction_type'] === 'deposit'): ?>

                                        <span class="badge bg-primary-subtle text-primary">
                                            জমা
                                        </span>

                                    <?php elseif ($row['transaction_type'] === 'withdraw'): ?>

                                        <span class="badge bg-warning-subtle text-warning">
                                            উত্তোলন
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary-subtle text-secondary">
                                            Transfer
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if (
                                        in_array(
                                            $row['transaction_type'],
                                            ['income', 'deposit']
                                        )
                                    ): ?>

                                        <span class="text-success fw-bold">
                                            + ৳ <?= number_format($row['amount'], 2) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="text-danger fw-bold">
                                            - ৳ <?= number_format($row['amount'], 2) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= ucfirst(htmlspecialchars($row['payment_method'])) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $row['description'] ?? ''
                                    ) ?>
                                </td>


                                <td class="text-center">

                                    <div class="btn-group">

                                        <a href="index.php?page=accounting/view&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-info"
                                           title="View">

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <a href="index.php?page=accounting/edit?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <a href="index.php?page=accounting/delete?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Delete"
                                           onclick="return confirm('এই হিসাবটি কি Delete করতে চান?');">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

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

