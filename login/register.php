<?php

 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $role      = trim($_POST['role']);
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
                        (username, name, email, phone, password, role, status, role) 
                        VALUES (?, ?, ?, ?, ?, 'user', 'active', ?)";

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

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-6">
        <div class="card shadow-lg p-4">

            <h3 class="text-center mb-4">নতুন অ্যাকাউন্ট তৈরি করুন</h3>

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
                <label class="form-label">পুরো নাম:</label>
                <input class="form-control" type="text" name="name" required>

                <label class="form-label">ইউজারনেম:</label>
                <input class="form-control" type="text" name="username" required>

                <label class="form-label">ইমেইল:</label>
                <input class="form-control" type="email" name="email" required>

                <label class="form-label">ফোন নম্বর:</label>
                <input class="form-control" type="text" name="phone">

                <label class="form-label">পাসওয়ার্ড:</label>
                <input class="form-control" type="password" name="password" required>

                <label class="form-label">পাসওয়ার্ড আবার:</label>
                <input class="form-control" type="password" name="confirm_password" required>

                <label>User Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="">নির্বাচন করুন</option>
                    <option value="user">user</option>
                    <option value="admin">Admin</option>
                    <option value="supper_admin">Supper Admin</option>

                </select>

                <div class="py-3">
                    <button class="btn btn-success" type="submit">রেজিস্টার করুন</button>
                </div>

            </form>

            <p style="text-align:center;">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">লগইন করুন</a></p>
        </div>
    </div>
</div>