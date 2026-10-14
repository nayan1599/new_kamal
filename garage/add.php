<?php

$garageStmt = $pdo->query("
    SELECT *
    FROM garages
    WHERE status = 'active'
    ORDER BY id
");

$garages = $garageStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h4 class="mb-0 fw-bold">
                ➕ নতুন আয় / ব্যয়
            </h4>

        </div>


        <div class="card-body">

            <form method="POST" action="index.php?page=sql/garage_save">

                <div class="row g-3">


                    <!-- GARAGE -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            গ্যারেজ
                        </label>

                        <select
                            name="garage_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                নির্বাচন করুন
                            </option>

                            <?php foreach ($garages as $garage): ?>

                                <option
                                    value="<?= $garage['id'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $garage['garage_name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- DATE -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            তারিখ
                        </label>

                        <input
                            type="date"
                            name="transaction_date"
                            value="<?= date('Y-m-d') ?>"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- TYPE -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            ধরন
                        </label>

                        <select
                            name="type"
                            id="type"
                            class="form-select"
                            required
                        >

                            <option value="">
                                নির্বাচন করুন
                            </option>

                            <option value="income">
                                💰 আয়
                            </option>

                            <option value="expense">
                                💸 ব্যয়
                            </option>

                        </select>

                    </div>


                    <!-- CATEGORY -->

<div class="col-md-6">

    <label class="form-label fw-bold">
        খাত
    </label>

    <select name="category" class="form-select" required>
        <option value="">-- খাত নির্বাচন করুন --</option>

        <option value="পার্টস বিল">পার্টস বিল</option>
        <option value="লেতের বিল">লেতের বিল</option>
        <option value="মিস্ত্রি বিল">মিস্ত্রি বিল</option>
        <option value="তেল বিল">তেল বিল</option>
        <option value="গ্যাস বিল">গ্যাস বিল</option>
        <option value="বিদ্যুৎ বিল">বিদ্যুৎ বিল</option>
        <option value="ব্যাটারি বিল">ব্যাটারি বিল</option>
        <option value="গ্যারেজ ভাড়া">গ্যারেজ ভাড়া</option>
        <option value="মালামাল বিল">মালামাল বিল</option>
        <option value="চা-নাস্তা বিল">চা-নাস্তা বিল</option>
        <option value="অফিস খরচ">অফিস খরচ</option>
        <option value="কাগজপত্র/ডকুমেন্ট বিল">কাগজপত্র/ডকুমেন্ট বিল</option>
        <option value="অন্যান্য বিল">অন্যান্য বিল</option>
    </select>

</div>


                    <!-- AMOUNT -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            টাকা
                        </label>

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            বিবরণ
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="2"
                            placeholder="প্রয়োজনে বিস্তারিত লিখুন"
                        ></textarea>

                    </div>


                    <!-- BUTTON -->

                    <div class="col-12">

                        <button
                            type="submit"
                            class="btn btn-primary px-5"
                        >

                            💾 সেভ করুন

                        </button>

                        <a
                            href="index.php?page=garage/index"
                            class="btn btn-secondary"
                        >

                            বাতিল

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>