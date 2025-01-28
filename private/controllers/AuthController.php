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

        $error = '';

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
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];

                    // Redirect to the home page
                    header("Location: /");
                    exit;
                } else {
                    $error = "Onjuist wachtwoord.";
                }
            } else {
                $error = "Gebruiker niet gevonden.";
            }
        }

        // Redirect back to login page with an error message
        header("Location: /login?error=" . urlencode($error));
        exit;
    }

    // Handle user logout
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        // Redirect to login page
        header("Location: /login");
        exit;
    }
}
