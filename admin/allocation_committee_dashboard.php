<?php
session_start(); // Start the session

// Check if the user is logged in and has the role 'allocation_committee'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'allocation_committee') {
    // Redirect to the login page if not logged in or not an allocation committee member
    header('location: log.php');
    exit(); // Ensure no further code is executed after the redirect
}
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
            --sidebar-width: 250px;
            --sidebar-min-width: 200px;
            --sidebar-max-width: 350px;
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
            width: var(--sidebar-width);
            min-width: var(--sidebar-min-width);
            max-width: var(--sidebar-max-width);
            height: 100%;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: width 0.3s ease, transform 0.3s ease;
            z-index: 1001;
        }

        .sidebar-collapsed {
            transform: translateX(calc(-1 * var(--sidebar-width)));
        }

        .sidebar-resize-handle {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 10px;
            cursor: col-resize;
            z-index: 1002;
        }

        .sidebar-toggle {
            position: fixed;
            left: var(--sidebar-width);
            top: 50%;
            transform: translateY(-50%);
            background: var(--sidebar-bg);
            color: white;
            border: none;
            width: 20px;
            height: 60px;
            cursor: pointer;
            z-index: 1000;
            border-radius: 0 5px 5px 0;
            transition: left 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-collapsed + .sidebar-toggle {
            left: 0;
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
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            min-width: 24px;
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
            display: block;
        }

        /* Content Area */
        .main-content {
            margin-left: calc(var(--sidebar-width) + 20px);
            padding: 100px 20px 20px;
            flex-grow: 1;
            position: relative;
            transition: margin-left 0.3s ease;
        }

        .main-content-expanded {
            margin-left: 20px;
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
            left: var(--sidebar-width);
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: left 0.3s ease;
        }

        .header-expanded {
            left: 0;
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
            margin-left: var(--sidebar-width);
            position: fixed;
            bottom: 0;
            width: calc(100% - var(--sidebar-width));
            z-index: 1000;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .footer-expanded {
            margin-left: 0;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 200px;
                --sidebar-min-width: 150px;
            }

            .sidebar-collapsed {
                transform: translateX(calc(-1 * var(--sidebar-width)));
            }

            .sidebar-toggle {
                left: var(--sidebar-width);
            }

            .main-content {
                margin-left: calc(var(--sidebar-width) + 20px);
                padding: 120px 20px 20px;
            }

            .header {
                left: var(--sidebar-width);
            }

            .footer {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-resize-handle" id="resizeHandle"></div>
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
            <li><a href="view_detail.php" class="d-flex align-items-center"><i class="bi bi-person-vcard"></i> View Staff Details</a></li>
            <li>
                <a href="#"><i class="bi bi-file-earmark-text"></i> Generate Report</a>
                <ul class="submenu">
                    <li><a href="send_report.php"><i class="bi bi-check-square"></i> Residence Report</a></li>
                    <li><a href="send_office_report.php"><i class="bi bi-x-square"></i> Office Report</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>

    <!-- Header -->
    <div class="header" id="header">
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
    <div class="main-content" id="mainContent">
        <h1><i class="bi bi-speedometer2"></i> Allocation Committee Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! You have allocation committee privileges.</p>

        <!-- Cards Section -->
        <div class="cards">
            <div class="card" onclick="window.location.href='residence_allocate.php'">
                <i class="bi bi-house-door"></i>
                <h3>Residence Allocation</h3>
                <p>Allocate residential resources to staff members.</p>
            </div>
            <div class="card" onclick="window.location.href='office_allocation.php'">
                <i class="bi bi-building"></i>
                <h3>Office Allocation</h3>
                <p>Allocate office spaces to staff members.</p>
            </div>
            <div class="card" onclick="window.location.href='manage_allocation_request.php'">
                <i class="bi bi-check-square"></i>
                <h3>Manage Requests</h3>
                <p>Review and manage allocation requests.</p>
            </div>
            <div class="card" onclick="window.location.href='office_track_usage.php'">
                <i class="bi bi-graph-up-arrow"></i>
                <h3>Office Track Resource</h3>
                <p>Monitor office resource usage and availability.</p>
            </div>
            <div class="card" onclick="window.location.href='track_usage.php'">
                <i class="bi bi-house-check"></i>
                <h3>Residence Track Resource</h3>
                <p>Monitor residential resource usage and availability.</p>
            </div>
            <div class="card" onclick="window.location.href='send_report.php'">
                <i class="bi bi-file-earmark-text"></i>
                <h3>Generate Report</h3>
                <p>Generate reports for allocation details.</p>
            </div>
            <div class="card" onclick="window.location.href='view_maintenance_history.php'">
                <i class="bi bi-cash"></i>
                <h3>View Maintenance History</h3>
                <p>View the history of maintenance activities.</p>
            </div>
            <div class="card" onclick="window.location.href='track_resolve_maintenance.php'">
                <i class="bi bi-tools"></i>
                <h3>Track & Resolve Issues</h3>
                <p>Track and resolve maintenance issues.</p>
            </div>
            <div class="card" onclick="window.location.href='solve_appeal.php'">
                <i class="bi bi-arrow-left-right"></i>
                <h3>Approve/Reject Appeal</h3>
                <p>Approve or reject appeals from staff members.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer" id="footer">
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

        // Sidebar Toggle Functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        const mainContent = document.getElementById('mainContent');
        const header = document.getElementById('header');
        const footer = document.getElementById('footer');

        // Check for saved sidebar state in localStorage
        const savedSidebarState = localStorage.getItem('sidebarState');
        if (savedSidebarState === 'collapsed') {
            sidebar.classList.add('sidebar-collapsed');
            mainContent.classList.add('main-content-expanded');
            header.classList.add('header-expanded');
            footer.classList.add('footer-expanded');
            toggleIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
        }

        // Toggle Sidebar
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-expanded');
            header.classList.toggle('header-expanded');
            footer.classList.toggle('footer-expanded');
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                toggleIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                toggleIcon.classList.replace('bi-chevron-right', 'bi-chevron-left');
                localStorage.setItem('sidebarState', 'expanded');
            }
        });

        // Sidebar Resize Functionality
        const resizeHandle = document.getElementById('resizeHandle');
        let isResizing = false;
        let lastDownX = 0;
        let initialWidth = 0;

        resizeHandle.addEventListener('mousedown', (e) => {
            isResizing = true;
            lastDownX = e.clientX;
            initialWidth = sidebar.offsetWidth;
            document.body.style.cursor = 'col-resize';
            document.addEventListener('mousemove', handleResize);
            document.addEventListener('mouseup', stopResize);
            e.preventDefault();
        });

        function handleResize(e) {
            if (!isResizing) return;
            
            const newWidth = initialWidth + (e.clientX - lastDownX);
            const minWidth = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--sidebar-min-width'));
            const maxWidth = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--sidebar-max-width'));
            
            if (newWidth >= minWidth && newWidth <= maxWidth) {
                document.documentElement.style.setProperty('--sidebar-width', `${newWidth}px`);
                updateSidebarPosition();
            }
        }

        function stopResize() {
            isResizing = false;
            document.body.style.cursor = '';
            document.removeEventListener('mousemove', handleResize);
            document.removeEventListener('mouseup', stopResize);
        }

        function updateSidebarPosition() {
            const sidebarWidth = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width'));
            sidebarToggle.style.left = `${sidebarWidth}px`;
        }

        // Make sure toggle button stays at the edge of the sidebar when resizing
        document.documentElement.style.setProperty('--sidebar-width', '250px');
    </script>
</body>
</html>