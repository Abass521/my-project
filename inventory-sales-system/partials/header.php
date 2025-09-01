<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user  = $_SESSION['user'] ?? null;
$role  = $user['role'] ?? 'Guest';
$name  = $user['username'] ?? 'Guest';
$current = basename($_SERVER['PHP_SELF']);
function active($file, $current) { return $file === $current ? 'active' : ''; }
?>
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
