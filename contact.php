<?php
session_start();
$hostname = "localhost";
$username = "root";
$password = "";
$database = "signup";
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$Name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];
$sql = "INSERT INTO contact (name, email, message) 
        VALUES ('$Name', '$email', '$message')";
if ($conn->query($sql) === TRUE) {  
    $_SESSION['message'] = "Thank you! We will contact you as soon as possible.";
    header("Location: contact_us.php"); 
} else {   
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>