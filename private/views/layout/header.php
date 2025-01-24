<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic - Watch Repair</title>
  <!-- Example: link to an external CSS file for styling -->
  <link rel="stylesheet" href="/../../../css/styles.css" />
  <style>
    /* Quick inline CSS just to demonstrate structure; 
       ideally, move this to css/styles.css */
       
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: sans-serif;
    }
    body {
      background-color: #f5f5f5;
    }
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #fff;
      padding: 1rem;
      border-bottom: 1px solid #ddd;
    }
    .logo {
      display: flex;
      align-items: center;
    }
    .logo img {
      height: 40px;
      margin-right: 10px;
    }
    .logo span {
      font-weight: bold;
      font-size: 1.2rem;
    }
    nav ul {
      list-style: none;
      display: flex;
      gap: 1rem;
    }
    nav a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }
    .user-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .user-info img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
    }
  </style>
</head>
<body>

<header>
  <!-- Left: Logo or Brand -->
  <div class="logo">
    <!-- If you have a logo, place it here -->
    <img src="path/to/logo.png" alt="Horlogic Logo" />
    <span>Horlogic</span>
  </div>

  <!-- Center: Navigation links -->
  <nav>
    <ul>
      <li><a href="home.php">Overzicht</a></li>
      <li><a href="#">Mijn reparaties</a></li>
      <li><a href="#">Bak toewijzen</a></li>
      <li><a href="#">Te factureren reparaties</a></li>
    </ul>
  </nav>

  <!-- Right: User Info -->
  <div class="user-info">
    <span>Gently Inge</span>
    <img src="path/to/user-avatar.png" alt="User Avatar">
  </div>
</header>