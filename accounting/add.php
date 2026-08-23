<div class="container mt-4">

    <h3 class="mb-4">
        💸 নতুন খরচ যোগ করুন
    </h3>


    <form
        class="needs-validation"
        novalidate
        method="POST"
        action="index.php?page=sql/transactions_add"
    >

        <div class="row">


            <!-- ==========================================
                 তারিখ
            =========================================== -->

            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">

                    তারিখ
                    <span class="text-danger">*</span>

                </label>

                <input
                    type="date"
                    name="date"
                    class="form-control"
                    value="<?= date('Y-m-d') ?>"
                    required
                >

            </div>



            <!-- ==========================================
                 খরচের টাকা
            =========================================== -->

            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">

                    খরচের পরিমাণ
                    <span class="text-danger">*</span>

                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        ৳
                    </span>

                    <input
                        type="number"
                        name="taka_out"
                        class="form-control"
                        placeholder="খরচের টাকা লিখুন"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>

            </div>



            <!-- ==========================================
                 ACCOUNT HEAD
            =========================================== -->

            <?php

            $stmt = $pdo->query("
                SELECT *
                FROM account_head
                WHERE status = 'active'
                ORDER BY head_name ASC
            ");

            $account_heads =
                $stmt->fetchAll(PDO::FETCH_ASSOC);

            ?>


            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">

                    খরচের হেড
                    <span class="text-danger">*</span>

                </label>


                <select
                    name="head_name"
                    class="form-select"
                    required
                >

                    <option value="">
                        নির্বাচন করুন
                    </option>


                    <?php foreach ($account_heads as $account_head): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $account_head['head_name']
                            ) ?>"
                        >

                            <?= htmlspecialchars(
                                $account_head['head_name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>



            <!-- ==========================================
                 বিবরণ
            =========================================== -->

            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">

                    খরচের বিবরণ

                </label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="3"
                    placeholder="কিসের জন্য খরচ হয়েছে লিখুন..."
                ></textarea>

            </div>



            <!-- ==========================================
                 TYPE HIDDEN
            =========================================== -->

            <input
                type="hidden"
                name="type"
                value="out"
            >


            <!-- ==========================================
                 SUBMIT
            =========================================== -->

            <div class="col-md-12">

                <button
                    type="submit"
                    class="btn btn-danger btn-lg px-5"
                >

                    <i class="fas fa-save"></i>

                    খরচ সেভ করুন

                </button>

            </div>


        </div>

    </form>

</div>