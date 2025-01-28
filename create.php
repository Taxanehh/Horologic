<?php
require_once './private/views/pages/db.php';

// Safety check - only allow access from localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Access denied');
}

// Database connection
try {
    $conn = getDbConnection();
    
    // Create users table if it doesn't exist (modify according to your schema)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Test user data
    $testUsers = [
        [
            'email' => 'tech@horlogic.nl',
            'password' => 'SecurePass123!', // Will be hashed
            'role' => 'admin'
        ],
        [
            'email' => 'mechanic@horlogic.nl',
            'password' => 'WatchRepair2024', // Will be hashed
            'role' => 'mechanic'
        ]
    ];

    foreach ($testUsers as $user) {
        // Hash password
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (email, password) 
            VALUES (:email, :password)
        ");
        
        $stmt->execute([
            ':email' => $user['email'],
            ':password' => $hashedPassword
        ]);
        
        echo "Created user: {$user['email']}<br>";
        echo "Password: {$user['password']}<br><br>";
    }
    
    echo "<strong>Important:</strong> Delete this file immediately after use!";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}