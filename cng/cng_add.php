<?php

// =====================================================
// ACTIVE GARAGES
// =====================================================

$stmt = $pdo->query("
    SELECT
        id,
        garage_name
    FROM garages
    WHERE status = 'active'
    ORDER BY garage_name ASC
");

$garages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                <i class="bi bi-car-front-fill text-primary"></i>

                নতুন CNG যোগ করুন

            </h3>

            <div class="text-muted">

                CNG গাড়ির তথ্য সংরক্ষণ করুন

            </div>

        </div>


        <div class="mt-2 mt-md-0">

            <a href="index.php?page=cng/index"
               class="btn btn-outline-secondary">

                <i class="bi bi-list-ul"></i>

                CNG লিস্ট

            </a>

        </div>

    </div>


    <!-- =================================================
         SUCCESS MESSAGE
    ================================================== -->

    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($_SESSION['success']) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <?= htmlspecialchars($_SESSION['error']) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <!-- =================================================
         FORM CARD
    ================================================== -->

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex align-items-center">

                <div class="bg-primary bg-opacity-10
                            rounded-3 p-2 me-3">

                    <i class="bi bi-car-front-fill
                              text-primary fs-4"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-0">
                        CNG তথ্য
                    </h5>

                    <small class="text-muted">
                        গাড়ির সম্পূর্ণ তথ্য দিন
                    </small>

                </div>

            </div>

        </div>


        <div class="card-body p-4">


            <form method="POST" action="index.php?page=sql/cng_save">


                <div class="row g-4">


                    <!-- =================================================
                         CAR NUMBER
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            গাড়ির নাম্বার

                            <span class="text-danger">*</span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-car-front-fill"></i>

                            </span>


                            <input type="text"
                                   name="car_number"
                                   class="form-control"
                                   placeholder="যেমন: ঢাকা মেট্রো-থ-১২-৩৪৫৬"
                                   required>

                        </div>


                        <small class="text-muted">
                            গাড়ির নাম্বার অবশ্যই ইউনিক হতে হবে।
                        </small>

                    </div>


                    <!-- =================================================
                         GARAGE
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            গ্যারেজ

                            <span class="text-danger">*</span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-house-door-fill"></i>

                            </span>


                            <select name="garage_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- গ্যারেজ নির্বাচন করুন --
                                </option>


                                <?php foreach ($garages as $garage): ?>

                                    <option value="<?= (int)$garage['id'] ?>">

                                        <?= htmlspecialchars(
                                            $garage['garage_name']
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>


                            </select>

                        </div>


                        <?php if (empty($garages)): ?>

                            <small class="text-danger">

                                কোনো Active গ্যারেজ পাওয়া যায়নি।

                            </small>

                        <?php endif; ?>

                    </div>


                    <!-- =================================================
                         DRIVER NAME
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            চালকের নাম

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-person-fill"></i>

                            </span>


                            <input type="text"
                                   name="driver_name"
                                   class="form-control"
                                   placeholder="চালকের নাম">

                        </div>

                    </div>


                    <!-- =================================================
                         DRIVER MOBILE
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            চালকের মোবাইল

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-telephone-fill"></i>

                            </span>


                            <input type="text"
                                   name="driver_mobile"
                                   class="form-control"
                                   placeholder="01XXXXXXXXX"
                                   maxlength="20">

                        </div>

                    </div>


                    <!-- =================================================
                         DAILY RENT
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            প্রতিদিনের ভাড়া

                            <span class="text-danger">*</span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                ৳

                            </span>


                            <input type="number"
                                   name="daily_rent"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="0"
                                   placeholder="800"
                                   required>

                        </div>


                        <small class="text-muted">

                            প্রতিদিন গাড়ি থেকে যে ভাড়া নেওয়া হবে।

                        </small>

                    </div>


                    <!-- =================================================
                         STATUS
                    ================================================== -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            গাড়ির Status

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-toggle-on"></i>

                            </span>


                            <select name="status"
                                    class="form-select">

                                <option value="active">

                                    Active

                                </option>


                                <option value="inactive">

                                    Inactive

                                </option>


                                <option value="maintenance">

                                    Maintenance

                                </option>


                                <option value="sale">

                                    Sale

                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- =================================================
                         NOTE
                    ================================================== -->

                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            নোট

                        </label>


                        <textarea name="note"
                                  class="form-control"
                                  rows="4"
                                  maxlength="500"
                                  placeholder="গাড়ি সম্পর্কে প্রয়োজনীয় কোনো তথ্য লিখুন..."></textarea>

                    </div>


                </div>


                <!-- =================================================
                     BUTTONS
                ================================================== -->

                <div class="d-flex justify-content-end
                            gap-2 mt-4 pt-3 border-top">


                    <a href="index.php?page=cng/dashboard"
                       class="btn btn-light border px-4">

                        <i class="bi bi-x-circle"></i>

                        বাতিল

                    </a>


                    <button type="submit"
                            class="btn btn-primary px-4">

                        <i class="bi bi-save-fill"></i>

                        CNG সংরক্ষণ করুন

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>