<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: user_login.html");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style1.css">
  <style>
    .upload-form {
      display: none;
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 10px;
      background: #f5f5f5;
    }
    textarea { width: 100%; height: 100px; }
    .dashboard ul { list-style: none; padding: 0; }
    .dashboard ul li { cursor: pointer; margin-bottom: 10px; }
    #map {
      width: 100%;
      height: 300px;
      margin-top: 10px;
      border: 2px solid #ccc;
      border-radius: 10px;
    }
    .dashboard ul li a {
      text-decoration: none;
      color: #000;
    }
    .dashboard ul li a:hover {
      text-decoration: underline;
    }
    .coin-box {
      background: #e8f4e8;
      padding: 15px;
      margin-top: 20px;
      border: 2px dashed green;
      border-radius: 10px;
      text-align: center;
      font-size: 18px;
      color: #006400;
    }
  </style>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
  <div class="dashboard">
    <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
    <ul>
      <li onclick="toggleForm()">📤 Upload Waste</li>
      <li><a href="user_dashboard.php">📄 All Uploads</a></li>
      <li><a href="user_dashboard.php?status=Verified">✅ Verified Uploads</a></li>
      <li><a href="user_dashboard.php?status=Pending">⏳ Pending Uploads</a></li>
      <li><a href="user_dashboard.php?status=Rejected">❌ Rejected Uploads</a></li>
      <li onclick="toggleCoins()">💰 Earn & Track Coins</li>
    </ul>

    <!-- Upload Form -->
    <div class="upload-form" id="uploadForm">
      <form action="upload_waste.php" method="POST" enctype="multipart/form-data">
        <label>Select Waste File (PDF/PNG/JPG/JPEG/WEPP, max 10MB):</label><br>
        <input type="file" name="wasteFile" accept=".pdf,.png,.jpg,.jpeg,.webp" required><br><br>

        <label>Select Waste Type:</label><br>
        <select name="wasteType" required>
          <option value="">--Select--</option>
          <option value="Plastic">Plastic</option>
          <option value="Paper">Paper</option>
          <option value="Glass">Glass</option>
          <option value="Metal">Metal</option>
          <option value="Organic">Organic</option>
          <option value="Miscellaneous">Miscellaneous</option>
        </select><br><br>

        <label>Location:</label><br>
        <input type="text" id="location" name="location" readonly><br><br>

        <div id="map"></div>

        <label>Description (Optional):</label><br>
        <textarea name="description" placeholder="Describe the waste (if needed)..."></textarea><br>

        <input type="submit" value="Upload Waste">
      </form>
    </div>

    <!-- Coin Section -->
    <div class="coin-box" id="coinBox" style="display:none;">
  <?php
    include 'db_config.php';
    $username = $_SESSION['user'];

    // Count Verified Uploads
    $sql = "SELECT COUNT(*) as verified_count FROM waste_uploads WHERE username = ? AND verification_status = 'Verified'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $verifiedCount = $row['verified_count'];
    $coinsEarned = $verifiedCount * 10;
  ?>

  <h3>🎉 Total Coins Earned: <?php echo $coinsEarned; ?></h3>

  <?php if ($coinsEarned >= 50): ?>
    <form action="redeem_coins.php" method="POST" style="margin-top: 15px;">
      <label for="upi_id">Enter your UPI ID to Redeem:</label><br>
      <input type="text" id="upi_id" name="upi_id" required placeholder="example@upi"
             pattern="[\w.-]+@[\w]+" style="padding: 8px; width: 250px;"><br><br>
      <input type="submit" value="Redeem Coins" style="padding: 10px 20px; background-color: green; color: white; border: none;">
    </form>
  <?php else: ?>
    <p style="color: red;">⚠️ You need at least 50 coins to redeem.</p>
  <?php endif; ?>
</div>


    <!-- Upload Table -->
    <div id="recentReports">
      <?php
      $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
      if ($statusFilter) {
        $sql = "SELECT * FROM waste_uploads WHERE username = ? AND verification_status = ? ORDER BY uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $statusFilter);
      } else {
        $sql = "SELECT * FROM waste_uploads WHERE username = ? ORDER BY uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
      }

      $stmt->execute();
      $result = $stmt->get_result();
      ?>

      <h2>
        <?php
          echo $statusFilter ? "📄 $statusFilter Waste Uploads" : "📄 All Waste Uploads";
        ?>
      </h2>

      <table border="1" cellpadding="8" cellspacing="0">
        <tr>
          <th>File</th>
          <th>Waste Type</th>
          <th>Location</th>
          <th>Description</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><a href="<?php echo $row['file_path']; ?>" target="_blank">View</a></td>
            <td><?php echo $row['waste_type']; ?></td>
            <td><?php echo $row['location']; ?></td>
            <td><?php echo $row['description'] ?: '—'; ?></td>
            <td><?php echo $row['uploaded_at']; ?></td>
            <td>
              <?php 
                $status = $row['verification_status'];
                if ($status == 'Verified') {
                  echo "<span style='color:green;'>✅ Verified</span>";
                } else if ($status == 'Rejected') {
                  echo "<span style='color:red;'>❌ Rejected</span>";
                } else {
                  echo "<span style='color:orange;'>⏳ Pending</span>";
                }
              ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

    <a href="logout.php">Logout</a>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <script>
    let map;

    function toggleForm() {
      const form = document.getElementById("uploadForm");
      if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
        setTimeout(() => {
          map.invalidateSize();
        }, 100);
      } else {
        form.style.display = "none";
      }
    }

    function toggleCoins() {
      const coinBox = document.getElementById("coinBox");
      coinBox.style.display = (coinBox.style.display === "none") ? "block" : "none";
    }

    function initLeafletMap(lat, lng) {
      map = L.map('map').setView([lat, lng], 15);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      L.marker([lat, lng]).addTo(map)
        .bindPopup('You are here.')
        .openPopup();
    }

    window.onload = function () {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          document.getElementById("location").value = lat + ", " + lng;
          initLeafletMap(lat, lng);
        }, function () {
          document.getElementById("location").value = "Location not available";
        });
      } else {
        document.getElementById("location").value = "Location not supported";
      }
    };
  </script>
</body>
</html>
