<!-- account head add  -->
<?php
session_start();   
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'sql/head_add.php';
}
?>

<div class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">অ্যাকাউন্টিং হেড যোগ করুন</h1>
            <p class="text-muted">নতুন অ্যাকাউন্টিং হেডের তথ্য যোগ করুন</p>
        </div>
        <a href="index.php?page=head/index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> ফিরে যান
        </a>
    </div>
 
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="needs-validation" novalidate method="POST" action="">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">হেডের নাম</label>
                        <input type="text" class="form-control" name="head_name"  required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">হেডের টাইপ</label>
                        <select class="form-select" name="head_type" required>
                            <option value="">বাছাই করুন</option>
                            <option value="asset" >অ্যাসেট</option>
                            <option value="liability" >লায়াবিলিটি</option>
                            <option value="equity" >ইকুইটি</option>
                            <option value="income"  >আয়</option>
                            <option value="expense"  >খরচ</option>
                        </select>
                    </div>

                     
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>