<?php
session_start(); // Start the session

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if not logged in or not an admin
    header('location: log.php');
    exit(); // Ensure no further code is executed after the redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            --sidebar-width: 250px;
            --sidebar-min-width: 200px;
            --sidebar-max-width: 350px;
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
            width: var(--sidebar-width);
            min-width: var(--sidebar-min-width);
            max-width: var(--sidebar-max-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
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

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
        }

        .sidebar ul li a i {
            min-width: 24px;
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
            margin-left: calc(var(--sidebar-width) + 20px);
            padding: 100px 30px 30px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }

        .main-content-expanded {
            margin-left: 20px;
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
            gap: 25px;
            margin-top: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            color: var(--card-text);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 50px;
            color: #1e3a8a;
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
        }

        /* Responsive Design */
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-resize-handle" id="resizeHandle"></div>
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
    <div class="main-content container" id="mainContent">
        <h1 class="mt-4">Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! You have admin privileges.</p>

        <!-- Cards Section -->
        <div class="cards">
            <div class="card" onclick="window.location.href='create.php'">
                <i class="bi bi-person-plus"></i>
                <h3>Create User</h3>
                <p>Add new users to the system.</p>
            </div>
            <div class="card" onclick="window.location.href='delete.php'">
                <i class="bi bi-trash"></i>
                <h3>Delete User</h3>
                <p>Remove users from the system.</p>
            </div>
            <div class="card" onclick="window.location.href='update.php'">
                <i class="bi bi-pencil-square"></i>
                <h3>Update User</h3>
                <p>Edit user details and information.</p>
            </div>
            <div class="card" onclick="window.location.href='permission.php'">
                <i class="bi bi-shield-lock"></i>
                <h3>Manage Permissions</h3>
                <p>Control user access and permissions.</p>
            </div>
            <div class="card" onclick="window.location.href='view_detail.php'">
                <i class="bi bi-person-vcard"></i>
                <h3>View Detail Staff</h3>
                <p>View Detail Information About Staff Memember.</p>
            </div>
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

        // Sidebar Toggle Functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        const mainContent = document.getElementById('mainContent');
        const header = document.getElementById('header');

        // Check for saved sidebar state in localStorage
        const savedSidebarState = localStorage.getItem('sidebarState');
        if (savedSidebarState === 'collapsed') {
            sidebar.classList.add('sidebar-collapsed');
            mainContent.classList.add('main-content-expanded');
            header.classList.add('header-expanded');
            toggleIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
        }

        // Toggle Sidebar
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-expanded');
            header.classList.toggle('header-expanded');
            
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