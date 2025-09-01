<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
  header("Location: index.php");
  exit();
}

$isEdit = false;
$product = [
  'name' => '',
  'category' => '',
  'price' => '',
  'quantity' => '',
  'threshold' => '',
  'supplier' => ''
];

// Handle edit mode
if (isset($_GET['edit'])) {
  $isEdit = true;
  $id = intval($_GET['edit']);
  $res = $conn->query("SELECT * FROM products WHERE id = $id LIMIT 1");
  if ($res && $res->num_rows === 1) {
    $product = $res->fetch_assoc();
  } else {
    die("Product not found.");
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $category = trim($_POST['category']);
  $price = floatval($_POST['price']);
  $quantity = intval($_POST['quantity']);
  $threshold = intval($_POST['threshold']);
  $supplier = trim($_POST['supplier']);

  if ($isEdit) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, quantity=?, threshold=?, supplier=? WHERE id=?");
    $stmt->bind_param("ssdiisi", $name, $category, $price, $quantity, $threshold, $supplier, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity, threshold, supplier) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $name, $category, $price, $quantity, $threshold, $supplier);
  }

  if ($stmt->execute()) {
    header("Location: inventory.php");
    exit();
  } else {
    die("Failed to save product: " . $conn->error);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Product</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .form-box {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 2rem;
      box-shadow: 0 0 10px #ccc;
      border-radius: 8px;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
    }

    label {
      font-weight: bold;
    }

    button {
      padding: 10px 20px;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="form-box">
    <h2><?php echo $isEdit ? '✏️ Edit' : '➕ Add'; ?> Product</h2>

    <form method="POST">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
      <?php endif; ?>

      <label>Product Name:</label>
      <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">

      <label>Category:</label>
      <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>">

      <label>Price (₦):</label>
      <input type="number" name="price" step="0.01" required value="<?php echo $product['price']; ?>">

      <label>Quantity:</label>
      <input type="number" name="quantity" required value="<?php echo $product['quantity']; ?>">

      <label>Threshold (Low stock level):</label>
      <input type="number" name="threshold" required value="<?php echo $product['threshold']; ?>">

      <label>Supplier:</label>
      <input type="text" name="supplier" value="<?php echo htmlspecialchars($product['supplier']); ?>">

      <br>
      <button type="submit"><?php echo $isEdit ? 'Update Product' : 'Add Product'; ?></button>
    </form>

    <br><a href="inventory.php">⬅ Back to Inventory</a>
  </div>
</body>
</html>
