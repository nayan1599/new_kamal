<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$name  = $_SESSION['user_name'];
$email = $_SESSION['user_email'];
?>

 

<div class="container">
    <div class="card shadow profile-card">
 
    <div class="profile-header">
        <h3>👤 Profile Page</h3>
    </div>

    <div class="card-body text-center">

        <p class="fs-5"><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
        <p class="fs-5"><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>

        <hr>

        <a href="index.php?page=profile/settings" class="btn btn-primary ">
            ⚙️ Account Settings
        </a>

        <a href="index.php?page=profile/logout" class="btn btn-danger  ">
            🚪 Logout
        </a>

    </div>
</div>
 

</div>
 