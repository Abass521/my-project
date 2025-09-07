<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user  = $_SESSION['user'] ?? null;
$role  = $user['role'] ?? 'Guest';
$name  = $user['username'] ?? 'Guest';
$current = basename($_SERVER['PHP_SELF']);
function active($file, $current) { return $file === $current ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    .sidebar {
  width: 240px;
  background: #1e293b; /* dark navy */
  color: #fff;
  min-height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h2 a {
  color: #fff;
  text-decoration: none;
}

.userbox {
  margin-top: 10px;
  font-size: 0.9rem;
  color: #cbd5e1;
}

.userbox .logout {
  display: block;
  margin-top: 20px;
  font-size: 0.85rem;
  color: #f87171;
  text-decoration: none;
}

.userbox .logout:hover {
  text-decoration: underline;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-nav a {
  display: block;
  padding: 12px 20px;
  color: #e2e8f0;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}

.sidebar-nav a:hover {
  background: #334155;
  color: #fff;
}

.sidebar-nav a.active {
  background: #0ea5e9;
  color: #fff;
  font-weight: bold;
}

  </style>
</head>
<body>
  <aside class="sidebar">
  <div class="sidebar-header">
    <h2><a href="dashboard.php">InventorySystem</a></h2>
    <div class="userbox">
      <span><?= htmlspecialchars($name) ?> (<?= $role ?>)</span>
      <a class="logout" href="logout.php">Logout</a>
    </div>
  </div>
  <nav class="sidebar-nav">
    <ul>
      <li><a class="<?=active('dashboard.php',$current)?>" href="dashboard.php">📊 Dashboard</a></li>
      <li><a class="<?=active('sales.php',$current)?>" href="sales.php">🛒 POS</a></li>
      <li><a class="<?=active('sales-history.php',$current)?>" href="sales-history.php">📑 Sales History</a></li>

      <?php if ($role === 'Admin'): ?>
        <li><a class="<?=active('product-form.php',$current)?>" href="product-form.php">➕ Add Product</a></li>
        <li><a class="<?=active('inventory.php',$current)?>" href="inventory.php">📦 Inventory</a></li>
        <li><a class="<?=active('daily-report.php',$current)?>" href="daily-report.php">📆 Daily Report</a></li>
        <li><a class="<?=active('users.php',$current)?>" href="users.php">👤 Users</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</aside>

</body>
</html>
