 <div class="container-fluid px-3 px-lg-4 py-4">

     <div class="page-heading">
         <div class="page-heading-copy">
             <span class="page-icon"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i></span>
             <div>
                 <p class="eyebrow mb-1">রেকর্ড যোগ করুন</p>
                 <h1 class="h3 mb-1">নতুন কাস্টমার রেকর্ড যোগ করুন</h1>
             </div>
         </div>

         <!-- Back Button -->
         <div>
             <a href="index.php?page=car/index" class="btn btn-light">
                 <i class="bi bi-arrow-left"></i> পিছনে যান
             </a>
         </div>
     </div>

     <section class="row g-3">
         <div class="col-12 col-xl-12">
             <form class="needs-validation" novalidate method="POST" action="index.php?page=sql/car_add">

                 <!-- কাস্টমারের তথ্য -->
                 <h4 class="mb-3">কাস্টমারের তথ্য</h4>
                 <div class="row g-3">
                     <div class="col-md-6">
                         <label class="form-label">কাস্টমারের পুরো নাম</label>
                         <input class="form-control" type="text" name="customer_name" placeholder="কাস্টমারের পুরো নাম"
                             required>
                         <div class="invalid-feedback">কাস্টমারের নাম দিতে হবে।</div>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">ফোন নম্বর</label>
                         <input class="form-control" type="text" name="customer_phone" placeholder="ফোন নম্বর" required>
                         <div class="invalid-feedback">ফোন নম্বর দিতে হবে।</div>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">এনআইডি নম্বর (ঐচ্ছিক)</label>
                         <input class="form-control" type="text" name="nid" placeholder="এনআইডি নম্বর">
                     </div>
                     <div class="col-12">
                         <label class="form-label">ঠিকানা</label>
                         <textarea name="address" class="form-control" rows="3"
                             placeholder="কাস্টমারের পুরো ঠিকানা"></textarea>
                     </div>
                 </div>

                 <hr class="my-4">

                 <!-- গাড়ির তথ্য -->
                 <h4 class="mb-3">গাড়ির তথ্য</h4>
                 <div class="row g-3">
                     <!-- <div class="col-md-6">
                        <label class="form-label">গাড়ির নাম/মডেল</label>
                        <input class="form-control" type="text" name="car_name" placeholder="গাড়ির নাম/মডেল">
                    </div> -->
                     <div class="col-md-6">
                         <label class="form-label">গাড়ির নম্বর</label>
                         <input class="form-control" type="text" name="car_number"
                             placeholder="গাড়ির নম্বর (যেমন: ঢাকা মেট্রো-গ-১২৩৪)">
                     </div>
                     <!-- <div class="col-md-6">
                        <label class="form-label">কার মডেল</label>
                        <input class="form-control" type="text" name="car_model" placeholder="মডেল (যেমন: Toyota Corolla)">
                    </div> -->
                     <!-- <div class="col-md-6">
                        <label class="form-label">গাড়ির সাল</label>
                        <input class="form-control" type="number" name="car_year" placeholder="২০২২" min="1900" max="<?= date('Y') ?>">
                    </div> -->
                 </div>

                 <hr class="my-4">

                 <!-- পেমেন্ট তথ্য -->
                 <h4 class="mb-3">পেমেন্ট তথ্য</h4>
                 <div class="row g-3">
                     <div class="col-md-6">
                         <label class="form-label">ধরন</label>
                         <select class="form-select" name="type" required>
                             <option value="">নির্বাচন করুন</option>
                             <option value="service">সার্ভিস</option>
                             <option value="sale">বিক্রি</option>
                             <option value="installment">কিস্তি</option>
                             <option value="repair">মেরামত</option>
                             <option value="parts">পার্টস</option>
                         </select>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">পরিমাণ</label>
                         <input class="form-control" type="number" name="quantity" value="1" min="1">
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">মোট টাকা</label>
                         <input class="form-control" type="number" name="total_price" step="0.01" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">পেইড অ্যামাউন্ট</label>
                         <input class="form-control" type="number" name="paid_amount" step="0.01" required>
                     </div>
                     <!-- <div class="col-md-6">
                        <label class="form-label">ডিসকাউন্ট</label>
                        <input class="form-control" type="number" name="discount_amount" step="0.01" value="0">
                    </div> -->
                     <!-- <div class="col-md-6">
                        <label class="form-label">ফাইন/পেনাল্টি</label>
                        <input class="form-control" type="number" name="fine_amount" step="0.01" value="0">
                    </div> -->
                 </div>

                 <hr class="my-4">

                 <!-- কিস্তি তথ্য -->
                 <h4 class="mb-3">কিস্তি তথ্য (শুধু কিস্তি হলে)</h4>
                 <div class="row g-3">
                     <div class="col-md-6">
                         <label class="form-label">মোট কিস্তির সংখ্যা</label>
                         <input class="form-control" type="number" name="total_kisti" placeholder="মোট কিস্তি" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">মাসিক কিস্তি</label>
                         <input class="form-control" type="number" name="monthly_kisti" step="0.01"
                             placeholder="মাসিক কিস্তির পরিমাণ" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label">কিস্তি শুরুর তারিখ</label>
                         <input class="form-control" type="date" name="kisti_start_date">
                     </div>
                     <!-- <div class="col-md-6">
                        <label class="form-label">পরবর্তী কিস্তির তারিখ</label>
                        <input class="form-control" type="date" name="next_due_date">
                    </div> -->
                 </div>

                 <div class="mt-4">
                     <label class="form-label">বিশেষ নোট</label>
                     <textarea name="note" class="form-control" rows="4"
                         placeholder="অতিরিক্ত তথ্য বা নোট..."></textarea>
                 </div>

                 <div class="d-flex justify-content-between mt-4">
                     <a href="index.php?page=car/index" class="btn btn-light">
                         <i class="bi bi-arrow-left"></i> পিছনে যান
                     </a>
                     <button class="btn btn-primary btn-lg" type="submit">
                         <i class="bi bi-save" aria-hidden="true"></i> সংরক্ষণ করুন
                     </button>
                 </div>

             </form>
         </div>
     </section>
 </div>