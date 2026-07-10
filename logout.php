<?php
// logout.php - সবচেয়ে নিরাপদ ভার্সন
secure_session_start(); // সেশন শুরু করার জন্য একটি নিরাপদ ফাংশন ব্যবহার করুন
 
// যদি ইউজার লগইন না থাকে তাহলে সরাসরি লগইন পেজে পাঠাও
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

// ডাটাবেস থেকে remember_token মুছে ফেলা (যদি ব্যবহার করো)
require_once './config/db.php';

try {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch(PDOException $e) {
    // এরর লগ করতে পারো, কিন্তু ইউজারকে দেখাবে না
}

// সব সেশন ডেটা মুছে ফেলা
$_SESSION = array();

// সেশন কুকি মুছে ফেলা
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,      // HTTPS এর জন্য
        'httponly' => true,    // JavaScript দিয়ে অ্যাক্সেস করা যাবে না
        'samesite' => 'Strict' // CSRF প্রতিরোধ
    ]);
}

// সেশন ধ্বংস
session_destroy();

// হেডার সিকিউরিটি
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// লগইন পেজে নিয়ে যাও
header("Location:login.php");
exit();
?>