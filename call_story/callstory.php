<?php

// =====================================================
// CHECK ID
// =====================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {

    die("
        <h3 class='text-center mt-5 text-danger'>
            ❌ ট্রানজেকশন আইডি পাওয়া যায়নি!
        </h3>
    ");

}

$car_id = trim($_GET['id']);


// =====================================================
// FETCH CUSTOMER RECORD
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM customer_records
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$car_id]);

$transactions = $stmt->fetch(PDO::FETCH_ASSOC);


// =====================================================
// RECORD NOT FOUND
// =====================================================

if (!$transactions) {

    die("
        <h3 class='text-center mt-5 text-danger'>
            ❌ এই ID-এর কোনো গ্রাহকের তথ্য পাওয়া যায়নি!
        </h3>
    ");

}


// =====================================================
// DEFAULT CUSTOMER INFORMATION
// =====================================================

$customer_name =
    $transactions['customer_name'] ?? '';

$default_phone =
    $transactions['customer_phone'] ?? '';

$default_car_number =
    $transactions['car_number'] ?? '';

$default_chassis =
    $transactions['chassis_number'] ?? '';


// =====================================================
// INSTALLMENT INFORMATION
// =====================================================

$total_kisti =
    $transactions['total_kisti'] ?? '';

$kisti_amount =
    $transactions['monthly_kisti'] ?? '';

$due_amount =
    $transactions['due_amount'] ?? '';

$kisti_start_date =
    $transactions['kisti_start_date'] ?? '';


// =====================================================
// DEFAULT NOTE
// =====================================================

$default_note =
    $transactions['note'] ?? '';


// =====================================================
// SUCCESS / ERROR
// =====================================================

$success = $success ?? '';
$error   = $error ?? '';

?>


<div style=" padding:10px;
    
    margin:25px auto;
    padding:0 15px;
">

    <div style="padding:10px;
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        box-shadow:0 4px 15px rgba(0,0,0,.06);
        overflow:hidden;
    ">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div style="
            padding:18px 22px;
            background:#f8fafc;
            border-bottom:1px solid #e5e7eb;
        ">

            <h3 style="
                margin:0;
                font-size:20px;
                font-weight:700;
                color:#1f2937;
            ">
                📞 কল স্টোরি
            </h3>


            <div style="
                margin-top:5px;
                color:#6b7280;
                font-size:13px;
            ">

                গাড়ির ID:
                <?= (int)$car_id ?>

                <?php if (!empty($transactions['invoice_no'])): ?>

                    &nbsp; | &nbsp;

                    Invoice:
                    <?= htmlspecialchars(
                        $transactions['invoice_no']
                    ) ?>

                <?php endif; ?>

            </div>

        </div>



        <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->

        <?php if (!empty($success)): ?>

            <div style="
                margin:18px 22px 0;
                padding:12px 15px;
                background:#ecfdf5;
                color:#065f46;
                border:1px solid #a7f3d0;
                border-radius:7px;
            ">

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
        ================================================== -->

        <?php if (!empty($error)): ?>

            <div style="
                margin:18px 22px 0;
                padding:12px 15px;
                background:#fef2f2;
                color:#991b1b;
                border:1px solid #fecaca;
                border-radius:7px;
            ">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             FORM
        ================================================== -->

        <form method="POST" action="index.php?page=sql/call_store">


            <!-- =================================================
                 CUSTOMER INFORMATION
            ================================================== -->

            <h4 style="
                margin:0 0 15px;
                padding-bottom:8px;
                border-bottom:1px solid #eee;
                color:#374151;
            ">
                👤 গ্রাহকের তথ্য
            </h4>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
                gap:15px;
            ">


                <!-- CUSTOMER NAME -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        গ্রাহকের নাম *
                    </label>


                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars(
                            $customer_name
                        ) ?>"
                        required
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- PHONE -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        ফোন নম্বর *
                    </label>


                    <input
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars(
                            $default_phone
                        ) ?>"
                        required
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- CAR NUMBER -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        গাড়ির নম্বর
                    </label>


                    <input
                        type="text"
                        name="car_number"
                        value="<?= htmlspecialchars(
                            $default_car_number
                        ) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- CHASSIS -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        Chassis Number
                    </label>


                    <input
                        type="text"
                        name="chassis_number"
                        value="<?= htmlspecialchars(
                            $default_chassis
                        ) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>


            </div>



            <!-- =================================================
                 INSTALLMENT INFORMATION
            ================================================== -->

            <h4 style="
                margin:28px 0 15px;
                padding-bottom:8px;
                border-bottom:1px solid #eee;
                color:#374151;
            ">
                💰 কিস্তির তথ্য
            </h4>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
                gap:15px;
            ">


                <!-- TOTAL KISTI -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        মোট কিস্তি
                    </label>


                    <input
                        type="number"
                        name="total_kisti"
                        min="0"
                        value="<?= htmlspecialchars(
                            $total_kisti
                        ) ?>"
                        placeholder="যেমন: 36"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- MONTHLY KISTI -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        কিস্তির পরিমাণ
                    </label>


                    <input
                        type="number"
                        step="0.01"
                        name="kisti_amount"
                        min="0"
                        value="<?= htmlspecialchars(
                            $kisti_amount
                        ) ?>"
                        placeholder="যেমন: 15000"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- DUE -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        বকেয়া টাকা
                    </label>


                    <input
                        type="number"
                        step="0.01"
                        name="due_amount"
                        min="0"
                        value="<?= htmlspecialchars(
                            $due_amount
                        ) ?>"
                        placeholder="যেমন: 30000"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- KISTI START DATE -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        কিস্তি শুরু
                    </label>


                    <input
                        type="date"
                        name="kisti_start_date"
                        value="<?= htmlspecialchars(
                            $kisti_start_date
                        ) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>


            </div>



            <!-- =================================================
                 JABIN INFORMATION
            ================================================== -->

            <h4 style="
                margin:28px 0 15px;
                padding-bottom:8px;
                border-bottom:1px solid #eee;
                color:#374151;
            ">
                👥 জাবিনের তথ্য
            </h4>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
                gap:15px;
            ">


                <!-- JABIN NAME -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        জাবিনের নাম
                    </label>


                    <input
                        type="text"
                        name="jabin_name"
                        value="<?= htmlspecialchars(
                            $transactions['jabin_name'] ?? ''
                        ) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- JABIN PHONE -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        জাবিনের ফোন
                    </label>


                    <input
                        type="text"
                        name="jabin_phone"
                        value="<?= htmlspecialchars(
                            $transactions['jabin_phone'] ?? ''
                        ) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>


            </div>



            <!-- =================================================
                 CALL INFORMATION
            ================================================== -->

            <h4 style="
                margin:28px 0 15px;
                padding-bottom:8px;
                border-bottom:1px solid #eee;
                color:#374151;
            ">
                📞 কলের তথ্য
            </h4>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
                gap:15px;
            ">


                <!-- CALL STATUS -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        কল স্ট্যাটাস
                    </label>


                    <select
                        name="call_status"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            background:#fff;
                        "
                    >

                        <option value="">
                            -- নির্বাচন করুন --
                        </option>

                        <option value="connected">
                            কথা হয়েছে
                        </option>

                        <option value="not_connected">
                            কথা হয়নি
                        </option>

                        <option value="busy">
                            ব্যস্ত
                        </option>

                        <option value="switched_off">
                            বন্ধ
                        </option>

                        <option value="wrong_number">
                            ভুল নম্বর
                        </option>

                    </select>

                </div>



                <!-- JABIN CALL STATUS -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        জাবিন কল স্ট্যাটাস
                    </label>


                    <select
                        name="jabin_call_status"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            background:#fff;
                        "
                    >

                        <option value="">
                            -- নির্বাচন করুন --
                        </option>

                        <option value="connected">
                            কথা হয়েছে
                        </option>

                        <option value="not_connected">
                            কথা হয়নি
                        </option>

                        <option value="busy">
                            ব্যস্ত
                        </option>

                        <option value="switched_off">
                            বন্ধ
                        </option>

                    </select>

                </div>



                <!-- CALL CATEGORY -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        কলের ধরন
                    </label>


                    <select
                        name="call_category"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            background:#fff;
                        "
                    >

                        <option value="">
                            -- নির্বাচন করুন --
                        </option>

                        <option value="payment_reminder">
                            পেমেন্ট রিমাইন্ডার
                        </option>

                        <option value="due_collection">
                            বকেয়া আদায়
                        </option>

                        <option value="promise">
                            পেমেন্টের প্রতিশ্রুতি
                        </option>

                        <option value="followup">
                            ফলোআপ
                        </option>

                        <option value="general">
                            সাধারণ কল
                        </option>

                    </select>

                </div>



                <!-- ATTEMPT -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        কল Attempt
                    </label>


                    <input
                        type="number"
                        name="call_attempt"
                        value="1"
                        min="1"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>


            </div>



            <!-- =================================================
                 FOLLOW UP
            ================================================== -->

            <h4 style="
                margin:28px 0 15px;
                padding-bottom:8px;
                border-bottom:1px solid #eee;
                color:#374151;
            ">
                📅 Follow-up / Promise
            </h4>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                gap:15px;
            ">


                <!-- NEXT FOLLOWUP -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        পরবর্তী Follow-up তারিখ
                    </label>


                    <input
                        type="date"
                        name="next_followup_date"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>



                <!-- PROMISE DATE -->

                <div>

                    <label style="
                        display:block;
                        margin-bottom:6px;
                        font-weight:600;
                    ">
                        Promise Date
                    </label>


                    <input
                        type="date"
                        name="promise_date"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >

                </div>


            </div>



            <!-- =================================================
                 NOTE
            ================================================== -->

            <div style="margin-top:18px;">

                <label style="
                    display:block;
                    margin-bottom:6px;
                    font-weight:600;
                ">
                    কলের বিস্তারিত / Note
                </label>


                <textarea
                    name="note"
                    rows="5"
                    placeholder="গ্রাহকের সাথে কী কথা হয়েছে লিখুন..."
                    style="
                        width:100%;
                        padding:10px 12px;
                        border:1px solid #d1d5db;
                        border-radius:6px;
                        box-sizing:border-box;
                        resize:vertical;
                    "
                ><?= htmlspecialchars($default_note) ?></textarea>

            </div>



            <!-- =================================================
                 HIDDEN CUSTOMER ID
            ================================================== -->

            <input
                type="hidden"
                name="customer_record_id"
                value="<?= (int)$car_id ?>"
            >



            <!-- =================================================
                 BUTTONS
            ================================================== -->

            <div style="
                display:flex;
                gap:10px;
                justify-content:flex-end;
                margin-top:25px;
                padding-top:18px;
                border-top:1px solid #eee;
            ">


                <a
                    href="javascript:history.back()"
                    style="
                        display:inline-block;
                        padding:10px 18px;
                        background:#6b7280;
                        color:#fff;
                        text-decoration:none;
                        border-radius:6px;
                    "
                >
                    ফিরে যান
                </a>



                <button
                    type="submit"
                    style="
                        border:0;
                        padding:10px 22px;
                        background:#2563eb;
                        color:#fff;
                        border-radius:6px;
                        cursor:pointer;
                        font-weight:600;
                    "
                >

                    💾 কল স্টোরি সংরক্ষণ

                </button>


            </div>


        </form>

    </div>

</div>