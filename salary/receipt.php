<?php

/*
|--------------------------------------------------------------------------
| Salary Receipt
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            বেতন রসিদের ID পাওয়া যায়নি।
        </div>
    ';

    return;
}

$salaryId = (int)$_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Salary Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        sp.*,

        e.employee_name,
        e.mobile,
        e.designation,

        g.garage_name

    FROM salary_payments sp

    INNER JOIN employees e
        ON e.id = sp.employee_id

    LEFT JOIN garages g
        ON g.id = sp.garage_id

    WHERE sp.id = ?

    LIMIT 1
");

$stmt->execute([
    $salaryId
]);

$salary = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$salary) {

    echo '
        <div class="alert alert-danger m-4">
            <i class="bi bi-receipt-cutoff me-2"></i>
            বেতনের তথ্য পাওয়া যায়নি।
        </div>
    ';

    return;
}


/*
|--------------------------------------------------------------------------
| Bengali Number
|--------------------------------------------------------------------------
*/

if (!function_exists('bn_number')) {

    function bn_number($number)
    {
        return strtr(
            (string)$number,
            [
                '0' => '০',
                '1' => '১',
                '2' => '২',
                '3' => '৩',
                '4' => '৪',
                '5' => '৫',
                '6' => '৬',
                '7' => '৭',
                '8' => '৮',
                '9' => '৯'
            ]
        );
    }

}


/*
|--------------------------------------------------------------------------
| Amounts
|--------------------------------------------------------------------------
*/

$basicSalary = (float)(
    $salary['basic_salary'] ?? 0
);

$bonus = (float)(
    $salary['bonus'] ?? 0
);

$overtime = (float)(
    $salary['overtime'] ?? 0
);

$deduction = (float)(
    $salary['deduction'] ?? 0
);

$advance = (float)(
    $salary['advance'] ?? 0
);

$totalSalary = (float)(
    $salary['total_salary'] ?? 0
);

$paidAmount = (float)(
    $salary['paid_amount'] ?? 0
);

$dueAmount = (float)(
    $salary['due_amount'] ?? 0
);


/*
|--------------------------------------------------------------------------
| Dates
|--------------------------------------------------------------------------
*/

$salaryMonth = '—';

if (!empty($salary['salary_month'])) {

    $salaryMonth = date(
        'F Y',
        strtotime($salary['salary_month'])
    );

}


$paymentDate = '—';

if (!empty($salary['payment_date'])) {

    $paymentDate = date(
        'd-m-Y',
        strtotime($salary['payment_date'])
    );

}


/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

$paymentMethod =
    $salary['payment_method'] ?? '';

$paymentMethodBangla = '—';

if ($paymentMethod === 'cash') {

    $paymentMethodBangla = 'নগদ';

} elseif ($paymentMethod === 'bank') {

    $paymentMethodBangla = 'ব্যাংক';

} elseif (
    $paymentMethod === 'mobile_banking'
) {

    $paymentMethodBangla =
        'মোবাইল ব্যাংকিং';

} elseif ($paymentMethod !== '') {

    $paymentMethodBangla =
        htmlspecialchars($paymentMethod);

}


/*
|--------------------------------------------------------------------------
| Receipt Number
|--------------------------------------------------------------------------
*/

$receiptNo =
    'SAL-' .
    date(
        'Ymd',
        strtotime(
            $salary['payment_date']
            ?? date('Y-m-d')
        )
    ) .
    '-' .
    str_pad(
        $salaryId,
        4,
        '0',
        STR_PAD_LEFT
    );

?>


<!-- =============================================================
     ACTION BAR
============================================================== -->

<div class="container-fluid px-3 px-lg-4 py-4 no-print">

    <div
        class="d-flex justify-content-between align-items-center"
    >

        <div>

            <h1 class="h4 mb-1">

                <i
                    class="bi bi-receipt-cutoff text-success me-2"
                ></i>

                বেতন রসিদ

            </h1>

            <small class="text-muted">

                বেতন প্রদানের রসিদ

            </small>

        </div>


        <div class="d-flex gap-2">

            <a
                href="index.php?page=salary/history"
                class="btn btn-outline-secondary"
            >

                <i
                    class="bi bi-arrow-left me-1"
                ></i>

                হিস্টোরি

            </a>


            <button
                type="button"
                class="btn btn-primary"
                onclick="window.print()"
            >

                <i
                    class="bi bi-printer me-1"
                ></i>

                প্রিন্ট রসিদ

            </button>

        </div>

    </div>

</div>



<!-- =============================================================
     RECEIPT
============================================================== -->

<div class="receipt-wrapper">

    <div class="salary-receipt">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="receipt-header">


            <div class="company-name">

                আপনার প্রতিষ্ঠানের নাম

            </div>


            <div class="company-subtitle">

                কর্মচারী বেতন প্রদানের রসিদ

            </div>


            <div class="receipt-title">

                বেতন রসিদ

            </div>


        </div>



        <!-- =====================================================
             RECEIPT META
        ====================================================== -->

        <div class="receipt-meta">

            <div>

                <strong>
                    রসিদ নং:
                </strong>

                <?= htmlspecialchars(
                    $receiptNo
                ) ?>

            </div>


            <div>

                <strong>
                    তারিখ:
                </strong>

                <?= bn_number(
                    $paymentDate
                ) ?>

            </div>

        </div>



        <!-- =====================================================
             EMPLOYEE INFORMATION
        ====================================================== -->

        <div class="section-title">

            কর্মচারীর তথ্য

        </div>


        <div class="employee-info">


            <div class="info-item">

                <span>
                    কর্মচারীর নাম
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $salary['employee_name']
                    ) ?>

                </strong>

            </div>


            <div class="info-item">

                <span>
                    মোবাইল
                </span>

                <strong>

                    <?= !empty(
                        $salary['mobile']
                    )
                        ? htmlspecialchars(
                            $salary['mobile']
                        )
                        : '—'
                    ?>

                </strong>

            </div>


            <div class="info-item">

                <span>
                    পদ / দায়িত্ব
                </span>

                <strong>

                    <?= !empty(
                        $salary['designation']
                    )
                        ? htmlspecialchars(
                            $salary['designation']
                        )
                        : '—'
                    ?>

                </strong>

            </div>


            <div class="info-item">

                <span>
                    গ্যারেজ
                </span>

                <strong>

                    <?= !empty(
                        $salary['garage_name']
                    )
                        ? htmlspecialchars(
                            $salary['garage_name']
                        )
                        : '—'
                    ?>

                </strong>

            </div>


            <div class="info-item">

                <span>
                    বেতনের মাস
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $salaryMonth
                    ) ?>

                </strong>

            </div>


        </div>



        <!-- =====================================================
             SALARY DETAILS
        ====================================================== -->

        <div class="section-title">

            বেতনের বিবরণ

        </div>


        <table class="salary-table">

            <thead>

            <tr>

                <th>
                    বিবরণ
                </th>

                <th class="amount">
                    টাকা
                </th>

            </tr>

            </thead>


            <tbody>


            <!-- Basic -->

            <tr>

                <td>
                    মূল বেতন
                </td>

                <td class="amount">

                    ৳ <?= bn_number(
                        number_format(
                            $basicSalary,
                            2
                        )
                    ) ?>

                </td>

            </tr>



            <!-- Bonus -->

            <?php if ($bonus > 0): ?>

                <tr>

                    <td>
                        Bonus
                    </td>

                    <td class="amount text-success">

                        + ৳ <?= bn_number(
                            number_format(
                                $bonus,
                                2
                            )
                        ) ?>

                    </td>

                </tr>

            <?php endif; ?>



            <!-- Overtime -->

            <?php if ($overtime > 0): ?>

                <tr>

                    <td>
                        Overtime
                    </td>

                    <td class="amount text-success">

                        + ৳ <?= bn_number(
                            number_format(
                                $overtime,
                                2
                            )
                        ) ?>

                    </td>

                </tr>

            <?php endif; ?>



            <!-- Deduction -->

            <?php if ($deduction > 0): ?>

                <tr>

                    <td>
                        Deduction
                    </td>

                    <td class="amount text-danger">

                        - ৳ <?= bn_number(
                            number_format(
                                $deduction,
                                2
                            )
                        ) ?>

                    </td>

                </tr>

            <?php endif; ?>



            <!-- Advance -->

            <?php if ($advance > 0): ?>

                <tr>

                    <td>
                        Advance
                    </td>

                    <td class="amount text-danger">

                        - ৳ <?= bn_number(
                            number_format(
                                $advance,
                                2
                            )
                        ) ?>

                    </td>

                </tr>

            <?php endif; ?>



            <!-- Total -->

            <tr class="total-row">

                <td>

                    মোট বেতন

                </td>

                <td class="amount">

                    ৳ <?= bn_number(
                        number_format(
                            $totalSalary,
                            2
                        )
                    ) ?>

                </td>

            </tr>



            <!-- Paid -->

            <tr class="paid-row">

                <td>

                    প্রদান করা হয়েছে

                </td>

                <td class="amount">

                    ৳ <?= bn_number(
                        number_format(
                            $paidAmount,
                            2
                        )
                    ) ?>

                </td>

            </tr>



            <!-- Due -->

            <tr class="due-row">

                <td>

                    বকেয়া

                </td>

                <td class="amount">

                    ৳ <?= bn_number(
                        number_format(
                            $dueAmount,
                            2
                        )
                    ) ?>

                </td>

            </tr>


            </tbody>

        </table>



        <!-- =====================================================
             PAYMENT INFORMATION
        ====================================================== -->

        <div class="payment-box">


            <div>

                <span>
                    পেমেন্টের মাধ্যম
                </span>

                <strong>

                    <?= $paymentMethodBangla ?>

                </strong>

            </div>


            <div>

                <span>
                    প্রদানের তারিখ
                </span>

                <strong>

                    <?= bn_number(
                        $paymentDate
                    ) ?>

                </strong>

            </div>


        </div>



        <!-- =====================================================
             NOTE
        ====================================================== -->

        <?php if (
            !empty(
                $salary['note']
            )
        ): ?>

            <div class="note-box">

                <strong>
                    মন্তব্য:
                </strong>

                <?= nl2br(
                    htmlspecialchars(
                        $salary['note']
                    )
                ) ?>

            </div>

        <?php endif; ?>



        <!-- =====================================================
             SIGNATURE
        ====================================================== -->

        <div class="signature-area">


            <div class="signature">

                <div class="signature-line"></div>

                <div>
                    কর্মচারীর স্বাক্ষর
                </div>

            </div>


            <div class="signature">

                <div class="signature-line"></div>

                <div>
                    কর্তৃপক্ষের স্বাক্ষর
                </div>

            </div>


        </div>



        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <div class="receipt-footer">

            <div>

                এই রসিদটি বেতন প্রদানের প্রমাণ হিসেবে সংরক্ষণ করুন।

            </div>

            <div class="mt-1">

                Receipt No:
                <?= htmlspecialchars(
                    $receiptNo
                ) ?>

            </div>

        </div>


    </div>

</div>



<!-- =============================================================
     STYLE
============================================================== -->

<style>

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

body {

    background: #f3f4f6;

}


/*
|--------------------------------------------------------------------------
| Receipt Wrapper
|--------------------------------------------------------------------------
*/

.receipt-wrapper {

    width: 100%;

    display: flex;

    justify-content: center;

    padding: 20px;

}


/*
|--------------------------------------------------------------------------
| Receipt
|--------------------------------------------------------------------------
*/

.salary-receipt {

    width: 800px;

    max-width: 100%;

    background: #fff;

    border: 1px solid #ddd;

    box-shadow:
        0 8px 30px rgba(0,0,0,.08);

    padding: 35px;

}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

.receipt-header {

    text-align: center;

    border-bottom:
        2px solid #222;

    padding-bottom: 20px;

}


.company-name {

    font-size: 25px;

    font-weight: 800;

}


.company-subtitle {

    font-size: 14px;

    color: #666;

    margin-top: 5px;

}


.receipt-title {

    display: inline-block;

    margin-top: 15px;

    padding: 7px 25px;

    border: 1px solid #222;

    border-radius: 4px;

    font-size: 18px;

    font-weight: 700;

}


/*
|--------------------------------------------------------------------------
| Meta
|--------------------------------------------------------------------------
*/

.receipt-meta {

    display: flex;

    justify-content: space-between;

    padding: 15px 0;

    font-size: 13px;

}


/*
|--------------------------------------------------------------------------
| Section
|--------------------------------------------------------------------------
*/

.section-title {

    background: #f5f6f7;

    border-left: 4px solid #198754;

    padding: 9px 12px;

    margin-top: 12px;

    margin-bottom: 10px;

    font-weight: 700;

}


/*
|--------------------------------------------------------------------------
| Employee Info
|--------------------------------------------------------------------------
*/

.employee-info {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 0;

    border: 1px solid #ddd;

}


.info-item {

    display: flex;

    justify-content: space-between;

    gap: 15px;

    padding: 10px 12px;

    border-bottom:
        1px solid #eee;

}


.info-item:nth-child(odd) {

    border-right:
        1px solid #eee;

}


.info-item span {

    color: #666;

}


.info-item strong {

    text-align: right;

}


/*
|--------------------------------------------------------------------------
| Salary Table
|--------------------------------------------------------------------------
*/

.salary-table {

    width: 100%;

    border-collapse: collapse;

}


.salary-table th,
.salary-table td {

    border: 1px solid #ddd;

    padding: 10px 12px;

}


.salary-table th {

    background: #f5f6f7;

    font-size: 13px;

}


.salary-table .amount {

    width: 180px;

    text-align: right;

    font-family:
        "JetBrains Mono",
        monospace;

}


.total-row {

    font-weight: 700;

    background: #f8f9fa;

}


.paid-row {

    font-weight: 700;

    background: #ecfdf5;

}


.due-row {

    font-weight: 700;

    background: #fff1f2;

}


/*
|--------------------------------------------------------------------------
| Payment Box
|--------------------------------------------------------------------------
*/

.payment-box {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    margin-top: 15px;

    border: 1px solid #ddd;

}


.payment-box > div {

    padding: 12px;

}


.payment-box > div:first-child {

    border-right:
        1px solid #ddd;

}


.payment-box span {

    display: block;

    color: #666;

    font-size: 12px;

}


.payment-box strong {

    display: block;

    margin-top: 4px;

}


/*
|--------------------------------------------------------------------------
| Note
|--------------------------------------------------------------------------
*/

.note-box {

    margin-top: 15px;

    padding: 12px;

    border: 1px dashed #aaa;

    background: #fafafa;

    font-size: 13px;

}


/*
|--------------------------------------------------------------------------
| Signature
|--------------------------------------------------------------------------
*/

.signature-area {

    display: flex;

    justify-content: space-between;

    margin-top: 70px;

}


.signature {

    width: 180px;

    text-align: center;

    font-size: 13px;

}


.signature-line {

    border-top:
        1px solid #333;

    margin-bottom: 7px;

}


/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

.receipt-footer {

    text-align: center;

    border-top:
        1px solid #ddd;

    margin-top: 35px;

    padding-top: 12px;

    color: #777;

    font-size: 11px;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 600px) {

    .receipt-wrapper {

        padding: 5px;

    }


    .salary-receipt {

        padding: 15px;

        box-shadow: none;

    }


    .employee-info {

        grid-template-columns: 1fr;

    }


    .info-item:nth-child(odd) {

        border-right: none;

    }


    .payment-box {

        grid-template-columns: 1fr;

    }


    .payment-box > div:first-child {

        border-right: none;

        border-bottom:
            1px solid #ddd;

    }


    .signature-area {

        margin-top: 50px;

    }

}


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

@media print {

    @page {

        size: A4;

        margin: 10mm;

    }


    body {

        background: #fff !important;

    }


    .no-print {

        display: none !important;

    }


    .receipt-wrapper {

        padding: 0;

        display: block;

    }


    .salary-receipt {

        width: 100%;

        max-width: none;

        border: none;

        box-shadow: none;

        padding: 10px;

    }


    .section-title {

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;

    }


    .total-row,
    .paid-row,
    .due-row {

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;

    }

}

</style>