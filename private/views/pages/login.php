<?php
session_start();

// If user is already logged in, redirect to index
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: home.php");
    exit;
}

// Handle form submission (dummy check)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Just a very simple “dummy” login check:
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        // In a real app, you’d verify credentials via a database.
        // For now, set session and redirect.
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Vul e-mailadres en wachtwoord in.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic | Inloggen</title>
  <style>
    /* Full-page background */
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: url("images/watch-bg.jpg") no-repeat center center fixed;
      background-size: cover;
    }

    /* Centered white panel */
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      width: 320px;
      margin: 5% auto;
      padding: 2rem;
      border-radius: 4px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    .logo img {
      max-height: 60px;
      margin-bottom: 1rem;
    }

    .form-group {
      margin-bottom: 1rem;
      text-align: left;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    button {
      background-color: #2b5d7f; /* A navy/blue color */
      color: #fff;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: bold;
    }
    button:hover {
      background-color: #234a65;
    }

    .error {
      color: red;
      margin-bottom: 1rem;
    }

    .forgot-password {
      margin-top: 1rem;
      display: inline-block;
      font-size: 0.9rem;
      text-decoration: none;
      color: #555;
    }
    .forgot-password:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="logo">
      <!-- Replace with your actual logo image if desired -->
      <img src="images/weisz-logo.png" alt="Weisz Group Logo" />
    </div>

    <?php if (!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="email">E-mailadres <span style="color:red">*</span></label>
        <input 
          type="email" 
          name="email" 
          id="email"
          placeholder="E-mailadres" 
          required
        >
      </div>

      <div class="form-group">
        <label for="password">Wachtwoord <span style="color:red">*</span></label>
        <input 
          type="password" 
          name="password" 
          id="password"
          placeholder="Wachtwoord" 
          required
        >
      </div>

      <div class="checkbox-group">
        <input type="checkbox" id="remember" name="remember" />
        <label for="remember" style="font-weight: normal;">Ingelogd blijven</label>
      </div>

      <button type="submit">Inloggen</button>

    </form>

    <a class="forgot-password" href="#">Wachtwoord vergeten?</a>
  </div>

</body>
</html>
