<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.html");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $uploadId = $_POST['upload_id'] ?? null;
  $status = $_POST['status'] ?? '';
  $type = $_POST['type'] ?? null;
  $weight = $_POST['weight'] ?? null;

  if ($uploadId && $status) {
    $stmt = $conn->prepare("UPDATE waste_uploads SET verification_status = ?, waste_type = ?, estimated_weight = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $status, $type, $weight, $uploadId);

    if ($stmt->execute()) {
      echo "<script>
              alert('✅ Update successful!');
              window.location.href='admin_dashboard.php';
            </script>";
    } else {
      echo "<script>
              alert('❌ Update failed: " . $stmt->error . "');
              window.location.href='admin_dashboard.php';
            </script>";
    }

    $stmt->close();
  } else {
    echo "<script>
            alert('❗ Invalid form submission.');
            window.location.href='admin_panel.php';
          </script>";
  }
} else {
  echo "<script>
          alert('❗ Unauthorized request.');
          window.location.href='admin_panel.php';
        </script>";
}
?>
