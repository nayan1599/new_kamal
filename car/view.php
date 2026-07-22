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
$customer_kisti_amount = $record['monthly_kisti'] ?? null;

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
$maxKisti = $totalKistiPaid - $totalKistiPlanned;
 
$remainingAmount = 0;
$remainingAmount = $totalPrice - ($paid_amount + $discountAmount + $totalPaid);
$total_due = $remainingAmount + $totalPaid;

$totalDue_amount = 0;

foreach ($payments as $p) {
$due = max(0, $monthlyKisti - $p['amount']);

    $totalDue_amount += $due;
}
 


$progressPct = ($hasContractTotal && $totalKistiPlanned > 0)
    ? min(100, round(($totalKistiPaid / $totalKistiPlanned) * 100))
    : null;
// date and time  
 

$startDate = $kistiStartDate;
$endDate   = date('Y-m-d');

$monthlyAmount = $monthlyKisti; // মাসিক কিস্তি

$start = new DateTime($startDate);
$end   = new DateTime($endDate);

$diff = $start->diff($end);

// মোট মাস
$totalMonths = ($diff->y * 12) + $diff->m;

// দিন
$totalDays = $diff->d;

// 👉 daily rate (৩০ দিন ধরে)
$dailyRate = $monthlyAmount / 30;

// 👉 total amount
$totalAmount = ($totalMonths * $monthlyAmount) + ($totalDays * $dailyRate) - $totalPaid ;


// 👉 বাকি টাকা
$dueAmount = $totalAmount ;
 
  
$totalKistiPlanned = $totalKistiPlanned; // মোট প্ল্যান মাস (যেমন: 24)
 

// 👉 কেটে যাওয়া সময়
$passedMonths = ($diff->y * 12) + $diff->m;
$passedDays   = $diff->d;

// 👉 total passed in decimal month
$passedTotalMonths = $passedMonths + ($passedDays / 30);

// 👉 বাকি মাস
$remainingMonths = $totalKistiPlanned - $passedTotalMonths;

// 👉 যদি negative হয় (মানে শেষ হয়ে গেছে)
$remainingMonths = max(0, $remainingMonths);

// 👉 মাস + দিন আলাদা করে
$remainingFullMonths = floor($remainingMonths);
$remainingDays = round(($remainingMonths - $remainingFullMonths) * 30);

// ✅ Output










// রিসিট নং — ইনভয়েস থাকলে সেটাই, নাহলে অটো-জেনারেট
$receiptSerial = $record['invoice_no'] ?? ('JE-' . preg_replace('/[^A-Za-z0-9]/', '', $car_number) . '-' . date('ym', strtotime($lastPaymentDate)));
?>
<div style="text-align:right;padding:8px 0;max-width:900px;margin:0 auto;">
    <button onclick="printDiv('receiptArea')"
        style="background:#198754;color:#fff;border:none;border-radius:6px;padding:10px 18px;font-size:.9rem;font-weight:600;cursor:pointer;">🖨️
        Print</button>
</div>

<div class="receipt" id="receiptArea"
    style="-webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;position:relative;max-width:900px;margin:0 auto;background:#faf6ec;border-radius:4px;box-shadow:0 25px 60px -15px rgba(8,21,39,0.35), 0 0 0 1px rgba(13,35,64,0.06);overflow:hidden;">

    <div
        style="print-color-adjust: exact !important;height:14px; background-color:#0d2340;background-image:repeating-linear-gradient(115deg, transparent 0 6px, rgba(184,134,60,0.55) 6px 7px, transparent 7px 13px), repeating-linear-gradient(65deg, transparent 0 6px, rgba(228,201,141,0.35) 6px 7px, transparent 7px 13px);">
    </div>

    <div
        style="print-color-adjust: exact !important;background:linear-gradient(160deg, #0d2340 0%, #081527 100%);color:#f4efe1;padding:30px 40px 26px;position:relative;border-bottom:1px solid rgba(228,201,141,0.35);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;">
            <div>
                <p
                    style="font-family:'Noto Serif Bengali',serif;font-size:1.9rem;font-weight:700;letter-spacing:.5px;margin:0;">
                    জহিরুল এন্টারপ্রাইজ</p>
                <p style="margin:4px 0 0;font-size:.82rem;color:#e4c98d;letter-spacing:1.5px;text-transform:uppercase;">
                    গাড়ি কিস্তি বিক্রয় ও পরিষেবা</p>
            </div>
            <div style="text-align:right;font-size:.85rem;color:#d9d2bd;line-height:1.7;">
                রিসিট নং: <b
                    style="color:#fff;font-family:'JetBrains Mono',monospace;"><?= htmlspecialchars($receiptSerial) ?></b><br>
                ইস্যুর তারিখ: <b style="color:#fff;"><?= date('d/m/Y') ?></b>
            </div>
        </div>
        <div style="margin-top:18px;display:flex;align-items:center;gap:14px;">
            <p style="margin:0;">মানি রিসিট • কিস্তি হিসাব বিবরণী</p>
            <div style="flex:1;height:1px;background:rgba(228,201,141,0.35);"></div>
            <p style="margin:0;font-family:'JetBrains Mono',monospace;"><?= htmlspecialchars($car_number) ?></p>
        </div>
    </div>

    <div style="padding:34px 40px 10px;position:relative;">
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:18px;margin-bottom:30px;">
            <div style="border:1px solid #e7ddc7;border-radius:6px;padding:18px 20px;background:#fffdf7;">
                <h6
                    style="margin:0 0 12px;font-size:.78rem;letter-spacing:1.5px;text-transform:uppercase;color:#b8863c;font-weight:700;">
                    গ্রাহকের তথ্য</h6>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;">
                    <span style="color:#5b6472;">নাম</span><span
                        style="font-weight:600;"><?= htmlspecialchars($customer_name) ?></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মোবাইল</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;"><?= bn_number(htmlspecialchars($customer_phone)) ?></span>
                </div>
                <?php if ($customer_email): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">ইমেইল</span><span
                        style="font-weight:600;"><?= htmlspecialchars($customer_email) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($customer_nid): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">এনআইডি</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;"><?= bn_number(htmlspecialchars($customer_nid)) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($customer_addr): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">ঠিকানা</span><span
                        style="font-weight:600;"><?= htmlspecialchars($customer_addr) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div style="border:1px solid #e7ddc7;border-radius:6px;padding:18px 20px;background:#fffdf7;">
                <h6
                    style="margin:0 0 12px;font-size:.78rem;letter-spacing:1.5px;text-transform:uppercase;color:#b8863c;font-weight:700;">
                    গাড়ির তথ্য</h6>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;">
                    <span style="color:#5b6472;">গাড়ির নম্বর</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;"><?= bn_number(htmlspecialchars($car_number)) ?></span>
                </div>
                <?php if (!empty($record)): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">গাড়ির নাম</span><span
                        style="font-weight:600;"><?= htmlspecialchars($record['car_name']) ?></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মডেল / বছর</span><span style="font-weight:600;">
                        <?= htmlspecialchars($record['car_model']) ?> /
                        <?= htmlspecialchars($record['car_year']) ?></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">ক্রয়ের ধরন</span><span
                        style="font-weight:600;"><?= $record['type'] === 'installment' ? 'কিস্তিতে' : htmlspecialchars($record['type']) ?></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">ক্রয়ের তারিখ</span>
                    <span
                        style="font-weight:600;"><?= bn_number(date('d/m/Y', strtotime($record['kisti_start_date']))) ?></span>
                </div>
                <?php endif; ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">সর্বশেষ পরিশোধ</span>
                    <span
                        style="font-weight:600;"><?= $lastPaymentDate ? bn_number(date('d/m/Y', strtotime($lastPaymentDate))) : '—' ?></span>
                </div>
            </div>

            <div style="border:1px solid #e7ddc7;border-radius:6px;padding:18px 20px;background:#fffdf7;">
                <h6
                    style="margin:0 0 12px;font-size:.78rem;letter-spacing:1.5px;text-transform:uppercase;color:#b8863c;font-weight:700;">
                    চুক্তির তথ্য</h6>
                <?php if ($hasContractTotal): ?>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;">
                    <span style="color:#5b6472;">মোট মূল্য</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;">৳
                        <?= bn_number(number_format($totalPrice)) ?></span>
                </div>
                <?php if ($discountAmount > 0): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">ডিসকাউন্ট</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;">৳
                        <?= bn_number(number_format($discountAmount)) ?></span>
                </div>
                <?php endif; ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">জমাঃ</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;">৳
                        <?= bn_number(number_format($paid_amount)) ?></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মোট বাকিঃ</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;">৳
                        <?= bn_number(number_format($total_due)) ?></span>
                </div>

                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মাসিক কিস্তি</span><span
                        style="font-weight:600;font-family:'JetBrains Mono',monospace;">৳
                        <?= bn_number(number_format($monthlyKisti)) ?></span>
                </div>

                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মোট কিস্তি সংখ্যা</span><span
                        style="font-weight:600;"><?= bn_number($totalKistiPlanned) ?> টি</span>
                </div>
                <?php if ($nextDueDate): ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">পরবর্তী কিস্তির তারিখ</span><span
                        style="font-weight:600;"><?= bn_number(date('d/m/Y', strtotime($nextDueDate))) ?></span>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;">
                    <span style="color:#5b6472;">চুক্তির তথ্য</span><span style="font-weight:600;">পাওয়া যায়নি</span>
                </div>
                <?php endif; ?>
                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">মোট সময়</span>
                    <span style="font-weight:600;">
                        <?= bn_number($totalMonths) ?> মাস <?= bn_number($totalDays) ?> দিন
                    </span>
                </div>

                <div
                    style="display:flex;justify-content:space-between;padding:5px 0;font-size:.95rem;border-top:1px dashed #e7ddc7;">
                    <span style="color:#5b6472;">বাকি সময়</span>
                    <span style="font-weight:600;">
                        <?= bn_number($remainingFullMonths) ?> মাস <?= bn_number(round($remainingDays)) ?> দিন
                    </span>
                </div>
            </div>
        </div>

        <div style="position:relative;margin-bottom:22px;">
            <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:14px;">
                <div
                    style="border:1px solid #e7ddc7;border-radius:6px;padding:16px 12px;text-align:center;background:#fffdf7;">
                    <div style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;">
                        মোট কিস্তি পরিশোধ</div>
                    <div style="font-size:1.35rem;font-weight:700;margin-top:6px;"><?= bn_number($totalKistiPaid) ?> টি
                    </div>
                    <div style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;">
                        মোট কিস্তি বাকি</div>
                    <div style="font-size:1.35rem;font-weight:700;margin-top:6px;">
                        <?= bn_number($hasContractTotal ? $totalKistiPlanned - $totalKistiPaid : '') ?> টি</div>
                </div>
                <div
                    style="border:1px solid #e7ddc7;border-radius:6px;padding:16px 12px;text-align:center;background:#fffdf7;">
                    <div style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;">
                        মোট আদায়</div>
                    <div
                        style="font-size:1.35rem;font-weight:700;margin-top:6px;color:#1f6f43;font-family:'JetBrains Mono',monospace;">
                        ৳ <?= bn_number(number_format($totalPaid)) ?></div>
                </div>
                <div
                    style="border:1px solid #e7ddc7;border-radius:6px;padding:16px 12px;text-align:center;background:#fffdf7;">
                    <div style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;">
                        মোট জরিমানা</div>
                    <div
                        style="font-size:1.35rem;font-weight:700;margin-top:6px;font-family:'JetBrains Mono',monospace;color:<?= $totalFine > 0 ? '#9b2226' : 'inherit' ?>;">
                        ৳ <?= bn_number(number_format($totalFine)) ?>
                    </div>
                    <div style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;">
                        প্রতি দিন <strong style="font-family:'JetBrains Mono',monospace;"> ১০০</strong> টাকা করে জরিমান
                    </div>
                </div>
                <div
                    style="border:1px solid #e7ddc7;border-radius:6px;padding:16px 12px;text-align:center;background:#fffdf7;">
                    <div
                        style="font-size:.72rem;color:#5b6472;letter-spacing:.5px;text-transform:uppercase;margin-bottom:0;">
                        বাকি টাকা</div>
                    <?php if ($remainingAmount !== null): ?>
                    <div
                        style="font-size:1.35rem;font-weight:700;margin-top:6px;color:#9b2226;font-family:'JetBrains Mono',monospace;">
                        ৳ <?= bn_number(number_format($remainingAmount)) ?></div>
                    <?php endif; ?>
                    <div
                        style="font-size:.72rem;color:#9b2226;letter-spacing:.5px;text-transform:uppercase;margin-bottom:0;">
                        মোট বকেয়া</div>
                    <div
                        style="font-size:1.35rem;font-weight:700;margin-top:6px;color:#ffc107;font-family:'JetBrains Mono',monospace;">
                        <?= bn_number(number_format($totalAmount)) ?></div>
                </div>
            </div>

            <?php if ($progressPct !== null): ?>
            <div style="margin-top:16px;height:8px;border-radius:6px;background:#e7ddc7;overflow:hidden;">
                <div
                    style="height:100%;border-radius:6px;background:linear-gradient(90deg, #b8863c, #e4c98d);width:<?= $progressPct ?>%">
                </div>
            </div>
            <div style="margin-top:6px;font-size:.78rem;color:#5b6472;text-align:right;"><?= $progressPct ?>%
                কিস্তি পরিশোধিত (<?= $totalKistiPaid ?> / <?= $totalKistiPlanned ?> কিস্তি)</div>
            <?php endif; ?>
        </div>

        <div
            style="display:flex;align-items:center;gap:10px;margin:0 0 14px;font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase;color:#b8863c;font-weight:700;">
            <span>কিস্তির বিস্তারিত তথ্য</span>
            <div style="flex:1;height:1px;background:#e7ddc7;print-color-adjust: exact !important;"></div>
        </div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:6px;print-color-adjust: exact !important;">
            <thead>
                <tr>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:left;">
                        #</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:left;">
                        তারিখ</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:left;">
                        কিস্তি নং</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:right;">
                        মাসিক কিস্তি</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:right;">
                        জমা</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:right;">
                        বাকি</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:right;">
                        জরিমানা</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:left;">
                        মেথড</th>
                    <th
                        style="background:#0d2340;color:#f2ead3;font-weight:600;font-size:.82rem;letter-spacing:.4px;padding:11px 12px;text-align:left;">
                        প্রাপক</th>
                </tr>
            </thead>
            <tbody>
                <?php 
$i = 0;
foreach ($payments as $row):
    $i++;
    $due = max(0, $monthlyKisti - $row['amount']);
    $rowBg = ($i % 2 === 0) ? '#f4efe1' : 'transparent';
?>
                <tr style="background:<?= $rowBg ?>;">
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;"><?= $i ?></td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;">
                        <?= bn_number(date('d/m/Y', strtotime($row['payment_date']))) ?></td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;">
                        <span
                            style="display:inline-block;min-width:26px;padding:2px 6px;border-radius:4px;background:#0d2340;color:#f2ead3;font-family:'JetBrains Mono',monospace;font-size:.8rem;text-align:center;"><?= bn_number($row['kisti_number']) ?></span>
                    </td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;text-align:right;">
                        <?= bn_number($monthlyKisti) ?></td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;text-align:right;">
                        <?= bn_number($row['amount']) ?></td>
                    <td
                        style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;text-align:right;font-family:'JetBrains Mono',monospace;">
                        <?= bn_number($due) ?></td>
                    <td
                        style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;text-align:right;font-family:'JetBrains Mono',monospace;">
                        <?= $row['fine_amount'] ? bn_number(number_format($row['fine_amount'])) : '—' ?>
                    </td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;">
                        <?= strtoupper(htmlspecialchars($row['payment_method'])) ?></td>
                    <td style="padding:10px 12px;font-size:.9rem;border-bottom:1px solid #e7ddc7;">
                        <?= htmlspecialchars($row['received_by'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div
            style="print-color-adjust: exact !important;display:grid;grid-template-columns:1fr 1fr;gap:40px;margin:38px 0 8px;print-color-adjust: exact !important;">
            <div
                style="print-color-adjust: exact !important;border-top:1px solid #1f2a37;padding-top:8px;font-size:.85rem;color:#5b6472;text-align:center;">
                গ্রহণকারীর স্বাক্ষর</div>
            <div
                style="print-color-adjust: exact !important;border-top:1px solid #1f2a37;padding-top:8px;font-size:.85rem;color:#5b6472;text-align:center;">
                কর্তৃপক্ষের স্বাক্ষর ও সিল</div>
        </div>
    </div>

    <div
        style="print-color-adjust: exact !important;padding:18px 40px 26px;text-align:center;color:#eee5c9;background:linear-gradient(160deg, #081527 0%, #0d2340 100%);">
        <p style="margin:0;font-size:.85rem;">ধন্যবাদ! আপনার সাথে ব্যবসা করতে পেরে আমরা আনন্দিত।</p>
        <small style="display:block;margin-top:6px;color:#a79f83;font-size:.72rem;letter-spacing:.4px;">এই
            ডকুমেন্ট কম্পিউটার জেনারেটেড এবং অফিসিয়াল — রিসিট নং
            <?= htmlspecialchars($receiptSerial) ?></small>
    </div>

    <div
        style="print-color-adjust: exact !important;height:14px;background-color:#0d2340;background-image:repeating-linear-gradient(115deg, transparent 0 6px, rgba(184,134,60,0.55) 6px 7px, transparent 7px 13px), repeating-linear-gradient(65deg, transparent 0 6px, rgba(228,201,141,0.35) 6px 7px, transparent 7px 13px);print-color-adjust: exact !important;">
    </div>
</div>

<div style="max-width:900px;margin:18px auto 0;display:flex;justify-content:space-between;gap:12px;print-color-adjust: exact !important;">
    <a href="index.php?page=car/index"
        style="border:1px solid #e7ddc7;border-radius:6px;padding:12px 22px;font-size:.9rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;background:#fff;color:#0d2340;">←
        লিস্টে ফিরুন</a>
    <button onclick="window.print()"
        style="border:none;border-radius:6px;padding:12px 22px;font-size:.9rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;background:#0d2340;color:#f4efe1;">প্রিন্ট
        করুন</button>
</div>