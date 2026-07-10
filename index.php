<?php
include './config/db.php';
include './config/session_check.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'layout/header.php';
include 'layout/sidebar.php';
include 'layout/top.php';
?>


<?php
$page = $_GET['page'] ?? 'dashboard';

// নিরাপত্তার জন্য শুধুমাত্র অক্ষর, সংখ্যা, / এবং .php অনুমোদন
$page = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $page);

$page_path = $page;
if (!str_ends_with($page_path, '.php')) {
    $page_path .= '.php';
}

if (file_exists($page_path)) {
    include $page_path;
} else {
    echo "<h3 style='color:red;'>Page not found: " . htmlspecialchars($page_path) . "</h3>";
}
?>
 
<?php 
include 'layout/footer.php';
?>