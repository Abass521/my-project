<?php
$host = "localhost";
$db = "inventory_system";
$user = "root";
$pass = ""; // XAMPP default has no password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
?>
