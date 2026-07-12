<?php
// আজকের তারিখ
$today = date('Y-m-d');

// শুধু যাদের followup আছে
$stmt = $pdo->prepare("
    SELECT * FROM call_stories 
    WHERE next_followup_date IS NOT NULL 
    AND next_followup_date != ''
    ORDER BY next_followup_date ASC
");
$stmt->execute();

$result = $stmt->fetchAll();
?>

<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📅 ফলোআপ তালিকা</h4>

        <a href="index.php?page=call_story/add" class="btn btn-primary">
            ➕ নতুন কল যোগ করুন
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
                        <th>বকেয়া</th>
                        <th>ফলোআপ তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>

                <tbody class="text-center">

                <?php 
                $i = 1;
                foreach($result as $row){ 

                    // Followup status color
                    if($row['next_followup_date'] == $today){
                        $badge = "bg-warning"; // আজ
                        $label = "আজ";
                    } elseif($row['next_followup_date'] < $today){
                        $badge = "bg-danger"; // overdue
                        $label = "বিলম্বিত";
                    } else {
                        $badge = "bg-info"; // upcoming
                        $label = "আসছে";
                    }
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['phone'] ?></td>

                        <!-- Due -->
                        <td>
                            <span class="badge bg-danger">
                                ৳ <?= $row['due_amount'] ?>
                            </span>
                        </td>

                        <!-- Followup Date -->
                        <td>
                            <span class="badge <?= $badge ?>">
                                <?= date('d-m-Y', strtotime($row['next_followup_date'])) ?>
                                (<?= $label ?>)
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge bg-secondary">
                                <?= $row['call_status'] ?>
                            </span>
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