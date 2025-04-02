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
    header("Location: log.php"); // Redirect to login page if not logged in
    exit();
}

$username = $_SESSION['username'];

// Fetch unread notifications for the logged-in staff member
$notification_sql = "SELECT * FROM notifications WHERE username = ? AND is_read = 0 ORDER BY created_at DESC";
$notification_stmt = $conn->prepare($notification_sql);
$notification_stmt->bind_param("s", $username);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
$unread_count = $notification_result->num_rows; // Count of unread notifications
$notification_stmt->close();

// Mark notifications as read when the dropdown is opened
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $mark_read_sql = "UPDATE notifications SET is_read = 1 WHERE username = ?";
    $stmt = $conn->prepare($mark_read_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    $unread_count = 0; // Reset unread count after marking as read
}

// Fetch office allocation details for the logged-in staff member
$office_sql = "SELECT a.*, r.resource_type, r.campus, r.building, r.floor, r.room_number
               FROM office_allocation a
               JOIN office_resource r ON a.resource_id = r.id
               WHERE a.allocated_to_username = ?";
$office_stmt = $conn->prepare($office_sql);
$office_stmt->bind_param("s", $username);
$office_stmt->execute();
$office_allocation_result = $office_stmt->get_result();
$office_stmt->close();

// Fetch general resource allocation details for the logged-in staff member
$general_sql = "SELECT a.*, r.resource_type, r.campus, r.building, r.floor, r.room_number
                FROM allocations a
                JOIN resources r ON a.resource_id = r.id
                WHERE a.allocated_to_username = ?";
$general_stmt = $conn->prepare($general_sql);
$general_stmt->bind_param("s", $username);
$general_stmt->execute();
$general_allocation_result = $general_stmt->get_result();
$general_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Allocation Status</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(135deg, #f4f4f4, #e0e0e0);
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e3a8a;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0d1b2a;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #1e3a8a, #0d1b2a);
            color: white;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo img {
            width: 80px;
            border-radius: 50%;
            animation: flip 4s infinite ease-in-out; /* Add flipping animation */
        }

        @keyframes flip {
            0% {
                transform: perspective(600px) rotateY(0deg); /* Front view */
            }
            50% {
                transform: perspective(600px) rotateY(180deg); /* Back view */
            }
            100% {
                transform: perspective(600px) rotateY(360deg); /* Front view */
            }
        }

        .sidebar h2 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            color: #fff;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.1);
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

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            position: relative;
            background: linear-gradient(135deg, #f4f4f4, #e0e0e0);
        }

        /* Header with Animation */
        .header {
            background: linear-gradient(90deg, #1e3a8a, #0d1b2a);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            animation: gradientAnimation 5s infinite alternate;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 100% 50%;
            }
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #fff;
            animation: float 3s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 25px;
        }

        .card-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #1e3a8a;
            font-weight: 600;
        }

        .card-text {
            font-size: 1rem;
            color: #555;
            margin-bottom: 10px;
        }

        .card-text strong {
            color: #333;
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            padding: 15px;
            background: #e9f5ff;
            color: #1e3a8a;
            border: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 10px; /* Reduced padding */
            background: #1e3a8a; /* Solid color for simplicity */
            color: white;
            margin-top: auto; /* Push footer to the bottom */
            width: calc(100% - 250px); /* Adjust width based on sidebar */
            margin-left: 250px; /* Align with sidebar */
            font-size: 14px; /* Smaller font size */
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Add shadow */
        }

        .footer p {
            margin: 0; /* Remove margin */
        }

        .footer a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: #f4f4f4;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .footer {
                width: calc(100% - 200px);
                margin-left: 200px;
            }
        }

        /* Additional Enhancements */
        .section-title {
            font-size: 1.8rem;
            color: #1e3a8a;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-icon {
            font-size: 2rem;
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        /* Back Button */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        /* Marquee Styling */
        .marquee {
            background: linear-gradient(90deg, #1e3a8a, #0d1b2a);
            color: white;
            padding: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        /* Parallel Layout */
        .parallel-layout {
            display: flex;
            gap: 20px;
        }

        .parallel-layout .column {
            flex: 1;
        }

        @media (max-width: 768px) {
            .parallel-layout {
                flex-direction: column;
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
            <li><a href="staff_member_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="log_maintenance.php"><i class="bi bi-tools"></i> Log Maintenance Issue</a></li>
            <li><a href="view_resources.php"><i class="bi bi-box"></i> View Available Resources</a></li>
            <li><a href="submit_appeal.php"><i class="bi bi-envelope"></i> Submit Appeal</a></li>
            <li><a href="allocation_status.php"><i class="bi bi-check-circle"></i> View Allocation Status</a></li>
            <li>
                <a href="#"><i class="bi bi-clipboard-plus"></i> Submit Allocation Request</a>
                <ul class="submenu">
                    <li><a href="allocation_request.php"><i class="fas fa-home"></i> Residence allocation request</a></li>
                    <li><a href="office_allocate.php"><i class="fas fa-building"></i>Office allocation request</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Back Button -->
        <div class="back-btn">
            <a href="staff_member_dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Marquee -->
        <div class="marquee">
            <span>Welcome to the Resource Allocation System! Check your allocation status here.</span>
        </div>

        <!-- Header -->
        <div class="header animate__animated animate__fadeInDown">
            <h1><i class="bi bi-building"></i> Your Allocation Status</h1>
        </div>

        <!-- Parallel Layout for Office and General Allocations -->
        <div class="parallel-layout">
            <!-- Office Allocation Section -->
            <div class="column">
                <h2 class="section-title animate__animated animate__fadeIn"><i class="bi bi-building"></i> Office Allocations</h2>
                <?php if ($office_allocation_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($row = $office_allocation_result->fetch_assoc()): ?>
                            <div class="col-md-12 mb-4 animate__animated animate__fadeInUp">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-icon"><i class="bi bi-building"></i></div>
                                        <h5 class="card-title"><?= $row['resource_type'] ?></h5>
                                        <p class="card-text"><strong><i class="bi bi-geo-alt"></i> Campus:</strong> <?= $row['campus'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-building"></i> Building:</strong> <?= $row['building'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-layers"></i> Floor:</strong> <?= $row['floor'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-door-open"></i> Room Number:</strong> <?= $row['room_number'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info animate__animated animate__fadeIn"><i class="bi bi-info-circle"></i> No office allocation found for you.</div>
                <?php endif; ?>
            </div>

            <!-- General Resource Allocation Section -->
            <div class="column">
                <h2 class="section-title animate__animated animate__fadeIn"><i class="bi bi-house-check"></i> Residence Allocations</h2>
                <?php if ($general_allocation_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($row = $general_allocation_result->fetch_assoc()): ?>
                            <div class="col-md-12 mb-4 animate__animated animate__fadeInUp">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-icon"><i class="bi bi-house"></i></div>
                                        <h5 class="card-title"><?= $row['resource_type'] ?></h5>
                                        <p class="card-text"><strong><i class="bi bi-geo-alt"></i> Campus:</strong> <?= $row['campus'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-building"></i> Building:</strong> <?= $row['building'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-layers"></i> Floor:</strong> <?= $row['floor'] ?></p>
                                        <p class="card-text"><strong><i class="bi bi-door-open"></i> Room Number:</strong> <?= $row['room_number'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info animate__animated animate__fadeIn"><i class="bi bi-info-circle"></i> No residence allocation found for you.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Copyright © 2012 - 2025 Arba Minch University</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>