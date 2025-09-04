<?php
session_start();
require_once "php/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Compare plain password (we'll add hashing later)
    if ($password === $user['password']) {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
      ];

      // Redirect based on role
      if ($user['role'] === 'Admin') {
        header("Location: dashboard.php");
      } else {
        header("Location: sales.php");
      }
      exit();
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "User not found.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Inventory System</title>
  <style>
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #007bff, #6610f2);
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-box {
    background: #fff;
    padding: 2.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 400px;
    text-align: center;
  }

  .login-box h2 {
    margin-bottom: 1.5rem;
    font-weight: 600;
    color: #333;
  }

  .login-box form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .login-box input {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    transition: border 0.2s, box-shadow 0.2s;
  }

  .login-box input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
    outline: none;
  }

  .login-box button {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  .login-box button:hover {
    background: #0056b3;
  }

  .login-box p {
    margin: 0;
    font-size: 0.9rem;
  }

  .error {
    color: #d93025;
    font-weight: 500;
    margin-bottom: 1rem;
  }

  </style>

</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <?php if ($error): ?>
  <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required><br><br>
      <input type="password" name="password" placeholder="Password" required><br><br>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
