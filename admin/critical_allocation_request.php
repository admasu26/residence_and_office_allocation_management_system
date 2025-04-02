<?php
session_start(); // Start the session
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $userName = $_POST['userName'];
    $residenceType = $_POST['residenceType'];
    $officeType = $_POST['officeType'];
    $reason = $_POST['reason'];

    // Validate input
    if (empty($userName) || empty($residenceType) || empty($officeType) || empty($reason)) {
        $_SESSION["error"] = "All fields are required.";
    } else {
        // Insert the request into the database
        $sql = "INSERT INTO critical_allocation_requests (user_name, resource_type, preferred_residence, preferred_office, reason, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssss", $userName, $resourceType, $residenceType, $officeType, $reason);
            $resourceType = 'residence'; // Default resource type (can be adjusted based on form logic)
            if ($stmt->execute()) {
                $_SESSION["message"] = "Critical allocation request submitted successfully.";
            } else {
                $_SESSION["error"] = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION["error"] = "Error preparing the SQL statement.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocation Committee</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            position: relative; /* Required for positioning the submenu */
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
            display: block; /* Show submenu when active */
        }

        /* Content Area */
        .main-content {
            margin-left: 260px;
            padding: 100px 20px 20px; /* Added top padding to avoid header overlap */
            flex-grow: 1;
            position: relative; /* Required for absolute positioning of the profile dropdown */
        }

        /* Header */
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px; /* Space between theme toggle and profile dropdown */
            padding: 10px 20px;
            background-color: var(--bg-color);
            position: fixed;
            top: 0;
            right: 0;
            left: 260px;
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

        /* Cards Section */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            color: var(--card-text);
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
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
            text-align: center;
            padding: 10px 20px;
            margin-left: 260px;
            position: fixed;
            bottom: 0;
            width: calc(100% - 260px);
            z-index: 1000;
        }

        /* Critical Allocation Request Form */
.allocation-form {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    width: 100%; /* Take up full width */
    margin: 20px 0; /* Remove auto margin to align with the edges */
}

.allocation-form label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    color: var(--text-color);
}

.allocation-form input[type="text"],
.allocation-form select,
.allocation-form textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    background-color: var(--bg-color);
    color: var(--text-color);
}

.allocation-form input[type="text"]:focus,
.allocation-form select:focus,
.allocation-form textarea:focus {
    border-color: #2575fc;
    outline: none;
}

.allocation-form textarea {
    resize: vertical;
    min-height: 150px; /* Increased height for the textarea */
}

.allocation-form button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

.allocation-form button:hover {
    background: linear-gradient(135deg, #2575fc, #6a11cb);
    transform: translateY(-2px);
}

.allocation-form button:active {
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 768px) {
    .allocation-form {
        padding: 20px; /* Adjust padding for smaller screens */
    }
}
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 210px;
                padding: 120px 20px 20px; /* Adjusted for smaller screens */
            }

            .header {
                left: 210px;
            }

            .footer {
                margin-left: 210px;
                width: calc(100% - 210px);
            }
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
            
        <li>
                <a href="#"><i class="bi bi-check-square"></i> Manage Allocation Request</a>
                <ul class="submenu">
                    <li><a href="manage_allocation_request.php"><i class="bi bi-house-door"></i> Residence Manage Request</a></li>
                    <li><a href="manage_office_allocation_request.php"><i class="bi bi-building"></i> Office Manage Request</a></li>
                </ul>
            </li>

            <li>
                <a href="#"><i class="bi bi-house-door"></i> Allocate Residence & Office</a>
                <ul class="submenu">
                    <li><a href="residence_allocate.php"><i class="bi bi-house-door"></i> Residence Allocate</a></li>
                    <li><a href="office_allocation.php"><i class="bi bi-building"></i> Office Allocate</a></li>
                </ul>
            </li>
            
            <li>
                <a href="#"><i class="bi bi-building"></i> Track Resource Usage</a>
                <ul class="submenu">
                    <li><a href="track_usage.php"><i class="bi bi-house-door"></i> Residence Track Resource</a></li>
                    <li><a href="office_track_usage.php"><i class="bi bi-building"></i> Office Track Resource</a></li>
                </ul>
            </li>
            <li>
                <a href="solve_appeal.php"><i class="bi bi-arrow-left-right"></i> Approve/Reject Appeal</a>
                
            </li>
           
            
            
            <li>
              <a href="critical_allocation_request.php"><i class="bi bi-exclamation-triangle"></i> Critical Allocation Request</a>
             </li>
             <li>
              <a href="view_critical_allocation_request_result.php"><i class="bi bi-list-check"></i> View Critical Allocation Request Result</a>
             </li>
             <li><a href="track_resolve_maintenance.php"><i class="bi bi-tools"></i> Track & Resolve Maintenance Issues</a></li>
             <li><a href="view_maintenance_history.php"><i class="bi bi-cash"></i> View Maintenance History</a></li>
             <li>
                <a href="#"><i class="bi bi-file-earmark-text"></i> Generate Report</a>
                <ul class="submenu">
                    <li><a href="send_report.php"><i class="bi bi-check-square"></i> Residence Report</a></li>
                    <li><a href="send_office_report.php"><i class="bi bi-x-square"></i> Office Report</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
<div class="main-content">
    <h1><i class="bi bi-speedometer2"></i> Critical Allocation Request</h1>
    <p>Submit a critical allocation request for exceptional cases.</p>

    <!-- Critical Allocation Request Form -->
    <div class="allocation-form">
        <form id="criticalAllocationForm" action="critical_allocation_request.php" method="POST">
            <!-- Preferred Residence Type -->
            <div class="form-group">
                <label for="residenceType">Preferred Residence Type:</label>
                <select id="residenceType" name="residenceType" required>
                    <option value="three_bedroom">Three-Bedroom</option>
                    <option value="two_bedroom">Two-Bedroom</option>
                    <option value="one_bedroom">One-Bedroom</option>
                    <option value="studio">Studio</option>
                    <option value="service">Service Quarters</option>
                </select>
            </div>

            <!-- Preferred Office Type -->
            <div class="form-group">
                <label for="officeType">Preferred Office Type:</label>
                <select id="officeType" name="officeType" required>
                    <option value="open-space">Open Space</option>
                    <option value="private-office">Private Office</option>
                    <option value="shared-office">Shared Office</option>
                </select>
            </div>

            <!-- Allocation User Name -->
            <div class="form-group">
                <label for="userName">Allocation User Name:</label>
                <input type="text" id="userName" name="userName" required>
            </div>

            <!-- Reason for Request -->
            <div class="form-group">
                <label for="reason">Reason for Request:</label>
                <textarea id="reason" name="reason" rows="6" required></textarea>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit">Submit Request</button>
            </div>
        </form>
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

        // JavaScript to toggle submenu on touch (click)
        document.querySelectorAll('.sidebar ul li').forEach(li => {
            li.addEventListener('click', function (e) {
                // Check if the clicked element is a link inside the submenu
                if (e.target.tagName === 'A' && e.target.parentElement.classList.contains('submenu')) {
                    return; // Do nothing if the clicked element is a submenu link
                }

                // Toggle the submenu
                const submenu = this.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            });
        });
    </script>
</body>
</html>