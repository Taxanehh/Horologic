<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../private/views/pages/db.php';
$conn = getDbConnection();

// Fetch current user's data
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$allowedUserIds = [1, 2];
$notifications = [];
if (isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $allowedUserIds)) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic - Watch Repair</title>
  <!-- Example: link to an external CSS file for styling -->
  <link rel="stylesheet" href="/css/globals.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
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

    .logo2 {
      margin-left: 20px;
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

    /* Notification styles */
    .notifications {
      position: relative;
      margin-right: 20px;
      display: flex;
      align-items: center;
    }
    .notifications .fa-bell {
      font-size: 1.5rem;
      cursor: pointer;
      color: #333;
    }
    /* Clear notifications button styling */
    #clearNotificationsBtn {
      border: none;
      background: none;
      cursor: pointer;
      font-size: 1rem;
      color: #333;
      margin-left: 5px;
    }
    .notification-list {
      position: absolute;
      right: 0;
      top: 100%;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      width: 300px;
      max-height: 400px;
      overflow-y: auto;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
      display: none;
      z-index: 1000;
    }
    .notification-list.visible {
      display: block;
    }
    .notification-item {
      padding: 10px;
      border-bottom: 1px solid #eee;
      font-size: 0.9rem;
    }
    .notification-item:last-child {
      border-bottom: none;
    }
    .notification-item time {
      display: block;
      font-size: 0.8rem;
      color: #666;
    }
  </style>
</head>
<body>

<header>
  <!-- Left: Logo or Brand -->
  <div class="logo2">
    <!-- If you have a logo, place it here -->
    <img src="/img/horologic.png" alt="Horologic Logo" />
  </div>

  <!-- Center: Navigation links -->
  <nav>
    <ul>
      <li><a href="/"><i class="fa fa-home"></i> Booking</a></li>
      <li><a href="/reparaties"><i class="fa fa-wrench"></i> Quotation</a></li>
      <li><a href="/complete"><i class="fa fa-euro"></i> Invoicing</a></li>
      <li><a href="https://watchbase.com/"><i class="fa fa-link"></i> Watch database</a></li>
    </ul>
  </nav>

  <!-- Right: User Info -->
  <div class="user-info" id="userInfo">
    <div class="notifications" id="notificationBellContainer">
        <i class="fa fa-bell" id="notificationBell"></i>
        <button id="clearNotificationsBtn" style="border: none; background: none; cursor: pointer; font-size: 1rem; color: #333; margin-left: 5px;" title="Clear Notifications">
          <i class="fa fa-trash"></i>
        </button>
        <div class="notification-list" id="notificationList">
          <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notif): ?>
              <div class="notification-item">
                <?= htmlspecialchars($notif['message']) ?>
                <time><?= date('M j, Y H:i', strtotime($notif['created_at'])) ?></time>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="notification-item">
              No new notifications.
            </div>
          <?php endif; ?>
        </div>
    </div>
    <div class="user-text">
      <span class="user-name">
      <?php 
        // Display full name if available, otherwise fall back to email
        echo !empty($user['full_name']) 
          ? htmlspecialchars($user['full_name']) 
          : htmlspecialchars($user['email']);
      ?>
      </span>
      <span class="user-email">
      <?php 
        // Always display email
        echo htmlspecialchars($user['email'] ?? 'Not logged in');
      ?>
      </span>
    </div>
    <div class="dropdown hidden" id="userDropdown">
      <a href="/logout">Uitloggen</a>
    </div>
  </div>
</header>

<script>
  // Toggle the dropdown's visibility when user-info is clicked
  const userInfo = document.getElementById('userInfo');
  const userDropdown = document.getElementById('userDropdown');

  userInfo.addEventListener('click', () => {
    userDropdown.classList.toggle('hidden');
  });
  // Toggle the notification list when clicking the bell
  const notificationBell = document.getElementById('notificationBell');
  const notificationList = document.getElementById('notificationList');
  notificationBell.addEventListener('click', (e) => {
    // Prevent the event from bubbling up
    e.stopPropagation();
    notificationList.classList.toggle('visible');
  });

  // Hide the notification list if clicking outside
  document.addEventListener('click', function(e) {
    if (!document.getElementById('notificationBellContainer').contains(e.target)) {
      notificationList.classList.remove('visible');
    }
  });
  // Handle clear notifications button with confirmation popup
  const clearNotificationsBtn = document.getElementById('clearNotificationsBtn');
  if (clearNotificationsBtn) {
      clearNotificationsBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          if (confirm("Are you sure you want to delete all notifications?")) {
              window.location.href = '/clear_notifications';
          }
      });
  }
</script>