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

// Fetch unread notifications for the logged-in staff member
$username = $_SESSION['username'];
$notification_sql = "SELECT * FROM notifications WHERE username = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt = $conn->prepare($notification_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$notification_result = $stmt->get_result();
$unread_count = $notification_result->num_rows; // Count of unread notifications
$stmt->close();

// Mark notifications as read when the dropdown is opened
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $mark_read_sql = "UPDATE notifications SET is_read = 1 WHERE username = ?";
    $stmt = $conn->prepare($mark_read_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    $unread_count = 0; // Reset unread count after marking as read
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION["error"] = "You must be logged in to submit an allocation request.";
        header("Location: login.php"); // Redirect to login page
        exit();
    }

    // Retrieve the username from the session
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Check if the applicant has already submitted a request
    $check_sql = "SELECT * FROM allocation_requests WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION["error"] = "You have already submitted an allocation request. Only one request is allowed per applicant.";
    } else {
        // Proceed with inserting the request
        $name = $_POST['name'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $date = $_POST['date'] ?? '';
        $campus = $_POST['campus'] ?? '';
        $office_type = $_POST['office_type'] ?? '';
        $academic_rank = $_POST['academic_rank'] ?? '';
        $work_range = $_POST['work_range'] ?? '';
        $disability = $_POST['disability'] ?? '';

        $sql = "INSERT INTO allocation_requests (name, username, gender, date, campus, office_type, academic_rank, work_range, disability, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $name, $username, $gender, $date, $campus, $office_type, $academic_rank, $work_range, $disability);

        if ($stmt->execute()) {
            $_SESSION["message"] = "Allocation request submitted successfully.";
        } else {
            $_SESSION["error"] = "Error: " . $conn->error;
        }
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Allocation Request</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .sidebar {
            width: 250px;
            height: 100%;
            background: #1e3a8a;
            color: white;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo img {
            width: 80px;
            border-radius: 50%;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar ul li a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            cursor: pointer;
        }

        .sidebar ul li a i {
            font-size: 18px;
        }

        .sidebar ul li:hover {
            background: rgb(9, 0, 0);
        }

        .sidebar ul li:hover a {
            color: #fff;
        }

        .submenu {
            display: none;
            padding-left: 20px;
        }

        .sidebar ul li:hover .submenu {
            display: block;
        }

        /* Notification and Profile Icons */
        .top-right-icons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 15px;
            z-index: 1000;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .profile-icon {
            cursor: pointer;
        }

        /* Dropdown Styling */
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Icon Styling for Form */
        .form-icon {
            margin-right: 10px;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2>Staff Dashboard</h2>
        <ul>
            <li><a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="log_maintenance.php"><i class="bi bi-tools"></i> Log Maintenance Issue</a></li>
            <li><a href="view_resources.php"><i class="bi bi-box"></i> View Available Resources</a></li>
            <li><a href="submit_appeal.php"><i class="bi bi-envelope"></i> Submit Appeal</a></li>
            <li><a href="allocation_status.php"><i class="bi bi-check-circle"></i> View Allocation Status</a></li>
            <li><a href="allocation_request.php"><i class="bi bi-clipboard-plus"></i> Submit Allocation Request</a></li>
        </ul>
    </div>

    <!-- Notification and Profile Icons -->
    <div class="top-right-icons">
        <!-- Notification Icon -->
        <div class="notification-icon dropdown">
            <a class="dropdown-toggle" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-4"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="badge"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <?php if ($unread_count > 0): ?>
                    <?php while ($row = $notification_result->fetch_assoc()): ?>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p><?= $row['message'] ?></p>
                                    <small><?= $row['created_at'] ?></small>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                    <li>
                        <form method="POST" action="">
                            <button type="submit" name="mark_as_read" class="dropdown-item text-center text-primary">
                                Mark all as read
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a class="dropdown-item text-center">No new notifications.</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Profile Icon -->
        <div class="dropdown">
            <a class="btn btn-light dropdown-toggle d-flex align-items-center" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> <!-- Display the logged-in username -->
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person me-2"></i>View Profile</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h2><i class="bi bi-clipboard-plus form-icon"></i>Submit Allocation Request</h2>
        <?php
        if (isset($_SESSION["message"])) {
            echo "<p class='success'>" . $_SESSION["message"] . "</p>";
            unset($_SESSION["message"]);
        }
        if (isset($_SESSION["error"])) {
            echo "<p class='error'>" . $_SESSION["error"] . "</p>";
            unset($_SESSION["error"]);
        }
        ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name"><i class="bi bi-person form-icon"></i>Full Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="gender"><i class="bi bi-gender-ambiguous form-icon"></i>Gender:</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email"><i class="bi bi-envelope form-icon"></i>Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="campus"><i class="bi bi-building form-icon"></i>Campus:</label>
                <select class="form-select" id="campus" name="campus" required>
                    <option value="Main">Main</option>
                    <option value="Chamo">Chamo</option>
                    <option value="Abaya">Abaya</option>
                    <option value="Kulfo">Kulfo</option>
                    <option value="Nechisar">Nechisar</option>
                    <option value="Sawula">Sawula</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="academic_rank"><i class="bi bi-mortarboard form-icon"></i>Academic Position (Title):</label>
                <select class="form-select" id="academic_rank" name="academic_rank" required>
                    <option value="professor">Professor</option>
                    <option value="researcher">Researcher</option>
                    <option value="phd">PhD</option>
                    <option value="msc">MSc</option>
                    <option value="bsc">BSc</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="work_range"><i class="bi bi-briefcase form-icon"></i>Work Range in University:</label>
                <select class="form-select" id="work_range" name="work_range" required>
                    <option value=">8">More than 8 years</option>
                    <option value="5-8">5-8 years</option>
                    <option value="3-5">3-5 years</option>
                    <option value="1-3">1-3 years</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="disability"><i class="bi bi-wheelchair form-icon"></i>Disability Status:</label>
                <select class="form-select" id="disability" name="disability" required>
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="office_type"><i class="bi bi-door-open form-icon"></i>Office Type:</label>
                <select class="form-select" id="office_type" name="office_type" required>
                    <option value="private">Private Office</option>
                    <option value="shared">Shared Office</option>
                    <option value="open_space">Open Space</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Submit Request</button>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Copyright © 2012 - 2025 Arba Minch University</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>