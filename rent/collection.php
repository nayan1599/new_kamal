
 

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🏠 নতুন ভাড়া যোগ করুন</h5>
        </div>


    <div class="card-body">
        <form method="POST" action="index.php?page=sql/rent_collection">

            <div class="row">
  <div class="col-md-6 mb-3">
                    <label class="form-label">গ্রাহকের নাম</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">গাড়ি নম্বর</label>
                    <input type="text" name="car_number" class="form-control" required>
                </div>

              

                <div class="col-md-6 mb-3">
                    <label class="form-label">মোবাইল নম্বর</label>
                    <input type="text" name="customer_phone" class="form-control" >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label"> ভাড়া</label>
                    <input type="number" step="0.01" name="rent_amount" class="form-control">
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label class="form-label">অগ্রিম</label>
                    <input type="number" step="0.01" name="advance_amount" class="form-control" value="0">
                </div> -->

                <!-- <div class="col-md-6 mb-3">
                    <label class="form-label">মাস (YYYY-MM)</label>
                    <input type="month" name="rent_month" class="form-control" required>
                </div> -->

                <div class="col-md-6 mb-3">
                    <label class="form-label">তারিখ</label>
                    <input type="date" name="rent_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">পেমেন্ট মেথড</label>
                    <select name="payment_method" class="form-control">
                        <option value="cash">ক্যাশ</option>
                        <option value="bkash">বিকাশ</option>
                        <option value="nagad">নগদ</option>
                        <option value="bank_transfer">ব্যাংক</option>
                        <option value="rocket">রকেট</option>
                        <option value="cheque">চেক</option>
                    </select>
                </div>


<div class="col-md-6 mb-3">
    <label class="form-label">পেমেন্ট স্ট্যাটাস</label>
    <select name="payment_status" class="form-control">
        <option value="">-- নির্বাচন করুন --</option>
        <option value="paid">পেইড</option>
        <option value="pending">পেন্ডিং</option>
        <option value="due">বাকি</option>
    </select>
</div>

                <!-- <div class="col-md-6 mb-3">
                    <label class="form-label">ট্রানজেকশন আইডি</label>
                    <input type="text" name="transaction_id" class="form-control">
                </div> -->

                <div class="col-12 mb-3">
                    <label class="form-label">নোট</label>
                    <textarea name="note" class="form-control"></textarea>
                </div>

            </div>

            <button type="submit" class="btn btn-success">
                💾 সংরক্ষণ করুন
            </button>

        </form>
    </div>
</div>


</div>
 
