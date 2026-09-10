
<?php

// ===============================
// Active Driver List
// ===============================
$driverStmt = $pdo->query("
    SELECT id, driver_name, phone
    FROM rent_drivers
    WHERE status = 'active'
    ORDER BY driver_name ASC
");

$drivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                🏠 নতুন ভাড়া যোগ করুন
            </h5>

        </div>


        <div class="card-body">

            <form method="POST"
                  action="index.php?page=sql/rent_collection">

                <div class="row">


                    <!-- গ্রাহকের নাম -->
                    <!-- <div class="col-md-6 mb-3">

                        <label class="form-label">
                            গ্রাহকের নাম
                        </label>

                        <input type="text"
                               name="customer_name"
                               class="form-control"
                               required>

                    </div> -->


                    <!-- গাড়ি নম্বর -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            গাড়ি নম্বর
                        </label>

                        <input type="text"
                               name="car_number"
                               class="form-control"
                               required>

                    </div>


                    <!-- =========================
                         চালকের তালিকা
                    ========================== -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            <i class="bi bi-person-fill"></i>
                            চালকের নাম
                        </label>

                        <select name="customer_name"
                                id="driver_id"
                                class="form-select">

                            <option value="">
                                -- চালক নির্বাচন করুন --
                            </option>

                            <?php foreach ($drivers as $driver): ?>

                                <option
                                    value="<?= htmlspecialchars($driver['driver_name']) ?>"
                                    data-phone="<?= htmlspecialchars($driver['phone'] ?? '') ?>"
                                >
                                    <?= htmlspecialchars($driver['driver_name']) ?>

                                 

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- চালকের মোবাইল -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            চালকের মোবাইল নম্বর
                        </label>

                        <input type="text"
                               name="customer_phone"
                               id="driver_phone"
                               class="form-control"
                               placeholder="চালক নির্বাচন করলে নম্বর আসবে"
                               readonly>

                    </div>


                    <!-- গ্রাহকের মোবাইল -->
                    <!-- <div class="col-md-6 mb-3">

                        <label class="form-label">
                            গ্রাহকের মোবাইল নম্বর
                        </label>

                        <input type="text"
                               name="customer_phone"
                               class="form-control">

                    </div> -->


                    <!-- ভাড়া -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            ভাড়া
                        </label>

                        <input type="number"
                               step="0.01"
                               name="rent_amount"
                               class="form-control"
                               required>

                    </div>


                    <!-- তারিখ -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            তারিখ
                        </label>

                        <input type="date"
                               name="rent_date"
                               class="form-control"
                               value="<?= date('Y-m-d') ?>"
                               required>

                    </div>


                    <!-- Payment Method -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            পেমেন্ট মেথড
                        </label>

                        <select name="payment_method"
                                class="form-select">

                            <option value="cash">
                                ক্যাশ
                            </option>

                            <option value="bkash">
                                বিকাশ
                            </option>

                            <option value="nagad">
                                নগদ
                            </option>

                            <option value="bank_transfer">
                                ব্যাংক
                            </option>

                            <option value="rocket">
                                রকেট
                            </option>

                            <option value="cheque">
                                চেক
                            </option>

                        </select>

                    </div>


                    <!-- Payment Status -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            পেমেন্ট স্ট্যাটাস
                        </label>

                        <select name="payment_status"
                                class="form-select"
                                required>

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <option value="paid">
                                পেইড
                            </option>

                            <option value="pending">
                                পেন্ডিং
                            </option>

                            <option value="due">
                                বাকি
                            </option>

                        </select>

                    </div>


                    <!-- Note -->
                    <div class="col-12 mb-3">

                        <label class="form-label">
                            নোট
                        </label>

                        <textarea name="note"
                                  class="form-control"
                                  rows="3"></textarea>

                    </div>

                </div>


                <button type="submit"
                        class="btn btn-success">

                    💾 সংরক্ষণ করুন

                </button>

            </form>

        </div>

    </div>

</div>


<script>

// ===============================
// Driver Phone Auto Fill
// ===============================

document.getElementById('driver_id').addEventListener('change', function () {

    const selected = this.options[this.selectedIndex];

    const phone = selected.getAttribute('data-phone') || '';

    document.getElementById('driver_phone').value = phone;

});

</script>

