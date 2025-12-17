<?php
// user_register.php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name']; // 👈 New field
    $username = $_POST['username'];
    // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password = $_POST['password']; // plain text password (not safe!)

    $role = 'user';

    $sql = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful!');
                window.location.href = 'user_login.html';
              </script>";
    } else {
        echo "<script>
                alert('Error: " . $conn->error . "');
                window.history.back();
              </script>";
    }
}
?>
