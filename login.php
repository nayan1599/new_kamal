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
        $errors[] = "পাসওয়ার্ড দিন";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                // অ্যাকাউন্ট Active কি না চেক
                if ($user['status'] !== 'active') {
                    $errors[] = "আপনার অ্যাকাউন্ট সক্রিয় নয়। Admin এর সাথে যোগাযোগ করুন।";
                } else {
                    // Session তৈরি
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    // Last Login Update
                    $update = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
                    $update->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

                    // ✅ লগইন সফল হলে সরাসরি ড্যাশবোর্ডে নিয়ে যাবে
                    header("Location: index.php");
                    exit();   // এটা খুব জরুরি
                }
            } else {
                $errors[] = "ইমেইল অথবা পাসওয়ার্ড ভুল";
            }
        } catch (PDOException $e) {
            $errors[] = "সিস্টেমে সমস্যা হয়েছে। পরে চেষ্টা করুন।";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="জহিরুল এন্টারপ্রাইজ — গাড়ি ক্রয় বিক্রয় ম্যানেজমেন্ট সিস্টেম লগইন">
  <title>Login | জহিরুল এন্টারপ্রাইজ</title>

  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --ze-amber:#f5a623;
      --ze-amber-dark:#c97f0e;
      --ze-asphalt:#15171a;
      --ze-asphalt-2:#1b1e22;
    }
    body, .auth-page, .auth-card, .form-label, .form-check-label, h1, .btn, p, small, a{
      font-family:'Hind Siliguri','Segoe UI',sans-serif;
    }
    .auth-body{ background-color: var(--ze-asphalt) !important; }
    .auth-brand strong{ color:#fff; }
    .auth-brand small{ color:#9aa3ad !important; }
    .auth-brand .brand-icon{
      background: var(--ze-amber) !important;
      color:#241701 !important;
    }
    .auth-card{
      background: var(--ze-asphalt-2) !important;
      border:1px solid rgba(255,255,255,.06);
    }
    .auth-visual{
      background:
        repeating-linear-gradient(135deg, rgba(245,166,35,.06) 0 2px, transparent 2px 26px),
        var(--ze-asphalt);
      display:flex;
      align-items:center;
      justify-content:center;
      position:relative;
    }
    .auth-visual::after{
      content:"";
      position:absolute;
      left:0; right:0; bottom:0;
      height:10px;
      background:repeating-linear-gradient(-45deg, var(--ze-amber) 0 16px, #17191c 16px 32px);
    }
    .auth-visual .visual-inner{ text-align:center; padding:2.5rem; }
    .auth-visual .visual-inner i{ font-size:3.2rem; color:var(--ze-amber); }
    .auth-visual .visual-inner h2{
      color:#fff; font-weight:700; font-size:1.4rem; margin:1rem 0 .4rem;
    }
    .auth-visual .visual-inner p{ color:#9aa3ad; font-size:.9rem; max-width:260px; margin:0 auto; }
    .h3, h1.h3{ color:#fff; }
    .text-muted{ color:#9aa3ad !important; }
    .form-label{ color:#c7ccd1 !important; font-weight:500; }
    .form-control{
      background:#22262b !important;
      border:1px solid rgba(255,255,255,.1) !important;
      color:#ecefF2 !important;
    }
    .form-control:focus{
      border-color:var(--ze-amber) !important;
      box-shadow:0 0 0 .2rem rgba(245,166,35,.2) !important;
    }
    .input-group-text{
      background:#22262b !important;
      border:1px solid rgba(255,255,255,.1) !important;
      color:#9aa3ad !important;
    }
    .form-check-label{ color:#9aa3ad !important; }
    .form-check-input:checked{
      background-color: var(--ze-amber) !important;
      border-color: var(--ze-amber) !important;
    }
    .auth-footer, .auth-footer a{ color:#9aa3ad; }
    .auth-footer a{ color:var(--ze-amber); font-weight:600; text-decoration:none; }
    .auth-footer a:hover{ text-decoration:underline; }
    a.small.fw-semibold{ color:var(--ze-amber) !important; }
    .btn-primary{
      background:var(--ze-amber) !important;
      border-color:var(--ze-amber) !important;
      color:#241701 !important;
      font-weight:600;
    }
    .btn-primary:hover{
      background:#ffb43d !important;
      border-color:#ffb43d !important;
    }
    .alert-danger{
      background:#3a1f1f;
      border:1px solid rgba(229,72,77,.4);
      color:#f6b3b5;
    }
    .eyebrow{
      font-size:.75rem;
      letter-spacing:.12em;
      text-transform:uppercase;
      color:var(--ze-amber) !important;
      font-weight:600;
    }
    .theme-toggle{ display:none; }
  </style>
</head>


<body class="auth-body">
    <main class="auth-page">
        <section class="auth-card">
            <a class="auth-brand" href="index.php">
                <span class="brand-icon"><i class="bi bi-car-front-fill" aria-hidden="true"></i></span>
                <span><strong>জহিরুল এন্টারপ্রাইজ</strong><small>গাড়ি ক্রয় বিক্রয় ম্যানেজমেন্ট সিস্টেম</small></span>
            </a>

            <div class="auth-visual">
                <div class="visual-inner">
                    <i class="bi bi-car-front-fill" aria-hidden="true"></i>
                    <h2>স্টক থেকে বিক্রি, সবকিছু এক জায়গায়</h2>
                    <p>গাড়ি এন্ট্রি, কাস্টমার, বিক্রয় ও কাগজপত্র নিয়ন্ত্রণ করুন একটি ড্যাশবোর্ড থেকে।</p>
                </div>
            </div>

            <form class="needs-validation" novalidate method="POST" action="">
                <div class="mb-4">
                    <p class="eyebrow mb-1">Secure Access</p>
                    <h1 class="h3 mb-1">লগইন</h1>
                    <p class="text-muted mb-0">আপনার অ্যাডমিন অ্যাকাউন্টে সাইন ইন করুন</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3">
                        <?php foreach ($errors as $error): ?>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="loginEmail">ইমেইল</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill" aria-hidden="true"></i></span>
                        <input class="form-control" id="loginEmail" name="email" type="email" required
                               placeholder="you@zahirulenterprise.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        <div class="invalid-feedback">সঠিক ইমেইল দিন।</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="loginPassword">পাসওয়ার্ড</label>
                        <a class="small fw-semibold" href="forgot-password.html">ভুলে গেছেন?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
                        <input class="form-control" id="loginPassword" name="password" type="password" minlength="6" required placeholder="••••••••">
                        <div class="invalid-feedback">পাসওয়ার্ড কমপক্ষে ৬ ক্যারেক্টার হতে হবে।</div>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">মনে রাখুন</label>
                </div>

                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> সাইন ইন
                </button>
            </form>

            <!-- <div class="auth-footer">নতুন? <a href="register.html">অ্যাকাউন্ট তৈরি করুন</a></div> -->
        </section>
    </main>

  <script src="./assets/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach((form) => {
        form.addEventListener('submit', (event) => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
  <script src="./assets/js/main.js"></script>
</body>
</html>