<div class="container mt-4">
    <h3 class="mb-4">নতুন লেনদেন যোগ করুন</h3>

    <form class="needs-validation" novalidate method="POST" action="index.php?page=sql/transactions_add">
        <div class="row">

            <!-- তারিখ -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">তারিখ <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- টাইপ -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">লেনদেনের ধরন <span class="text-danger">*</span></label>
                <select name="type" id="type" class="form-select" required>
                    <option value="">নির্বাচন করুন</option>
                    <option value="in">আয় (IN)</option>
                    <option value="out">খরচ (OUT)</option>
                </select>
            </div>

            <!-- টাকা ইন -->
            <div class="col-md-6 mb-3" id="taka_in_div" style="display:none;">
                <label class="form-label fw-bold">প্রাপ্ত টাকা (আয়)</label>
                <input type="number" name="taka_in" class="form-control">
            </div>

            <!-- টাকা আউট -->
            <div class="col-md-6 mb-3" id="taka_out_div" style="display:none;">
                <label class="form-label fw-bold">খরচের টাকা</label>
                <input type="number" name="taka_out" class="form-control">
            </div>

            <?php 
            $stmt = $pdo->query("SELECT * FROM account_head WHERE status='active'");
            $account_heads = $stmt->fetchAll();
            ?>

            <!-- হেড -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">হিসাবের হেড <span class="text-danger">*</span></label>
                <select name="head_name" class="form-select" required>
                    <option value="">নির্বাচন করুন</option>
                    <?php foreach($account_heads as $account_head): ?>
                        <option value="<?= $account_head['head_name'] ?>">
                            <?= $account_head['head_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- বিবরণ -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">বিবরণ</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <!-- সাবমিট -->
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save"></i> সেভ করুন
                </button>
            </div>

        </div>
    </form>
</div>

<!-- JavaScript -->
<script>
document.getElementById('type').addEventListener('change', function() {
    let type = this.value;

    if (type === 'in') {
        document.getElementById('taka_in_div').style.display = 'block';
        document.getElementById('taka_out_div').style.display = 'none';
    } else if (type === 'out') {
        document.getElementById('taka_in_div').style.display = 'none';
        document.getElementById('taka_out_div').style.display = 'block';
    } else {
        document.getElementById('taka_in_div').style.display = 'none';
        document.getElementById('taka_out_div').style.display = 'none';
    }
});
</script>