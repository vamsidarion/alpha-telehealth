<?php
include '../config.php';
$message = '';

$id = $_GET['id'] ?? null;

if(!$id) { header('Location: observations.php'); exit; }

// Fetch log
$log = $conn->query("SELECT * FROM activity_logs WHERE id=$id")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    $stmt = $conn->prepare("UPDATE activity_logs SET user_id=?, action=? WHERE id=?");
    $stmt->bind_param("isi", $user_id, $action, $id);
    if($stmt->execute()){ $message="Updated successfully!"; } else { $message=$stmt->error; }
    $stmt->close();
}

// Fetch all users
$users = $conn->query("SELECT user_id, name FROM users ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Edit Activity Log</title></head>
<body>
<?php include '../includes/header.php'; ?>
<h1>Edit Activity Log</h1>
<?php if($message) echo "<div>$message</div>"; ?>
<form method="POST">
    <label>User:</label>
    <select name="user_id" required>
        <?php while($u=$users->fetch_assoc()): ?>
        <option value="<?= $u['user_id'] ?>" <?= ($u['user_id']==$log['user_id']?'selected':'') ?>><?= $u['name'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Action:</label>
    <input type="text" name="action" value="<?= $log['action'] ?>" required>

    <button type="submit">Update</button>
</form>
</body>
</html>
