<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_appeal'])) {
    $username = $_SESSION['username']; // Get the username from the session
    $appeal_reason = $_POST['appeal_reason'];
    $allocation_type = $_POST['allocation_type']; // Get the selected allocation type

    // Insert the appeal into the database
    $sql = "INSERT INTO appeals (username, appeal_reason, allocation_type, status) 
            VALUES (?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $appeal_reason, $allocation_type);

    if ($stmt->execute()) {
        $_SESSION["message"] = "Appeal submitted successfully.";
    } else {
        $_SESSION["error"] = "Error: " . $conn->error;
    }
    $stmt->close();

    // Redirect to the same page to display the message
    header("Location: submit_appeal.php");
    exit();
}

// Fetch the user's allocation details (both residence and office) using their username
$username = $_SESSION['username'];

// Fetch residence allocations
$residence_sql = "SELECT a.allocated_to_name, a.campus, a.building, a.floor, a.room_number, r.resource_type, 'Residence' AS type 
                  FROM allocations a
                  JOIN resources r ON a.resource_id = r.id
                  WHERE a.allocated_to_username = ?";
$stmt = $conn->prepare($residence_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$residence_result = $stmt->get_result();
$stmt->close();

// Fetch office allocations
$office_sql = "SELECT a.allocated_to_name, a.campus, a.building, a.floor, a.room_number, r.resource_type, 'Office' AS type 
               FROM office_allocation a
               JOIN office_resource r ON a.resource_id = r.id
               WHERE a.allocated_to_username = ?";
$stmt = $conn->prepare($office_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$office_result = $stmt->get_result();
$stmt->close();

// Combine both results into a single array
$allocations = [];
while ($row = $residence_result->fetch_assoc()) {
    $allocations[] = $row;
}
while ($row = $office_result->fetch_assoc()) {
    $allocations[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Appeal</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
    height: 100vh;
    background-color: var(--bg-color);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
}

/* Light Mode Variables */
:root {
    --bg-color: #f4f4f4; /* Light background */
    --text-color: #333; /* Dark text */
    --sidebar-bg: linear-gradient(180deg, #1e3a8a, #0d1b2a);
    --card-bg: white; /* Light card background */
    --card-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    --table-head-bg: #1e3a8a; /* Dark blue for table headers */
    --table-head-color: white; /* White text for table headers */
    --table-row-hover: #f8f9fa; /* Light hover for table rows */
    --form-control-bg: white; /* Light background for form controls */
    --form-control-text: #333; /* Dark text for form controls */
}

/* Dark Mode Variables */
body.dark-mode {
    --bg-color: #1a1a1a; /* Dark background */
    --text-color: #f4f4f4; /* Light text */
    --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1a1a1a);
    --card-bg: #2d2d2d; /* Dark card background */
    --card-shadow: 0px 4px 8px rgba(255, 255, 255, 0.1);
    --table-head-bg: #0d1b2a; /* Dark blue for table headers */
    --table-head-color: #f4f4f4; /* Light text for table headers */
    --table-row-hover: #333; /* Dark hover for table rows */
    --form-control-bg: #2d2d2d; /* Dark background for form controls */
    --form-control-text:rgb(250, 239, 239); /* Light text for form controls */
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
    padding: 20px;
    flex-grow: 1;
    position: relative;
}

/* Top-Right Section (Profile) */
.top-right-section {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 1000;
}

/* Profile Dropdown */
.profile-dropdown {
    position: relative;
}

.profile-dropdown .dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: transparent;
    border: none;
    cursor: pointer;
}

.profile-dropdown .dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    z-index: 1000;
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

/* Table Styling */
.table {
    width: 100%;
    margin-bottom: 1rem;
    color: var(--text-color);
    background-color: var(--card-bg);
    border-collapse: collapse;
}

.table thead {
    background-color: var(--table-head-bg);
    color: var(--table-head-color);
}

.table th,
.table td {
    padding: 12px;
    vertical-align: middle;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.table tbody tr:hover {
    background-color: var(--table-row-hover);
}

/* Form Styling */
.form-label {
    font-weight: bold;
    color: var(--text-color);
}

.form-control {
    background-color: var(--form-control-bg);
    color: var(--form-control-text);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.form-control:focus {
    background-color: var(--form-control-bg);
    color: var(--form-control-text);
    border-color: #1e3a8a;
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
}

/* Alert Styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

/* Button Styling */
.btn-primary {
    background-color: #1e3a8a;
    border-color: #1e3a8a;
    color: white;
    transition: background-color 0.3s, border-color 0.3s;
}

.btn-primary:hover {
    background-color: #153061;
    border-color: #153061;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
    }

    .top-right-section {
        right: 10px;
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
                    <li><a href="office_allocate.php"><i class="fas fa-building"></i> Office allocation request</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Profile Section -->
        <div class="top-right-section">
            <!-- Profile Dropdown -->
            <div class="dropdown profile-dropdown">
                <a class="btn btn-light dropdown-toggle d-flex align-items-center" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person me-2"></i>View Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Appeal Form -->
        <h1><i class="fas fa-exclamation-circle"></i> Submit Appeal</h1>
        <?php
        if (isset($_SESSION["message"])) {
            echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> " . $_SESSION["message"] . "</div>";
            unset($_SESSION["message"]);
        }
        if (isset($_SESSION["error"])) {
            echo "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> " . $_SESSION["error"] . "</div>";
            unset($_SESSION["error"]);
        }
        ?>

        <!-- Allocation Details -->
<h2><i class="fas fa-list"></i> Your Allocation Details</h2>
<div class="row">
    <?php
    if (!empty($allocations)) {
        foreach ($allocations as $row) {
            echo '
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user"></i> ' . htmlspecialchars($row['allocated_to_name']) . '</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><i class="fas fa-university"></i> Campus: ' . htmlspecialchars($row['campus']) . '</li>
                            <li class="list-group-item"><i class="fas fa-building"></i> Building: ' . htmlspecialchars($row['building']) . '</li>
                            <li class="list-group-item"><i class="fas fa-layer-group"></i> Floor: ' . htmlspecialchars($row['floor']) . '</li>
                            <li class="list-group-item"><i class="fas fa-door-open"></i> Room Number: ' . htmlspecialchars($row['room_number']) . '</li>
                            <li class="list-group-item"><i class="fas fa-home"></i> Resource Type: ' . htmlspecialchars($row['resource_type']) . '</li>
                            <li class="list-group-item"><i class="fas fa-info-circle"></i> Allocation Type: ' . htmlspecialchars($row['type']) . '</li>
                        </ul>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo '<div class="col-12 text-center"><p>No allocations found.</p></div>';
    }
    ?>
</div>
        <!-- Appeal Form -->
        <h2><i class="fas fa-edit"></i> Appeal Form</h2>
        <form method="POST" action="">
        <div class="mb-3">
                <label for="name" class="form-label"><i class="fas fa-user"></i> username:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="allocation_type" class="form-label"><i class="fas fa-home"></i> Allocation Type:</label>
                <select name="allocation_type" class="form-select" required>
                    <option value="Residence">Residence</option>
                    <option value="Office">Office</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="appeal_reason" class="form-label"><i class="fas fa-comment"></i> Reason for Appeal:</label>
                <textarea name="appeal_reason" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" name="submit_appeal" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Appeal
            </button>
        </form>
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