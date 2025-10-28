<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test MySQL connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "alpha_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connected successfully!";
?>
