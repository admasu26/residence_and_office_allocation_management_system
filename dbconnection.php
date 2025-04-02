<?php
session_start();
$conn = new mysqli("localhost", "root", "", "signup");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
