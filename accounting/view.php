<?php
 

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid ID');
}


$stmt = $pdo->prepare("
    SELECT
        at.*,
        ah.head_name,
        ca.account_name AS cash_account_name,
        ba.bank_name,
        ba.account_name AS bank_account_name,
        ba.account_number
    FROM accounting_transactions at

    LEFT JOIN account_heads ah
        ON ah.id = at.head_id

    LEFT JOIN cash_accounts ca
        ON ca.id = at.cash_account_id

    LEFT JOIN bank_accounts ba
        ON ba.id = at.bank_account_id

    WHERE at.id = ?
");

$stmt->execute([$id]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$row) {
    die('হিসাব পাওয়া যায়নি।');
}
?>
 
<div class="container py-4">

    <div class="d-flex justify-content-between mb-4">

        <h3>হিসাবের বিস্তারিত</h3>

        <div>

            <a href="edit.php?id=<?= $row['id'] ?>"
               class="btn btn-primary">
                Edit
            </a>

            <a href="index.php"
               class="btn btn-secondary">
                Back
            </a>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="row g-4">


                <div class="col-md-6">

                    <strong>তারিখ</strong>

                    <div>
                        <?= htmlspecialchars($row['transaction_date']) ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Account Head</strong>

                    <div>
                        <?= htmlspecialchars($row['head_name'] ?? '') ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>হিসাবের ধরন</strong>

                    <div>
                        <?= htmlspecialchars($row['transaction_type']) ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>পরিমাণ</strong>

                    <div class="fs-4 fw-bold">
                        ৳ <?= number_format($row['amount'], 2) ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Payment Method</strong>

                    <div>
                        <?= htmlspecialchars($row['payment_method']) ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Cash Account</strong>

                    <div>
                        <?= htmlspecialchars(
                            $row['cash_account_name'] ?? '-'
                        ) ?>
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Bank Account</strong>

                    <div>

                        <?php if (!empty($row['bank_name'])): ?>

                            <?= htmlspecialchars($row['bank_name']) ?>

                            -

                            <?= htmlspecialchars(
                                $row['bank_account_name'] ?? ''
                            ) ?>

                            <?php if (!empty($row['account_number'])): ?>

                                (<?= htmlspecialchars(
                                    $row['account_number']
                                ) ?>)

                            <?php endif; ?>

                        <?php else: ?>

                            -

                        <?php endif; ?>

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Reference</strong>

                    <div>
                        <?= htmlspecialchars(
                            $row['reference'] ?? '-'
                        ) ?>
                    </div>

                </div>


                <div class="col-12">

                    <strong>বিবরণ</strong>

                    <div class="border rounded p-3 mt-2">

                        <?= nl2br(
                            htmlspecialchars(
                                $row['description'] ?? ''
                            )
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
 