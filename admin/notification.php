<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the staff member is logged in
if (!isset($_SESSION['username'])) {
    header("Location: log.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch unread notifications
$notification_sql = "SELECT * FROM notifications WHERE username = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt = $conn->prepare($notification_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$notification_result = $stmt->get_result();
$stmt->close();

// Mark notifications as read
$mark_read_sql = "UPDATE notifications SET is_read = 1 WHERE username = ?";
$stmt = $conn->prepare($mark_read_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .notification-badge {
            position: relative;
            display: inline-block;
        }

        .notification-badge .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <?php if ($notification_result->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($row = $notification_result->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <p><?= $row['message'] ?></p>
                            <small><?= $row['created_at'] ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No new notifications.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>