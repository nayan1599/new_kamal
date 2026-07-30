<?php

// ================= FETCH DATA =================
$stmt = $pdo->query("SELECT * FROM kisti_payments ORDER BY payment_date DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= SUMMARY (initial - all data) =================
$total_amount = 0;
$total_paid   = 0;

foreach ($records as $r) {
    $total_amount += $r['amount'] ?? 0;
    $total_paid   += $r['paid'] ?? 0;
}

$total_due = $total_amount - $total_paid;

?>

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>📊 কিস্তি রিপোর্ট</h4>
            <small class="text-muted">সকল কিস্তি লেনদেন</small>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn btn-dark btn-sm">
                🖨️ প্রিন্ট
            </button>
        </div>
    </div>

    <!-- SUMMARY (will be updated by JS) -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট টাকা</h6>
                    <h4 class="text-primary" id="sumAmount">
                        ৳ <?= bn_number(number_format($total_amount, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট পরিশোধ</h6>
                    <h4 class="text-success" id="sumPaid">
                        ৳ <?= bn_number(number_format($total_paid, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">মোট বকেয়া</h6>
                    <h4 class="text-danger" id="sumDue">
                        ৳ <?= bn_number(number_format($total_due, 2)) ?>
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <!-- SEARCH + DATE FILTER -->
    <div class="row g-2 mb-3 no-print">
        <div class="col-md-5">
            <input type="text" id="searchInput" class="form-control"
                   placeholder="🔍 নাম / মোবাইল / গাড়ি নম্বর সার্চ করুন...">
        </div>
        <div class="col-md-3">
            <input type="date" id="fromDate" class="form-control" title="শুরুর তারিখ">
        </div>
        <div class="col-md-3">
            <input type="date" id="toDate" class="form-control" title="শেষ তারিখ">
        </div>
        <div class="col-md-1">
            <button type="button" id="clearFilter" class="btn btn-outline-secondary w-100" title="ক্লিয়ার">
                ✕
            </button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead class="table-dark">
                    <tr>
                        <th>তারিখ</th>
                        <th>নাম</th>
                        <th>মোবাইল</th>
                        <th>গাড়ি</th>
                        <th>মোট টাকা</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($records as $row):
                        $amount = (float)($row['amount'] ?? 0);
                        $paid   = (float)($row['paid'] ?? 0);
                        $due    = $amount - $paid;
                        $isoDate = date('Y-m-d', strtotime($row['payment_date']));
                    ?>
                    <tr data-date="<?= $isoDate ?>"
                        data-amount="<?= $amount ?>"
                        data-paid="<?= $paid ?>">
                        <td><?= bn_number(date('d-m-Y', strtotime($row['payment_date']))) ?></td>
                        <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                        <td><?= bn_number($row['mobile'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['car_number'] ?? '') ?></td>
                        <td class="text-primary fw-semibold">
                            ৳ <?= bn_number(number_format($amount, 2)) ?>
                        </td>
                        <td class="text-success fw-semibold">
                            ৳ <?= bn_number(number_format($paid, 2)) ?>
                        </td>
                        <td class="text-danger fw-semibold">
                            ৳ <?= bn_number(number_format($due, 2)) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SEARCH + DATE FILTER + DYNAMIC SUMMARY SCRIPT -->
<script>
(function () {
    const searchInput = document.getElementById("searchInput");
    const fromDate    = document.getElementById("fromDate");
    const toDate      = document.getElementById("toDate");
    const clearBtn    = document.getElementById("clearFilter");
    const rows        = document.querySelectorAll("#dataTable tbody tr");

    // ===== Default: today =====
    const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
    fromDate.value = today;
    toDate.value   = today;

    // Bangla number helper (same style as your PHP bn_number)
    function bnNumber(num) {
        return num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
    }

    function updateSummary() {
        let totalAmount = 0;
        let totalPaid   = 0;

        rows.forEach(row => {
            if (row.style.display === "none") return;

            totalAmount += parseFloat(row.getAttribute("data-amount") || 0);
            totalPaid   += parseFloat(row.getAttribute("data-paid") || 0);
        });

        const totalDue = totalAmount - totalPaid;

        document.getElementById("sumAmount").textContent = "৳ " + bnNumber(totalAmount);
        document.getElementById("sumPaid").textContent   = "৳ " + bnNumber(totalPaid);
        document.getElementById("sumDue").textContent    = "৳ " + bnNumber(totalDue);
    }

    function filterRows() {
        const text = (searchInput.value || "").toLowerCase().trim();
        const from = fromDate.value;
        const to   = toDate.value;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowDate = row.getAttribute("data-date");

            const textMatch = !text || rowText.includes(text);

            let dateMatch = true;
            if (from && rowDate < from) dateMatch = false;
            if (to   && rowDate > to)   dateMatch = false;

            row.style.display = (textMatch && dateMatch) ? "" : "none";
        });

        // Update summary after filtering
        updateSummary();
    }

    // Events
    searchInput.addEventListener("keyup", filterRows);
    fromDate.addEventListener("change", filterRows);
    toDate.addEventListener("change", filterRows);

    clearBtn.addEventListener("click", function () {
        searchInput.value = "";
        fromDate.value = "";
        toDate.value = "";
        filterRows();
    });

    // Run once on page load (shows today's data + correct summary)
    filterRows();
})();
</script>