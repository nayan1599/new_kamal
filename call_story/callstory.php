 
<div style="
    max-width:1000px;
    margin:25px auto;
    padding:0 15px;
">

    <div style="
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        box-shadow:0 4px 15px rgba(0,0,0,.06);
        overflow:hidden;
    ">

        <!-- Header -->
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
                গাড়ির ID: <?= (int)$car_id ?>
            </div>
        </div>


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


        <form method="POST" style="padding:22px;">

            <!-- ================================
                 Customer Information
            ================================= -->

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

                <!-- Name -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        গ্রাহকের নাম *
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars($default_name) ?>"
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


                <!-- Phone -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        ফোন নম্বর *
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars($default_phone) ?>"
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


                <!-- Car Number -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        গাড়ির নম্বর
                    </label>

                    <input
                        type="text"
                        name="car_number"
                        value="<?= htmlspecialchars($default_car_number) ?>"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >
                </div>


                <!-- Chassis -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        Chassis Number
                    </label>

                    <input
                        type="text"
                        name="chassis_number"
                        value="<?= htmlspecialchars($default_chassis) ?>"
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


            <!-- ================================
                 Installment Information
            ================================= -->

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

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        মোট কিস্তি
                    </label>

                    <input
                        type="number"
                        name="total_kisti"
                        min="0"
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


                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        কিস্তির পরিমাণ
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="kisti_amount"
                        min="0"
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


                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        বকেয়া টাকা
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="due_amount"
                        min="0"
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

            </div>


            <!-- ================================
                 Jabin Information
            ================================= -->

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

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        জাবিনের নাম
                    </label>

                    <input
                        type="text"
                        name="jabin_name"
                        style="
                            width:100%;
                            padding:10px 12px;
                            border:1px solid #d1d5db;
                            border-radius:6px;
                            box-sizing:border-box;
                        "
                    >
                </div>


                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
                        জাবিনের ফোন
                    </label>

                    <input
                        type="text"
                        name="jabin_phone"
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


            <!-- ================================
                 Call Information
            ================================= -->

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

                <!-- Call Status -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="connected">কথা হয়েছে</option>
                        <option value="not_connected">কথা হয়নি</option>
                        <option value="busy">ব্যস্ত</option>
                        <option value="switched_off">বন্ধ</option>
                        <option value="wrong_number">ভুল নম্বর</option>
                    </select>
                </div>


                <!-- Jabin Call Status -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="connected">কথা হয়েছে</option>
                        <option value="not_connected">কথা হয়নি</option>
                        <option value="busy">ব্যস্ত</option>
                        <option value="switched_off">বন্ধ</option>
                    </select>
                </div>


                <!-- Category -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="payment_reminder">পেমেন্ট রিমাইন্ডার</option>
                        <option value="due_collection">বকেয়া আদায়</option>
                        <option value="promise">পেমেন্টের প্রতিশ্রুতি</option>
                        <option value="followup">ফলোআপ</option>
                        <option value="general">সাধারণ কল</option>
                    </select>
                </div>


                <!-- Attempt -->
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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


            <!-- ================================
                 Follow-up
            ================================= -->

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

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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


                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;">
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


            <!-- ================================
                 Note
            ================================= -->

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
                ></textarea>

            </div>


            <!-- ================================
                 Buttons
            ================================= -->

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