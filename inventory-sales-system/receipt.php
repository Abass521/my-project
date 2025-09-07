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
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .receipt {
    max-width: 100%;
    margin-left: 14.9rem;
    background: #ffffff;
    padding: 2rem 2.5rem;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }

  .receipt h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    font-weight: 600;
    color: #222;
  }

  .receipt p {
    margin: 6px 0;
    font-size: 0.95rem;
  }

  .receipt strong {
    color: #111;
  }

  .receipt-items {
    margin-top: 1.5rem;
    border-top: 2px dashed #ccc;
    padding-top: 1rem;
  }

  .receipt-items div {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 0.95rem;
  }

  .receipt-items div:nth-child(even) {
    background: #f9fbfd;
    border-radius: 6px;
    padding: 6px 8px;
  }

  .total-line {
    margin-top: 1.5rem;
    font-size: 1.05rem;
    font-weight: 600;
    text-align: right;
  }

  .print-btn {
    margin-top: 2rem;
    display: block;
    background: #007bff;
    color: white;
    padding: 12px 20px;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-size: 1rem;
    transition: background 0.2s;
  }

  .print-btn:hover {
    background: #0056b3;
  }

  a.back-link {
    display: inline-block;
    margin-top: 1.5rem;
    color: #007bff;
    text-decoration: none;
    transition: color 0.2s;
  }

  a.back-link:hover {
    color: #0056b3;
    text-decoration: underline;
  }

  /* Print-friendly */
  @media print {
    body {
      background: #fff;
    }
    .receipt {
      box-shadow: none;
      border-radius: 0;
      margin: 0;
      padding: 0;
    }
    .print-btn, .back-link, header {
      display: none;
    }
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
