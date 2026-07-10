<?php
session_start();
include './config/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "সঠিক ইমেইল দিন";
    }
    if (empty($password)) {
        $errors[] = "পাসওয়ার্ড দিন";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                // অ্যাকাউন্ট Active কি না চেক
                if ($user['status'] !== 'active') {
                    $errors[] = "আপনার অ্যাকাউন্ট সক্রিয় নয়। Admin এর সাথে যোগাযোগ করুন।";
                } else {
                    // Session তৈরি
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    // Last Login Update
                    $update = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
                    $update->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

                    // ✅ লগইন সফল হলে সরাসরি ড্যাশবোর্ডে নিয়ে যাবে
                    header("Location: index.php");
                    exit();   // এটা খুব জরুরি
                }
            } else {
                $errors[] = "ইমেইল অথবা পাসওয়ার্ড ভুল";
            }
        } catch (PDOException $e) {
            $errors[] = "সিস্টেমে সমস্যা হয়েছে। পরে চেষ্টা করুন।";
        }
    }

    // Error দেখানো
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red'>⚠️ $error</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="adminHMD authentication page">
  <title>Login | adminHMD</title>

  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

 
<body class="auth-body">
    <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle
        aria-label="Switch color theme" title="Switch color theme">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>
    <main class="auth-page">
        <section class="auth-card">
            <a class="auth-brand" href="index.html"><span class="brand-icon"><i class="bi bi-grid-1x2-fill"
                        aria-hidden="true"></i></span><span><strong>adminHMD</strong><small>Sign in to your admin
                        workspace.</small></span></a>
            <div class="auth-visual"><img src="../assets/images/png/dasher-ui-bootstrap-5.jpg"
                    alt="adminHMD dashboard interface"></div>
            <form class="needs-validation" novalidate method="POST" action="">
                <div class="mb-4">
                    <p class="eyebrow mb-1">Secure Access</p>
                    <h1 class="h3 mb-1">Login</h1>
                    <p class="text-muted mb-0">Sign in to your admin workspace.</p>
                </div>
                <div class="mb-3"><label class="form-label" for="loginEmail">Email address</label><input
                        class="form-control" name="email"  type="email" required>
                    <div class="invalid-feedback">Enter a valid email.</div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between"><label class="form-label"
                            for="loginPassword">Password</label><a class="small fw-semibold"
                            href="forgot-password.html">Forgot?</a></div><input class="form-control" name="password"
                        type="password" minlength="6" required>
                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                </div>
                <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="rememberMe"><label
                        class="form-check-label" for="rememberMe">Remember me</label></div>
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"
                        aria-hidden="true"></i> Sign In</button>
            </form>

            <div class="auth-footer">New here? <a href="register.html">Create an account</a></div>
        </section>
    </main>

 
  <script src="./assets/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/main.js"></script>
</body>
</html>
