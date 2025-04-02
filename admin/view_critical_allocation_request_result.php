<?php
session_start();

// Check if the user is logged in and has the role 'allocation_committee'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'allocation_committee') {
    header('location: log.php');
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status'];

    // Update the status in the database
    $update_sql = "UPDATE critical_allocation_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $request_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to refresh the page and avoid form resubmission
    header("Location: view_critical_allocation_request_result.php");
    exit();
}

// Fetch all requests (approved, rejected, and pending)
$sql = "SELECT * FROM critical_allocation_requests ORDER BY created_at DESC";
$requests_result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        }

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
            height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 100px 20px 20px; /* Added top padding to avoid header overlap */
            flex-grow: 1;
        }

        /* Table Styling */
        .table-responsive {
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table-striped tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
        }

        .table th {
            background-color: var(--sidebar-bg);
            color: white;
        }

        /* Status Colors */
        .status-approved {
            color: #28a745; /* Green for approved */
        }

        .status-rejected {
            color: #dc3545; /* Red for rejected */
        }

        .status-pending {
            color: #ffc107; /* Yellow for pending */
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>
<body>
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

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2>SRAM</h2>
        <ul>
        <li>
                <a href="allocation_committee_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
           
            <li><a href="view_critical_allocation_request_result.php"><i class="bi bi-file-earmark-text"></i> View Critical Request Result</a></li>
            <li><a href="critical_allocation_request.php"><i class="bi bi-exclamation-triangle"></i> Critical Allocation Request</a></li>
            <!-- Add other sidebar links as needed -->
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="mb-4"><i class="bi bi-clipboard-check"></i> View Request Result</h1>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Resource Type</th>
                        <th>Preferred Residence</th>
                        <th>Preferred Office</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($requests_result->num_rows > 0) {
                        while ($row = $requests_result->fetch_assoc()) {
                            // Determine status color
                            $status_class = '';
                            if ($row['status'] === 'Approved') {
                                $status_class = 'status-approved';
                            } elseif ($row['status'] === 'Rejected') {
                                $status_class = 'status-rejected';
                            } else {
                                $status_class = 'status-pending';
                            }

                            echo "<tr class='fade-in'>
                                    <td>{$row['id']}</td>
                                    <td>{$row['user_name']}</td>
                                    <td>{$row['resource_type']}</td>
                                    <td>{$row['preferred_residence']}</td>
                                    <td>{$row['preferred_office']}</td>
                                    <td>{$row['reason']}</td>
                                    <td class='{$status_class}'>{$row['status']}</td>
                                    <td>{$row['created_at']}</td>
                                    <td>
                                        <form method='POST' action='view_critical_allocation_request_result.php' style='display:inline;'>
                                            <input type='hidden' name='request_id' value='{$row['id']}'>
                                            <select name='status' onchange='this.form.submit()'>
                                                <option value='Pending' " . ($row['status'] === 'Pending' ? 'selected' : '') . ">Pending</option>
                                                <option value='Approved' " . ($row['status'] === 'Approved' ? 'selected' : '') . ">Allocated</option>
                                                <option value='Rejected' " . ($row['status'] === 'Rejected' ? 'selected' : '') . ">Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No requests found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?= date('Y') ?> Resource Management System. All rights reserved.
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