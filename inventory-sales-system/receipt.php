<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit();
}

$user = $_SESSION['user'];
$username = $user['username'];
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
  $cart = json_decode($_POST['cartData'], true);

  if (!$cart || !is_array($cart)) {
    die("Invalid cart data.");
  }

  $total = 0;
  foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
  }

  // 1. Insert into sales table
  $stmt = $conn->prepare("INSERT INTO sales (user_id, total_amount) VALUES (?, ?)");
  $stmt->bind_param("id", $user_id, $total);
  $stmt->execute();
  $sale_id = $stmt->insert_id;

  // 2. Insert each item into sale_items table
  $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
  foreach ($cart as $item) {
    $stmt2->bind_param("iiid", $sale_id, $item['id'], $item['qty'], $item['price']);
    $stmt2->execute();

    // 3. Deduct quantity from products
    $conn->query("UPDATE products SET quantity = quantity - {$item['qty']} WHERE id = {$item['id']}");
  }

  // 4. Get final receipt data
  $receipt = [
    'saleId' => 'TXN' . str_pad($sale_id, 5, '0', STR_PAD_LEFT),
    'date' => date("Y-m-d H:i:s"),
    'items' => $cart,
    'total' => $total,
    'soldBy' => $username
  ];

} else {
  die("No data to show.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receipt</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .receipt {
      max-width: 600px;
      margin: 40px auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px #ccc;
      font-family: monospace;
    }

    .receipt h2 {
      text-align: center;
    }

    .receipt-items {
      margin-top: 1rem;
      border-top: 1px dashed #ccc;
      padding-top: 1rem;
    }

    .receipt-items div {
      display: flex;
      justify-content: space-between;
    }

    .print-btn {
      margin-top: 2rem;
      display: block;
      background: black;
      color: white;
      padding: 10px 20px;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="receipt">
    <h2>🧾 Sales Receipt</h2>
    <p><strong>Sale ID:</strong> <?php echo $receipt['saleId']; ?></p>
    <p><strong>Date:</strong> <?php echo $receipt['date']; ?></p>
    <p><strong>Sold By:</strong> <?php echo $receipt['soldBy']; ?></p>

    <div class="receipt-items">
      <?php foreach ($receipt['items'] as $item): ?>
        <div>
          <span><?php echo $item['name']; ?> x <?php echo $item['qty']; ?></span>
          <span>₦<?php echo $item['price'] * $item['qty']; ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <p style="margin-top: 1rem;"><strong>Total:</strong> ₦<?php echo number_format($receipt['total'], 2); ?></p>

    <a href="#" class="print-btn" onclick="window.print()">🖨️ Print Receipt</a>
    <br><br>
    <a href="sales.php">⬅ Back to POS</a>
  </div>
</body>
</html>
