<?php
// db.php - Professional Database Connection

$host     = 'localhost';
$dbname   = 'garage_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password
    );
    
    // গুরুত্বপূর্ণ সেটিংস
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "✅ Database Connected Successfully!";  // টেস্টের জন্য

} catch(PDOException $e) {
    die("❌ Connection Failed: " . $e->getMessage());
}
?>