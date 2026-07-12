 
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>কল তথ্য যুক্ত করুন</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="index.php?page=sql/call_store">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">নাম</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">মোবাইল নম্বর</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">গাড়ির নম্বর</label>
                        <input type="text" name="car_number" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">চ্যাসিস নম্বর</label>
                        <input type="text" name="chassis_number" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">মোট কিস্তি</label>
                        <input type="number" name="total_kisti" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">কিস্তির পরিমাণ</label>
                        <input type="number" step="0.01" name="kisti_amount" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">জামিনদারের নাম</label>
                        <input type="text" name="jabin_name" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">জামিনদারের মোবাইল</label>
                        <input type="text" name="jabin_phone" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">কল স্ট্যাটাস</label>
                        <select name="call_status" class="form-select">
                            <option value="">নির্বাচন করুন</option>
                            <option value="received">কল রিসিভ হয়েছে</option>
                            <option value="not_received">কল রিসিভ হয়নি</option>
                            <option value="switched_off">ফোন বন্ধ</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">জামিনদার কল স্ট্যাটাস</label>
                        <select name="jabin_call_status" class="form-select">
                            <option value="">নির্বাচন করুন</option>
                            <option value="received">কল রিসিভ হয়েছে</option>
                            <option value="not_received">কল রিসিভ হয়নি</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">বাকি টাকা</label>
                        <input type="number" step="0.01" name="due_amount" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">পরবর্তী ফলোআপ তারিখ</label>
                        <input type="date" name="next_followup_date" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">প্রতিশ্রুতির তারিখ</label>
                        <input type="date" name="promise_date" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">কল চেষ্টা সংখ্যা</label>
                        <input type="number" name="call_attempt" value="1" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">কল ক্যাটাগরি</label>
                        <input type="text" name="call_category" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">নোট</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-success">সংরক্ষণ করুন</button>
                <a href="call_list.php" class="btn btn-secondary">ফিরে যান</a>

            </form>
        </div>
    </div>
</div>

 