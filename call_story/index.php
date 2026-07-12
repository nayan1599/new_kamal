<?php
$result = $pdo->query("SELECT * FROM call_stories ORDER BY id DESC");
?>

<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📞 কল স্টোরি তালিকা</h4>

        <a href="index.php?page=call_story/add" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> নতুন কল যোগ করুন
        </a>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark text-center">
                    <tr>
                        <th>ক্রমিক</th>
                        <th>নাম</th>
                        <th>মোবাইল</th>
                        <th>গাড়ি নম্বর</th>
                        <th>বকেয়া</th>
                        <th>স্ট্যাটাস</th>
                        <th>ফলোআপ তারিখ</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody class="text-center">

                <?php 
                $i = 1;
                foreach($result as $row ){
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['car_number'] ?></td>

                        <!-- Due -->
                        <td>
                            <span class="badge bg-danger">
                                ৳ <?= $row['due_amount'] ?>
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php if($row['call_status'] == 'Connected') { ?>
                                <span class="badge bg-success">সংযুক্ত</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">
                                    <?= $row['call_status'] ?>
                                </span>
                            <?php } ?>
                        </td>

                        <!-- Followup -->
                        <td>
                            <?= $row['next_followup_date'] ?>
                        </td>

                        <!-- Action -->
                        <td>
                            <a href="index.php?page=call_story/edit&id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <a href="index.php?page=call_story/delete&id=<?= $row['id'] ?>" 
                               onclick="return confirm('আপনি কি ডিলিট করতে চান?')" 
                               class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>
    </div>

</div>