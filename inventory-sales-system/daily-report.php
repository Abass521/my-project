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
  <script defer src="js/sidebar.js"></script>
  <style>
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .report-box {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
  }

  h2 {
    margin-top: 0;
    font-size: 1.8rem;
    font-weight: 600;
    color: #222;
  }

  .summary {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 2rem 0;
  }

  .summary div {
    flex: 1;
    min-width: 200px;
    background: #f9fafc;
    padding: 15px;
    border-radius: 8px;
    border-left: 5px solid #007bff;
    box-shadow: inset 0 0 0 2px #eee;
    font-weight: 500;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: #fefefe;
    border-radius: 8px;
    overflow: hidden;
  }

  th {
    background: #007bff;
    color: #fff;
    padding: 12px;
    text-align: left;
  }

  td {
    padding: 10px;
    border-bottom: 1px solid #eee;
  }

  tr:hover td {
    background: #f8faff;
  }

  .print-btn {
    margin-top: 2rem;
    background: #007bff;
    color: white;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 6px;
    display: inline-block;
    font-weight: 600;
    transition: background 0.2s;
  }

  .print-btn:hover {
    background: #0056b3;
  }

  a.back-link {
    display: inline-block;
    margin-top: 1rem;
    color: #555;
    text-decoration: none;
  }

  a.back-link:hover {
    text-decoration: underline;
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

  <canvas id="topChart" height="150"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('topChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode(array_column($topSelling, 'name')); ?>,
      datasets: [{
        label: 'Units Sold',
        data: <?php echo json_encode(array_column($topSelling, 'qty')); ?>,
        backgroundColor: '#007bff'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>

</body>
</html>
