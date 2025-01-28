<?php 

if (!($_SESSION['logged_in'])) {
  // Redirect to login page if not logged in
  header("Location: /private/views/pages/login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" type="text/css" href="/../../../public/css/globals.css">
  <title>Bak | Horlogic</title>
  <style>
    html, body {
      margin: 0; 
      padding: 0; 
      font-family: Arial, sans-serif;
      background-color: #f9f9f9; 
    }

    .page-container {
      display: flex;
      /* fill the screen or grow as needed */
      min-height: 100vh; 
    }

    /* Left sidebar */
    .sidebar {
      width: 300px;
      background-color: #f5f5f5;
      padding: 1rem;
      box-sizing: border-box;
      border-right: 1px solid #ddd;
    }
    .sidebar h3 {
      margin-top: 0; 
      margin-bottom: 1rem; 
      font-size: 1.1rem;
    }
    .sidebar .add-button {
      display: inline-block;
      background-color: #28b97b; 
      color: #fff; 
      border: none; 
      padding: 0.5rem 1rem; 
      border-radius: 4px; 
      margin-bottom: 1rem;
      cursor: pointer;
    }
    /* Each row in the left list */
    .sidebar .repair-bin {
      background: #fff; 
      border-radius: 4px; 
      margin-bottom: 5px; 
      padding: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer; 
    }
    .sidebar .repair-bin:hover {
      background-color: #eee;
    }
    .sidebar .repair-bin span:last-child {
      font-weight: bold;
      color: #666;
    }

    /* Right content area */
    .main-content {
      flex: 1; 
      padding: 1rem; 
      box-sizing: border-box;
    }

    /* Top row: search + select + "x" button + Filter button */
    .filter-row {
      display: flex; 
      align-items: center; 
      gap: 0.5rem; 
      margin-bottom: 1rem;
    }
    .filter-row input[type="text"] {
      padding: 0.5rem; 
      border: 1px solid #ccc; 
      border-radius: 4px; 
      width: 200px;
    }
    .select-wrapper {
      position: relative;
      display: inline-block;
    }
    .select-wrapper select {
      padding: 0.5rem; 
      border: 1px solid #ccc; 
      border-radius: 4px; 
      appearance: none; /* hide default dropdown arrow if desired */
      width: 150px;
    }
    .select-wrapper button.clear-filter {
      position: absolute; 
      top: 50%; 
      right: -1.5rem; 
      transform: translateY(-50%);
      background: none; 
      border: none; 
      font-size: 1rem; 
      cursor: pointer;
    }
    .filter-row button.filter-btn {
      background-color: #153c63; 
      color: #fff; 
      border: none; 
      padding: 0.5rem 1rem; 
      border-radius: 4px;
      cursor: pointer;
    }

    /* Repair list rows on the right */
    .repair-item {
      background: #fff; 
      border-radius: 4px; 
      margin-bottom: 5px; 
      padding: 0.5rem; 
      display: flex; 
      justify-content: space-between; 
      align-items: center;
      border-left: 4px solid #ffad0a; /* example orange left border */
    }
    .repair-item:hover {
      background-color: #fafafa;
    }
    .repair-item .info {
      font-size: 0.9rem;
      color: #333;
    }
    .repair-item .info .repair-number {
      font-weight: bold;
      margin-bottom: 2px;
    }
    .repair-item .status-badge {
      padding: 0.25rem 0.5rem; 
      border-radius: 4px; 
      font-size: 0.85rem; 
      color: #fff;
    }
    .status-new {
      background-color: #fdda73; 
      color: #333; 
      font-weight: bold;
    }
    .status-toestemming {
      background-color: #000; 
      color: #fff;
    }
    /* etc. for other statuses if needed */
  </style>
</head>
<body>

<div class="page-container">
  <!-- Left Sidebar -->
  <aside class="sidebar">
    <!-- Remove the <h3> and replace with a button -->
    <button class="all-repairs-btn">Alle reparaties</button>

    <!-- Example bins: 1. A, 2. B, etc. with counts -->
    <div class="repair-bin">
      <span>1. A</span><span>11</span>
    </div>
    <div class="repair-bin">
      <span>2. B</span><span>11</span>
    </div>
    <div class="repair-bin">
      <span>3. C</span><span>11</span>
    </div>
    <div class="repair-bin">
      <span>4. D</span><span>11</span>
    </div>
    <div class="repair-bin">
      <span>5. E</span><span>10</span>
    </div>
    <div class="repair-bin">
      <span>6. F</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>7. G</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>8. H</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>9. I</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>10. J</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>11. K</span><span>2</span>
    </div>
    <div class="repair-bin">
      <span>12. L</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>13. M</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>14. N</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>15. Y</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>16. Diversen te versturen etc.</span><span>0</span>
    </div>
    <div class="repair-bin">
      <span>17. Export</span><span>25</span>
    </div>
  </aside>

  <!-- Right Main Content -->
  <main class="main-content">
    <!-- Top filter row -->
    <div class="filter-row">
    <button 
        style="
        background-color: #28b97b; 
        color: #fff; 
        border: none; 
        padding: 0.5rem 1rem; 
        border-radius: 4px;
        cursor: pointer;
        "
    >
        <i class="fa fa-check-circle"></i> Toevoegen
    </button>
      <input type="text" placeholder="Zoek naar.." />

      <div class="select-wrapper">
        <select>
          <option>Reparatie klaar</option>
          <option>Nieuw</option>
          <option>Toestemming</option>
          <!-- etc... -->
        </select>
        <button class="clear-filter">x</button>
      </div>

      <button class="filter-btn">Filter</button>
    </div>

    <!-- Example list of repairs on the right -->
    <div class="repair-item">
      <div class="info">
        <div class="repair-number">209425</div>
        <div>24-01-2025 10:36</div>
        <div>Maurice Lacroix watches - 89582 | Serie: 6163</div>
        <div>Horlogemaker: Sten Weenk</div>
      </div>
      <div class="status-badge status-toestemming">Toestemming</div>
    </div>

    <div class="repair-item">
      <div class="info">
        <div class="repair-number">209424</div>
        <div>24-01-2025 10:34</div>
        <div>Danish Design watches - DANISH DESIGN WATCH IV629995 STAINLESS STEEL</div>
      </div>
      <div class="status-badge status-new">Nieuw</div>
    </div>

    <div class="repair-item">
      <div class="info">
        <div class="repair-number">209423</div>
        <div>24-01-2025 10:32</div>
        <div>Jacob Jensen watches - JACOB JENSEN SAPPHIRE -CLASSIC- 527</div>
      </div>
      <div class="status-badge status-new">Nieuw</div>
    </div>

    <!-- ... more rows ... -->
  </main>
</div>

</body>
</html>