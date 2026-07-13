<?php
  
if (!isset($_GET['car_number']) || empty($_GET['car_number'])) {
    die("<h3 class='text-center mt-5 text-danger'>গাড়ির নম্বর দেয়া হয়নি!</h3>");
}
$car_number = trim($_GET['car_number']);

// এই গাড়ির চুক্তি/কাস্টমার রেকর্ড আনা
$stmt = $pdo->prepare("SELECT * FROM customer_records WHERE car_number = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$car_number]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

// সব কিস্তি ফেচ করা
$stmt = $pdo->prepare("SELECT * FROM kisti_payments 
                       WHERE car_number = ? 
                       ORDER BY kisti_number ASC, payment_date DESC");
$stmt->execute([$car_number]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($payments)) {
    die("<h3 class='text-center mt-5 text-danger'>এই গাড়ির কোনো কিস্তি পাওয়া যায়নি!</h3>");
}

// গ্রাহকের তথ্য — রেকর্ড থাকলে সেখান থেকে, না থাকলে পেমেন্ট থেকে
$customer_name = $record['customer_name'] ?? $payments[0]['customer_name'];
$customer_phone = $record['customer_phone'] ?? $payments[0]['customer_phone'];
$customer_email = $record['customer_email'] ?? null;
$customer_nid = $record['nid'] ?? null;
$customer_addr = $record['address'] ?? null;

// সামারি ক্যালকুলেশন (পেমেন্ট হিস্ট্রি থেকে)
$totalPaid = 0;
$totalFine = 0;
$totalKistiPaid = 0; // এখানে kisti_number গুলো যোগ হবে
$maxKisti = 0;
$totalKistiPlanned = 0;
$lastPaymentDate = null;

foreach ($payments as $p) {
    $totalPaid += $p['amount'];
    $totalFine += $p['fine_amount'] ?? 0;
    $totalKistiPaid += $p['kisti_number']; // 1+2+3+5 = 11
    if ($p['kisti_number'] > $maxKisti)
        $maxKisti = $p['kisti_number'];
    if ($lastPaymentDate === null || strtotime($p['payment_date']) > strtotime($lastPaymentDate)) {
        $lastPaymentDate = $p['payment_date'];
    }
}

$maxKisti = $totalKistiPaid - $totalKistiPlanned;

// চুক্তির তথ্য — customer_records টেবিল থেকে (থাকলে)
$hasContractTotal = !empty($record) && floatval($record['total_price']) > 0;
$totalPrice = $hasContractTotal ? floatval($record['total_price']) : null;
$discountAmount = $hasContractTotal ? floatval($record['discount_amount'] ?? 0) : 0;
$netPayable = $hasContractTotal ? ($totalPrice - $discountAmount) : null;
$totalKistiPlanned = $hasContractTotal ? intval($record['total_kisti']) : null;
$monthlyKisti = $hasContractTotal ? floatval($record['monthly_kisti']) : null;
$paid_amount = $hasContractTotal ? floatval($record['paid_amount'] ?? 0) : null;
$nextDueDate = $record['next_due_date'] ?? null;
$kistiStartDate = $record['kisti_start_date'] ?? null;



// বাকি টাকা — রেকর্ডে due_amount থাকলে সেটাই ব্যবহার হবে (সবচেয়ে নির্ভরযোগ্য),
// নাহলে চুক্তির টাকা থেকে হিসাব করা হবে
// if (!empty($record) && isset($record['due_amount'])) {
//     $remainingAmount = floatval($record['due_amount']);
// } elseif ($hasContractTotal) {
//     $remainingAmount = max($netPayable - $totalPaid, 0);
// } else {
//     $remainingAmount = null;
// }




$remainingAmount = 0;

$remainingAmount = $totalPrice - ($paid_amount + $discountAmount + $totalPaid);

$total_due = $remainingAmount + $totalPaid;


$progressPct = ($hasContractTotal && $totalKistiPlanned > 0)
    ? min(100, round(($totalKistiPaid / $totalKistiPlanned) * 100))
    : null;

// রিসিট নং — ইনভয়েস থাকলে সেটাই, নাহলে অটো-জেনারেট
$receiptSerial = $record['invoice_no'] ?? ('JE-' . preg_replace('/[^A-Za-z0-9]/', '', $car_number) . '-' . date('ym', strtotime($lastPaymentDate)));
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মানি রিসিট — <?= htmlspecialchars($car_number) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@500;600;700&family=Hind+Siliguri:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --navy: #0d2340;
        --navy-deep: #081527;
        --gold: #b8863c;
        --gold-light: #e4c98d;
        --paper: #faf6ec;
        --paper-line: #e7ddc7;
        --ink: #1f2a37;
        --ink-soft: #5b6472;
        --green: #1f6f43;
        --red: #9b2226;
        --gray: #6b7280;
    }

    * {
        box-sizing: border-box;
    }



    .mono {
        font-family: 'JetBrains Mono', monospace;
    }

    .display {
        font-family: 'Noto Serif Bengali', serif;
    }

    .receipt {
        position: relative;
        max-width: 900px;
        margin: 0 auto;
        background: var(--paper);
        border-radius: 4px;
        box-shadow: 0 25px 60px -15px rgba(8, 21, 39, 0.35), 0 0 0 1px rgba(13, 35, 64, 0.06);
        overflow: hidden;
    }

    /* guilloche security band */
    .guilloche {
        height: 14px;
        background-color: var(--navy);
        background-image:
            repeating-linear-gradient(115deg, transparent 0 6px, rgba(184, 134, 60, 0.55) 6px 7px, transparent 7px 13px),
            repeating-linear-gradient(65deg, transparent 0 6px, rgba(228, 201, 141, 0.35) 6px 7px, transparent 7px 13px);
    }

    .letterhead {
        background: linear-gradient(160deg, var(--navy) 0%, var(--navy-deep) 100%);
        color: #f4efe1;
        padding: 30px 40px 26px;
        position: relative;
    }

    .letterhead::after {
        content: "";
        position: absolute;
        left: 40px;
        right: 40px;
        bottom: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--gold-light), transparent);
    }

    .lh-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .brand-name {
        font-size: 1.9rem;
        font-weight: 700;
        letter-spacing: .5px;
        margin: 0;
    }

    .brand-tag {
        margin: 4px 0 0;
        font-size: .82rem;
        color: var(--gold-light);
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .doc-meta {
        text-align: right;
        font-size: .85rem;
        color: #d9d2bd;
        line-height: 1.7;
    }

    .doc-meta b {
        color: #fff;
    }

    .doc-title {
        margin-top: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .doc-title .rule {
        flex: 1;
        height: 1px;
        background: rgba(228, 201, 141, 0.35);
    }

    .doc-title span {
        font-size: .95rem;
        letter-spacing: 3px;
        color: var(--gold-light);
        text-transform: uppercase;
    }

    .body-pad {
        padding: 34px 40px 10px;
        position: relative;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-bottom: 30px;
    }

    .info-card {
        border: 1px solid var(--paper-line);
        border-radius: 6px;
        padding: 18px 20px;
        background: #fffdf7;
    }

    .info-card h6 {
        margin: 0 0 12px;
        font-size: .78rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 700;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: .95rem;
    }

    .info-row+.info-row {
        border-top: 1px dashed var(--paper-line);
    }

    .info-row .k {
        color: var(--ink-soft);
    }

    .info-row .v {
        font-weight: 600;
    }

    .stats-wrap {
        position: relative;
        margin-bottom: 22px;
    }

    .seal {
        position: absolute;
        top: -34px;
        right: -6px;
        opacity: .94;
        transform: rotate(-9deg);
        pointer-events: none;
        z-index: 3;
        filter: drop-shadow(0 4px 8px rgba(13, 35, 64, 0.15));
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .progress-track {
        margin-top: 16px;
        height: 8px;
        border-radius: 6px;
        background: var(--paper-line);
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 6px;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
    }

    .progress-label {
        margin-top: 6px;
        font-size: .78rem;
        color: var(--ink-soft);
        text-align: right;
    }

    .stat {
        border: 1px solid var(--paper-line);
        border-radius: 6px;
        padding: 16px 12px;
        text-align: center;
        background: #fffdf7;
    }

    .stat .label {
        font-size: .72rem;
        color: var(--ink-soft);
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .stat .value {
        font-size: 1.35rem;
        font-weight: 700;
        margin-top: 6px;
    }

    .stat .value.gold {
        color: var(--gold);
    }

    .stat .value.green {
        color: var(--green);
    }

    .stat .value.red {
        color: var(--red);
    }

    .stat .sub {
        font-size: .72rem;
        color: var(--gray);
        margin-top: 2px;
    }

    table.ledger {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }

    table.ledger thead th {
        background: var(--navy);
        color: #f2ead3;
        font-weight: 600;
        font-size: .82rem;
        letter-spacing: .4px;
        padding: 11px 12px;
        text-align: left;
    }

    table.ledger thead th.num {
        text-align: right;
    }

    table.ledger tbody td {
        padding: 10px 12px;
        font-size: .9rem;
        border-bottom: 1px solid var(--paper-line);
    }

    table.ledger tbody tr:nth-child(even) {
        background: #f4efe1;
    }

    table.ledger tbody td.num {
        text-align: right;
    }

    table.ledger tbody td.amt {
        font-family: 'JetBrains Mono', monospace;
    }

    .kisti-badge {
        display: inline-block;
        min-width: 26px;
        padding: 2px 6px;
        border-radius: 4px;
        background: var(--navy);
        color: #f2ead3;
        font-family: 'JetBrains Mono', monospace;
        font-size: .8rem;
        text-align: center;
    }

    .section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 14px;
        font-size: .8rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 700;
    }

    .section-label .rule {
        flex: 1;
        height: 1px;
        background: var(--paper-line);
    }

    .signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin: 38px 0 8px;
    }

    .sig-line {
        border-top: 1px solid var(--ink);
        padding-top: 8px;
        font-size: .85rem;
        color: var(--ink-soft);
        text-align: center;
    }

    .footer {
        padding: 18px 40px 26px;
        text-align: center;
        color: #eee5c9;
        background: linear-gradient(160deg, var(--navy-deep) 0%, var(--navy) 100%);
    }

    .footer p {
        margin: 0;
        font-size: .85rem;
    }

    .footer small {
        display: block;
        margin-top: 6px;
        color: #a79f83;
        font-size: .72rem;
        letter-spacing: .4px;
    }

    .actions {
        max-width: 900px;
        margin: 18px auto 0;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .btn {
        border: none;
        border-radius: 6px;
        padding: 12px 22px;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back {
        background: #fff;
        color: var(--navy);
        border: 1px solid var(--paper-line);
    }

    .btn-print {
        background: var(--navy);
        color: #f4efe1;
    }

    .btn-print:hover {
        background: var(--navy-deep);
    }

    @media (max-width: 900px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 720px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .letterhead,
        .body-pad {
            padding-left: 22px;
            padding-right: 22px;
        }

        .seal {
            position: static;
            transform: none;
            margin: 0 auto 14px;
            display: block;
        }
    }

    @media print {
        body {
            background: #fff;
            padding: 0;
        }

        .actions {
            display: none;
        }

        .receipt {
            box-shadow: none;
            border-radius: 0;
        }
    }
    </style>
</head>

<body>
<button onclick="printDiv('receiptArea')" class="btn btn-success">🖨️ Print</button>
    <div class="receipt" id="receiptArea">
        <div class="guilloche"></div>

        <div class="letterhead">
            <div class="lh-top">
                <div>
                    <p class="brand-name display">জাহিরুল এন্টারপ্রাইজ</p>
                    <p class="brand-tag">গাড়ি কিস্তি বিক্রয় ও পরিষেবা</p>
                </div>
                <div class="doc-meta">
                    রিসিট নং: <b class="mono"><?= htmlspecialchars($receiptSerial) ?></b><br>
                    ইস্যুর তারিখ: <b><?= date('d/m/Y') ?></b>
                </div>
            </div>
            <div class="doc-title">
                <p class="">মানি রিসিট • কিস্তি হিসাব বিবরণী</p>
                <div class="rule"></div>
                <p class="mono"><?= htmlspecialchars($car_number) ?></p>
            </div>
        </div>

        <div class="body-pad">
            <div class="info-grid">
                <div class="info-card">
                    <h6>গ্রাহকের তথ্য</h6>
                    <div class="info-row"><span class="k">নাম</span><span
                            class="v"><?= htmlspecialchars($customer_name) ?></span></div>
                    <div class="info-row"><span class="k">মোবাইল</span><span
                            class="v mono"><?= bn_number(htmlspecialchars($customer_phone)) ?></span></div>
                    <?php if ($customer_email): ?>
                    <div class="info-row"><span class="k">ইমেইল</span><span
                            class="v"><?= htmlspecialchars($customer_email) ?></span></div>
                    <?php endif; ?>
                    <?php if ($customer_nid): ?>
                    <div class="info-row"><span class="k">এনআইডি</span><span
                            class="v mono"><?= bn_number(htmlspecialchars($customer_nid)) ?></span></div>
                    <?php endif; ?>
                    <?php if ($customer_addr): ?>
                    <div class="info-row"><span class="k">ঠিকানা</span><span
                            class="v"><?= htmlspecialchars($customer_addr) ?></span></div>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h6>গাড়ির তথ্য</h6>
                    <div class="info-row"><span class="k">গাড়ির নম্বর</span><span
                            class="v mono"><?= bn_number(htmlspecialchars($car_number)) ?></span></div>
                    <?php if (!empty($record)): ?>
                    <div class="info-row"><span class="k">গাড়ির নাম</span><span
                            class="v"><?= htmlspecialchars($record['car_name']) ?></span></div>
                    <div class="info-row"><span class="k">মডেল / বছর</span><span
                            class="v"><?= htmlspecialchars($record['car_model']) ?> /
                            <?= htmlspecialchars($record['car_year']) ?></span></div>
                    <div class="info-row"><span class="k">ক্রয়ের ধরন</span><span
                            class="v"><?= $record['type'] === 'installment' ? 'কিস্তিতে' : htmlspecialchars($record['type']) ?></span>
                    </div>
                       <div class="info-row"><span class="k">ক্রয়ের তারিখ</span>
                       <span class="v"><?= bn_number(date('d/m/Y', strtotime($record['kisti_start_date']))) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row"><span class="k">সর্বশেষ পরিশোধ</span>
                   <span class="v"><?= $lastPaymentDate ? bn_number(date('d/m/Y', strtotime($lastPaymentDate))) : '—' ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h6>চুক্তির তথ্য</h6>
                    <?php if ($hasContractTotal): ?>
                    <div class="info-row"><span class="k">মোট মূল্য</span><span class="v mono">৳
                            <?= bn_number(number_format($totalPrice, 2)) ?></span></div>
                    <?php if ($discountAmount > 0): ?>
                    <div class="info-row"><span class="k">ডিসকাউন্ট</span><span class="v mono">৳
                            <?= bn_number(number_format($discountAmount, 2)) ?></span></div>
                    <?php endif; ?>
                    <div class="info-row"><span class="k">জমাঃ</span><span class="v mono">৳
                            <?= bn_number(number_format($paid_amount, 2)) ?></span></div>
                             <div class="info-row"><span class="k">মোট বাকিঃ</span><span class="v mono">৳
                            <?= bn_number(number_format($total_due, 2)) ?></span></div>
                            
                    <div class="info-row"><span class="k">মাসিক কিস্তি</span><span class="v mono">৳
                            <?= bn_number(number_format($monthlyKisti, 2)) ?></span></div>
                    <!-- paid_amount -->



                    <div class="info-row"><span class="k">মোট কিস্তি সংখ্যা</span><span
                            class="v"><?= bn_number($totalKistiPlanned) ?> টি</span></div>
                    <?php if ($nextDueDate): ?>
                    <div class="info-row"><span class="k">পরবর্তী কিস্তির তারিখ</span><span
                            class="v"><?= bn_number(date('d/m/Y', strtotime($nextDueDate))) ?></span></div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="info-row"><span class="k">চুক্তির তথ্য</span><span class="v">পাওয়া যায়নি</span></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-wrap">
                <div class="stats">
                    <div class="stat">
                        <div class="label">মোট কিস্তি পরিশোধ</div>
                        <div class="value"><?= bn_number($totalKistiPaid) ?> টি</div>
                        <div class="label">মোট কিস্তি বাকি</div>
                        <div class="value">
                            <?= bn_number($hasContractTotal ? $totalKistiPlanned - $totalKistiPaid : '') ?> টি</div>

                    </div>
                    <div class="stat">
                        <div class="label">মোট আদায়</div>
                        <div class="value green mono">৳ <?= bn_number(number_format($totalPaid, 2)) ?></div>
                    </div>
                    <div class="stat">
                        <div class="label">মোট জরিমানা</div>
                        <div class="value <?= $totalFine > 0 ? 'red' : '' ?> mono">৳
                            <?= bn_number(number_format($totalFine, 2)) ?>
                        </div>
<!-- প্রতি দিন ১০০ টাকা করে জরিমান  -->
  <div class="label"> প্রতি দিন <strong class="mono"> ১০০</strong> টাকা করে জরিমান</div>

                    </div>
                    <div class="stat">
                        <div class="label">বাকি টাকা</div>
                        <?php if ($remainingAmount !== null): ?>
                        <div class="value red mono">৳ <?= bn_number(number_format($remainingAmount, 2)) ?></div>
                        <!-- <div class="sub"><?= $hasContractTotal ? 'চুক্তি অনুযায়ী' : 'রেকর্ড অনুযায়ী' ?></div> -->
                        <?php else: ?>
                        <!-- <div class="value gold">নির্ধারিত নয়</div>
                        <div class="sub">চুক্তির তথ্য পাওয়া যায়নি</div> -->
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($progressPct !== null): ?>
                <div class="progress-track">
                    <div class="progress-fill" style="width:<?= $progressPct ?>%"></div>
                </div>
                <div class="progress-label"><?= $progressPct ?>% কিস্তি পরিশোধিত (<?= $totalKistiPaid ?> /
                    <?= $totalKistiPlanned ?> কিস্তি)</div>
                <?php endif; ?>
            </div>

            <div class="section-label"><span>কিস্তির বিস্তারিত তথ্য</span>
                <div class="rule"></div>
            </div>
            <table class="ledger">
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>কিস্তি নং</th>
                        <th class="num">মূল টাকা</th>
                        <th class="num">জরিমানা</th>
                        <th class="num">মোট</th>
                        <th>মেথড</th>
                        <th>প্রাপক</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $row): ?>
                    <tr>
                        <td><?= bn_number(date('d/m/Y', strtotime($row['payment_date']))) ?></td>
                        <td><span class="kisti-badge"><?= bn_number($row['kisti_number']) ?></span></td>
                        <td class="num amt"><?= bn_number(number_format($row['amount'], 2)) ?></td>
                        <td class="num amt">
                            <?= $row['fine_amount'] ? bn_number(number_format($row['fine_amount'], 2)) : '—' ?>
                        </td>
                        <td class="num amt" style="font-weight:700;">
                            <?= bn_number(number_format($row['amount'] + ($row['fine_amount'] ?? 0), 2)) ?></td>
                        <td><?= strtoupper(htmlspecialchars($row['payment_method'])) ?></td>
                        <td><?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="signatures">
                <div class="sig-line">গ্রহণকারীর স্বাক্ষর</div>
                <div class="sig-line">কর্তৃপক্ষের স্বাক্ষর ও সিল</div>
            </div>
        </div>

        <div class="footer">
            <p>ধন্যবাদ! আপনার সাথে ব্যবসা করতে পেরে আমরা আনন্দিত।</p>
            <small>এই ডকুমেন্ট কম্পিউটার জেনারেটেড এবং অফিসিয়াল — রিসিট নং
                <?= htmlspecialchars($receiptSerial) ?></small>
        </div>

        <div class="guilloche"></div>
    </div>

    <div class="actions">
        <a href="kisti_payment_list.php" class="btn btn-back">← লিস্টে ফিরুন</a>
        <button onclick="window.print()" class="btn btn-print">প্রিন্ট করুন</button>
    </div>

</body>

</html>