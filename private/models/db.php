<?php

function getDbConnection() {
    static $conn;

    if ($conn === null) {
        $dsn = 'mysql:host=localhost;port=3306;dbname=your_db;charset=utf8mb4';
        try {
            $conn = new PDO($dsn, 'localhost', 'root');
            // Enable exceptions on errors
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}
