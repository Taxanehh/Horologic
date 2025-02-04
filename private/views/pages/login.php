<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$emailValue = '';

// Retrieve session error and email
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if (isset($_SESSION['old_email'])) {
    $emailValue = $_SESSION['old_email'];
    unset($_SESSION['old_email']);
}

require_once 'db.php';

function findUserByEmail($email) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 1) Check if there's already a session or a remember_me cookie
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // ADDED FOR REMEMBER ME: Check cookie if session not active
    if (!empty($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        // Check if token exists in DB
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Token is valid -> auto-login
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            session_regenerate_id(true);

            header("Location: /home");
            exit;
        }
    }
} else {
    // Already logged in via session
    header("Location: /home");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']); // ADDED FOR REMEMBER ME

    $user = findUserByEmail($email);

    if ($user) {
        $passwordIsValid = false;
        $needsRehash = false;

        // Check if password is correct (hashed or plaintext)
        if (password_verify($password, $user['password'])) {
            $passwordIsValid = true;
            $needsRehash = password_needs_rehash($user['password'], PASSWORD_DEFAULT);
        } elseif ($password === $user['password']) {
            // Handle plaintext password (upgrade to hash)
            $passwordIsValid = true;
            $needsRehash = true;
        }

        if ($passwordIsValid) {
            // Update password hash if necessary
            if ($needsRehash) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $conn = getDbConnection();
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$newHash, $user['id']]);
            }

            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            session_regenerate_id(true);

            // 2) If "remember me" is checked, generate token + set cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32)); // 64 char hex
                $conn = getDbConnection();
                $updateToken = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $updateToken->execute([$token, $user['id']]);

                // Set cookie for ~30 days
                setcookie('remember_token', $token, [
                    'expires' => time() + (86400 * 30),
                    'path'    => '/',
                    'domain'  => $_SERVER['HTTP_HOST'], // adjust if needed
                    'secure'  => isset($_SERVER['HTTPS']),
                    'httponly'=> true,
                    'samesite'=> 'Strict',
                ]);
            }

            header("Location: /home");
            exit;
        }
    }

    // If invalid credentials, set session error and redirect
    $_SESSION['login_error'] = "Incorrect credentials.";
    $_SESSION['old_email'] = $email;
    header("Location: /login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic | Inloggen</title>
  <link rel="stylesheet" type="text/css" href="/css/globals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="login-page">

  <div class="login-container">
    <div class="logo">
      <!-- Replace with your actual logo image if desired -->
      <img src="/../../../img/horologic2.png" alt="Weisz Group Logo" style="width: 300px; height: 115.083px;" />
    </div>

    <?php if (isset($error) && $error !== ''): ?>
      <div class="error-message">
        <strong>⚠️:</strong> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <form method="POST" action="/login">
      <div class="form-group">
        <label for="email">E-mail <span style="color:red">*</span></label>
        <input 
          type="email" 
          name="email" 
          id="email"
          value="<?php echo htmlspecialchars($emailValue); ?>"
          placeholder="E-mail" 
          required
        >
      </div>

      <div class="form-group">
        <label for="password">Password <span style="color:red">*</span></label>
        <input 
          type="password" 
          name="password" 
          id="password"
          placeholder="Password" 
          required
        >
      </div>

      <div class="checkbox-group">
        <input type="checkbox" id="remember" name="remember" />
        <label for="remember" style="font-weight: normal;">Keep me signed in</label>
      </div>

      <button type="submit">Log in <i class="fa fa-sign-in"></i></button>
    </form>

    <a class="forgot-password" href="#">Forgot Password?</a>
  </div>

</body>
</html>
