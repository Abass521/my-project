<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit();
}

$user = $_SESSION['user'];

// Fetch data (simulated — we’ll connect real counts later)
$totalProducts = 0;
$totalSalesToday = 0;
$itemsSoldToday = 0;
$lowStock = 0;

// Get today's date
$today = date("Y-m-d");

// Total Products
$res = $conn->query("SELECT COUNT(*) AS total FROM products");
if ($res) $totalProducts = $res->fetch_assoc()['total'];

// Total Sales & Items Sold (for Admin)
if ($user['role'] === 'Admin') {
  $sales = $conn->query("SELECT * FROM sales WHERE DATE(date_time) = '$today'");
  while ($row = $sales->fetch_assoc()) {
    $totalSalesToday += $row['total_amount'];

    // Count items
    $saleId = $row['id'];
    $items = $conn->query("SELECT SUM(quantity) AS qty FROM sale_items WHERE sale_id = $saleId");
    if ($items) {
      $itemsSoldToday += $items->fetch_assoc()['qty'];
    }
  }

  // Low stock alert
  $low = $conn->query("SELECT COUNT(*) AS low_count FROM products WHERE quantity <= threshold");
  if ($low) $lowStock = $low->fetch_assoc()['low_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Inventory System</title>
  <link rel="stylesheet" href="css/style.css">
  <script defer src="js/nav.js"></script>
  <script defer src="js/sidebar.js"></script>
  <style>
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .dashboard {
    max-width: 1000px;
    margin: 40px auto;
    padding: 2.5rem;
    background: #fff;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    border-radius: 12px;
  }

  h1 {
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
    font-weight: 600;
    color: #222;
  }

  p {
    margin: 0 0 1.5rem;
    color: #666;
  }

  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
  }

  .stat-box {
    background: #f9fafc;
    padding: 20px;
    border-radius: 10px;
    position: relative;
    box-shadow: inset 0 0 0 3px #f0f0f0;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .stat-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
  }

  .stat-box h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #444;
  }

  .stat-box p {
    font-size: 1.4rem;
    font-weight: bold;
    margin-top: 0.5rem;
    color: #007bff;
  }

  /* Color accents for quick scanning */
  .stat-box:nth-child(1) { border-left: 6px solid #007bff; }
  .stat-box:nth-child(2) { border-left: 6px solid #28a745; }
  .stat-box:nth-child(3) { border-left: 6px solid #ffc107; }
  .stat-box:nth-child(4) { border-left: 6px solid #dc3545; }

  .logout-btn {
    float: right;
    background: #dc3545;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  .logout-btn:hover {
    background: #b52a37;
  }

  </style>
</head>
<body>
  <?php include 'partials/header.php'; ?>
  <!-- Your existing dashboard HTML continues below -->
  <div class="dashboard">
    <form method="post" style="text-align: right;">
    </form>

    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    <p>Role: <strong><?php echo $user['role']; ?></strong></p>

    <div class="stats">
      <?php if ($user['role'] === 'Admin'): ?>
        <div class="stat-box">
          <h3>Total Products</h3>
          <p><?php echo $totalProducts; ?></p>
        </div>
        <div class="stat-box">
          <h3>Today’s Sales</h3>
          <p>₦<?php echo $totalSalesToday; ?></p>
        </div>
        <div class="stat-box">
          <h3>Items Sold Today</h3>
          <p><?php echo $itemsSoldToday; ?></p>
        </div>
        <div class="stat-box">
          <h3>Low Stock Products</h3>
          <p><?php echo $lowStock; ?></p>
        </div>
      <?php else: ?>
        <div class="stat-box">
          <h3>Today's Sales</h3>
          <p>(We’ll personalize this in sales-history)</p>
        </div>
        <div class="stat-box">
          <h3>Profile Summary</h3>
          <p>You're logged in as <strong><?php echo $user['username']; ?></strong></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

<?php
// Logout handler
if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: index.php");
  exit();
}
?>
