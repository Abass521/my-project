<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit();
}

// Fetch all available products
$res = $conn->query("SELECT * FROM products WHERE quantity > 0");
$products = [];
while ($row = $res->fetch_assoc()) {
  $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>POS - Sales</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
    }

    .container {
      display: flex;
      gap: 20px;
      max-width: 1200px;
      margin: 30px auto;
    }

    .products, .cart {
      flex: 1;
      background: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
    }

    /* GRID for Products */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 15px;
    }

    .product-card {
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 6px;
      background: #fafafa;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .product-card strong {
      font-size: 16px;
      color: #333;
    }

    .product-card input {
      margin-top: 5px;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px dashed #ccc;
      padding: 8px 0;
    }

    .checkout-btn {
      background: green;
      color: white;
      padding: 10px 20px;
      margin-top: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    h3 {
      margin-top: 0;
    }

    .cart-summary {
      font-weight: bold;
      margin-top: 1rem;
    }

    input[type="number"] {
      width: 60px;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="container">
    <!-- Products Section -->
    <div class="products">
      <h3>🛒 Available Products</h3>
      <div class="products-grid">
        <?php foreach ($products as $p): ?>
          <div class="product-card">
            <div>
              <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
              ₦<?php echo $p['price']; ?> | Stock: <?php echo $p['quantity']; ?>
            </div>
            <div>
              <input type="number" min="1" max="<?php echo $p['quantity']; ?>" value="1" id="qty_<?php echo $p['id']; ?>">
              <button onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>, <?php echo $p['quantity']; ?>)">Add</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Cart Section -->
    <div class="cart">
      <h3>🧾 Cart</h3>
      <div id="cartItems"></div>
      <div class="cart-summary">
        Total: ₦<span id="cartTotal">0</span>
      </div>
      <form method="POST" action="receipt.php" onsubmit="return checkoutCart()">
        <input type="hidden" name="cartData" id="cartData">
        <button type="submit" class="checkout-btn">✅ Checkout</button>
      </form>
    </div>
  </div>

  <script>
    let cart = [];

    function addToCart(id, name, price, maxQty) {
      const qty = parseInt(document.getElementById("qty_" + id).value);
      if (qty < 1 || qty > maxQty) {
        alert("Invalid quantity");
        return;
      }

      const existing = cart.find(item => item.id === id);
      if (existing) {
        if (existing.qty + qty > maxQty) {
          alert("Exceeds stock!");
          return;
        }
        existing.qty += qty;
      } else {
        cart.push({ id, name, price, qty });
      }
      renderCart();
    }

    function renderCart() {
      const wrapper = document.getElementById("cartItems");
      wrapper.innerHTML = "";
      let total = 0;

      cart.forEach(item => {
        total += item.price * item.qty;
        wrapper.innerHTML += `
          <div class="cart-item">
            <span>${item.name} x ${item.qty}</span>
            <span>₦${item.price * item.qty}</span>
          </div>
        `;
      });

      document.getElementById("cartTotal").innerText = total.toFixed(2);
    }

    function checkoutCart() {
      if (cart.length === 0) {
        alert("Cart is empty!");
        return false;
      }

      document.getElementById("cartData").value = JSON.stringify(cart);
      return true;
    }
  </script>
</body>
</html>
