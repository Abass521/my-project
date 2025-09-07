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
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .history-box {
    max-width: 100%;
    margin-left: 14.9rem;
    background: #ffffff;
    padding: 2rem 2.5rem;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  }

  h2 {
    margin: 0 0 1.5rem;
    color: #222;
    font-weight: 600;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
  }

  thead {
    background: #007bff;
    color: #fff;
  }

  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e6e6e6;
  }

  tr:nth-child(even) {
    background: #f9fbfd;
  }

  tr:hover {
    background: #f1f6ff;
  }

  td {
    font-size: 0.95rem;
  }

  .no-sales {
    padding: 1rem;
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    margin-top: 1rem;
    text-align: center;
  }

  a {
    display: inline-block;
    margin-top: 1.5rem;
    color: #007bff;
    text-decoration: none;
    transition: color 0.2s;
  }

  a:hover {
    color: #0056b3;
    text-decoration: underline;
  }

  /* Responsive Table */
  @media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }

    thead {
      display: none;
    }

    tr {
      margin-bottom: 1rem;
      background: #fff;
      border: 1px solid #eee;
      border-radius: 6px;
      padding: 10px;
    }

    td {
      border: none;
      display: flex;
      justify-content: space-between;
      padding: 8px 10px;
      font-size: 0.9rem;
    }

    td::before {
      content: attr(data-label);
      font-weight: bold;
      color: #444;
    }
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
