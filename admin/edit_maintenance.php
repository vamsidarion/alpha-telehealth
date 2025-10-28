<?php
include '../config.php';
$message='';
$id = $_GET['id'] ?? null;
if(!$id) { header('Location: observations.php'); exit; }

$record = $conn->query("SELECT * FROM maintenance_records WHERE id=$id")->fetch_assoc();

if($_SERVER['REQUEST_METHOD']=='POST'){
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE maintenance_records SET title=?, description=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $desc, $status, $id);
    $stmt->execute();
    $stmt->close();
    $message="Updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Edit Maintenance Record</title></head>
<body>
<?php include '../includes/header.php'; ?>
<h1>Edit Maintenance Record</h1>
<?php if($message) echo "<div>$message</div>"; ?>
<form method="POST">
    <label>Title:</label>
    <input type="text" name="title" value="<?= $record['title'] ?>" required>
    <label>Description:</label>
    <textarea name="description" required><?= $record['description'] ?></textarea>
    <label>Status:</label>
    <select name="status">
        <option value="Pending" <?= $record['status']=='Pending'?'selected':'' ?>>Pending</option>
        <option value="In Progress" <?= $record['status']=='In Progress'?'selected':'' ?>>In Progress</option>
        <option value="Completed" <?= $record['status']=='Completed'?'selected':'' ?>>Completed</option>
    </select>
    <button type="submit">Update</button>
</form>
</body>
</html>
