<?php 
//  phplibary/libary.php

 
  
// ---------- সার্চ + রেকর্ড খোঁজা ----------
// GET id দিয়ে অথবা সার্চ বক্স থেকে invoice_no / phone দিয়ে খোঁজা যাবে
$id      = trim($_GET['id'] ?? '');
$search  = trim($_GET['search'] ?? '');
$record  = null;
$error   = '';

if ($search !== '') {
    // সার্চ বক্স থেকে ইনভয়েস নং / ফোন নম্বর দিয়ে খোঁজা
    $stmt = $pdo->prepare("SELECT * FROM customer_records WHERE invoice_no = ? OR customer_phone = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$search, $search]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$record) {
        $error = 'কোনো রেকর্ড খুঁজে পাওয়া যায়নি!';
    }
} elseif ($id !== '') {
    if (!is_numeric($id)) {
        $error = 'অবৈধ আইডি!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customer_records WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            $error = 'রেকর্ড পাওয়া যায়নি!';
        }
    }
}

// কার্ডের জন্য নিরাপদ ডিফল্ট মান
$total = $record['total_price']  ?? 0;
$paid  = $record['paid_amount']  ?? 0;
$due   = $record['due_amount']   ?? 0;


?>

<style>
:root {
    --primary: #1e3a8a;
    --primary-light: #2563eb;
    --accent: #0ea5e9;
    --success: #16a34a;
    --danger: #dc2626;
    --bg: #f1f5f9;
    --card-bg: #ffffff;
    --border: #e2e8f0;
    --text: #1e293b;
    --muted: #64748b;
}

* {
    box-sizing: border-box;
}

.wrap {
    max-width: 900px;
    margin: 0 auto;
}

/* ---------- সার্চ বার ---------- */
.search-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 22px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-box input[type=text] {
    flex: 1;
    min-width: 220px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-family: inherit;
    font-size: 15px;
    outline: none;
    transition: border-color .15s;
}

.search-box input[type=text]:focus {
    border-color: var(--primary-light);
}

.search-box button {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}

.search-box button:hover {
    background: var(--primary-light);
}

.alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: var(--danger);
    padding: 14px 18px;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
}

/* ---------- সামারি কার্ড ---------- */
.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    text-align: center;
    border-top: 4px solid var(--primary-light);
    transition: transform .15s;
}

.card.total {
    border-top-color: var(--primary-light);
}

.card.paid {
    border-top-color: var(--success);
}

.card.due {
    border-top-color: var(--danger);
}

.card .label {
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: .3px;
}

.card .value {
    font-size: 24px;
    font-weight: 700;
}

.card.total .value {
    color: var(--primary);
}

.card.paid .value {
    color: var(--success);
}

.card.due .value {
    color: var(--danger);
}

/* ---------- রিসিপ্ট ---------- */
.receipt {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.receipt-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    padding: 32px 30px 26px;
    text-align: center;
}

.receipt-header .title {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: 1px;
    margin: 0 0 12px;
}

.receipt-header .meta {
    display: flex;
    justify-content: center;
    gap: 26px;
    flex-wrap: wrap;
    font-size: 14px;
    opacity: .95;
}

.receipt-body {
    padding: 26px 30px 10px;
}

.section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--primary);
    margin: 22px 0 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--border);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
}

th,
td {
    padding: 11px 14px;
    font-size: 14.5px;
}

th {
    width: 38%;
    text-align: left;
    color: var(--muted);
    font-weight: 600;
    background: #f8fafc;
    border: 1px solid var(--border);
    border-right: none;
}

td {
    border: 1px solid var(--border);
    border-left: none;
}

.amount-table td {
    text-align: right;
    font-weight: 700;
}

.amount-table .due-row td {
    color: var(--danger);
    font-size: 16px;
}

.amount-table .paid-row td {
    color: var(--success);
}

.footer {
    text-align: center;
    padding: 22px 20px 30px;
    font-size: 13.5px;
    color: var(--muted);
    border-top: 1px dashed var(--border);
    margin-top: 10px;
}

.footer strong {
    color: var(--text);
}

.print-btn {
    display: block;
    margin: 20px auto 0;
    background: var(--accent);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.print-btn:hover {
    opacity: .9;
}

@media(max-width:640px) {
    .cards {
        grid-template-columns: 1fr;
    }

    th {
        width: 45%;
    }
}

@media print {
    body {
        background: #fff;
        padding: 0;
    }

    .search-box,
    .print-btn {
        display: none;
    }

    .receipt {
        box-shadow: none;
        border: 1px solid #000;
    }
}
</style>



<div class="wrap">

    <!-- সার্চ বার -->
    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="ইনভয়েস নং অথবা ফোন নম্বর দিয়ে সার্চ করুন..."
            value="<?= htmlspecialchars($search) ?>">
        <button type="submit">সার্চ করুন</button>
    </form>

    <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($record): ?>

    <!-- অ্যামাউন্ট কার্ড (সার্চ/আইডি অনুযায়ী আপডেট হবে) -->
    <div class="cards">
        <div class="card total">
            <div class="label">মোট টাকা</div>
            <div class="value">৳ <?= number_format($total, 2) ?></div>
        </div>
        <div class="card paid">
            <div class="label">পেইড অ্যামাউন্ট</div>
            <div class="value">৳ <?= number_format($paid, 2) ?></div>
        </div>
        <div class="card due">
            <div class="label">বাকি টাকা</div>
            <div class="value">৳ <?= number_format($due, 2) ?></div>
        </div>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <p class="title">ইনভয়েস / রসিদ</p>
            <div class="meta">
                <span><strong>ইনভয়েস নং:</strong> <?= htmlspecialchars($record['invoice_no'] ?? 'N/A') ?></span>
                <span><strong>তারিখ:</strong> <?= date('d-m-Y h:i A', strtotime($record['created_at'])) ?></span>
            </div>
        </div>

        <div class="receipt-body">
            <div class="section-title">কাস্টমারের তথ্য</div>
            <table>
                <tr>
                    <th>কাস্টমারের নাম</th>
                    <td><?= bn_number(htmlspecialchars($record['customer_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>ফোন নম্বর</th>
                    <td><?= bn_number(htmlspecialchars($record['customer_phone'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>এনআইডি নম্বর</th>
                    <td><?= bn_number(htmlspecialchars($record['nid'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>ঠিকানা</th>
                    <td><?= bn_number(htmlspecialchars($record['address'] ?? '')) ?></td>
                </tr>
            </table>

            <div class="section-title">গাড়ির তথ্য</div>
            <table>
                <tr>
                    <th>গাড়ির নাম/মডেল</th>
                    <td><?= htmlspecialchars($record['car_name'] ?? '') ?>
                        (<?= htmlspecialchars($record['car_model'] ?? '') ?>)</td>
                </tr>
                <tr>
                    <th>গাড়ির নম্বর</th>
                    <td><?= htmlspecialchars($record['car_number'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>সাল</th>
                    <td><?= htmlspecialchars($record['car_year'] ?? '') ?></td>
                </tr>
            </table>

            <div class="section-title">পেমেন্ট তথ্য</div>
            <table class="amount-table">
                <tr>
                    <th>ধরন</th>
                    <td>
                        <?= ($record['type'] === 'installment') 
    ? 'কিস্তি' 
    : strtoupper(htmlspecialchars($record['type'] ?? '')) ?>
                    </td>
                </tr>
                <tr>
                    <th>মোট টাকা</th>
                    <td>৳ <?= bn_number(number_format($total, 2)) ?></td>
                </tr>
                <tr class="paid-row">
                    <th>পেইড অ্যামাউন্ট</th>
                    <td>৳ <?= bn_number(number_format($paid, 2)) ?></td>
                </tr>
                <tr class="due-row">
                    <th>বাকি টাকা</th>
                    <td>৳ <?= bn_number(number_format($due, 2)) ?></td>
                </tr>
            </table>

            <?php if (($record['type'] ?? '') == 'installment'): ?>
            <div class="section-title">কিস্তি তথ্য</div>
            <table>
                <tr>
                    <th>মোট কিস্তি</th>
                    <td><?= bn_number((int)($record['total_kisti'] ?? 0)) ?> মাস</td>
                </tr>
                <tr>
                    <th>মাসিক কিস্তি</th>
                    <td>৳ <?= bn_number(number_format($record['monthly_kisti'] ?? 0, 2)) ?></td>
                </tr>
                <tr>
                    <th>কিস্তি শুরুর তারিখ</th>
                    <td><?= bn_number(!empty($record['kisti_start_date']) ? date('d-m-Y', strtotime($record['kisti_start_date'])) : 'N/A') ?>
                    </td>
                </tr>
                <tr>
                    <th>পরবর্তী কিস্তির তারিখ</th>
                    <td><?= bn_number(!empty($record['next_due_date']) ? date('d-m-Y', strtotime($record['next_due_date'])) : 'N/A') ?>
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <?php if (!empty($record['note'])): ?>
            <div class="section-title">বিশেষ নোট</div>
            <p><?= nl2br(htmlspecialchars($record['note'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p><strong>ধন্যবাদ! আপনার সাথে থাকার জন্য।</strong></p>
            <p>প্রিন্ট তারিখ: <?= date('d-m-Y h:i A') ?></p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ প্রিন্ট করুন</button>

    <?php elseif (!$error): ?>
    <div class="alert" style="background:#eff6ff;border-color:#bfdbfe;color:var(--primary);">
        উপরে ইনভয়েস নং বা ফোন নম্বর লিখে সার্চ করুন।
    </div>
    <?php endif; ?>

</div>
</body>

</html>