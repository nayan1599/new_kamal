<?php
 

// ID check
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h3 style='text-align:center;color:red;'>গাড়ির আইডি দেয়া হয়নি!</h3>");
}

$id = $_GET['id'];

// ================= DATA FETCH =================
$stmt = $pdo->prepare("SELECT * FROM customer_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);



// print_r($record);
if (!$record) {
    die("<h3 style='text-align:center;color:red;'>ডাটা পাওয়া যায়নি!</h3>");
}

// ================= VARIABLES =================
$customer_name   = $record['customer_name'];
$customer_phone  = $record['customer_phone'];
$car_number      = $record['car_number'];
$nid              = $record['nid'];
$address         = $record['address'];




$totalPrice      = $record['total_price'];
$paid_amount     = $record['paid_amount'] ?? 0;
$dueAmount      = $totalPrice - $paid_amount;
$monthlyKisti    = $record['monthly_kisti'];
$totalKisti      = $record['total_kisti'];
 

$totalPaid = $paid_amount;
 
 
?>
<div class="container-fluid mt-5">
    <div class="text-end mb-3">
        <button class="btn btn-primary" onclick="printDiv('receiptArea')">প্রিন্ট</button>
    </div>
    <div id="receiptArea">
        <div
            style=" margin:auto; border:2px solid #000; padding:25px; font-family: 'SolaimanLipi', sans-serif;">

            <!-- HEADER -->
            <h2 style="text-align:center; margin:0;">জাহিরুল এন্টারপ্রাইজ</h2>

            <hr>

            <!-- INFO -->
            <table style="width:100%; font-size:14px;">
                <tr>
                    <td><b>রিসিট নং:</b> <?= $record['invoice_no'] ?></td>
                    <td style="text-align:right;">
                        <b>তারিখ:</b> <?= date('d-m-Y', strtotime($record['kisti_start_date'])) ?>
                    </td>
                </tr>
            </table>




            <table style="width:100%; font-size:15px;">
                <tr>
                    <td><b>নাম:</b> <?= $customer_name ?></td>
                </tr>
                <tr>
                    <td><b>মোবাইল:</b> <?= $customer_phone ?></td>
                </tr>
                <!-- nid  -->
                <tr>
                    <td><b>আইডি:</b> <?= $nid ?></td>
                </tr>
                <tr>
                    <td><b>ঠিকানা:</b> <?= $address ?></td>
                </tr>
            </table>



            <table style="width:100%; border-collapse: collapse; font-size:14px;">
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>গাড়ি নম্বর</b></td>
                    <td style="border:1px solid #000; padding:8px;"><?= $car_number ?></td>
                    <td style="border:1px solid #000; padding:8px;"><b>পরিমাণ</b></td>
                    <td style="border:1px solid #000; padding:8px;"><?= bn_number($record['quantity']) ?></td>
                </tr>
            </table>


            <table style="width:100%; border-collapse: collapse; font-size:14px;">
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>মোট মূল্য</b></td>
                    <td style="border:1px solid #000; padding:8px;">৳ <?= bn_number(number_format($totalPrice)) ?></td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>মোট পরিশোধ</b></td>
                    <td style="border:1px solid #000; padding:8px;">৳ <?= bn_number(number_format($totalPaid)) ?></td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>বাকি টাকা</b></td>
                    <td style="border:1px solid #000; padding:8px; color:red; font-weight:bold;">
                        ৳ <?=bn_number( number_format($dueAmount)) ?>
                    </td>
                </tr>
                <!-- kisti -->
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>মাসিক কিস্তি</b></td>
                    <td style="border:1px solid #000; padding:8px;">৳ <?= bn_number(number_format($monthlyKisti)) ?>
                    </td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>মোট কিস্তি</b></td>
                    <td style="border:1px solid #000; padding:8px;"><?= bn_number($totalKisti) ?> মাস</td>
                </tr>
                <!-- জাবিন দার -->
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><b>জাবিন দার</b></td>
                    <td style="border:1px solid #000; padding:8px;"><?= $record['note'] ?></td>
                </tr>

            </table>
            <!-- takaInWords -->
            <p><b>মোট মূল্য:</b> <?= takaInWordsBn($totalPrice) ?></p>
            <!-- SIGN -->
            <table style="width:100%; margin-top:60px; font-size:14px;">
                <tr>
                    <td style="text-align:left;">
                        _______________________<br>
                        গ্রাহকের স্বাক্ষর
                    </td>
                    <td style="text-align:right;">
                        _______________________<br>
                        কর্তৃপক্ষ
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>