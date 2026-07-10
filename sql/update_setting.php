<?php
 
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    try {

        // password change check
        if (!empty($password)) {

            if ($password !== $confirm) {
                $_SESSION['error'] = "❌ Password match হয়নি!";
                header("Location: index.php?page=profile/settings");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, phone = ?, password = ?
                WHERE id = ?
            ");

            $stmt->execute([$name, $phone, $hashedPassword, $user_id]);

        } else {

            // password change না করলে
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, phone = ?
                WHERE id = ?
            ");

            $stmt->execute([$name, $phone, $user_id]);
        }

        $_SESSION['success'] = "✅ Account updated successfully!";
        header("Location: index.php?page=profile/settings");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Update Failed!";
        header("Location: index.php?page=profile/settings");
        exit();
    }

} else {
    echo "Invalid Request";
}
?>
