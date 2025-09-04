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
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
  }

  .products, .cart {
    flex: 1;
    background: #ffffff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    min-width: 300px;
  }

  h3 {
    margin: 0 0 1rem;
    font-weight: 600;
    color: #222;
  }

  /* Products grid */
  .products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
  }

  .product-card {
    border: 1px solid #e0e0e0;
    padding: 12px;
    border-radius: 10px;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: box-shadow 0.2s, transform 0.2s;
  }

  .product-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  .product-card strong {
    font-size: 15px;
    color: #333;
    display: block;
    margin-bottom: 5px;
  }

  .product-card input[type="number"] {
    margin-top: 6px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 70px;
  }

  .product-card button {
    margin-top: 8px;
    background: #007bff;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
  }

  .product-card button:hover {
    background: #0056b3;
  }

  /* Cart styles */
  .cart-item {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed #ddd;
    padding: 8px 0;
    font-size: 0.95rem;
  }

  .cart-summary {
    font-weight: 600;
    margin-top: 1rem;
    font-size: 1rem;
    color: #222;
  }

  .checkout-btn {
    background: #28a745;
    color: white;
    padding: 12px 20px;
    margin-top: 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    width: 100%;
    transition: background 0.2s;
  }

  .checkout-btn:hover {
    background: #1e7e34;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .container {
      flex-direction: column;
    }

    .products, .cart {
      width: 100%;
    }
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
