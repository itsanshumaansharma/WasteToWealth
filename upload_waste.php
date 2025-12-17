<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: user_login.html");
  exit();
}

include 'db_config.php';

// Check if form submitted and file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['wasteFile'])) {
    $username = $_SESSION['user'];
    $wasteType = $_POST['wasteType'];
    $location = $_POST['location'];
    $description = $_POST['description'];

    $file = $_FILES['wasteFile'];
    $fileName = basename($file['name']);
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // ✅ Validate file size (max 10MB)
    if ($fileSize > 10 * 1024 * 1024) {
        die("❌ File size should not exceed 10MB.");
    }

    // ✅ Allow only PDF, PNG, JPG, JPEG
    $allowedTypes = ['pdf', 'png', 'jpg', 'jpeg'];
    if (!in_array($fileType, $allowedTypes)) {
        die("❌ Only PDF, PNG, JPG, and JPEG files are allowed.");
    }

    // ✅ Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = time() . '_' . $fileName;
    $destination = $uploadDir . $newFileName;

    // ✅ Move uploaded file to destination
    if (move_uploaded_file($fileTmpPath, $destination)) {
        // ✅ Insert record into database
        $sql = "INSERT INTO waste_uploads (username, waste_type, location, description, file_path) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $wasteType, $location, $description, $destination);

        if ($stmt->execute()) {
            // echo "✅ Waste uploaded successfully! <a href='user_dashboard.php'>Go back</a>";
            echo "<script>alert('✅ Waste uploaded successfully!'); window.location.href='user_dashboard.php';</script>";

        } else {
            echo "❌ Failed to save to database: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "❌ Failed to move uploaded file.";
    }
} else {
    echo "⚠️ No file uploaded!";
}
?>
