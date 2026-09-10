<?php


$error = '';

$heads = $pdo->query("
    SELECT id, head_name, head_type
    FROM account_heads
    WHERE status = 1
    ORDER BY head_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$cashAccounts = $pdo->query("
    SELECT id, account_name
    FROM cash_accounts
    WHERE status = 1
    ORDER BY account_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$bankAccounts = $pdo->query("
    SELECT id, bank_name, account_name, account_number
    FROM bank_accounts
    WHERE status = 1
    ORDER BY bank_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
    $headId          = (int)($_POST['head_id'] ?? 0);
    $transactionType = $_POST['transaction_type'] ?? '';
    $amount          = (float)($_POST['amount'] ?? 0);
    $paymentMethod   = $_POST['payment_method'] ?? 'cash';

    $cashAccountId = !empty($_POST['cash_account_id'])
        ? (int)$_POST['cash_account_id']
        : null;

    $bankAccountId = !empty($_POST['bank_account_id'])
        ? (int)$_POST['bank_account_id']
        : null;

    $description = trim($_POST['description'] ?? '');
    $reference   = trim($_POST['reference'] ?? '');


    if ($headId <= 0) {
        $error = 'হিসাবের Head নির্বাচন করুন।';

    } elseif (
        !in_array(
            $transactionType,
            ['income', 'expense', 'deposit', 'withdraw', 'transfer']
        )
    ) {
        $error = 'সঠিক হিসাবের ধরন নির্বাচন করুন।';

    } elseif ($amount <= 0) {
        $error = 'সঠিক টাকার পরিমাণ দিন।';

    } elseif (!in_array($paymentMethod, ['cash', 'bank'])) {
        $error = 'সঠিক Payment Method নির্বাচন করুন।';

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO accounting_transactions
                (
                    transaction_date,
                    head_id,
                    transaction_type,
                    amount,
                    payment_method,
                    cash_account_id,
                    bank_account_id,
                    description,
                    reference
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->execute([
                $transactionDate,
                $headId,
                $transactionType,
                $amount,
                $paymentMethod,
                $cashAccountId,
                $bankAccountId,
                $description,
                $reference
            ]);


            header("Location: index.php?success=added");
            exit;

        } catch (PDOException $e) {

            $error = 'হিসাব সংরক্ষণ করতে সমস্যা হয়েছে: '
                   . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <title>নতুন হিসাব</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>নতুন হিসাব</h3>
            <p class="text-muted mb-0">
                নতুন আয় বা খরচের হিসাব যোগ করুন
            </p>
        </div>

        <a href="index.php"
           class="btn btn-secondary">
            হিসাবের তালিকা
        </a>

    </div>


    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form method="POST">

                <div class="row g-3">


                    <!-- Date -->

                    <div class="col-md-6">

                        <label class="form-label">
                            হিসাবের তারিখ
                        </label>

                        <input type="date"
                               name="transaction_date"
                               class="form-control"
                               value="<?= htmlspecialchars(
                                   $_POST['transaction_date']
                                   ?? date('Y-m-d')
                               ) ?>"
                               required>

                    </div>


                    <!-- Head -->

                    <div class="col-md-6">

                        <label class="form-label">
                            হিসাবের Head
                        </label>

                        <select name="head_id"
                                class="form-select"
                                required>

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <?php foreach ($heads as $head): ?>

                                <option value="<?= $head['id'] ?>"
                                    <?= (
                                        ($_POST['head_id'] ?? '') == $head['id']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars(
                                        $head['head_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Type -->

                    <div class="col-md-6">

                        <label class="form-label">
                            হিসাবের ধরন
                        </label>

                        <select name="transaction_type"
                                class="form-select"
                                required>

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <option value="income"
                                <?= (
                                    ($_POST['transaction_type'] ?? '') === 'income'
                                ) ? 'selected' : '' ?>>
                                আয়
                            </option>

                            <option value="expense"
                                <?= (
                                    ($_POST['transaction_type'] ?? '') === 'expense'
                                ) ? 'selected' : '' ?>>
                                খরচ
                            </option>

                            <option value="deposit"
                                <?= (
                                    ($_POST['transaction_type'] ?? '') === 'deposit'
                                ) ? 'selected' : '' ?>>
                                জমা
                            </option>

                            <option value="withdraw"
                                <?= (
                                    ($_POST['transaction_type'] ?? '') === 'withdraw'
                                ) ? 'selected' : '' ?>>
                                উত্তোলন
                            </option>

                            <option value="transfer"
                                <?= (
                                    ($_POST['transaction_type'] ?? '') === 'transfer'
                                ) ? 'selected' : '' ?>>
                                Transfer
                            </option>

                        </select>

                    </div>


                    <!-- Amount -->

                    <div class="col-md-6">

                        <label class="form-label">
                            টাকার পরিমাণ
                        </label>

                        <input type="number"
                               name="amount"
                               class="form-control"
                               step="0.01"
                               min="0.01"
                               placeholder="0.00"
                               value="<?= htmlspecialchars(
                                   $_POST['amount'] ?? ''
                               ) ?>"
                               required>

                    </div>


                    <!-- Payment Method -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Payment Method
                        </label>

                        <select name="payment_method"
                                id="payment_method"
                                class="form-select"
                                onchange="toggleAccounts()"
                                required>

                            <option value="cash"
                                <?= (
                                    ($_POST['payment_method'] ?? 'cash') === 'cash'
                                )
                                    ? 'selected'
                                    : '' ?>>
                                Cash
                            </option>

                            <option value="bank"
                                <?= (
                                    ($_POST['payment_method'] ?? '') === 'bank'
                                )
                                    ? 'selected'
                                    : '' ?>>
                                Bank
                            </option>

                        </select>

                    </div>


                    <!-- Cash -->

                    <div class="col-md-6"
                         id="cash_box">

                        <label class="form-label">
                            Cash Account
                        </label>

                        <select name="cash_account_id"
                                class="form-select">

                            <option value="">
                                -- Cash Account --
                            </option>

                            <?php foreach ($cashAccounts as $cash): ?>

                                <option value="<?= $cash['id'] ?>">

                                    <?= htmlspecialchars(
                                        $cash['account_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Bank -->

                    <div class="col-md-6"
                         id="bank_box"
                         style="display:none;">

                        <label class="form-label">
                            Bank Account
                        </label>

                        <select name="bank_account_id"
                                class="form-select">

                            <option value="">
                                -- Bank Account --
                            </option>

                            <?php foreach ($bankAccounts as $bank): ?>

                                <option value="<?= $bank['id'] ?>">

                                    <?= htmlspecialchars(
                                        $bank['bank_name']
                                    ) ?>

                                    -
                                    <?= htmlspecialchars(
                                        $bank['account_name'] ?? ''
                                    ) ?>

                                    <?php if (!empty($bank['account_number'])): ?>

                                        (<?= htmlspecialchars(
                                            $bank['account_number']
                                        ) ?>)

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Reference -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Reference
                        </label>

                        <input type="text"
                               name="reference"
                               class="form-control"
                               placeholder="Reference No"
                               value="<?= htmlspecialchars(
                                   $_POST['reference'] ?? ''
                               ) ?>">

                    </div>


                    <!-- Description -->

                    <div class="col-12">

                        <label class="form-label">
                            বিবরণ
                        </label>

                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="বিস্তারিত লিখুন"><?= htmlspecialchars(
                                      $_POST['description'] ?? ''
                                  ) ?></textarea>

                    </div>


                    <!-- Submit -->

                    <div class="col-12">

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="bi bi-check-circle"></i>
                            হিসাব সংরক্ষণ

                        </button>

                        <a href="index.php"
                           class="btn btn-secondary">

                            বাতিল

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

function toggleAccounts() {

    const method =
        document.getElementById('payment_method').value;

    const cashBox =
        document.getElementById('cash_box');

    const bankBox =
        document.getElementById('bank_box');


    if (method === 'bank') {

        cashBox.style.display = 'none';
        bankBox.style.display = 'block';

    } else {

        cashBox.style.display = 'block';
        bankBox.style.display = 'none';

    }
}

toggleAccounts();

</script>

</body>
</html>