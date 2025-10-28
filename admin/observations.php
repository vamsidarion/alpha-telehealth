<?php
include '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Observations & Maintenance | Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f8; margin:0; }
    h1 { text-align:center; color:#2c6e49; margin:30px 0; }
    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:30px; }
    th, td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#2c6e49; color:#fff; }
    tr:hover { background:#f9f9f9; }
    .section { margin-bottom:40px; }
    .btn {
      display:inline-block;
      background:#2c6e49;
      color:#fff;
      padding:8px 15px;
      border-radius:5px;
      text-decoration:none;
      font-weight:bold;
      transition:0.3s;
      margin-right:5px;
    }
    .btn:hover { background:#1f5037; }
    .btn-delete { background:red; }
    .actions { text-align:right; margin-bottom:15px; }
  </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
  <h1>Observations & Maintenance Dashboard</h1>

  <!-- 🔹 Activity Logs Section -->
  <div class="section">
    <div class="actions">
      <a href="add_log.php" class="btn">Add New Log</a>
    </div>
    <h2>Recent Activity Logs</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Action</th>
        <th>Timestamp</th>
        <th>Actions</th>
      </tr>
      <?php
      $logs = $conn->query("
          SELECT l.id, u.name AS user, l.action, l.timestamp 
          FROM activity_logs l 
          LEFT JOIN users u ON l.user_id = u.user_id 
          ORDER BY l.timestamp DESC
      ");
      if ($logs && $logs->num_rows > 0) {
          while ($log = $logs->fetch_assoc()) {
              echo "<tr>
                      <td>{$log['id']}</td>
                      <td>".($log['user'] ?? 'Unknown')."</td>
                      <td>{$log['action']}</td>
                      <td>{$log['timestamp']}</td>
                      <td>
                        <a href='edit_log.php?id={$log['id']}' class='btn'>Edit</a>
                        <a href='delete_log.php?id={$log['id']}' class='btn btn-delete'>Delete</a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No activity logs found.</td></tr>";
      }
      ?>
    </table>
  </div>

  <!-- 🔹 Maintenance Records Section -->
  <div class="section">
    <div class="actions">
      <a href="add_maintenance.php" class="btn">Add Maintenance Record</a>
    </div>
    <h2>Maintenance Records</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
      <?php
      $maintenance = $conn->query("SELECT * FROM maintenance_records ORDER BY date DESC");
      if ($maintenance && $maintenance->num_rows > 0) {
          while ($m = $maintenance->fetch_assoc()) {
              echo "<tr>
                      <td>{$m['id']}</td>
                      <td>{$m['title']}</td>
                      <td>{$m['description']}</td>
                      <td>{$m['status']}</td>
                      <td>{$m['date']}</td>
                      <td>
                        <a href='edit_maintenance.php?id={$m['id']}' class='btn'>Edit</a>
                        <a href='delete_maintenance.php?id={$m['id']}' class='btn btn-delete'>Delete</a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No maintenance records found.</td></tr>";
      }
      ?>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
