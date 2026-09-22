 
<div class=" ">

    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">

        <!-- ==============================
             SIDEBAR HEADER
        =============================== -->

        <div class="sidebar-header">

            <a
                class="brand-mark"
                href="index.php"
                aria-label="<?= htmlspecialchars($_SESSION['user_name']) ?>"
            >

                <span class="brand-icon">
                    <i class="bi bi-grid-1x2-fill"></i>
                </span>

                <span class="brand-copy">

                    <span class="brand-title">
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </span>

                </span>

            </a>

        </div>


        <!-- ==============================
             SIDEBAR NAVIGATION
        =============================== -->

        <nav class="sidebar-nav p-3">


        <?php if ($_SESSION['user_role'] == 'user'): ?>


            <!-- ==============================
                 USER / RENT
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#userRentMenu"
                role="button"
                aria-expanded="false"
                aria-controls="userRentMenu"
            >

                <span>
                    <i class="bi bi-car-front-fill me-2"></i>
                    ভাড়া ব্যবস্থাপনা
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="userRentMenu">

                <div class="submenu">

                    <a
                        class="nav-link"
                        href="index.php?page=rent/index"
                    >
                        <i class="bi bi-list-ul me-2"></i>
                        সকল ভাড়া
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=rent/collection"
                    >
                        <i class="bi bi-cash-coin me-2"></i>
                        ভাড়া গ্রহণ
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=rent/due"
                    >
                        <i class="bi bi-exclamation-circle me-2"></i>
                        বকেয়া ভাড়া
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=rent/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        ভাড়া রিপোর্ট
                    </a>

                </div>

            </div>


        <?php else: ?>


            <!-- ==============================
                 DASHBOARD
            =============================== -->

            <a
                class="nav-link active"
                href="index.php"
            >

                <i class="bi bi-speedometer2 me-2"></i>
                ড্যাশবোর্ড

            </a>


            <!-- ==============================
                 গাড়ি ও গ্রাহক
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#carMenu"
                role="button"
                aria-expanded="false"
                aria-controls="carMenu"
            >

                <span>
                    <i class="bi bi-car-front-fill me-2"></i>
                    গাড়ি ও গ্রাহক
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="carMenu">

                <div class="submenu">

                    <a
                        class="nav-link"
                        href="index.php?page=car/index"
                    >
                        <i class="bi bi-list-ul me-2"></i>
                        সকল গাড়ি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=car/add"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        নতুন গাড়ি যোগ
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=car/time_out"
                    >
                        <i class="bi bi-clock-history me-2"></i>
                        সময় শেষ হওয়া গাড়ি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=car/completed"
                    >
                        <i class="bi bi-check-circle me-2"></i>
                        সম্পন্ন গাড়ি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=car/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        গাড়ি রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 কিস্তি ও পেমেন্ট
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#paymentMenu"
                role="button"
                aria-expanded="false"
                aria-controls="paymentMenu"
            >

                <span>
                    <i class="bi bi-cash-stack me-2"></i>
                    কিস্তি ও পেমেন্ট
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="paymentMenu">

                <div class="submenu">
    <a
                        class="nav-link"
                        href="index.php?page=payment/dashboard"
                    >
                        <i class="bi bi-speedometer2 me-2"></i>
                        কিস্তি ড্যাশবোর্ড
                    </a>
                    <a
                        class="nav-link"
                        href="index.php?page=payment/index"
                    >
                        <i class="bi bi-list-ul me-2"></i>
                        সকল কিস্তি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=payment/add"
                    >
                        <i class="bi bi-cash-coin me-2"></i>
                        কিস্তি গ্রহণ
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=payment/due"
                    >
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        বকেয়া কিস্তি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=payment/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        কিস্তি রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 ভাড়া ব্যবস্থাপনা
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#mainRentMenu"
                role="button"
                aria-expanded="false"
                aria-controls="mainRentMenu"
            >

                <span>
                    <i class="bi bi-car-front me-2"></i>
                    ভাড়া ব্যবস্থাপনা
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="mainRentMenu">

                <div class="submenu">
<!-- ভাড়া ড্যাশবোর্ড -->
  <a class="nav-link" href="index.php?page=rent/dashboard" > <i class="bi bi-speedometer2 me-2"></i> ভাড়া ড্যাশবোর্ড </a> 
  <!-- সকল ভাড়া --> <a class="nav-link" href="index.php?page=rent/index" > <i class="bi bi-list-ul me-2"></i> সকল ভাড়া </a>
    <!-- নতুন ভাড়া এন্ট্রি --> <a class="nav-link" href="index.php?page=rent/add" > <i class="bi bi-car-front-fill me-2"></i> নতুন ভাড়া এন্ট্রি </a> 
     <!-- ভাড়া আদায় --> <a class="nav-link" href="index.php?page=rent/collection" > <i class="bi bi-cash-coin me-2"></i> ভাড়া গ্রহণ </a>
       <!-- বকেয়া ভাড়া --> <a class="nav-link" href="index.php?page=rent/due" > <i class="bi bi-exclamation-triangle me-2"></i> বকেয়া ভাড়া </a>
         
        <a class="nav-link" href="index.php?page=rent/driver" > <i class="bi bi-people-fill me-2"></i> চালকদের তালিকা </a> 
        <!-- নতুন চালক --> <a class="nav-link" href="index.php?page=rent/driver_add" > <i class="bi bi-person-plus-fill me-2"></i> নতুন চালক যোগ </a>
          <!-- ভাড়া রিপোর্ট --> <a class="nav-link" href="index.php?page=rent/report" > <i class="bi bi-bar-chart-line me-2"></i> ভাড়া রিপোর্ট </a>

                </div>

            </div>


            <!-- ==============================
                 কল স্টোরি
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#callStoryMenu"
                role="button"
                aria-expanded="false"
                aria-controls="callStoryMenu"
            >

                <span>
                    <i class="bi bi-telephone-forward me-2"></i>
                    কল স্টোরি
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="callStoryMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=call_story/index"
                    >
                        <i class="bi bi-speedometer2 me-2"></i>
                        কল ড্যাশবোর্ড
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=call_story/today_followup"
                    >
                        <i class="bi bi-calendar-event me-2"></i>
                        আজকের Follow-up
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=call_story/promise"
                    >
                        <i class="bi bi-hand-thumbs-up me-2"></i>
                        Promise তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=call_story/call_report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        কল রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 গ্যারেজ হিসাব
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#garageAccountingMenu"
                role="button"
                aria-expanded="false"
                aria-controls="garageAccountingMenu"
            >

                <span>
                    <i class="bi bi-building me-2"></i>
                    গ্যারেজের হিসাব
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="garageAccountingMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=garage/dashboard"
                    >
                        <i class="bi bi-speedometer2 me-2"></i>
                        গ্যারেজ ড্যাশবোর্ড
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=garage/index"
                    >
                        <i class="bi bi-journal-text me-2"></i>
                        গ্যারেজের হিসাব
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=garage/add"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        নতুন আয় / ব্যয়
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=garage/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        গ্যারেজ রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 মেট্রো গাড়ির হিসাব
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#metroMenu"
                role="button"
                aria-expanded="false"
                aria-controls="metroMenu"
            >

                <span>
                    <i class="bi bi-car-front-fill me-2"></i>
                    মেট্রো গাড়ির হিসাব
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="metroMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=metro/index"
                    >
                        <i class="bi bi-list-ul me-2"></i>
                        গাড়ির তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=metro/add"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        নতুন গাড়ি যোগ
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=metro/payments"
                    >
                        <i class="bi bi-cash-stack me-2"></i>
                        জমার তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=metro/due"
                    >
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        বকেয়া তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=metro/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        মেট্রো রিপোর্ট
                    </a>

                </div>

            </div>

<!-- ==============================
     CNG ব্যবস্থাপনা
=============================== -->

<a
    class="nav-link d-flex justify-content-between align-items-center"
    data-bs-toggle="collapse"
    href="#cngMenu"
    role="button"
    aria-expanded="false"
    aria-controls="cngMenu"
>

    <span>
        <i class="bi bi-fuel-pump-fill me-2"></i>
        CNG ব্যবস্থাপনা
    </span>

    <i class="bi bi-chevron-down toggle-icon"></i>

</a>


<div class="collapse" id="cngMenu">

    <div class="submenu">


        <!-- CNG Dashboard -->

        <a
            class="nav-link"
            href="index.php?page=cng/dashboard"
        >

            <i class="bi bi-speedometer2 me-2"></i>
            CNG ড্যাশবোর্ড

        </a>


        <!-- সকল CNG গাড়ি -->

        <a
            class="nav-link"
            href="index.php?page=cng/index"
        >

            <i class="bi bi-car-front-fill me-2"></i>
            সকল CNG গাড়ি

        </a>


        <!-- নতুন CNG গাড়ি -->

        <a
            class="nav-link"
            href="index.php?page=cng/cng_add"
        >

            <i class="bi bi-plus-circle me-2"></i>
            নতুন CNG গাড়ি

        </a>


        <!-- দৈনিক জমা -->

        <a
            class="nav-link"
            href="index.php?page=cng/cng_rent"
        >

            <i class="bi bi-cash-coin me-2"></i>
            দৈনিক জমা গ্রহণ

        </a>


      

        <!-- রিপোর্ট -->

        <a
            class="nav-link"
            href="index.php?page=cng/report"
        >

            <i class="bi bi-bar-chart-line-fill me-2"></i>
            CNG রিপোর্ট

        </a>


  

 

        


    </div>

</div>
            <!-- ==============================
                 বেতন ব্যবস্থাপনা
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#salaryMenu"
                role="button"
                aria-expanded="false"
                aria-controls="salaryMenu"
            >

                <span>
                    <i class="bi bi-wallet2 me-2"></i>
                    বেতন ব্যবস্থাপনা
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="salaryMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=salary/payment"
                    >
                        <i class="bi bi-cash-coin me-2"></i>
                        বেতন প্রদান
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=salary/due"
                    >
                        <i class="bi bi-exclamation-circle me-2"></i>
                        বেতন বকেয়া
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=salary/history"
                    >
                        <i class="bi bi-clock-history me-2"></i>
                        বেতন হিস্টোরি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=salary/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        বেতন রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 কর্মচারী ব্যবস্থাপনা
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#employeeMenu"
                role="button"
                aria-expanded="false"
                aria-controls="employeeMenu"
            >

                <span>
                    <i class="bi bi-people-fill me-2"></i>
                    কর্মচারী ব্যবস্থাপনা
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="employeeMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=employee/index"
                    >
                        <i class="bi bi-person-lines-fill me-2"></i>
                        কর্মচারীর তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=employee/add"
                    >
                        <i class="bi bi-person-plus-fill me-2"></i>
                        নতুন কর্মচারী
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=employee/attendance"
                    >
                        <i class="bi bi-calendar-check me-2"></i>
                        উপস্থিতি
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=employee/attendance_add"
                    >
                        <i class="bi bi-calendar-plus me-2"></i>
                        উপস্থিতি যোগ
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=employee/attendance_report"
                    >
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>
                        উপস্থিতি রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 হিসাব ব্যবস্থাপনা
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#accountingMenu"
                role="button"
                aria-expanded="false"
                aria-controls="accountingMenu"
            >

                <span>
                    <i class="bi bi-calculator-fill me-2"></i>
                    হিসাব ব্যবস্থাপনা
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="accountingMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=accounting/index"
                    >
                        <i class="bi bi-list-ul me-2"></i>
                        হিসাবের তালিকা
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=accounting/add"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        নতুন হিসাব হেড
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=accounting/report"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        হিসাব রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 রিপোর্ট
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#reportMenu"
                role="button"
                aria-expanded="false"
                aria-controls="reportMenu"
            >

                <span>
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    রিপোর্ট
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="reportMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=report/daily"
                    >
                        <i class="bi bi-calendar-day me-2"></i>
                        দৈনিক রিপোর্ট
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=report/monthly"
                    >
                        <i class="bi bi-calendar-month me-2"></i>
                        মাসিক রিপোর্ট
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=report/customer"
                    >
                        <i class="bi bi-person-vcard me-2"></i>
                        গ্রাহক রিপোর্ট
                    </a>

                </div>

            </div>


            <!-- ==============================
                 সেটিংস
            =============================== -->

            <a
                class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#settingsMenu"
                role="button"
                aria-expanded="false"
                aria-controls="settingsMenu"
            >

                <span>
                    <i class="bi bi-gear-fill me-2"></i>
                    সেটিংস
                </span>

                <i class="bi bi-chevron-down toggle-icon"></i>

            </a>


            <div class="collapse" id="settingsMenu">

                <div class="submenu">


                    <a
                        class="nav-link"
                        href="index.php?page=profile/settings"
                    >
                        <i class="bi bi-person-gear me-2"></i>
                        অ্যাকাউন্ট সেটিংস
                    </a>


                    <a
                        class="nav-link"
                        href="index.php?page=head/add"
                    >
                        <i class="bi bi-folder-plus me-2"></i>
                        নতুন হেড
                    </a>

                </div>

            </div>


        <?php endif; ?>


        </nav>

    </aside>

</div>


<!-- ==============================
     SIDEBAR CSS
============================== -->

<style>

.submenu {
    padding-left: 18px;
}

.submenu .nav-link {
    font-size: 14px;
    padding: 9px 12px;
    border-radius: 7px;
}

.sidebar-nav > .nav-link {
    margin-bottom: 3px;
    border-radius: 8px;
}

.toggle-icon {
    transition: transform .25s ease;
}

.nav-link[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

.nav-link i {
    vertical-align: middle;
}

</style>
 
