<?php

$editId = (int)($_GET['edit'] ?? 0);

$editData = [
    'id'            => 0,
    'vehicle_number'=> '',
    'driver_name'   => '',
    'driver_mobile' => '',
    'status'        => 'active'
];

if ($editId > 0) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM hice_vehicles
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$editId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $editData = $row;
    }
}
?>

<style>
.hice-form-wrap {
    max-width: 850px;
    margin: 25px auto;
}

.hice-form-header {
    background: linear-gradient(135deg, #198754, #20c997);
    color: #fff;
    padding: 25px;
    border-radius: 18px 18px 0 0;
}

.hice-form-card {
    background: #fff;
    padding: 30px;
    border-radius: 0 0 18px 18px;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
}

.form-label {
    font-weight: 600;
}

.form-control,
.form-select {
    min-height: 48px;
    border-radius: 10px;
}

.btn-save {
    min-height: 48px;
    border-radius: 10px;
    font-weight: 600;
}
</style>


<div class="container-fluid hice-form-wrap">

    <div class="hice-form-header">

        <h3 class="mb-1">
            <i class="bi bi-car-front-fill"></i>

            <?= $editId > 0 ? 'Hice গাড়ি সম্পাদনা' : 'নতুন Hice গাড়ি যোগ করুন' ?>
        </h3>

        <div>
            গাড়ি ও ড্রাইভারের তথ্য সংরক্ষণ করুন
        </div>

    </div>


    <div class="hice-form-card">

        <?php if (!empty($_SESSION['error'])): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>

            <?php unset($_SESSION['error']); ?>

        <?php endif; ?>


        <?php if (!empty($_SESSION['success'])): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>

            <?php unset($_SESSION['success']); ?>

        <?php endif; ?>


        <form
            method="POST"
            action="index.php?page=sql/hice_vehicle_save"
        >

            <?php if ($editId > 0): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$editData['id'] ?>"
                >

            <?php endif; ?>


            <div class="row g-4">

                <!-- Vehicle Number -->
                <div class="col-md-6">

                    <label class="form-label">
                        গাড়ির নম্বর
                    </label>

                    <input
                        type="text"
                        name="vehicle_number"
                        class="form-control"
                        placeholder="যেমন: ঢাকা মেট্রো-হ ১২-৩৪৫৬"
                        value="<?= htmlspecialchars($editData['vehicle_number']) ?>"
                        required
                    >

                </div>


                <!-- Driver Name -->
                <div class="col-md-6">

                    <label class="form-label">
                        ড্রাইভারের নাম
                    </label>

                    <input
                        type="text"
                        name="driver_name"
                        class="form-control"
                        placeholder="ড্রাইভারের নাম লিখুন"
                        value="<?= htmlspecialchars($editData['driver_name']) ?>"
                        required
                    >

                </div>


                <!-- Driver Mobile -->
                <div class="col-md-6">

                    <label class="form-label">
                        ড্রাইভারের মোবাইল
                    </label>

                    <input
                        type="text"
                        name="driver_mobile"
                        class="form-control"
                        placeholder="01XXXXXXXXX"
                        value="<?= htmlspecialchars($editData['driver_mobile'] ?? '') ?>"
                    >

                </div>


                <!-- Status -->
                <div class="col-md-6">

                    <label class="form-label">
                        স্ট্যাটাস
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option
                            value="active"
                            <?= $editData['status'] === 'active' ? 'selected' : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $editData['status'] === 'inactive' ? 'selected' : '' ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            <hr class="my-4">


            <div class="d-flex justify-content-between align-items-center">

                <a
                    href="index.php?page=hice/vehicles"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    ফিরে যান
                </a>


                <button
                    type="submit"
                    class="btn btn-success btn-save px-4"
                >

                    <i class="bi bi-check-circle"></i>

                    <?= $editId > 0 ? 'আপডেট করুন' : 'গাড়ি সংরক্ষণ করুন' ?>

                </button>

            </div>

        </form>

    </div>

</div>