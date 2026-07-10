<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>কিস্তি পেমেন্ট ফর্ম</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>কিস্তি পেমেন্ট এন্ট্রি</h4>
        </div>

        <div class="card-body">
            <form action="store_payment.php" method="POST">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Customer Record ID</label>
                        <input type="number" name="customer_record_id" class="form-control" >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control" >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Customer Phone</label>
                        <input type="text" name="customer_phone" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>কিস্তি নাম্বার</label>
                        <input type="number" name="kisti_number" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Fine</label>
                        <input type="number" step="0.01" name="fine_amount" class="form-control" value="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Received</label>
                        <input type="number" step="0.01" name="total_received" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="bkash">Bkash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Note</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-success">Save Payment</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>