<?php

 

$id = $_GET['id'] ?? '';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// =========================
// ডাটা লোড
// =========================
$stmt = $pdo->prepare("SELECT * FROM call_stories WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "Data not found!";
    exit;
}


// =========================
// UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jabin_name          = trim($_POST['jabin_name'] ?? '');
    $jabin_phone         = trim($_POST['jabin_phone'] ?? '');
    $call_status         = $_POST['call_status'] ?? '';
    $jabin_call_status   = $_POST['jabin_call_status'] ?? '';
    $due_amount          = $_POST['due_amount'] ?? 0;
    $next_followup_date  = $_POST['next_followup_date'] ?? null;
    $promise_date        = $_POST['promise_date'] ?? null;
    $call_attempt        = $_POST['call_attempt'] ?? 0;
    $call_category       = $_POST['call_category'] ?? '';
    $note                = trim($_POST['note'] ?? '');

    try {

        $sql = "
            UPDATE call_stories SET

                jabin_name = ?,
                jabin_phone = ?,
                call_status = ?,
                jabin_call_status = ?,
                due_amount = ?,
                next_followup_date = ?,
                promise_date = ?,
                call_attempt = ?,
                call_category = ?,
                note = ?

            WHERE id = ?
        ";

        $update = $pdo->prepare($sql);

        $update->execute([
            $jabin_name,
            $jabin_phone,
            $call_status,
            $jabin_call_status,
            $due_amount,
            $next_followup_date ?: null,
            $promise_date ?: null,
            $call_attempt,
            $call_category,
            $note,
            $id
        ]);

        header("Location: index.php?success=updated");
        exit;

    } catch (PDOException $e) {

        $error = "ডাটা আপডেট করতে সমস্যা হয়েছে: " . $e->getMessage();
    }
}

?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-pencil-square"></i>
                কল স্টোরি এডিট
            </h3>

            <p class="text-muted mb-0">
                কল স্টোরির তথ্য পরিবর্তন করুন
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            ফিরে যান
        </a>

    </div>


    <!-- Error -->
    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>

            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- Form -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                <i class="bi bi-pencil-square"></i>
                কল স্টোরি আপডেট
            </h5>

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row g-3">


                    <!-- Name -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            নাম
                        </label>

                        <input
                            type="text"
                            name="jabin_name"
                            class="form-control"
                            value="<?= htmlspecialchars($data['jabin_name'] ?? '') ?>"
                            placeholder="নাম লিখুন"
                            required
                        >

                    </div>


                    <!-- Phone -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            মোবাইল নম্বর
                        </label>

                        <input
                            type="text"
                            name="jabin_phone"
                            class="form-control"
                            value="<?= htmlspecialchars($data['jabin_phone'] ?? '') ?>"
                            placeholder="মোবাইল নম্বর"
                        >

                    </div>


                    <!-- Call Status -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            কল স্ট্যাটাস
                        </label>

                        <select name="call_status" class="form-select">

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <option value="answered"
                                <?= (($data['call_status'] ?? '') == 'answered') ? 'selected' : '' ?>>
                                কল রিসিভ করেছে
                            </option>

                            <option value="not_answered"
                                <?= (($data['call_status'] ?? '') == 'not_answered') ? 'selected' : '' ?>>
                                কল রিসিভ করেনি
                            </option>

                            <option value="busy"
                                <?= (($data['call_status'] ?? '') == 'busy') ? 'selected' : '' ?>>
                                ব্যস্ত
                            </option>

                            <option value="switched_off"
                                <?= (($data['call_status'] ?? '') == 'switched_off') ? 'selected' : '' ?>>
                                বন্ধ
                            </option>

                        </select>

                    </div>


                    <!-- Jabin Call Status -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            কলের ফলাফল
                        </label>

                        <select name="jabin_call_status" class="form-select">

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <option value="promise"
                                <?= (($data['jabin_call_status'] ?? '') == 'promise') ? 'selected' : '' ?>>
                                টাকা দেওয়ার প্রতিশ্রুতি
                            </option>

                            <option value="paid"
                                <?= (($data['jabin_call_status'] ?? '') == 'paid') ? 'selected' : '' ?>>
                                টাকা দিয়েছে
                            </option>

                            <option value="followup"
                                <?= (($data['jabin_call_status'] ?? '') == 'followup') ? 'selected' : '' ?>>
                                ফলোআপ
                            </option>

                            <option value="no_response"
                                <?= (($data['jabin_call_status'] ?? '') == 'no_response') ? 'selected' : '' ?>>
                                কোনো সাড়া পাওয়া যায়নি
                            </option>

                        </select>

                    </div>


                    <!-- Due Amount -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            বকেয়া টাকা
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            name="due_amount"
                            class="form-control"
                            value="<?= htmlspecialchars($data['due_amount'] ?? '0') ?>"
                            placeholder="0.00"
                        >

                    </div>


                    <!-- Call Attempt -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            কলের চেষ্টা
                        </label>

                        <input
                            type="number"
                            name="call_attempt"
                            class="form-control"
                            value="<?= htmlspecialchars($data['call_attempt'] ?? '0') ?>"
                            min="0"
                        >

                    </div>


                    <!-- Next Followup -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            পরবর্তী ফলোআপ তারিখ
                        </label>

                        <input
                            type="date"
                            name="next_followup_date"
                            class="form-control"
                            value="<?= htmlspecialchars($data['next_followup_date'] ?? '') ?>"
                        >

                    </div>


                    <!-- Promise Date -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            প্রতিশ্রুতির তারিখ
                        </label>

                        <input
                            type="date"
                            name="promise_date"
                            class="form-control"
                            value="<?= htmlspecialchars($data['promise_date'] ?? '') ?>"
                        >

                    </div>


                    <!-- Call Category -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            কল ক্যাটাগরি
                        </label>

                        <select name="call_category" class="form-select">

                            <option value="">
                                -- নির্বাচন করুন --
                            </option>

                            <option value="due"
                                <?= (($data['call_category'] ?? '') == 'due') ? 'selected' : '' ?>>
                                বকেয়া
                            </option>

                            <option value="payment"
                                <?= (($data['call_category'] ?? '') == 'payment') ? 'selected' : '' ?>>
                                পেমেন্ট
                            </option>

                            <option value="followup"
                                <?= (($data['call_category'] ?? '') == 'followup') ? 'selected' : '' ?>>
                                ফলোআপ
                            </option>

                            <option value="other"
                                <?= (($data['call_category'] ?? '') == 'other') ? 'selected' : '' ?>>
                                অন্যান্য
                            </option>

                        </select>

                    </div>


                    <!-- Note -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            নোট
                        </label>

                        <textarea
                            name="note"
                            rows="5"
                            class="form-control"
                            placeholder="কলের বিস্তারিত লিখুন..."
                        ><?= htmlspecialchars($data['note'] ?? '') ?></textarea>

                    </div>


                    <!-- Buttons -->
                    <div class="col-12 mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary px-4"
                        >
                            <i class="bi bi-check-circle"></i>
                            আপডেট করুন
                        </button>


                        <a
                            href="index.php"
                            class="btn btn-secondary px-4 ms-2"
                        >
                            <i class="bi bi-x-circle"></i>
                            বাতিল
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>