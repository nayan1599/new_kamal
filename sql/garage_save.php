<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: index.php?page=garage/index'
    );

    exit;

}


$garage_id =
    (int)($_POST['garage_id'] ?? 0);

$transaction_date =
    $_POST['transaction_date']
    ?? date('Y-m-d');

$type =
    $_POST['type']
    ?? '';

$category =
    trim($_POST['category'] ?? '');

$amount =
    (float)($_POST['amount'] ?? 0);

$description =
    trim($_POST['description'] ?? '');


// =========================
// VALIDATION
// =========================

if (
    !$garage_id ||
    !$category ||
    $amount <= 0 ||
    !in_array(
        $type,
        ['income', 'expense'],
        true
    )
) {

    die("
        <div style='
            text-align:center;
            margin-top:50px;
            color:red;
        '>
            <h3>তথ্য সঠিকভাবে পূরণ করুন!</h3>
        </div>
    ");

}


// =========================
// INSERT
// =========================

$stmt = $pdo->prepare("
    INSERT INTO garage_transactions
    (
        garage_id,
        transaction_date,
        type,
        category,
        amount,
        description
    )

    VALUES
    (
        :garage_id,
        :transaction_date,
        :type,
        :category,
        :amount,
        :description
    )
");


$stmt->execute([

    ':garage_id' =>
        $garage_id,

    ':transaction_date' =>
        $transaction_date,

    ':type' =>
        $type,

    ':category' =>
        $category,

    ':amount' =>
        $amount,

    ':description' =>
        $description

]);


header(
    'Location: index.php?page=garage/index'
);

exit;