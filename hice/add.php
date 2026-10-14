
<?php
/*
|--------------------------------------------------------------------------
| Hice Rent Add / Edit
|--------------------------------------------------------------------------
| New  : index.php?page=hice/add
| Edit : index.php?page=hice/add&edit=ID
|--------------------------------------------------------------------------
*/

if (!isset($pdo)) {
    die('Database connection পাওয়া যায়নি।');
}

/* EDIT DATA */
$editId = (int)($_GET['edit'] ?? 0);
$editData = null;

if ($editId > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM hice_rents
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editData) {
        echo '<div class="alert alert-danger">হিসাব পাওয়া যায়নি।</div>';
        return;
    }
}

/* ACTIVE VEHICLES */
$stmt = $pdo->query("
    SELECT id, vehicle_number, driver_name, driver_mobile
    FROM hice_vehicles
    WHERE status = 'active'
    ORDER BY vehicle_number ASC
");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* DEFAULT VALUES */
$formDate = $editData['collection_date'] ?? date('Y-m-d');
$formVehicle = $editData['vehicle_id'] ?? '';
$formRent = $editData['total_rent'] ?? '';
$formExpense = $editData['expense_amount'] ?? 0;
$formExpenseNote = $editData['expense_note'] ?? '';
$formMode = $editData['entry_mode'] ?? 'normal';
$formOwnerAmount = $editData['owner_amount'] ?? '';

$isDirect = ($formMode === 'direct');

function hiceEscape($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<style>
.hice-add-wrapper {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, sans-serif;
}
.hice-page-heading {
    background: linear-gradient(120deg,#14532d,#16a34a,#22c55e);
    border-radius: 18px;
    padding: 22px 26px;
    color: #fff;
    margin-bottom: 20px;
    box-shadow: 0 12px 30px rgba(22,163,74,.20);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}
.hice-heading-left {
    display: flex;
    align-items: center;
    gap: 15px;
}
.hice-page-icon {
    width: 55px;
    height: 55px;
    border-radius: 14px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
}
.hice-heading-left h1 {
    font-weight: 800;
    margin: 0;
}
.hice-heading-left p {
    margin: 3px 0 0;
    color: rgba(255,255,255,.85);
}
.hice-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(15,23,42,.06);
    overflow: hidden;
    margin-bottom: 20px;
}
.hice-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 16px 20px;
    font-weight: 700;
    color: #1f2937;
}
.hice-card-body {
    padding: 22px;
}
.hice-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 7px;
}
.hice-input {
    width: 100%;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    padding: 11px 13px;
    font-size: 14px;
    background-color: #fff;
}
.hice-input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34,197,94,.12);
    outline: none;
}
.hice-form-group {
    margin-bottom: 18px;
}
.hice-calculation {
    display: grid;
    grid-template-columns: repeat(5,minmax(0,1fr));
    gap: 14px;
}
.hice-calc-box {
    border-radius: 13px;
    padding: 17px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    min-width: 0;
}
.hice-calc-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 7px;
}
.hice-calc-value {
    font-size: 21px;
    font-weight: 800;
    overflow-wrap: anywhere;
}
.calc-rent { color: #2563eb; }
.calc-expense { color: #dc2626; }
.calc-net { color: #0891b2; }
.calc-driver { color: #d97706; }
.calc-owner { color: #16a34a; }

.driver-info {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 15px;
    margin-top: 8px;
    display: none;
}
.driver-info strong { color: #166534; }

.hice-mode-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    padding: 17px 20px;
    margin-bottom: 22px;
}
.hice-mode-box.direct-active {
    background: #f0fdf4;
    border-color: #86efac;
}
.hice-direct-input {
    border: 1px solid #86efac;
    background: #f0fdf4;
    font-size: 20px;
    font-weight: 800;
    padding: 15px;
}
.hice-submit,.hice-back {
    border-radius: 10px;
    padding: 11px 20px;
    font-weight: 700;
}
.hice-form-group[hidden],
[hidden] {
    display: none !important;
}
@media(max-width:1100px) {
    .hice-calculation {
        grid-template-columns: repeat(3,minmax(0,1fr));
    }
}
@media(max-width:700px) {
    .hice-calculation {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
    .hice-card-body { padding: 15px; }
    .hice-page-heading { padding: 18px; }
}
@media(max-width:420px) {
    .hice-calculation { grid-template-columns: 1fr; }
}
</style>

<div class="container-fluid px-3 px-lg-4 py-4 hice-add-wrapper">

    <!-- PAGE HEADING -->
    <div class="hice-page-heading">
        <div class="hice-heading-left">
            <div class="hice-page-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <h1 class="h4">
                    <?= $editData
                        ? 'Hice ভাড়া হিসাব সম্পাদনা'
                        : 'নতুন Hice ভাড়া এন্ট্রি' ?>
                </h1>
                <p>সাধারণ হিসাব অথবা মালিকের টাকা সরাসরি এন্ট্রি</p>
            </div>
        </div>

        <a href="index.php?page=hice/index" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> হিসাব তালিকা
        </a>
    </div>

    <div class="hice-card">
        <div class="hice-card-header">
            <i class="bi bi-pencil-square"></i>
            <?= $editData ? 'হিসাব পরিবর্তন করুন' : 'ভাড়া হিসাব দিন' ?>
        </div>

        <div class="hice-card-body">

            <form
                method="POST"
                action="index.php?page=sql/hice_save"
                id="hiceRentForm"
            >

                <?php if ($editData): ?>
                    <input type="hidden" name="id"
                           value="<?= (int)$editData['id'] ?>">
                <?php endif; ?>

                <!-- SWITCH MODE -->
                <input
                    type="hidden"
                    name="entry_mode"
                    id="entry_mode"
                    value="<?= hiceEscape($formMode) ?>"
                >

                <div class="hice-mode-box" id="modeBox">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">

                        <div>
                            <div class="fw-bold mb-1">
                                <i class="bi bi-arrow-left-right"></i>
                                হিসাবের ধরন নির্বাচন করুন
                            </div>
                            <div class="small text-muted" id="modeDescription"></div>
                        </div>

                        <div class="form-check form-switch fs-5">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="directOwnerMode"
                                <?= $isDirect ? 'checked' : '' ?>
                            >
                            <label
                                class="form-check-label fs-6 fw-bold"
                                for="directOwnerMode"
                                id="modeLabel"
                            >
                                <?= $isDirect
                                    ? 'মালিকের টাকা সরাসরি'
                                    : 'সাধারণ হিসাব' ?>
                            </label>
                        </div>

                    </div>
                </div>

                <div class="row">

                    <!-- DATE -->
                    <div class="col-md-4">
                        <div class="hice-form-group">
                            <label class="hice-label">
                                <i class="bi bi-calendar3"></i> তারিখ
                            </label>
                            <input
                                type="date"
                                name="collection_date"
                                class="hice-input"
                                required
                                value="<?= hiceEscape($formDate) ?>"
                            >
                        </div>
                    </div>

                    <!-- VEHICLE -->
                    <div class="col-md-8">
                        <div class="hice-form-group">
                            <label class="hice-label">
                                <i class="bi bi-truck"></i> Hice গাড়ি
                            </label>

                            <select
                                name="vehicle_id"
                                id="vehicle_id"
                                class="hice-input"
                                required
                            >
                                <option value="">-- গাড়ি নির্বাচন করুন --</option>

                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option
                                        value="<?= (int)$vehicle['id'] ?>"
                                        data-driver="<?= hiceEscape($vehicle['driver_name']) ?>"
                                        data-mobile="<?= hiceEscape($vehicle['driver_mobile'] ?? '') ?>"
                                        <?= (int)$formVehicle === (int)$vehicle['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= hiceEscape($vehicle['vehicle_number']) ?>
                                        -
                                        <?= hiceEscape($vehicle['driver_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="driver-info" id="driverInfo">
                                <strong>
                                    <i class="bi bi-person-badge"></i>
                                    ড্রাইভার: <span id="driverName"></span>
                                </strong>
                                <br>
                                <small>
                                    মোবাইল: <span id="driverMobile"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NORMAL MODE FIELDS -->
                <div id="normalFields">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="hice-form-group">
                                <label class="hice-label">
                                    <i class="bi bi-cash-stack"></i> মোট ভাড়া
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="total_rent"
                                    id="total_rent"
                                    class="hice-input"
                                    placeholder="যেমন: 5000"
                                    value="<?= hiceEscape($formRent) ?>"
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="hice-form-group">
                                <label class="hice-label">
                                    <i class="bi bi-wallet2"></i> মোট খরচ
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="expense_amount"
                                    id="expense_amount"
                                    class="hice-input"
                                    placeholder="যেমন: 1000"
                                    value="<?= hiceEscape($formExpense) ?>"
                                >
                            </div>
                        </div>

                    </div>

                    <div class="hice-form-group">
                        <label class="hice-label">
                            <i class="bi bi-card-text"></i> খরচের বিবরণ
                        </label>
                        <textarea
                            name="expense_note"
                            id="expense_note"
                            class="hice-input"
                            rows="3"
                            placeholder="যেমন: তেল, মেরামত, টোল ইত্যাদি"
                        ><?= hiceEscape($formExpenseNote) ?></textarea>
                    </div>

                </div>

                <!-- DIRECT OWNER MODE FIELD -->
                <div id="directOwnerSection" <?= $isDirect ? '' : 'hidden' ?>>
                    <div class="hice-form-group">
                        <label class="hice-label">
                            <i class="bi bi-person-check-fill"></i>
                            মালিকের প্রাপ্য টাকা
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="owner_amount"
                                id="owner_amount"
                                class="form-control hice-input hice-direct-input"
                                placeholder="মালিকের টাকা লিখুন"
                                value="<?= hiceEscape($formOwnerAmount) ?>"
                            >
                        </div>
                        <small class="text-muted">
                            এই মোডে শুধু মালিকের প্রাপ্য টাকা সরাসরি এন্ট্রি হবে।
                            ড্রাইভারের ৩০% ভাগ গণনা হবে না।
                        </small>
                    </div>
                </div>

                <!-- AUTOMATIC CALCULATION -->
                <div class="hice-card mb-4" id="calculationCard">
                    <div class="hice-card-header">
                        <i class="bi bi-calculator"></i> অটোমেটিক হিসাব
                    </div>

                    <div class="hice-card-body">
                        <div class="hice-calculation">

                            <div class="hice-calc-box" id="rentBox">
                                <div class="hice-calc-label">মোট ভাড়া</div>
                                <div class="hice-calc-value calc-rent" id="showRent">০</div>
                                <small>টাকা</small>
                            </div>

                            <div class="hice-calc-box" id="expenseBox">
                                <div class="hice-calc-label">খরচ</div>
                                <div class="hice-calc-value calc-expense" id="showExpense">০</div>
                                <small>টাকা</small>
                            </div>

                            <div class="hice-calc-box" id="netBox">
                                <div class="hice-calc-label">নিট আয়</div>
                                <div class="hice-calc-value calc-net" id="showNet">০</div>
                                <small>টাকা</small>
                            </div>

                            <div class="hice-calc-box" id="driverBox">
                                <div class="hice-calc-label">ড্রাইভার ৩০%</div>
                                <div class="hice-calc-value calc-driver" id="showDriver">০</div>
                                <small>টাকা</small>
                            </div>

                            <div class="hice-calc-box" id="ownerBox">
                                <div class="hice-calc-label" id="ownerCalcLabel">
                                    মালিকের আয় ৭০%
                                </div>
                                <div class="hice-calc-value calc-owner" id="showOwner">০</div>
                                <small>টাকা</small>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="alert alert-info" id="normalFormula">
                    <i class="bi bi-info-circle"></i>
                    <strong>হিসাবের নিয়ম:</strong>
                    মোট ভাড়া − খরচ = নিট আয়।
                    নিট আয়ের ৩০% ড্রাইভার এবং ৭০% মালিক পাবেন।
                </div>

                <div class="alert alert-success" id="directFormula" hidden>
                    <i class="bi bi-info-circle"></i>
                    <strong>সরাসরি হিসাব:</strong>
                    মালিকের প্রাপ্য টাকা আপনি লিখবেন। এই মোডে ড্রাইভারের
                    ৩০% ভাগ স্বয়ংক্রিয়ভাবে গণনা করা হবে না।
                </div>

                <div class="mt-3">
                    <button
                        type="submit"
                        name="save_hice"
                        value="1"
                        class="btn btn-success hice-submit"
                    >
                        <i class="bi bi-check-circle"></i>
                        <?= $editData ? 'হিসাব আপডেট করুন' : 'হিসাব সংরক্ষণ করুন' ?>
                    </button>

                    <a
                        href="index.php?page=hice/index"
                        class="btn btn-secondary hice-back"
                    >
                        <i class="bi bi-x-circle"></i> বাতিল
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const form = document.getElementById('hiceRentForm');
    const directSwitch = document.getElementById('directOwnerMode');
    const entryMode = document.getElementById('entry_mode');
    const normalFields = document.getElementById('normalFields');
    const directSection = document.getElementById('directOwnerSection');
    const modeBox = document.getElementById('modeBox');

    const rentInput = document.getElementById('total_rent');
    const expenseInput = document.getElementById('expense_amount');
    const noteInput = document.getElementById('expense_note');
    const ownerInput = document.getElementById('owner_amount');

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function calculateHice() {
        const rent = Math.max(0, parseFloat(rentInput.value) || 0);
        const expense = Math.max(0, parseFloat(expenseInput.value) || 0);
        const net = Math.max(0, rent - expense);
        const driver = net * 0.30;
        const owner = net - driver;

        document.getElementById('showRent').textContent = formatMoney(rent);
        document.getElementById('showExpense').textContent = formatMoney(expense);
        document.getElementById('showNet').textContent = formatMoney(net);
        document.getElementById('showDriver').textContent = formatMoney(driver);
        document.getElementById('showOwner').textContent = formatMoney(owner);
    }

    function updateDirectOwner() {
        const direct = directSwitch.checked;

        entryMode.value = direct ? 'direct' : 'normal';

        normalFields.hidden = direct;
        directSection.hidden = !direct;

        // Hidden ফিল্ডগুলো POST-এ যাবে না।
        rentInput.disabled = direct;
        expenseInput.disabled = direct;
        noteInput.disabled = direct;

        rentInput.required = !direct;
        ownerInput.disabled = !direct;
        ownerInput.required = direct;

        modeBox.classList.toggle('direct-active', direct);

        document.getElementById('modeLabel').textContent =
            direct ? 'মালিকের টাকা সরাসরি' : 'সাধারণ হিসাব';

        document.getElementById('modeDescription').textContent =
            direct
                ? 'মালিকের প্রাপ্য টাকা সরাসরি লিখুন।'
                : 'ভাড়া থেকে খরচ বাদ দিয়ে ৩০% ও ৭০% হিসাব হবে।';

        document.getElementById('normalFormula').hidden = direct;
        document.getElementById('directFormula').hidden = !direct;

        document.getElementById('rentBox').hidden = direct;
        document.getElementById('expenseBox').hidden = direct;
        document.getElementById('netBox').hidden = direct;
        document.getElementById('driverBox').hidden = direct;

        document.getElementById('ownerCalcLabel').textContent =
            direct ? 'মালিকের সরাসরি টাকা' : 'মালিকের আয় ৭০%';

        if (direct) {
            document.getElementById('showOwner').textContent =
                formatMoney(ownerInput.value);
        } else {
            calculateHice();
        }
    }

    rentInput.addEventListener('input', calculateHice);
    expenseInput.addEventListener('input', calculateHice);

    ownerInput.addEventListener('input', function () {
        if (directSwitch.checked) {
            document.getElementById('showOwner').textContent =
                formatMoney(this.value);
        }
    });

    directSwitch.addEventListener('change', updateDirectOwner);

    document.getElementById('vehicle_id').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        const driver = option ? (option.dataset.driver || '') : '';
        const mobile = option ? (option.dataset.mobile || '') : '';
        const info = document.getElementById('driverInfo');

        document.getElementById('driverName').textContent = driver;
        document.getElementById('driverMobile').textContent = mobile || '-';
        info.style.display = driver ? 'block' : 'none';
    });

    // ফর্ম সাবমিটের আগে সঠিক entry_mode নিশ্চিত করা
    form.addEventListener('submit', function (event) {
        entryMode.value = directSwitch.checked ? 'direct' : 'normal';

        if (directSwitch.checked) {
            const amount = parseFloat(ownerInput.value);

            if (ownerInput.value.trim() === '' || !Number.isFinite(amount) || amount < 0) {
                event.preventDefault();
                alert('মালিকের প্রাপ্য টাকার সঠিক পরিমাণ লিখুন।');
                ownerInput.focus();
            }
        }
    });

    updateDirectOwner();

    // Direct mode-এ পুরোনো owner amount দেখানো
    if (directSwitch.checked) {
        document.getElementById('showOwner').textContent =
            formatMoney(ownerInput.value);
    }

    document.getElementById('vehicle_id').dispatchEvent(new Event('change'));
})();
</script>
