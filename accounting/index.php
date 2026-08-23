<?php

// =====================================================
// DATE FILTER
// DEFAULT = TODAY
// =====================================================

$today = date('Y-m-d');

$from_date = $_GET['from_date'] ?? $today;
$to_date   = $_GET['to_date'] ?? $today;


// =====================================================
// VALIDATE DATE
// =====================================================

if (empty($from_date)) {
    $from_date = $today;
}

if (empty($to_date)) {
    $to_date = $today;
}


// =====================================================
// IF FROM DATE > TO DATE
// =====================================================

if ($from_date > $to_date) {

    $temp = $from_date;

    $from_date = $to_date;

    $to_date = $temp;
}


// =====================================================
// FETCH TRANSACTIONS
// =====================================================

$stmt = $pdo->query("
    SELECT *
    FROM transactions
    ORDER BY created_at DESC
    LIMIT 100
");

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// HEAD SUMMARY
// DATE WISE
// =====================================================

$sql = "
    SELECT
        head_name,
        COALESCE(SUM(taka_in), 0) AS total_in,
        COALESCE(SUM(taka_out), 0) AS total_out
    FROM transactions
    WHERE created_at >= :from_date
      AND created_at < :to_date
";


// =====================================================
// DATE PARAMETERS
// =====================================================

$params = [];


// FROM DATE = 00:00:00

$params[':from_date'] =
    $from_date . ' 00:00:00';


// TO DATE
// NEXT DAY 00:00:00
// যাতে পুরো শেষ দিনের হিসাব আসে

$next_date = date(
    'Y-m-d',
    strtotime($to_date . ' +1 day')
);

$params[':to_date'] =
    $next_date . ' 00:00:00';


// =====================================================
// GROUP BY HEAD
// =====================================================

$sql .= "
    GROUP BY head_name
    ORDER BY head_name ASC
";


// =====================================================
// EXECUTE QUERY
// =====================================================

$summaryStmt = $pdo->prepare($sql);

$summaryStmt->execute($params);

$head_summaries =
    $summaryStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// GRAND TOTAL
// =====================================================

$grand_in = 0;
$grand_out = 0;


foreach ($head_summaries as $row) {

    $grand_in += (float)($row['total_in'] ?? 0);

    $grand_out += (float)($row['total_out'] ?? 0);
}


$grand_balance =
    $grand_in - $grand_out;

?>



<div class="container-fluid p-4">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-heading d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>📊 কাস্টমার একাউন্টিং</h3>

            <small class="text-muted">
                তারিখ অনুযায়ী সকল লেনদেনের হিসাব
            </small>

        </div>


        <a
            href="index.php?page=accounting/add"
            class="btn btn-success"
        >

            <i class="bi bi-plus-circle"></i>

            নতুন এন্ট্রি

        </a>

    </div>



    <!-- =====================================================
         DATE FILTER
    ====================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET" action="index.php"  >
 
                <input  type="hidden"  name="page"  value="accounting/index" >


                <div class="row g-3 align-items-end">


                    <!-- FROM DATE -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            শুরু তারিখ

                        </label>

                        <input
                            type="date"
                            name="from_date"
                            class="form-control"
                            value="<?= htmlspecialchars($from_date) ?>"
                            required
                        >

                    </div>



                    <!-- TO DATE -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            শেষ তারিখ

                        </label>

                        <input
                            type="date"
                            name="to_date"
                            class="form-control"
                            value="<?= htmlspecialchars($to_date) ?>"
                            required
                        >

                    </div>



                    <!-- BUTTON -->

                    <div class="col-md-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            🔍 হিসাব দেখুন

                        </button>


                        <a
                            href="index.php?page=accounting/index"
                            class="btn btn-secondary"
                        >

                            আজ

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>



    <!-- =====================================================
         CURRENT FILTER
    ====================================================== -->

    <div class="alert alert-info">

        <strong>📅 হিসাবের সময়:</strong>

        <?= bn_number(
            date(
                'd-m-Y',
                strtotime($from_date)
            )
        ) ?>

        থেকে

        <?= bn_number(
            date(
                'd-m-Y',
                strtotime($to_date)
            )
        ) ?>

    </div>



    <!-- =====================================================
         GRAND SUMMARY
    ====================================================== -->

    <div class="row mb-4">


        <!-- =================================================
             TOTAL IN
        ================================================== -->

        <div class="col-md-4">

            <div class="card bg-success text-white shadow">

                <div class="card-body">

                    <h6>
                        মোট জমা (IN)
                    </h6>

                    <h4 class="mb-0">

                        ৳

                        <?= bn_number(
                            number_format(
                                $grand_in,
                                2
                            )
                        ) ?>

                    </h4>

                </div>

            </div>

        </div>



        <!-- =================================================
             TOTAL OUT
        ================================================== -->

        <div class="col-md-4">

            <div class="card bg-danger text-white shadow">

                <div class="card-body">

                    <h6>
                        মোট খরচ (OUT)
                    </h6>

                    <h4 class="mb-0">

                        ৳

                        <?= bn_number(
                            number_format(
                                $grand_out,
                                2
                            )
                        ) ?>

                    </h4>

                </div>

            </div>

        </div>



        <!-- =================================================
             BALANCE
        ================================================== -->

        <div class="col-md-4">

            <div
                class="card
                <?= $grand_balance >= 0
                    ? 'bg-primary'
                    : 'bg-warning'
                ?>
                text-white shadow"
            >

                <div class="card-body">

                    <h6>
                        মোট ব্যালেন্স
                    </h6>

                    <h4 class="mb-0">

                        ৳

                        <?= bn_number(
                            number_format(
                                $grand_balance,
                                2
                            )
                        ) ?>

                    </h4>

                </div>

            </div>

        </div>


    </div>



    <!-- =====================================================
         SEARCH
    ====================================================== -->

    <div class="mb-3">

        <input
            type="text"
            id="searchInput"
            class="form-control"
            placeholder="🔍 সার্চ করুন (হেড)..."
        >

    </div>



    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="card shadow">

        <div class="table-responsive">

            <table
                class="table table-hover mb-0"
                id="dataTable"
            >


                <!-- TABLE HEADER -->

                <thead class="table-dark text-white">

                    <tr>

                        <th>
                            হেড
                        </th>

                        <th>
                            জমা (IN)
                        </th>

                        <th>
                            খরচ (OUT)
                        </th>

                        <th>
                            ব্যালেন্স
                        </th>

                        <th class="text-end">
                            অ্যাকশন
                        </th>

                    </tr>

                </thead>



                <!-- TABLE BODY -->

                <tbody>


                <?php if (!empty($head_summaries)): ?>


                    <?php foreach ($head_summaries as $row): ?>


                        <?php

                        $total_in =
                            (float)($row['total_in'] ?? 0);

                        $total_out =
                            (float)($row['total_out'] ?? 0);

                        $balance =
                            $total_in - $total_out;

                        ?>


                        <tr>


                            <!-- HEAD -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['head_name']
                                        ?? 'N/A'
                                    ) ?>

                                </strong>

                            </td>



                            <!-- IN -->

                            <td class="text-primary fw-semibold">

                                ৳

                                <?= bn_number(
                                    number_format(
                                        $total_in,
                                        2
                                    )
                                ) ?>

                            </td>



                            <!-- OUT -->

                            <td class="text-danger fw-semibold">

                                ৳

                                <?= bn_number(
                                    number_format(
                                        $total_out,
                                        2
                                    )
                                ) ?>

                            </td>



                            <!-- BALANCE -->

                            <td>

                                <span
                                    class="badge
                                    <?= $balance >= 0
                                        ? 'bg-success'
                                        : 'bg-danger'
                                    ?>"
                                >

                                    ৳

                                    <?= bn_number(
                                        number_format(
                                            $balance,
                                            2
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- ACTION -->

                            <td class="text-end">


                                <a
                                    href="index.php?page=accounting/sammery&head_name=<?= urlencode($row['head_name']) ?>&from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >

                                    Summary

                                </a>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- NO DATA -->

                    <tr>

                        <td
                            colspan="5"
                            class="text-center py-4"
                        >

                            <div class="text-muted">

                                📭 এই তারিখে কোনো লেনদেন পাওয়া যায়নি।

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>

        </div>

    </div>


</div>



<!-- =====================================================
     SEARCH SCRIPT
====================================================== -->

<script>

document
    .getElementById("searchInput")
    .addEventListener("keyup", function () {

        let value =
            this.value.toLowerCase();


        let rows =
            document.querySelectorAll(
                "#dataTable tbody tr"
            );


        rows.forEach(function (row) {

            let text =
                row.innerText.toLowerCase();


            row.style.display =
                text.includes(value)
                    ? ""
                    : "none";

        });

    });

</script>