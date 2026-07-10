<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // ইউজার লগইন না করলে লগইন পেজে রিডাইরেক্ট করবে
    header("Location: login.php");
    exit;
}
?>
