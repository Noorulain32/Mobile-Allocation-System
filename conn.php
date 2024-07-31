<?php
session_start(); // Start session at the very beginning

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mobile_allocation_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
