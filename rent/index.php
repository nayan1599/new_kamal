 
<?php 
$today = date('Y-m-d');
// শুধু আজকের ডাটা আনো
$stmt = $pdo->prepare("
    SELECT *
    FROM rents
    WHERE DATE(created_at) = ?
    ORDER BY created_at DESC
");

$stmt->execute([$today]);
$rents = $stmt->fetchAll();
?>


<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Page Heading -->
    <div class="row align-items-center">

        <div class="col-md-9 col-sm-12 my-2">

            <div class="page-heading-copy">

                <span class="page-icon">
                    <i class="bi bi-car-front"></i>
                </span>

                <div>

                    <p class="eyebrow mb-1">
                        রেকর্ডস
                    </p>

                    <h1 class="h3 mb-1">
                        গাড়ি ভাড়া লিস্ট
                    </h1>

                    <p class="text-muted mb-0">
                        আজকের সকল গাড়ি ভাড়া লিস্ট
                    </p>

                </div>

            </div>

        </div>


        <div class="col-md-3 col-sm-12 my-2 text-md-end">

            <a href="index.php?page=rent/collection"
               class="btn btn-success">

                <i class="bi bi-plus-circle"></i>
                নতুন এন্ট্রি

            </a>

            <a href="index.php?page=rent/due"
               class="btn btn-primary">

                <i class="bi bi-exclamation-circle"></i>
                বকেয়া ভাড়া

            </a>

        </div>

    </div>


    <!-- সব রেকর্ড -->
    <section class="panel mt-3">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-car-front"></i>

                    <span>
                        আজকের সকল রেকর্ড
                    </span>

                </h2>

            </div>


            <div class="d-flex align-items-center gap-2">

                <input
                    class="form-control form-control-sm table-search"
                    type="search"
                    id="searchInput"
                    placeholder="🔍 নাম, ফোন বা গাড়ির নম্বর সার্চ করুন..."
                    aria-label="Search"
                >

            </div>

        </div>


        <div class="table-responsive">

            <table
                class="table table-hover align-middle mb-0 border shadow-sm custom-table"
                id="dataTable"
            >

                <thead class="text-center">

                    <tr>

                        <th>#</th>

                        <th>তারিখ</th>

                        <th>কাস্টমার</th>

                        <th>ফোন</th>

                        <th>গাড়ি</th>

                        <th>চালক</th>

                        <th class="text-end">
                            মোট টাকা
                        </th>

                        <th>
                            স্ট্যাটাস
                        </th>

                        <th>
                            নোট
                        </th>

                        <th>
                            একশন
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!empty($rents)): ?>

                        <?php foreach($rents as $key => $row): ?>

                            <tr>

                                <!-- Serial -->
                                <td class="text-center">
                                    <?= $key + 1 ?>
                                </td>


                                <!-- Date -->
                                <td>
                                    <?= bn_number(
                                        date(
                                            'd-m-Y',
                                            strtotime($row['rent_date'])
                                        )
                                    ) ?>
                                </td>


                                <!-- Customer -->
                                <td class="fw-semibold text-dark">

                                    <?= htmlspecialchars(
                                        $row['customer_name'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- Phone -->
                                <td>

                                    <?= bn_number(
                                        htmlspecialchars(
                                            $row['customer_phone'] ?? '-'
                                        )
                                    ) ?>

                                </td>


                                <!-- Car -->
                                <td>

                                    <span class="badge bg-secondary">

                                        <?= htmlspecialchars(
                                            $row['car_number'] ?? '-'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- Driver -->
                                <td>

                                    <?php if (!empty($row['driver_id'])): ?>

                                        <?php

                                        $driverStmt = $pdo->prepare("
                                            SELECT driver_name
                                            FROM rent_drivers
                                            WHERE id = ?
                                            LIMIT 1
                                        ");

                                        $driverStmt->execute([
                                            $row['driver_id']
                                        ]);

                                        $driver = $driverStmt->fetch(
                                            PDO::FETCH_ASSOC
                                        );

                                        ?>

                                        <?php if ($driver): ?>

                                            <span class="badge bg-info text-dark">

                                                <i class="bi bi-person-fill"></i>

                                                <?= htmlspecialchars(
                                                    $driver['driver_name']
                                                ) ?>

                                            </span>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <!-- Amount -->
                                <td class="text-end text-success fw-bold">

                                    ৳
                                    <?= bn_number(
                                        number_format(
                                            $row['rent_amount'] ?? 0,
                                            2
                                        )
                                    ) ?>

                                </td>


                                <!-- Status -->
                                <td class="text-center">

                                    <?php

                                    $status = strtolower(
                                        $row['payment_status'] ?? ''
                                    );

                                    if ($status == 'paid') {

                                        echo '
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i>
                                            PAID
                                        </span>';

                                    } elseif ($status == 'due') {

                                        echo '
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-circle"></i>
                                            DUE
                                        </span>';

                                    } else {

                                        echo '
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i>
                                            PENDING
                                        </span>';

                                    }

                                    ?>

                                </td>


                                <!-- Note -->
                                <td class="text-muted small">

                                    <?= htmlspecialchars(
                                        $row['note'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- Actions -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-1">

                                        <!-- VIEW -->
                                        <a
                                            href="index.php?page=rent/view&id=<?= (int)$row['id'] ?>"
                                            class="btn btn-sm btn-outline-info"
                                            title="দেখুন"
                                        >

                                            <i class="bi bi-eye-fill"></i>

                                        </a>


                                        <!-- EDIT -->
                                        <a
                                            href="index.php?page=rent/edit&id=<?= (int)$row['id'] ?>"
                                            class="btn btn-sm btn-outline-success"
                                            title="এডিট"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                        </a>


                                        <!-- DELETE -->
                                        <a
                                            href="index.php?page=rent/delete&id=<?= (int)$row['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            title="ডিলিট"
                                            onclick="return confirm('আপনি কি এই ভাড়া রেকর্ডটি ডিলিট করতে চান?');"
                                        >

                                            <i class="bi bi-trash-fill"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bi bi-car-front fs-1 d-block mb-2"
                                ></i>

                                আজকের কোনো ভাড়া রেকর্ড পাওয়া যায়নি।

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- Search Script -->
<script>

document
    .getElementById("searchInput")
    .addEventListener("keyup", function () {

        let value = this.value
            .toLowerCase()
            .trim();

        let rows = document.querySelectorAll(
            "#dataTable tbody tr"
        );

        rows.forEach(function (row) {

            let text = row.innerText
                .toLowerCase();

            row.style.display =
                text.includes(value)
                ? ""
                : "none";

        });

    });

</script>


<style>

/* ==========================
   Action Buttons
========================== */

.custom-table .btn-sm {

    width: 34px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border-radius: 7px;

}


/* ==========================
   Table
========================== */

.custom-table th {

    white-space: nowrap;
    font-size: 14px;

}

.custom-table td {

    font-size: 14px;

}


/* ==========================
   Panel
========================== */

.panel {

    background: #fff;

    border-radius: 12px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.06);

    overflow: hidden;

}

.panel-header {

    padding: 16px 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    background: #fff;

    border-bottom: 1px solid #eee;

}


/* ==========================
   Search
========================== */

.table-search {

    min-width: 280px;

    border-radius: 8px;

}


/* ==========================
   Responsive
========================== */

@media (max-width: 768px) {

    .panel-header {

        flex-direction: column;

        align-items: stretch;

    }

    .table-search {

        width: 100%;

        min-width: 100%;

    }

}

</style>
 
