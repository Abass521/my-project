<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
  header("Location: index.php");
  exit();
}

// Handle product deletion
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM products WHERE id = $id");
  header("Location: inventory.php");
  exit();
}

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Inventory</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .inventory-box {
    max-width: 100%;
    margin-left: 14.9rem;
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }

  .header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header-bar h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #222;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 0.95rem;
  }

  th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
  }

  th {
    background: #f8f9fc;
    font-weight: 600;
    color: #444;
  }

  tr:hover {
    background: #f9fbff;
  }

  .low-stock {
    background: #fff5f5;
    color: #d93025;
  }

  a.button {
    background: #007bff;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background 0.2s;
  }

  a.button:hover {
    background: #0056b3;
  }

  a.button.danger {
    background: #dc3545;
  }

  a.button.danger:hover {
    background: #b52a37;
  }

  a.back-link {
    display: inline-block;
    margin-top: 1.5rem;
    color: #007bff;
    text-decoration: none;
  }

  a.back-link:hover {
    text-decoration: underline;
  }

  </style>
</head>
<?php include 'partials/header.php'; ?>
<body>
  <div class="inventory-box">
    <div class="header-bar">
      <h2>📦 Product Inventory</h2>
      <a href="product-form.php" class="button">➕ Add Product</a>
    </div>

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Threshold</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr><td colspan="7">No products found.</td></tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr class="<?php echo $p['quantity'] <= $p['threshold'] ? 'low-stock' : ''; ?>">
              <td><?php echo htmlspecialchars($p['name']); ?></td>
              <td><?php echo htmlspecialchars($p['category']); ?></td>
              <td>₦<?php echo $p['price']; ?></td>
              <td><?php echo $p['quantity']; ?></td>
              <td><?php echo $p['threshold']; ?></td>
              <td><?php echo $p['quantity'] <= $p['threshold'] ? 'Low Stock' : 'In Stock'; ?></td>
              <td>
                <a href="product-form.php?edit=<?php echo $p['id']; ?>" class="button">✏️</a>
                <a href="inventory.php?delete=<?php echo $p['id']; ?>" class="button danger" onclick="return confirm('Delete this product?');">🗑️</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <br><a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
