<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($name)) $errors[] = "পুরো নাম দিতে হবে";
    if (empty($username)) $errors[] = "ইউজারনেম দিতে হবে";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "সঠিক ইমেইল দিন";
    if (empty($password) || strlen($password) < 6) $errors[] = "পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে";
    if ($password !== $confirm_password) $errors[] = "পাসওয়ার্ড দুইবার মিলছে না";

    if (empty($errors)) {
        try {
            // ইমেইল বা ইউজারনেম চেক
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "এই ইমেইল অথবা ইউজারনেম ইতিমধ্যে নেওয়া হয়েছে";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // নতুন টেবিল অনুসারে INSERT
                $sql = "INSERT INTO users 
                        (username, name, email, phone, password, role, status) 
                        VALUES (?, ?, ?, ?, ?, 'user', 'active')";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $name, $email, $phone, $hashed_password]);

                $_SESSION['success'] = "✅ রেজিস্ট্রেশন সফল হয়েছে! এখন লগইন করুন।";
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            $errors[] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন অ্যাকাউন্ট তৈরি করুন</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 450px; margin: 40px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2>নতুন অ্যাকাউন্ট তৈরি করুন</h2>

<?php
if (isset($_SESSION['success'])) {
    echo "<p class='success'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}

if (!empty($errors)) {
    foreach($errors as $error) {
        echo "<p class='error'>⚠️ $error</p>";
    }
}
?>

<form method="POST" action="">
    <label>পুরো নাম:</label>
    <input type="text" name="name" required>
    
    <label>ইউজারনেম:</label>
    <input type="text" name="username" required>
    
    <label>ইমেইল:</label>
    <input type="email" name="email" required>
    
    <label>ফোন নম্বর:</label>
    <input type="text" name="phone">
    
    <label>পাসওয়ার্ড:</label>
    <input type="password" name="password" required>
    
    <label>পাসওয়ার্ড আবার:</label>
    <input type="password" name="confirm_password" required>
    
    <button type="submit">রেজিস্টার করুন</button>
</form>

<p style="text-align:center;">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">লগইন করুন</a></p>

</body>
</html>