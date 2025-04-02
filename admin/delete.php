<?php
session_start(); // Start the session

// Redirect if user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Include the database connection file
$servername = "localhost"; // your database server
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "signup"; // the database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user deletion
if (isset($_GET["delete_id"])) {
    $delete_id = $_GET["delete_id"];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Fetch all users from the database
$users_result = $conn->query("SELECT id, username, role FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            padding: 20px;
            margin-left: 250px;
            position: relative;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
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
            color: var(--sidebar-text);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            cursor: pointer;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .container {
            margin-top: 20px;
        }

        .table-container {
            background-color: var(--card-bg);
            color: var(--card-text);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: absolute;
            top: 20px;
            right: 20px;
        }

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

        /* Dark Mode Styles */
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: rgb(45, 232, 20);
            --sidebar-bg: #0d1b2a;
            --sidebar-text: #e0e0e0;
            --card-bg: rgb(243, 236, 236);
            --card-text: #e0e0e0;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
            --dropdown-bg: #1e1e1e;
            --dropdown-text: #e0e0e0;
        }

        /* Light Mode (Default) */
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --sidebar-bg: #1e3a8a;
            --sidebar-text: white;
            --card-bg: white;
            --card-text: #333;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            --dropdown-bg: white;
            --dropdown-text: #333;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .profile-dropdown {
                position: static;
                text-align: center;
                margin-top: 20px;
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
            <li><a href="update.php" class="d-flex align-items-center"><i class="bi bi-tools"></i> Update User</a></li>
            <li><a href="permission.php" class="d-flex align-items-center"><i class="bi bi-shield-lock"></i> Manage Permissions</a></li>
            <li><a href="view_detail.php" class="d-flex align-items-center"><i class="bi bi-person-vcard"></i> View Staff Details</a></li>
        </ul>
    </nav>

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

    <!-- Main Content -->
    <div class="container">
        <h2><i class="bi bi-people"></i> Delete User</h2>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['role']; ?></td>
                            <td>
                                <a href="delete.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        // Dark Mode Toggle Logic
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