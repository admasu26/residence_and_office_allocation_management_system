<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Fetch the request details
    $sql = "SELECT * FROM staff_requests WHERE request_id = $request_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $unit_type = $row['unit_type'];

        // Find an available room of the requested type
        $room_sql = "SELECT * FROM Rooms WHERE unit_type = '$unit_type' AND status = 'available' LIMIT 1";
        $room_result = $conn->query($room_sql);

        if ($room_result->num_rows > 0) {
            $room = $room_result->fetch_assoc();
            $room_id = $room['room_id'];

            // Allocate the room
            $update_sql = "UPDATE Rooms SET status = 'allocated' WHERE room_id = $room_id";
            $conn->query($update_sql);

            $allocation_sql = "UPDATE staff_requests SET allocation_status = 'allocated' WHERE request_id = $request_id";
            $conn->query($allocation_sql);

            // Redirect with success message
            header("Location: manage_allocation_request.php?message=Room allocated for request ID: $request_id");
            exit();
        } else {
            // Redirect with error message
            header("Location: manage_allocation_request.php?message=No available rooms for request ID: $request_id");
            exit();
        }
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>