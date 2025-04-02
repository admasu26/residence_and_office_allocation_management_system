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
    $check_sql = "SELECT * FROM office_allocation_requests WHERE username = ?";
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

        $sql = "INSERT INTO office_allocation_requests (name, username, gender, date, campus, office_type, academic_rank, work_range, disability, status) 
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
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        /* Light Mode (Default) */
        :root {
            --bg-color: #f4f4f4;
            --text-color: #333;
            --sidebar-bg: #1e3a8a;
            --sidebar-text: white;
            --card-bg: white;
            --card-text: #333;
            --card-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            --footer-bg: #1e3a8a;
            --footer-text: white;
            --dropdown-bg: #1e3a8a;
            --dropdown-text: white;
            --form-bg: white;
            --form-text: #333;
            --form-border: #ced4da;
            --input-bg: white;
            --input-text: #495057;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        /* Dark Mode */
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --sidebar-bg: #0d1b2a;
            --sidebar-text: #e0e0e0;
            --card-bg: #1e1e1e;
            --card-text: #e0e0e0;
            --card-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            --footer-bg: #0d1b2a;
            --footer-text: #e0e0e0;
            --dropdown-bg: #0d1b2a;
            --dropdown-text: #e0e0e0;
            --form-bg: #2d2d2d;
            --form-text: #e0e0e0;
            --form-border: #444;
            --input-bg: #333;
            --input-text: #e0e0e0;
            --success-color: #4CAF50;
            --error-color: #f44336;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            height: 100%;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
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
            position: relative;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--sidebar-text);
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
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li:hover a {
            color: #fff;
        }

        .submenu {
            display: none;
            padding-left: 20px;
        }

        .submenu.active {
            display: block;
        }

        @keyframes flip {
            0% { transform: perspective(600px) rotateY(0deg); }
            50% { transform: perspective(600px) rotateY(180deg); }
            100% { transform: perspective(600px) rotateY(360deg); }
        }

        /* Profile Dropdown */
        .profile-dropdown .dropdown-toggle {
            background: var(--sidebar-bg);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .profile-dropdown .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-dropdown .dropdown-menu {
            background: var(--sidebar-bg);
            border: none;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-dropdown .dropdown-item {
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .profile-dropdown .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Content Area */
        .main-content {
            margin-left: 250px;
            padding: 100px 20px 20px; /* Added top padding to avoid header overlap */
            flex-grow: 1;
            position: relative;
        }


        /* Header */
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            padding: 10px 20px;
            background-color: var(--bg-color);
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 80px 20px 60px;
            flex-grow: 1;
        }

        /* Form Container */
        .form-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px;
            background-color: var(--form-bg);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        /* Form Elements */
        .form-control, .form-select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border: 1px solid var(--form-border);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-icon {
            margin-right: 8px;
            color: var(--text-color);
        }

        /* Footer */
        .footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
            text-align: center;
            padding: 10px 20px;
            margin-left: 250px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content, .footer {
                margin-left: 200px;
            }
            .header {
                left: 200px;
            }
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
            <li><a href="staff_member_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="log_maintenance.php"><i class="bi bi-tools"></i> Log Maintenance</a></li>
            <li><a href="view_resources.php"><i class="bi bi-box"></i> View Resources</a></li>
            <li><a href="submit_appeal.php"><i class="bi bi-envelope"></i> Submit Appeal</a></li>
            <li><a href="allocation_status.php"><i class="bi bi-check-circle"></i> Allocation Status</a></li>
            <li><a href="allocation_request.php"><i class="bi bi-clipboard-plus"></i> Allocation Request</a></li>
        </ul>
    </div>

    <!-- Header -->
    <div class="header">
        <!-- Theme Toggle -->
        <div class="theme-toggle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="themeToggle">
                <label class="form-check-label" for="themeToggle">Dark Mode</label>
            </div>
        </div>

        <!-- Notifications -->
        <div class="notification-icon dropdown">
            <a class="btn dropdown-toggle" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-4"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <?php if ($unread_count > 0): ?>
                    <?php while ($row = $notification_result->fetch_assoc()): ?>
                        <li><a class="dropdown-item" href="#"><?= $row['message'] ?></a></li>
                    <?php endwhile; ?>
                    <li>
                        <form method="POST" action="">
                            <button type="submit" name="mark_as_read" class="dropdown-item text-center text-primary">
                                Mark all as read
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a class="dropdown-item text-center">No new notifications</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Profile -->
        <div class="profile-dropdown dropdown">
            <a class="dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <h2><i class="bi bi-clipboard-plus form-icon"></i> Submit Allocation Request</h2>
            
            <?php if (isset($_SESSION["message"])): ?>
                <div class="alert alert-success"><?= $_SESSION["message"] ?></div>
                <?php unset($_SESSION["message"]); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION["error"])): ?>
                <div class="alert alert-danger"><?= $_SESSION["error"] ?></div>
                <?php unset($_SESSION["error"]); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label"><i class="bi bi-person form-icon"></i>Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="gender" class="form-label"><i class="bi bi-gender-ambiguous form-icon"></i>Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label"><i class="bi bi-envelope form-icon"></i>Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="campus" class="form-label"><i class="bi bi-building form-icon"></i>Campus</label>
                        <select class="form-select" id="campus" name="campus" required>
                            <option value="Main">Main</option>
                            <option value="Chamo">Chamo</option>
                            <option value="Abaya">Abaya</option>
                            <option value="Kulfo">Kulfo</option>
                            <option value="Nechisar">Nechisar</option>
                            <option value="Sawula">Sawula</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="academic_rank" class="form-label"><i class="bi bi-mortarboard form-icon"></i>Academic Position</label>
                        <select class="form-select" id="academic_rank" name="academic_rank" required>
                            <option value="professor">Professor</option>
                            <option value="researcher">Researcher</option>
                            <option value="phd">PhD</option>
                            <option value="msc">MSc</option>
                            <option value="bsc">BSc</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="work_range" class="form-label"><i class="bi bi-briefcase form-icon"></i>Work Range</label>
                        <select class="form-select" id="work_range" name="work_range" required>
                            <option value=">8">More than 8 years</option>
                            <option value="5-8">5-8 years</option>
                            <option value="3-5">3-5 years</option>
                            <option value="1-3">1-3 years</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="disability" class="form-label"><i class="bi bi-wheelchair form-icon"></i>Disability Status</label>
                        <select class="form-select" id="disability" name="disability" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="office_type" class="form-label"><i class="bi bi-door-open form-icon"></i>Office Type</label>
                        <select class="form-select" id="office_type" name="office_type" required>
                            <option value="private">Private Office</option>
                            <option value="shared">Shared Office</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Copyright © 2012 - 2025 Arba Minch University</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.setAttribute('data-theme', savedTheme);
            themeToggle.checked = savedTheme === 'dark';
        }

        // Toggle theme when switch is clicked
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                body.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html>