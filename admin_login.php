<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $adminname = $_POST['adminname'];
  $password = $_POST['password'];

  // Simple hardcoded login
  if ($adminname === "admin" && $password === "admin123") {
    $_SESSION['admin'] = $adminname;
    header("Location: admin_dashboard.php");
    exit();
  } else {
    echo "<script>alert('Invalid admin credentials'); window.location.href='admin_login.html';</script>";
  }
}
?>
