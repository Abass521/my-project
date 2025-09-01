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
    .dashboard {
      max-width: 900px;
      margin: 40px auto;
      padding: 2rem;
      background: white;
      box-shadow: 0 0 10px #aaa;
      border-radius: 10px;
    }

    .stats {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stat-box {
      flex: 1;
      min-width: 180px;
      background: #f9f9f9;
      padding: 20px;
      border-left: 5px solid #007bff;
      border-radius: 6px;
    }

    .logout-btn {
      float: right;
      background: red;
      color: white;
      padding: 5px 10px;
      border: none;
      cursor: pointer;
    }

    h1 {
      margin-bottom: 1rem;
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
