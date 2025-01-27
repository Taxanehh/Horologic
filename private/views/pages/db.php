<?php
function getDbConnection() {
    $host = 'localhost'; // Update to your DB host
    $dbname = 'horlogic_db'; // Replace with your database name
    $username = 'root'; // Update to your DB username
    $password = 'root'; // Update to your DB password (default is empty for XAMPP)

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
