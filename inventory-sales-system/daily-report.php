<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
  header("Location: index.php");
  exit();
}

$today = date("Y-m-d");

// Total revenue and number of sales
$res1 = $conn->query("SELECT COUNT(*) AS txn_count, SUM(total_amount) AS revenue FROM sales WHERE DATE(date_time) = '$today'");
$data1 = $res1->fetch_assoc();
$txnCount = $data1['txn_count'] ?? 0;
$revenue = $data1['revenue'] ?? 0;

// Total units sold
$res2 = $conn->query("SELECT SUM(sale_items.quantity) AS total_units 
                      FROM sale_items 
                      JOIN sales ON sale_items.sale_id = sales.id 
                      WHERE DATE(sales.date_time) = '$today'");
$units = $res2->fetch_assoc()['total_units'] ?? 0;

// Optional: Top-selling products
$res3 = $conn->query("SELECT p.name, SUM(si.quantity) AS qty 
                      FROM sale_items si 
                      JOIN products p ON si.product_id = p.id 
                      JOIN sales s ON si.sale_id = s.id 
                      WHERE DATE(s.date_time) = '$today' 
                      GROUP BY si.product_id 
                      ORDER BY qty DESC 
                      LIMIT 5");

$topSelling = [];
while ($row = $res3->fetch_assoc()) {
  $topSelling[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily Report</title>
  <link rel="stylesheet" href="css/style.css">
  <script defer src="js/sidebar.js"></script>
  <style>
    .report-box {
      max-width: 800px;
      margin: 40px auto;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
    }

    .summary {
      margin-bottom: 2rem;
    }

    .summary div {
      margin: 8px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
    }

    .print-btn {
      margin-top: 2rem;
      background: black;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 6px;
      display: inline-block;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="report-box">
    <h2>📊 Daily Sales Report</h2>
    <p><strong>Date:</strong> <?php echo $today; ?></p>

    <div class="summary">
      <div><strong>Total Revenue:</strong> ₦<?php echo number_format($revenue, 2); ?></div>
      <div><strong>Total Units Sold:</strong> <?php echo $units; ?></div>
      <div><strong>Transactions:</strong> <?php echo $txnCount; ?></div>
    </div>

    <h4>🏆 Top-Selling Products</h4>
    <?php if (empty($topSelling)): ?>
      <p>No sales yet today.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Units Sold</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topSelling as $item): ?>
            <tr>
              <td><?php echo $item['name']; ?></td>
              <td><?php echo $item['qty']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <a href="#" class="print-btn" onclick="window.print()">🖨️ Print Report</a>
    <br><br>
    <a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
