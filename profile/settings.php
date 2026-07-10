<?php
session_start();
require_once 'config/db.php';

// login check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];


 

// user data load
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container mt-5">


<!-- Message -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header text-center">
        <h3>👤 Account Settings</h3>
    </div>

    <div class="card-body">
        <form method="POST" action="index.php?page=profile/update_settings">

            <div class="mb-3">
                <label>নাম</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label>মোবাইল</label>
                <input type="text" name="phone" class="form-control"
                       value="<?= htmlspecialchars($user['phone']) ?>">
            </div>

            <hr>

            <h5>🔒 Password Change</h5>

            <div class="mb-3">
                <label>নতুন পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>

            <button class="btn btn-primary  ">Update Settings</button>

        </form>
    </div>
</div>

</div>
