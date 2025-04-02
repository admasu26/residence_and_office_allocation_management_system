<?php
session_start(); // Start the session

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if not logged in or not an admin
    header('location: log.php');
    exit(); // Ensure no further code is executed after the redirect
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

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    // Delete old permissions
    $delete_sql = "DELETE FROM form_permissions";
    if ($conn->query($delete_sql)) {
        // Insert the new permission into the database
        $insert_sql = "INSERT INTO form_permissions (start_date, end_date) VALUES ('$start_date', '$end_date')";
        if ($conn->query($insert_sql)) {
            $message = "<div class='alert alert-success'>Form permission set successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error setting form permission: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Error deleting old permissions: " . $conn->error . "</div>";
    }
}

// Fetch the current form permission
$sql = "SELECT * FROM form_permissions ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$current_permission = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Permission</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Light Mode (Default) */
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --sidebar-bg: linear-gradient(180deg, #1e3a8a, #0d1b2a);
            --sidebar-text: white;
            --card-bg: white;
            --card-text: #333;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            --footer-bg: #1e3a8a;
            --footer-text: white;
            --dropdown-bg: #1e3a8a;
            --dropdown-text: white;
            --primary-color: #1e3a8a; /* Blue */
            --primary-hover-color: #0d2a5e; /* Darker Blue */
        }

        /* Dark Mode */
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1e3a8a);
            --sidebar-text: #e0e0e0;
            --card-bg: #1e1e1e;
            --card-text: #e0e0e0;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
            --footer-bg: #0d1b2a;
            --footer-text: #e0e0e0;
            --dropdown-bg: #0d1b2a;
            --dropdown-text: #e0e0e0;
            --primary-color: #3b82f6; /* Light Blue */
            --primary-hover-color: #2563eb; /* Darker Blue */
        }

        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo img {
            width: 80px;
            border-radius: 50%;
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
            color: var(--sidebar-text);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li:hover a {
            color: #fff;
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 100px 30px 30px; /* Added top padding to avoid header overlap */
            flex-grow: 1;
            width: calc(100% - 250px); /* Ensure main content takes full width */
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: var(--bg-color);
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Theme Toggle Switch */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .theme-toggle .form-check-input {
            cursor: pointer;
        }

        .theme-toggle .form-check-label {
            color: var(--text-color);
        }

        /* Profile Dropdown */
        .profile-dropdown .dropdown-toggle {
            background: var(--dropdown-bg);
            color: var(--dropdown-text);
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
            background: var(--dropdown-bg);
            border: none;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-dropdown .dropdown-item {
            color: var(--dropdown-text);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .profile-dropdown .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Form Container */
        .form-container {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            width: 100%; /* Full width */
        }

        .form-container h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .form-container label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .form-container input[type="date"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid var(--input-border-color);
            border-radius: 8px;
            background-color: var(--input-bg-color);
            color: var(--text-color);
            font-size: 1rem;
        }

        .form-container button {
            width: 100%;
            padding: 14px;
            background: var(--primary-color); /* Blue color */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background: var(--primary-hover-color); /* Darker blue on hover */
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .current-permission {
            margin-top: 30px;
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--input-border-color);
        }

        .current-permission h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .current-permission p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 120px 20px 20px; /* Adjusted for smaller screens */
                width: 100%; /* Full width on smaller screens */
            }

            .header {
                left: 0;
            }

            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2 class="text-center">SRAM</h2>
        <h2 class="text-center">Admin Dashboard</h2>
        <ul class="list-unstyled">
            <li><a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="create.php" class="d-flex align-items-center"><i class="bi bi-person-plus"></i> Create User</a></li>
            <li><a href="delete.php" class="d-flex align-items-center"><i class="bi bi-trash"></i> Delete User</a></li>
            <li><a href="update.php" class="d-flex align-items-center"><i class="bi bi-pencil-square"></i> Update User</a></li>
            <li><a href="permission.php" class="d-flex align-items-center"><i class="bi bi-shield-lock"></i> Manage Permissions</a></li>
            <li><a href="view_detail.php" class="d-flex align-items-center"><i class="bi bi-person-vcard"></i> View Staff Details</a></li>
        </ul>
    </nav>
    
    <!-- Header -->
    <div class="header">
        <!-- Theme Toggle Switch -->
        <div class="theme-toggle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="themeToggle">
                <label class="form-check-label" for="themeToggle">Dark Mode</label>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown dropdown">
            <a class="btn dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content container">
        <h1 class="mt-4">Set Form Permission</h1>
        <?= $message; ?>

        <!-- Form to Set Permission -->
        <div class="form-container">
            <h2><i class="bi bi-shield-lock"></i> Set Form Permission</h2>
            <form action="permission.php" method="POST">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <button type="submit">Set Permission</button>
            </form>

            <!-- Display Current Permission -->
            <?php if ($current_permission): ?>
                <div class="current-permission">
                    <h3>Current Permission</h3>
                    <p><strong>Start Date:</strong> <?= $current_permission['start_date']; ?></p>
                    <p><strong>End Date:</strong> <?= $current_permission['end_date']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.setAttribute('data-theme', savedTheme);
            themeToggle.checked = savedTheme === 'dark';
        }

        // Toggle Theme
        themeToggle.addEventListener('change', () => {
            if (themeToggle.checked) {
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