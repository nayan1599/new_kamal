<?php
ob_start();
session_start();

// session clear
$_SESSION = [];
session_unset();
session_destroy();

// cookie delete (secure)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
?>

<!DOCTYPE html>

<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>

 
<!-- Bootstrap (optional সুন্দর করার জন্য) -->
<link href="./assets/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: #f8f9fa;
    }
    .box {
        margin-top: 120px;
        text-align: center;
    }
</style>
 

</head>
<body>

<div class="container box">
    <div class="alert alert-success">
        <h4>✅ Logout সফল হয়েছে</h4>
        <p>২ সেকেন্ড পরে Login page এ নিয়ে যাওয়া হবে...</p>
    </div>
</div>

<script>
// 2 second পরে redirect
setTimeout(function() {
    window.location.href = "index.php?page=login";
}, 20);
</script>

</body>
</html>

<?php
ob_end_flush();
?>
