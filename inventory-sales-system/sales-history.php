<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];

// Fetch sales
if ($role === 'Admin') {
  $query = "SELECT sales.*, users.username FROM sales 
            JOIN users ON sales.user_id = users.id 
            ORDER BY sales.date_time DESC";
  $stmt = $conn->prepare($query);
} else {
  $query = "SELECT sales.*, users.username FROM sales 
            JOIN users ON sales.user_id = users.id 
            WHERE sales.user_id = ? ORDER BY sales.date_time DESC";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$sales = [];

while ($row = $result->fetch_assoc()) {
  $sales[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales History</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .history-box {
      max-width: 1000px;
      margin: 40px auto;
      background: white;
      padding: 2rem;
      box-shadow: 0 0 10px #ccc;
      border-radius: 8px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
    }

    th {
      background: #f9f9f9;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="history-box">
    <h2>🧾 Sales History</h2>

    <?php if (empty($sales)): ?>
      <p>No sales found.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Sale ID</th>
            <th>Total (₦)</th>
            <th>Date/Time</th>
            <?php if ($role === 'Admin'): ?>
              <th>Sold By</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sales as $s): ?>
            <tr>
              <td><?php echo 'TXN' . str_pad($s['id'], 5, '0', STR_PAD_LEFT); ?></td>
              <td><?php echo number_format($s['total_amount'], 2); ?></td>
              <td><?php echo $s['date_time']; ?></td>
              <?php if ($role === 'Admin'): ?>
                <td><?php echo $s['username']; ?></td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <br><a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
