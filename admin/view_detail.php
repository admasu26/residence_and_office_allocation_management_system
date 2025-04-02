<?php
session_start(); // Start the session

// Check if the user is logged in and has the role 'admin' or 'allocation_committee'
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'allocation_committee')) {
    // Redirect to the login page if not logged in or not authorized
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

// Handle approval or rejection of staff member details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    if (isset($_POST['approve'])) {
        // Approve the staff member
        $sql = "UPDATE user_detail SET is_approved = 1, is_rejected = 0 WHERE user_id = ?";
        $message = "Staff member approved successfully!";
    } elseif (isset($_POST['reject'])) {
        // Reject the staff member
        $sql = "UPDATE user_detail SET is_rejected = 1, is_approved = 0 WHERE user_id = ?";
        $message = "Staff member rejected successfully!";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('$message');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Fetch all staff member details
$sql = "SELECT * FROM user_detail";
$result = $conn->query($sql);

$staff_details = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staff_details[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
            --secondary-color: #3b82f6; /* Light Blue */
            --accent-color: #10b981; /* Green */
            --warning-color: #f59e0b; /* Orange */
            --danger-color: #ef4444; /* Red */
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
            --secondary-color: #1e3a8a; /* Blue */
            --accent-color: #10b981; /* Green */
            --warning-color: #f59e0b; /* Orange */
            --danger-color: #ef4444; /* Red */
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

        /* Staff List */
        .staff-list {
            margin-top: 20px;
        }

        .staff-item {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 10px;
            padding: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
            border-left: 5px solid var(--primary-color); /* Add a colored border */
        }

        .staff-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-color); /* Change border color on hover */
        }

        .staff-details {
            display: none;
            padding: 15px;
            background: var(--card-bg);
            border-radius: 10px;
            margin-top: 10px;
            border-left: 5px solid var(--warning-color); /* Add a colored border */
        }

        .staff-details p {
            margin: 0;
            padding: 5px 0;
        }

        
        /* Search Bar */
        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: var(--card-bg);
            color: var(--card-text);
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
            }

            .header {
                left: 0;
            }
        }

/* Approve Button */
.approve-button {
    background-color: #10b981; /* Green */
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-right: 10px;
}

.approve-button:hover {
    background-color: #0d9488; /* Darker green */
}

.approve-button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Reject Button */
.reject-button {
    background-color: #ef4444; /* Red */
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.reject-button:hover {
    background-color: #dc2626; /* Darker red */
}

.reject-button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Filter Section */
.filter-section {
    margin-bottom: 20px;
}

.filter-section label {
    font-weight: 500;
    margin-right: 10px;
}

.filter-section select {
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: var(--card-bg);
    color: var(--card-text);
    font-size: 16px;
    cursor: pointer;
}

.filter-section select:focus {
    border-color: var(--primary-color);
    outline: none;
}
/* Submenu Styling */
.submenu {
    display: none;
    padding-left: 20px;
}

.sidebar ul li:hover .submenu {
    display: block;
}

.submenu li {
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.submenu li a {
    font-size: 14px;
    color: var(--sidebar-text);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.submenu li a:hover {
    color: #fff;
}

    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo text-center">
            <img src="logo.png" alt="Logo">
        </div>
        <h2 class="text-center">SRAM</h2>
        <h2 class="text-center"><?= $_SESSION['role'] === 'admin' ? 'Admin' : 'Allocation Committee'; ?> Dashboard</h2>
        <ul class="list-unstyled">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Admin Sidebar -->
                <li><a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li> 
                <li><a href="create.php" class="d-flex align-items-center"><i class="bi bi-person-plus"></i> Create User</a></li>
                <li><a href="delete.php" class="d-flex align-items-center"><i class="bi bi-trash"></i> Delete User</a></li>
                <li><a href="update.php" class="d-flex align-items-center"><i class="bi bi-pencil-square"></i> Update User</a></li>
                <li><a href="permission.php" class="d-flex align-items-center"><i class="bi bi-shield-lock"></i> Manage Permissions</a></li>
            <?php else: ?>
                <!-- Allocation Committee Sidebar -->
                <li><a href="residence_allocate.php" class="d-flex align-items-center"><i class="bi bi-house"></i> Allocate Residence</a></li>
                <li><a href="office_allocation.php" class="d-flex align-items-center"><i class="bi bi-building"></i> Allocate Office</a></li>
                <li><a href="manage_allocation_request.php" class="d-flex align-items-center"><i class="bi bi-list-check"></i> Manage Allocation Request</a></li>
                <li><a href="track_usage.php" class="d-flex align-items-center"><i class="bi bi-bar-chart"></i> Track Resource Usage</a></li>
                <li><a href="solve_appeal.php" class="d-flex align-items-center"><i class="bi bi-check-circle"></i> Approve/Reject Appeal</a></li>
                <li><a href="send_report.php" class="d-flex align-items-center"><i class="bi bi-file-earmark-text"></i> Generate Report</a></li>
                <li><a href="view_maintenance_history.php" class="d-flex align-items-center"><i class="bi bi-clock-history"></i> View Maintenance History</a></li>
                <li><a href="track_resolve_maintenance.php" class="d-flex align-items-center"><i class="bi bi-tools"></i> Track & Resolve Maintenance Issues</a></li>
            <?php endif; ?>
            <!-- Shared Option -->
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
        <h1 class="mt-4">Staff Member Details</h1>
       
        <!-- Search Bar -->
        <div class="search-bar mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name..." oninput="filterStaff()">
        </div>
        <!-- Filter Dropdown -->
        <div class="filter-section mb-3">
            <label for="filterStatus" class="form-label">Filter by Status:</label>
            <select id="filterStatus" class="form-select" onchange="filterByStatus()">
                <option value="all">All</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
            </select>
        </div>

       <!-- Staff List -->
        <div class="staff-list" id="staffList">
            <?php if (!empty($staff_details)): ?>
                <?php foreach ($staff_details as $staff): ?>
                    <?php
                    // Determine the status of the staff member
                    $status = 'pending';
                    if (isset($staff['is_approved']) && $staff['is_approved']) {
                        $status = 'approved';
                    } elseif (isset($staff['is_rejected']) && $staff['is_rejected']) {
                        $status = 'rejected';
                    }
                    ?>
                    <div class="staff-item" onclick="toggleDetails(this)" data-status="<?= $status; ?>">
                        <strong><?= htmlspecialchars($staff['name']); ?></strong>
                        <div class="staff-details">
                            <p><strong>Gender:</strong> <?= htmlspecialchars($staff['gender']); ?></p>
                            <p><strong>College:</strong> <?= htmlspecialchars($staff['college']); ?></p>
                            <p><strong>Department:</strong> <?= htmlspecialchars($staff['department']); ?></p>
                            <p><strong>Employment Date:</strong> <?= htmlspecialchars($staff['employment_date']); ?></p>
                            <p><strong>Academic Rank:</strong> <?= htmlspecialchars($staff['academic_rank']); ?></p>
                            <p><strong>Work Range:</strong> <?= htmlspecialchars($staff['work_range']); ?></p>
                            <p><strong>Marital Status:</strong> <?= htmlspecialchars($staff['marital_status']); ?></p>
                            <p><strong>Children:</strong> <?= htmlspecialchars($staff['children']); ?></p>
                            <p><strong>Spouse:</strong> <?= htmlspecialchars($staff['spouse']); ?></p>
                            <p><strong>Disability:</strong> <?= htmlspecialchars($staff['disability']); ?></p>
                            <p><strong>SOAMU:</strong> <?= htmlspecialchars($staff['soamu']); ?></p>
                            <p><strong>Current Address:</strong> <?= htmlspecialchars($staff['current_address']); ?></p>
                            <p><strong>Unit Type:</strong> <?= htmlspecialchars($staff['unit_type']); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($staff['email']); ?></p>
                            <p><strong>Phone (Mobile):</strong> <?= htmlspecialchars($staff['phone_mobile']); ?></p>
                            
                            <!-- Approve/Reject Buttons -->
                            <form method="POST" action="" class="mt-3">
                                <input type="hidden" name="user_id" value="<?= $staff['user_id']; ?>">
                                <button type="submit" name="approve" class="approve-button" <?= isset($staff['is_approved']) && $staff['is_approved'] ? 'disabled' : ''; ?>>
                                    <?= isset($staff['is_approved']) && $staff['is_approved'] ? 'Approved' : 'Approve'; ?>
                                </button>
                                <button type="submit" name="reject" class="reject-button" <?= isset($staff['is_rejected']) && $staff['is_rejected'] ? 'disabled' : ''; ?>>
                                    <?= isset($staff['is_rejected']) && $staff['is_rejected'] ? 'Rejected' : 'Reject'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No staff details found.
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

        // Toggle Staff Details
        function toggleDetails(element) {
            const details = element.querySelector('.staff-details');
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }

        // Filter Staff Members
        function filterStaff() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const staffList = document.getElementById('staffList').getElementsByClassName('staff-item');

            for (let i = 0; i < staffList.length; i++) {
                const name = staffList[i].getElementsByTagName('strong')[0].textContent.toLowerCase();
                if (name.includes(input)) {
                    staffList[i].style.display = '';
                } else {
                    staffList[i].style.display = 'none';
                }
            }
        }

        // Filter Staff Members by Status
        function filterByStatus() {
            const filterStatus = document.getElementById('filterStatus').value;
            const staffList = document.getElementById('staffList').getElementsByClassName('staff-item');

            for (let i = 0; i < staffList.length; i++) {
                const status = staffList[i].getAttribute('data-status');
                if (filterStatus === 'all' || status === filterStatus) {
                    staffList[i].style.display = '';
                } else {
                    staffList[i].style.display = 'none';
                }
            }
        }

        // Initial filter on page load
        filterByStatus();
    </script>
</body>
</html>