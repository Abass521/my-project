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
      body {
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .user-box {
    max-width: 100%;
    margin-left: 14.9rem;
    background: #ffffff;
    padding: 2rem 2.5rem;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  }

  h2, h3 {
    margin: 0 0 1rem;
    color: #222;
    font-weight: 600;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
    border-radius: 8px;
    overflow: hidden;
  }

  thead {
    background: #007bff;
    color: #fff;
    text-align: left;
  }

  th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #e6e6e6;
  }

  tr:nth-child(even) {
    background: #f9fbfd;
  }

  tr:hover {
    background: #f1f6ff;
  }

  .form-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 2rem;
  }

  .form-inline input,
  .form-inline select {
    flex: 1;
    min-width: 150px;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    transition: border-color 0.2s;
  }

  .form-inline input:focus,
  .form-inline select:focus {
    outline: none;
    border-color: #007bff;
  }

  button {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
  }

  button:hover {
    background: #0056b3;
  }

  a.button {
    display: inline-block;
    padding: 6px 14px;
    background: #007bff;
    color: #fff;
    font-size: 0.9rem;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.2s;
  }

  a.button:hover {
    background: #0056b3;
  }

  a.button.danger {
    background: #dc3545;
  }

  a.button.danger:hover {
    background: #a71d2a;
  }

  hr {
    margin: 2rem 0;
    border: none;
    border-top: 1px solid #e6e6e6;
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
