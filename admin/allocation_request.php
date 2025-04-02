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
        $prefered_type = $_POST['prefered_type'] ?? '';
        $academic_rank = $_POST['academic_rank'] ?? '';
        $work_range = $_POST['work_range'] ?? '';
        $marital_status = $_POST['marital_status'] ?? '';
        $disability = $_POST['disability'] ?? '';
        $soamu = $_POST['soamu'] ?? '';

        $sql = "INSERT INTO allocation_requests (name, username, gender, date, campus, prefered_type, academic_rank, work_range, marital_status, disability, soamu, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $name, $username, $gender, $date, $campus, $prefered_type, $academic_rank, $work_range, $marital_status, $disability, $soamu);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        /* Light Mode Variables */
        :root {
            --bg-color: #f4f4f4;
            --text-color: #333;
            --sidebar-bg: linear-gradient(180deg, #1e3a8a, #0d1b2a);
            --card-bg: white;
            --card-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Dark Mode Variables */
        body.dark-mode {
            --bg-color: #1a1a1a;
            --text-color: #f4f4f4;
            --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1a1a1a);
            --card-bg: #2d2d2d;
            --card-shadow: 0px 4px 8px rgba(255, 255, 255, 0.1);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            height: 100%;
            background: var(--sidebar-bg);
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

        /* Header */
        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            background-color: var(--bg-color);
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Notification Icon */
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

        /* Notification Dropdown */
        .notification-dropdown {
            max-height: 300px;
            overflow-y: auto;
            width: 300px;
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

        /* Cards Section */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 40px;
            color: #1e3a8a;
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 18px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
            color: var(--text-color);
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--card-bg);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: background 0.3s;
        }

        .theme-toggle i {
            font-size: 24px;
            color: var(--text-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .header {
                left: 200px;
            }
        }

        /* Form Styling */
        .container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        h1, h2 {
            text-align: center;
            color: var(--text-color);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: var(--card-bg);
            color: var(--text-color);
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
            <li><a href="allocation_request.php"><i class="bi bi-clipboard-plus"></i> Submit Allocation Request</a></li>
        </ul>
    </div>

    <!-- Header -->
    <div class="header">
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

        <!-- Profile Dropdown -->
        <div class="profile-dropdown dropdown">
            <a class="btn dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person me-2"></i>View Profile</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
                    <label for="marital_status"><i class="bi bi-people form-icon"></i>Marital Status:</label>
                    <select class="form-select" id="marital_status" name="marital_status" required>
                        <option value="married">Married</option>
                        <option value="unmarried">Unmarried</option>
                        <option value="divorced">Divorced/Widowed</option>
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
                    <label for="soamu"><i class="bi bi-globe form-icon"></i>Service Outside AMU (SOAMU):</label>
                    <select class="form-select" id="soamu" name="soamu" required>
                        <option value="1-4">1-4 years</option>
                        <option value=">4">More than 4 years</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="prefered_type"><i class="bi bi-house form-icon"></i>Preferred Unit Type:</label>
                    <select class="form-select" id="prefered_type" name="prefered_type" required>
                        <option value="three_bedroom">Three-Bedroom</option>
                        <option value="two_bedroom">Two-Bedroom</option>
                        <option value="one_bedroom">One-Bedroom</option>
                        <option value="studio">Studio</option>
                        <option value="service">Service Quarters</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Submit Request</button>
            </form>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="bi bi-moon" id="themeIcon"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        // Check localStorage for theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark-mode') {
            body.classList.add('dark-mode');
            themeIcon.classList.replace('bi-moon', 'bi-sun');
        }

        // Toggle Theme
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeIcon.classList.replace('bi-moon', 'bi-sun');
                localStorage.setItem('theme', 'dark-mode');
            } else {
                themeIcon.classList.replace('bi-sun', 'bi-moon');
                localStorage.setItem('theme', 'light-mode');
            }
        });
    </script>
</body>
</html>