<?php 
session_start();
// accounting

$stmt = $pdo->query("SELECT * FROM accounts");
$accountings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid px-3 px-lg-4 py-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">অ্যাকাউন্টিং ম্যানেজমেন্ট</h1>
            <p class="text-muted">সকল অ্যাকাউন্টিং হেডের রেকর্ড ও সামারি</p>
        </div>
        <a href="index.php?page=accounting/add" class="btn btn-success btn-lg">
            <i class="fas fa-plus me-2"></i> নতুন অ্যাকাউন্টিং হেড
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="opacity-75">মোট অ্যাকাউন্টিং হেড</h6>
                            <h2 class="mb-0"><?= count($accountings) ?></h2>
                        </div>
                        <i class="fas fa-receipt fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>


        
    </div>
<!-- table  -->

 
 