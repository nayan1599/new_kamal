<div class="container mt-4">
    <h3 class="mb-4">নতুন অ্যাকাউন্টিং হেড যোগ করুন</h3>

    <form method="POST">

        <div class="row">
            

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">হেড নাম<span class="text-danger">*</span></label>
                <input type="text" name="head_name" class="form-control" required>
            </div>

            

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">হেড টাইপ <span class="text-danger">*</span></label>
                <select name="head_type" class="form-select" required>
                    <option value="">নির্বাচন করুন</option>
                    <option value="Asset">Asset (সম্পদ)</option>
                    <option value="Liability">Liability (দায়)</option>
                    <option value="Equity">Equity (মালিকানা স্বত্ব)</option>
                    <option value="Revenue">Revenue (আয়)</option>
                    <option value="Expense">Expense (খরচ)</option>
                    <option value="COGS">COGS (পণ্য বিক্রয় মূল্য)</option>
                </select>
            </div>

 
            </div>

        <!-- status -->



        
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save"></i> সেভ করুন
                </button>
            </div>
        </div>

    </form>
</div>