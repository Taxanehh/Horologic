<?php
// private/controllers/AuthController.php
require_once __DIR__ . '/../views/pages/db.php';

class AuthController {
    // Check if user is authenticated
    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['login_error'] = "Please log in to continue";
            session_write_close();
            header("Location: /login");
            exit;
        }
    }

    // Handle user login
    public static function handleLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Enhanced session security
        session_regenerate_id(true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            // Find user by email
            $conn = getDbConnection();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Hybrid check for plaintext/hashed passwords
                if ($password === $user['password'] || password_verify($password, $user['password'])) {
                    // Password migration logic
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update->execute([$newHash, $user['id']]);
                    }

                    // Set session variables
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Clear errors and redirect
                    unset($_SESSION['login_error']);
                    header("Location: /home");
                    exit;
                }
            } else {
                // Set error and redirect (for both invalid user and wrong password)
                $_SESSION['login_error'] = "Incorrect credentials";
                session_write_close(); // Ensure session is saved before redirect
                header("Location: /login");
                exit;
            }

            
            header("Location: /login");
            exit;
        }

        // Non-POST requests get redirected
        header("Location: /login");
        exit;
    }

    // Handle user logout
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /login");
        exit;
    }
}