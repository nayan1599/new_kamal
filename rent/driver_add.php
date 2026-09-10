 
<?php
 
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $driver_name      = trim($_POST['driver_name'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $nid              = trim($_POST['nid'] ?? '');
    $driving_license  = trim($_POST['driving_license'] ?? '');
    $license_expiry  = trim($_POST['license_expiry'] ?? '');
    $status            = $_POST['status'] ?? 'active';
    $note              = trim($_POST['note'] ?? '');

    // Validation
    if ($driver_name === '') {
        $error = 'চালকের নাম লিখুন।';
    }

    if ($error === '') {

        // Duplicate phone check
        if ($phone !== '') {

            $check = $pdo->prepare("
                SELECT id
                FROM rent_drivers
                WHERE phone = ?
                LIMIT 1
            ");

            $check->execute([$phone]);

            if ($check->fetch()) {
                $error = 'এই মোবাইল নম্বর দিয়ে চালক ইতিমধ্যে যোগ করা হয়েছে।';
            }
        }
    }

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO rent_drivers
                (
                    driver_name,
                    phone,
                    address,
                    nid,
                    driving_license,
                    license_expiry,
                    status,
                    note
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->execute([
                $driver_name,
                $phone !== '' ? $phone : null,
                $address !== '' ? $address : null,
                $nid !== '' ? $nid : null,
                $driving_license !== '' ? $driving_license : null,
                $license_expiry !== '' ? $license_expiry : null,
                $status,
                $note !== '' ? $note : null
            ]);

            $success = 'চালকের তথ্য সফলভাবে সংরক্ষণ হয়েছে।';

            // Form reset
            $driver_name = '';
            $phone = '';
            $address = '';
            $nid = '';
            $driving_license = '';
            $license_expiry = '';
            $status = 'active';
            $note = '';

        } catch (PDOException $e) {

            $error = 'তথ্য সংরক্ষণ করতে সমস্যা হয়েছে।';
        }
    }
}
?>

<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-person-plus-fill text-primary"></i>
                নতুন ভাড়া চালক যোগ করুন
            </h4>

            <small class="text-muted">
                গাড়ি ভাড়া চালানোর জন্য চালকের তথ্য এন্ট্রি করুন
            </small>
        </div>

        <a href="index.php?page=rent/driver"
           class="btn btn-outline-primary">
            <i class="bi bi-list-ul"></i>
            চালকের তালিকা
        </a>

    </div>


    <!-- Success -->
    <?php if ($success): ?>

        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i>
            <?= htmlspecialchars($success) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>


    <!-- Error -->
    <?php if ($error): ?>

        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>


    <div class="card border-0 shadow-sm">

        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-person-vcard"></i>
                চালকের তথ্য
            </h5>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row g-3">

                    <!-- Name -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            চালকের নাম
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="driver_name"
                               class="form-control"
                               value="<?= htmlspecialchars($driver_name ?? '') ?>"
                               placeholder="চালকের পূর্ণ নাম"
                               required>

                    </div>


                    <!-- Phone -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            মোবাইল নম্বর
                        </label>

                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="<?= htmlspecialchars($phone ?? '') ?>"
                               placeholder="01XXXXXXXXX">

                    </div>


                    <!-- NID -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            NID নম্বর
                        </label>

                        <input type="text"
                               name="nid"
                               class="form-control"
                               value="<?= htmlspecialchars($nid ?? '') ?>"
                               placeholder="জাতীয় পরিচয়পত্র নম্বর">

                    </div>


                    <!-- Driving License -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            ড্রাইভিং লাইসেন্স নম্বর
                        </label>

                        <input type="text"
                               name="driving_license"
                               class="form-control"
                               value="<?= htmlspecialchars($driving_license ?? '') ?>"
                               placeholder="লাইসেন্স নম্বর">

                    </div>


                    <!-- License Expiry -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            লাইসেন্সের মেয়াদ শেষ
                        </label>

                        <input type="date"
                               name="license_expiry"
                               class="form-control"
                               value="<?= htmlspecialchars($license_expiry ?? '') ?>">

                    </div>


                    <!-- Status -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            স্ট্যাটাস
                        </label>

                        <select name="status" class="form-select">

                            <option value="active"
                                <?= (($status ?? 'active') === 'active') ? 'selected' : '' ?>>
                                সক্রিয়
                            </option>

                            <option value="inactive"
                                <?= (($status ?? '') === 'inactive') ? 'selected' : '' ?>>
                                নিষ্ক্রিয়
                            </option>

                        </select>

                    </div>


                    <!-- Address -->
                    <div class="col-md-12">

                        <label class="form-label fw-semibold">
                            ঠিকানা
                        </label>

                        <textarea name="address"
                                  class="form-control"
                                  rows="3"
                                  placeholder="চালকের ঠিকানা"><?= htmlspecialchars($address ?? '') ?></textarea>

                    </div>


                    <!-- Note -->
                    <div class="col-md-12">

                        <label class="form-label fw-semibold">
                            মন্তব্য
                        </label>

                        <textarea name="note"
                                  class="form-control"
                                  rows="3"
                                  placeholder="অতিরিক্ত কোনো তথ্য থাকলে লিখুন"><?= htmlspecialchars($note ?? '') ?></textarea>

                    </div>

                </div>


                <hr class="my-4">


                <div class="d-flex justify-content-end gap-2">

                    <a href="index.php?page=rent/driver"
                       class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        ফিরে যান
                    </a>

                    <button type="reset"
                            class="btn btn-outline-danger">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        রিসেট
                    </button>

                    <button type="submit"
                            class="btn btn-primary px-4">
                        <i class="bi bi-check-circle"></i>
                        চালক সংরক্ষণ
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<style>
.card {
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.form-control,
.form-select {
    min-height: 44px;
    border-radius: 8px;
}

textarea.form-control {
    min-height: auto;
}

.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
}
</style>
 
