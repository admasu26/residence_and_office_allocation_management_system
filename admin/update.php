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

// Handle form submission
if (isset($_POST["submit"])) {
    $user_id = $_POST["user_id"];
    $new_username = $_POST["username"];
    $password = $_POST["password"]; // Update password in plain text (not recommended for production)
    $role = $_POST["role"];

    // Check if the new username already exists (excluding the current user)
    $check_sql = "SELECT id FROM users WHERE username = '$new_username' AND id != $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username already exists! Please choose a different username.');</script>";
    } else {
        // Update the user in the database
        $sql = "UPDATE users SET username = '$new_username', password = '$password', role = '$role' WHERE id = $user_id";
        if ($conn->query($sql)) {
            echo "<script>alert('User updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating user: " . $conn->error . "');</script>";
        }
    }
}

// Fetch all users for the dropdown
$search = isset($_GET["search"]) ? $_GET["search"] : "";
$role_filter = isset($_GET["role_filter"]) ? $_GET["role_filter"] : "";

$sql = "SELECT id, username, role FROM users WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND username LIKE '%$search%'";
}
if (!empty($role_filter)) {
    $sql .= " AND role = '$role_filter'";
}

$users_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
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

        .sidebar h2 {
          color: #ffffff; /* White text for light mode */
            font-size: 1.5rem; /* Adjust as needed */
          line-height: 1.5; /* Ensure proper spacing */
            margin-bottom: 10px; /* Add spacing between headings */
}

[data-theme="dark"] .sidebar h2 {
    color: #e0e0e0; /* Light gray text for dark mode */
}

        /* Main Content */
        .container {
            margin-top: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-container {
            background-color: var(--card-bg);
            color: var(--card-text);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .form-label {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .form-control {
            padding: 12px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1px solid var(--table-border-color);
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover-color);
        }

        .search-filter {
            margin-bottom: 30px;
        }

        .search-filter input, .search-filter select {
            margin-right: 10px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--table-border-color);
        }

        .search-filter button {
            padding: 10px 20px;
            border-radius: 8px;
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
            --primary-color: #3b82f6; /* Blue */
            --primary-hover-color: #2563eb; /* Darker Blue */
            --table-border-color: #333;
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
            --primary-color: #1e3a8a; /* Blue */
            --primary-hover-color: #0d2a5e; /* Darker Blue */
            --table-border-color: #ddd;
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
        <div class="form-container">
            <h2><i class="bi bi-pencil-square"></i> Update User</h2>

            <!-- Search and Filter Form -->
            <form action="update.php" method="GET" class="search-filter mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" placeholder="Search by username" value="<?php echo $search; ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="role_filter">
                            <option value="">Filter by role</option>
                            <option value="admin" <?php echo ($role_filter == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="allocation_committee" <?php echo ($role_filter == 'allocation_committee') ? 'selected' : ''; ?>>Allocation Committee</option>
                            <option value="staff_member" <?php echo ($role_filter == 'staff_member') ? 'selected' : ''; ?>>Staff Member</option>
                            <option value="managing_director" <?php echo ($role_filter == 'managing_director') ? 'selected' : ''; ?>>Managing Director</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>

            <!-- Update User Form -->
            <form action="update.php" method="POST">
                <!-- Select User -->
                <div class="mb-4">
                    <label for="user_id" class="form-label">Select User</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <?php while ($row = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['username']; ?> (<?php echo $row['role']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Username Field -->
                <div class="mb-4">
                    <label for="username" class="form-label">New Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <!-- Password Field -->
                <div class="mb-4">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <!-- Role Field -->
                <div class="mb-4">
                    <label for="role" class="form-label">New Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="allocation_committee">Allocation Committee</option>
                        <option value="staff_member">Staff Member</option>
                        <option value="managing_director">Managing Director</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update User
                </button>
            </form>
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