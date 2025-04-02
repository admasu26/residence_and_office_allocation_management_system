<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campus = $_POST['campus'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $desired_housing = $_POST['desired_housing'];

    // Check if the user already submitted a request
    $check_sql = "SELECT * FROM allocation_requests WHERE name = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('You have already submitted a request. Only one submission is allowed.'); window.location.href='index.php';</script>";
    } else {
        // Insert new request
        $insert_sql = "INSERT INTO allocation_requests (campus, name, gender, desired_housing) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $campus, $name, $gender, $desired_housing);

        if ($stmt->execute()) {
            echo "<script>alert('Request Submitted Successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
    $stmt->close();
}
$conn->close();
?>
