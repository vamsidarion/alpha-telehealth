<?php
// Ensure session is started
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Example: assume $_SESSION['role'] is set after login
$role = $_SESSION['role'] ?? 'guest';
?>
<nav class="admin-sidebar">
    <ul class="sidebar-menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="patients.php">Patients</a></li>
        <li><a href="doctors.php">Doctors</a></li>
        <li><a href="appointments.php">Appointments</a></li>
        
        <?php if($role === 'admin'): ?>
            <li><a href="documents.php">Documents</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="employees.php">Manage Employees</a></li>
            <li><a href="observations.php">Observations & Maintenance</a></li>
        <?php endif; ?>
        
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<style>
.admin-sidebar {
    width: 220px;
    background: #2c6e49;
    min-height: 100vh;
    padding-top: 20px;
    float: left;
}
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar-menu li {
    margin: 5px 0;
}
.sidebar-menu li a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 10px 20px;
    border-left: 4px solid transparent;
    transition: 0.3s;
}
.sidebar-menu li a:hover {
    background: #1f5037;
    border-left: 4px solid #fff;
}
</style>
