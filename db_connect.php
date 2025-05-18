<?php
$host = 'localhost:3306';
$dbname = 'sts';
$username = 'root'; 
$password = 'Eyadelmo2zy69'; // Eyadelmo2zy69

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user_type column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'user_type'");
    if ($stmt->rowCount() == 0) {
        // Add user_type column if it doesn't exist
        $pdo->exec("ALTER TABLE users ADD COLUMN user_type ENUM('customer', 'admin') DEFAULT 'customer'");
        
        // Set first user as admin
        $pdo->exec("UPDATE users SET user_type = 'admin' WHERE id = 1");
    }

    // Always ensure eyadsalah2222@gmail.com is admin
    $pdo->exec("UPDATE users SET user_type = 'admin' WHERE email = 'eyadsalah2222@gmail.com'");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 