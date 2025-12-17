<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.html");
  exit();
}

$sql = "SELECT * FROM waste_uploads WHERE verification_status = 'Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel AI System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
    }
    img {
      width: 150px;
      border-radius: 8px;
      cursor: pointer;
    }
    .approve {
      background-color: green;
      color: white;
      padding: 6px 10px;
      border: none;
    }
    .reject {
      background-color: red;
      color: white;
      padding: 6px 10px;
      border: none;
    }
    .predict {
      background-color: blue;
      color: white;
      padding: 5px 10px;
      border: none;
    }
    .logout {
      position: absolute;
      top: 20px;
      right: 30px;
      background-color: #333;
      color: white;
      padding: 8px 16px;
      text-decoration: none;
      border-radius: 5px;
    }
    .logout:hover {
      background-color: #555;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      padding-top: 60px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.8);
    }
    .modal-content {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 80vh;
      border-radius: 10px;
    }
    .close {
      position: absolute;
      top: 15px;
      right: 30px;
      color: white;
      font-size: 30px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover {
      color: #ccc;
    }
  </style>
</head>
<body>

<a class="logout" href="logout.php">Logout</a>
<h2>♻️ Admin Waste Verification Panel AI SYSTEM</h2>

<table>
  <tr>
    <th>Username</th>
    <th>Waste Image</th>
    <th>Location</th>
    <th>Predicted Type</th>
    <th>Estimated Weight</th>
    <th>Actions</th>
  </tr>

<?php while ($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($row['username']) ?></td>

    <td>
      <img src="<?= htmlspecialchars($row['file_path']) ?>" alt="Waste Image"
           onclick="openModal('<?= htmlspecialchars($row['file_path']) ?>')">
    </td>

    <td><?= htmlspecialchars($row['location']) ?></td>

    <td id="type_<?= $row['id'] ?>">--</td>
    <td id="weight_<?= $row['id'] ?>">--</td>

    <td>
      <button class="predict" onclick="predict(<?= $row['id'] ?>, '<?= $row['file_path'] ?>')">Predict</button>

      <form method="POST" action="verify_action.php" style="display:inline;">
        <input type="hidden" name="upload_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="Verified">
        <input type="hidden" name="type" id="type_input_<?= $row['id'] ?>" value="">
        <input type="hidden" name="weight" id="weight_input_<?= $row['id'] ?>" value="">
        <button class="approve" type="submit">Approve</button>
      </form>

      <form method="POST" action="verify_action.php" style="display:inline;">
        <input type="hidden" name="upload_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="Rejected">
        <button class="reject" type="submit">Reject</button>
      </form>
    </td>
  </tr>
<?php endwhile; ?>
</table>

<!-- Modal for large image -->
<div id="imgModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImg">
</div>

<script>
function predict(id, filePath) {
  fetch('predict.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image: filePath })
  })
  .then(response => response.json())
  .then(data => {
    document.getElementById('type_' + id).innerText = data.type;
    document.getElementById('weight_' + id).innerText = data.weight + ' kg';
    document.getElementById('type_input_' + id).value = data.type;
    document.getElementById('weight_input_' + id).value = data.weight;
  })
  .catch(err => alert("Prediction failed! " + err));
}

function openModal(src) {
  document.getElementById("imgModal").style.display = "block";
  document.getElementById("modalImg").src = src;
}

function closeModal() {
  document.getElementById("imgModal").style.display = "none";
}
</script>

</body>
</html>
