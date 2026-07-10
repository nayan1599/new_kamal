<div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="index.php" aria-label="<?php echo $_SESSION['user_name']; ?>">
          <span class="brand-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title"><?php echo $_SESSION['user_name']; ?></span>
            <!-- <span class="brand-subtitle">Admin Template</span> -->
          </span>
        </a>
      </div>
 <nav class="sidebar-nav p-3">

<!-- Dashboard -->

<a class="nav-link active" href="index.php">
    <i class="bi bi-speedometer2"></i> ড্যাশবোর্ড
</a>

<!-- Customer / Cars -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#carMenu"> <span><i class="bi bi-car-front"></i> গাড়ি ও গ্রাহক</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="carMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=car/index">সকল গাড়ি</a>
        <a class="nav-link" href="index.php?page=car/add">নতুন গাড়ি যোগ</a>
    </div>
</div>

<!-- Installment / Payment -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#paymentMenu"> <span><i class="bi bi-cash-coin"></i> কিস্তি ও পেমেন্ট</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="paymentMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=payment/index">সকল কিস্তি</a>
        <a class="nav-link" href="index.php?page=payment/add">কিস্তি গ্রহণ</a>
        <a class="nav-link" href="index.php?page=payment/due">বকেয়া তালিকা</a>
    </div>
</div>

<!-- Accounting -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#accountingMenu"> <span><i class="bi bi-calculator"></i> হিসাব ব্যবস্থাপনা</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="accountingMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=accounting/index">হিসাবের তালিকা</a>
        <a class="nav-link" href="index.php?page=accounting/add">নতুন হিসাব হেড</a>
        <a class="nav-link" href="index.php?page=accounting/report">রিপোর্ট</a>
    </div>
</div>


<!-- Rent / ভাড়া -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#rentMenu"> <span><i class="bi bi-house-door"></i> ভাড়া ব্যবস্থাপনা</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="rentMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=rent/index">সকল ভাড়া</a>
        <a class="nav-link" href="index.php?page=rent/collection">ভাড়া গ্রহণ</a>
        <a class="nav-link" href="index.php?page=rent/due">বকেয়া ভাড়া</a>
        <a class="nav-link" href="index.php?page=rent/report">ভাড়া রিপোর্ট</a>
    </div>
</div>





<!-- Reports -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#reportMenu"> <span><i class="bi bi-bar-chart-line"></i> রিপোর্ট</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="reportMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=report/daily">দৈনিক রিপোর্ট</a>
        <a class="nav-link" href="index.php?page=report/monthly">মাসিক রিপোর্ট</a>
        <a class="nav-link" href="index.php?page=report/customer">গ্রাহক রিপোর্ট</a>
    </div>
</div>

<!-- Settings -->

<a class="nav-link d-flex justify-content-between align-items-center" 
data-bs-toggle="collapse" 
href="#settingsMenu"> <span><i class="bi bi-gear"></i> সেটিংস</span> <i class="bi bi-chevron-down toggle-icon"></i> </a>

<div class="collapse" id="settingsMenu">
    <div class="submenu">
        <a class="nav-link" href="index.php?page=profile">প্রোফাইল</a>
        <a class="nav-link" href="index.php?page=profile/settings">অ্যাকাউন্ট সেটিংস</a>
        <a class="nav-link text-danger" href="index.php?page=profile/logout">লগআউট</a>
    </div>
</div>

</nav>


       
 
    </aside>

    <!-- <style> .submenu { padding-left: 25px; } /* icon rotate */ .nav-link[aria-expanded="true"] .toggle-icon { transform: rotate(180deg); transition: 0.3s; } .nav-link .toggle-icon { transition: 0.3s; } </style> -->

 