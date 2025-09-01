<?php
session_start();
require_once "php/db.php";

// Restrict to Admins only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
  header("Location: index.php");
  exit();
}

// Handle deletion
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM users WHERE id = $id AND id != {$_SESSION['user']['id']}"); // Prevent deleting yourself
  header("Location: users.php");
  exit();
}

// Fetch users
$res = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = [];
while ($row = $res->fetch_assoc()) {
  $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .user-box {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
    }

    .form-inline {
      display: flex;
      gap: 10px;
      margin-top: 2rem;
    }

    .form-inline input, .form-inline select {
      padding: 10px;
    }

    button {
      padding: 10px 20px;
    }

    a.button {
      padding: 5px 10px;
      background: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }

    .danger {
      background: crimson;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>
  <div class="user-box">
    <h2>👥 Manage Users</h2>

    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td>
              <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                <a href="users.php?delete=<?php echo $u['id']; ?>" class="button danger" onclick="return confirm('Delete this user?')">Delete</a>
              <?php else: ?>
                (You)
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <hr>

    <h3>➕ Add New User</h3>
    <form method="POST" action="php/create-user.php" class="form-inline">
      <input type="text" name="username" placeholder="Username" required>
      <input type="text" name="password" placeholder="Password" required>
      <select name="role">
        <option value="User">User</option>
        <option value="Admin">Admin</option>
      </select>
      <button type="submit">Add</button>
    </form>

    <br><a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
