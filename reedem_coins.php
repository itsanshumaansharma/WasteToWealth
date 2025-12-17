<?php
session_start();
include 'db_config.php'; // Make sure this file has your DB connection

if (!isset($_SESSION['user'])) {
  echo "Login required.";
  exit();
}

$username = $_SESSION['user'];
$upi_id = $_POST['upi_id'] ?? '';

// Basic UPI ID validation
if (empty($upi_id) || !preg_match("/^[\w.-]+@[\w]+$/", $upi_id)) {
  echo "❌ Invalid UPI ID.";
  exit();
}

// Step 1: Calculate verified entries
$sql = "SELECT COUNT(*) as verified_count FROM waste_uploads WHERE username = ? AND verification_status = 'Verified'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$verifiedCount = $row['verified_count'];
$coins = $verifiedCount * 10;

// Step 2: Check coin threshold
if ($coins < 50) {
  echo "❌ Minimum 50 coins required to redeem.";
  exit();
}

// Step 3: Store redeem request
$redeemSql = "INSERT INTO coin_redeem (username, coins_redeemed, upi_id, redeem_date) VALUES (?, ?, ?, NOW())";
$redeemStmt = $conn->prepare($redeemSql);
$redeemStmt->bind_param("sis", $username, $coins, $upi_id);
$redeemStmt->execute();

// Step 4: Update redeemed waste_uploads
$resetSql = "UPDATE waste_uploads SET verification_status='Redeemed' WHERE username = ? AND verification_status = 'Verified'";
$resetStmt = $conn->prepare($resetSql);
$resetStmt->bind_param("s", $username);
$resetStmt->execute();

// echo "✅ Coins redeemed successfully! Payment will be processed to UPI: <b>$upi_id</b>";
echo "<script>alert('✅ Coins Redeemed!'); window.location.href='user_dashboard.php';</script>";

?>
