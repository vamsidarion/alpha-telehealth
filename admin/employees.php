<?php
include '../config.php';

// Handle Add Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $conn->real_escape_string($_POST['password']); // Plain text for now

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    if ($conn->query($sql)) {
        $success = "Employee added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Handle Delete Employee
if (isset($_GET['delete'])) {
    $emp_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id=$emp_id AND role IN ('doctor','manager','support')");
}

// Fetch all employees
$employees = $conn->query("SELECT * FROM users WHERE role IN ('doctor','manager','support') ORDER BY user_id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; }
        .container { max-width: 1000px; margin:30px auto; }
        h1 { text-align:center; color:#2c6e49; margin-bottom:30px; }
        table { width: 100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:left; }
        th { background-color:#2c6e49; color:#fff; }
        tr:hover { background-color:#f1f1f1; }
        form { background:#fff; padding:20px; margin-bottom:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        input, select { width:100%; padding:10px; margin:8px 0; border-radius:5px; border:1px solid #ccc; }
        input[type="submit"] { background-color:#2c6e49; color:#fff; border:none; cursor:pointer; padding:10px 20px; border-radius:5px; }
        input[type="submit"]:hover { background-color:#1f5037; }
        .action-links a { margin-right:10px; color:red; text-decoration:none; }
        .success { color:green; text-align:center; }
        .error { color:red; text-align:center; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1>Manage Employees</h1>

    <?php if(!empty($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <!-- Add Employee Form -->
    <form method="POST">
        <h3>Add New Employee</h3>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="doctor">Doctor</option>
            <option value="manager">Manager</option>
            <option value="support">Support Staff</option>
        </select>
        <input type="submit" name="add_employee" value="Add Employee">
    </form>

    <!-- Employees List -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($employees) > 0): ?>
                <?php foreach($employees as $emp): ?>
                    <tr>
                        <td><?= $emp['user_id']; ?></td>
                        <td><?= htmlspecialchars($emp['name']); ?></td>
                        <td><?= htmlspecialchars($emp['email']); ?></td>
                        <td><?= ucfirst($emp['role']); ?></td>
                        <td class="action-links">
                            <a href="employees.php?delete=<?= $emp['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No employees found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
