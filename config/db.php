<?php
// db.php - Professional Database Connection

$host     = 'localhost';
// server 
// $dbname   = 'oncalldr_oncalldr_kamal';
// $username = 'oncalldr_kamal';
// $password = '@nayan1122';
// local 
$dbname   = 'garage_management';
// laptop 
$username = 'nayan';
$password = '123456';

// pc 
// $username = 'root';
// $password = '';
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