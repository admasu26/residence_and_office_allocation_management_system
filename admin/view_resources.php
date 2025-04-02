<?php
session_start();

// Check if the staff member is logged in
if (!isset($_SESSION['username'])) {
    header("Location: log.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available office resources grouped by campus, building, and floor
$office_sql = "SELECT * FROM office_resource WHERE status = 'Available' ORDER BY campus, building, floor, room_number";
$office_result = $conn->query($office_sql);

// Group office resources by campus, building, and floor
$office_campuses = [];
if ($office_result->num_rows > 0) {
    while ($row = $office_result->fetch_assoc()) {
        $campus = $row['campus'];
        $building = $row['building'];
        $floor = $row['floor'];
        if (!isset($office_campuses[$campus])) {
            $office_campuses[$campus] = [];
        }
        if (!isset($office_campuses[$campus][$building])) {
            $office_campuses[$campus][$building] = [];
        }
        if (!isset($office_campuses[$campus][$building][$floor])) {
            $office_campuses[$campus][$building][$floor] = [];
        }
        $office_campuses[$campus][$building][$floor][] = $row;
    }
}

// Fetch available general resources grouped by campus, building, and floor
$general_sql = "SELECT * FROM resources WHERE status = 'Available' ORDER BY campus, building, floor, room_number";
$general_result = $conn->query($general_sql);

// Group general resources by campus, building, and floor
$general_campuses = [];
if ($general_result->num_rows > 0) {
    while ($row = $general_result->fetch_assoc()) {
        $campus = $row['campus'];
        $building = $row['building'];
        $floor = $row['floor'];
        if (!isset($general_campuses[$campus])) {
            $general_campuses[$campus] = [];
        }
        if (!isset($general_campuses[$campus][$building])) {
            $general_campuses[$campus][$building] = [];
        }
        if (!isset($general_campuses[$campus][$building][$floor])) {
            $general_campuses[$campus][$building][$floor] = [];
        }
        $general_campuses[$campus][$building][$floor][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Available Resources</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        /* Light Mode Variables */
        :root {
            --bg-color: #f8f9fa;
            --text-color: #212529;
            --sidebar-bg: linear-gradient(180deg, #1e3a8a, #0d1b2a);
            --card-bg: white;
            --card-shadow: 0px 4px 12px rgba(0, 0, 0, 0.08);
            --primary-color: #1e3a8a;
            --secondary-color: #0d1b2a;
            --success-color: #28a745;
            --info-color: #17a2b8;
        }

        /* Dark Mode Variables */
        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f8f9fa;
            --sidebar-bg: linear-gradient(180deg, #0d1b2a, #121212);
            --card-bg: #1e1e1e;
            --card-shadow: 0px 4px 12px rgba(255, 255, 255, 0.05);
            --primary-color: #3a5ca9;
            --secondary-color: #1e2a3a;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .sidebar .logo img {
            width: 80px;
            border-radius: 50%;
            animation: flip 4s infinite ease-in-out;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes flip {
            0% { transform: perspective(600px) rotateY(0deg); }
            50% { transform: perspective(600px) rotateY(180deg); }
            100% { transform: perspective(600px) rotateY(360deg); }
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 25px;
            color: #fff;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .sidebar ul li:hover a {
            color: #fff;
            transform: translateX(5px);
        }

        .submenu {
            display: none;
            padding-left: 15px;
            background: rgba(0, 0, 0, 0.2);
            margin-top: 10px;
            border-radius: 5px;
        }

        .sidebar ul li.active .submenu {
            display: block;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 25px;
            flex-grow: 1;
            position: relative;
        }

        /* Marquee Styling */
        .marquee {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Alert Messages */
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Parallel Layout */
        .parallel-layout {
            display: flex;
            gap: 25px;
        }

        .parallel-layout .column {
            flex: 1;
        }

        @media (max-width: 992px) {
            .parallel-layout {
                flex-direction: column;
            }
        }

        /* Section Headers */
        .section-header {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header i {
            font-size: 1.5rem;
        }

        /* Campus, Building, and Floor Cards */
        .campus-card, .building-card, .floor-card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .campus-card:hover, .building-card:hover, .floor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .campus-card h2 {
            color: var(--primary-color);
            margin-bottom: 18px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .building-card h3 {
            color: var(--secondary-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .floor-card h4 {
            color: var(--info-color);
            margin-bottom: 12px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .room-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .room-list li {
            padding: 10px 15px;
            margin-bottom: 8px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 6px;
            font-size: 0.95rem;
            color: var(--text-color);
            border-left: 4px solid var(--success-color);
            transition: all 0.3s ease;
        }

        .room-list li:hover {
            background: rgba(0, 0, 0, 0.07);
            transform: translateX(5px);
        }

        .room-list li strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .text-success {
            color: var(--success-color) !important;
            font-weight: 600;
        }

        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 12px 20px;
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            z-index: 100;
            font-size: 0.9rem;
        }

        /* No Resources Message */
        .no-resources {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: var(--text-color);
            font-size: 1rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-left: 220px;
                padding: 15px;
            }
            .footer {
                left: 220px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .footer {
                position: static;
                left: 0;
                margin-top: 20px;
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
            <li><a href="log_maintenance.php"><i class="bi bi-tools"></i> Log Maintenance</a></li>
            <li class="active"><a href="view_resources.php"><i class="bi bi-box-seam"></i> View Resources</a></li>
            <li><a href="submit_appeal.php"><i class="bi bi-envelope"></i> Submit Appeal</a></li>
            <li><a href="allocation_status.php"><i class="bi bi-check-circle"></i> Allocation Status</a></li>
            <li>
                <a href="#"><i class="bi bi-clipboard-plus"></i> Allocation Request</a>
                <ul class="submenu">
                    <li><a href="allocation_request.php"><i class="fas fa-home"></i> Residence Request</a></li>
                    <li><a href="office_allocate.php"><i class="fas fa-building"></i> Office Request</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Marquee -->
        <div class="marquee">
            <span>Welcome to the Resource Management System! Check available resources here.</span>
        </div>

        <?php
        if (isset($_SESSION["message"])) {
            echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i> " . $_SESSION["message"] . "</div>";
            unset($_SESSION["message"]);
        }
        if (isset($_SESSION["error"])) {
            echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i> " . $_SESSION["error"] . "</div>";
            unset($_SESSION["error"]);
        }
        ?>

        <!-- Parallel Layout for Office and General Resources -->
        <div class="parallel-layout">
            <!-- Office Resources Section -->
            <div class="column">
                <h2 class="section-header"><i class="fas fa-building text-primary"></i> Office Resources</h2>
                <?php if (!empty($office_campuses)): ?>
                    <?php foreach ($office_campuses as $campus => $buildings): ?>
                        <div class="campus-card">
                            <h2><i class="fas fa-university"></i> <?= htmlspecialchars($campus) ?></h2>
                            <?php foreach ($buildings as $building => $floors): ?>
                                <div class="building-card">
                                    <h3><i class="fas fa-building"></i> <?= htmlspecialchars($building) ?></h3>
                                    <?php foreach ($floors as $floor => $rooms): ?>
                                        <div class="floor-card">
                                            <h4><i class="fas fa-layer-group"></i> Floor <?= htmlspecialchars($floor) ?></h4>
                                            <ul class="room-list">
                                                <?php foreach ($rooms as $room): ?>
                                                    <li>
                                                        <strong>Room:</strong> <?= htmlspecialchars($room['room_number']) ?> | 
                                                        <strong>Type:</strong> <?= htmlspecialchars($room['resource_type']) ?> | 
                                                        <strong>Status:</strong> <span class="text-success"><?= htmlspecialchars($room['status']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-resources">
                        <i class="fas fa-info-circle me-2"></i> No available office resources found.
                    </div>
                <?php endif; ?>
            </div>

            <!-- General Resources Section -->
            <div class="column">
                <h2 class="section-header"><i class="fas fa-home text-primary"></i> Residence Resources</h2>
                <?php if (!empty($general_campuses)): ?>
                    <?php foreach ($general_campuses as $campus => $buildings): ?>
                        <div class="campus-card">
                            <h2><i class="fas fa-university"></i> <?= htmlspecialchars($campus) ?></h2>
                            <?php foreach ($buildings as $building => $floors): ?>
                                <div class="building-card">
                                    <h3><i class="fas fa-building"></i> <?= htmlspecialchars($building) ?></h3>
                                    <?php foreach ($floors as $floor => $rooms): ?>
                                        <div class="floor-card">
                                            <h4><i class="fas fa-layer-group"></i> Floor <?= htmlspecialchars($floor) ?></h4>
                                            <ul class="room-list">
                                                <?php foreach ($rooms as $room): ?>
                                                    <li>
                                                        <strong>Room:</strong> <?= htmlspecialchars($room['room_number']) ?> | 
                                                        <strong>Type:</strong> <?= htmlspecialchars($room['resource_type']) ?> | 
                                                        <strong>Status:</strong> <span class="text-success"><?= htmlspecialchars($room['status']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-resources">
                        <i class="fas fa-info-circle me-2"></i> No available residence resources found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?= date('Y') ?> Resource Management System. All rights reserved.
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>