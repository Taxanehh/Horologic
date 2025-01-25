<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic - Watch Repair</title>
  <!-- Example: link to an external CSS file for styling -->
  <link rel="stylesheet" href="/../../../css/globals.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
      justify-content: flex-start;
      background-color: #fff;
      margin: 0;
      border-bottom: 1px solid #ddd;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
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
      font-weight: 100;
      font-size: 0.9rem;
    }
    .user-info {
      margin-left: auto;
      margin-right: 2rem;        
      display: flex;
      align-items: center;  
      gap: 0.75rem;
      font-weight: bold;
      font-size: 0.9rem;
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
  <div class="logo2">
    <!-- If you have a logo, place it here -->
    <img src="/../../../img/logo2.png" alt="Horlogic Logo" />
  </div>

  <!-- Center: Navigation links -->
  <nav>
    <ul>
      <li><a href="home.php"><i class="fa fa-home"></i> Overzicht</a></li>
      <li><a href="#"><i class="fa fa-wrench"></i> Mijn reparaties</a></li>
      <li><a href="#"><i class="fa fa-wrench"></i> Bak toewijzen</a></li>
      <li><a href="#"><i class="fa fa-euro"></i> Te factureren reparaties</a></li>
    </ul>
  </nav>

  <!-- Right: User Info -->
  <div class="user-info">
    <img src="/../../../img/hond.png" alt="User Avatar">
    <div class="user-text">
      <span class="user-name">Fabian Goede</span>
      <span class="user-email">dummy@dummy.nl</span>
    </div>
  </div>
</header>