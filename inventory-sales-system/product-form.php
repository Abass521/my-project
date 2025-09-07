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
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .form-box {
    max-width: 100%;
    margin-left: 14.9rem;
    background: #fff;
    padding: 2rem 2.5rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    border-radius: 12px;
  }

  h2 {
    margin-bottom: 1.5rem;
    font-weight: 600;
    text-align: center;
    color: #222;
  }

  form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  label {
    font-weight: 600;
    margin-bottom: 0.3rem;
    color: #444;
    display: block;
  }

  input, select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: border 0.2s, box-shadow 0.2s;
  }

  input:focus, select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
    outline: none;
  }

  button {
    padding: 12px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  button:hover {
    background: #0056b3;
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
