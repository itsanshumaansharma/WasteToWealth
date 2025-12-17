<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT name, password FROM users WHERE username = ? AND role = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($name, $db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $_SESSION['user'] = $username;
            $_SESSION['name'] = $name; // 👈 Name store karna session mein
            header("Location: user_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.location.href='user_login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='user_login.html';</script>";
    }
}
?>
