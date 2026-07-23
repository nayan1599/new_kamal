<?php
function calculateEMI($principal, $annualRate, $months) {
    $monthlyRate = ($annualRate / 100) / 12;

    if ($monthlyRate == 0) {
        return $principal / $months;
    }

    $emi = $principal * $monthlyRate * pow((1 + $monthlyRate), $months) 
          / (pow((1 + $monthlyRate), $months) - 1);

    return round($emi, 2);
}

function generateSchedule($principal, $annualRate, $months) {
    $monthlyRate = ($annualRate / 100) / 12;
    $emi = calculateEMI($principal, $annualRate, $months);

    $balance = $principal;
    $schedule = [];

    for ($i = 1; $i <= $months; $i++) {
        $interest = $balance * $monthlyRate;
        $principalPay = $emi - $interest;
        $balance -= $principalPay;

        $schedule[] = [
            'month' => $i,
            'emi' => round($emi, 2),
            'interest' => round($interest, 2),
            'principal' => round($principalPay, 2),
            'balance' => round(max($balance, 0), 2)
        ];
    }

    return $schedule;
}

$result = null;
$schedule = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $paid = $_POST['paid'];
    $rate = $_POST['rate'];
    $months = $_POST['months'];

    $due = $amount - $paid;

    $emi = calculateEMI($due, $rate, $months);
    $totalPay = $emi * $months;
    $totalInterest = $totalPay - $due;

    $result = [
        'due' => $due,
        'emi' => $emi,
        'totalPay' => $totalPay,
        'interest' => $totalInterest
    ];

    $schedule = generateSchedule($due, $rate, $months);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🚗 EMI Calculator</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">🚗 Car EMI Calculator</h4>
        </div>

        <div class="card-body">

            <form method="post" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Total Price</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Paid Amount</label>
                    <input type="number" name="paid" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Interest (%)</label>
                    <input type="number" step="0.01" name="rate" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Months</label>
                    <input type="number" name="months" class="form-control" required>
                </div>

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-success px-5">Calculate</button>
                </div>
            </form>

        </div>
    </div>

    <?php if ($result): ?>

    <!-- Result Cards -->
    <div class="row mt-4 text-center">

        <div class="col-md-3">
            <div class="card border-primary shadow">
                <div class="card-body">
                    <h6>Due</h6>
                    <h5><?= $result['due'] ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow">
                <div class="card-body">
                    <h6>Monthly EMI</h6>
                    <h5><?= $result['emi'] ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning shadow">
                <div class="card-body">
                    <h6>Total Payment</h6>
                    <h5><?= round($result['totalPay'],2) ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow">
                <div class="card-body">
                    <h6>Total Interest</h6>
                    <h5><?= round($result['interest'],2) ?></h5>
                </div>
            </div>
        </div>

    </div>

    <!-- Table -->
    <div class="card mt-4 shadow">
        <div class="card-header bg-dark text-white">
            Installment Schedule
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Month</th>
                        <th>EMI</th>
                        <th>Interest</th>
                        <th>Principal</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $row): ?>
                    <tr>
                        <td><?= $row['month'] ?></td>
                        <td><?= $row['emi'] ?></td>
                        <td><?= $row['interest'] ?></td>
                        <td><?= $row['principal'] ?></td>
                        <td><?= $row['balance'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

    <?php endif; ?>

</div>

</body>
</html>