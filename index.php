<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMU</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Body styling with dynamic background images */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-size: cover;
            background-position: center;
            animation: changeBackground 20s infinite; /* Animation to change background images */
        }

        /* Keyframes for background image animation */
        @keyframes changeBackground {
            0%, 25% {
                background-image: url('o.jpg'); /* First image (5 seconds) */
            }
            25.01%, 50% {
                background-image: url('m.jfif'); /* Second image (5 seconds) */
            }
            50.01%, 75% {
                background-image: url('z.jpg'); /* Third image (5 seconds) */
            }
            75.01%, 100% {
                background-image: url('a.jpg'); /* Fourth image (5 seconds) */
            }
        }

        /* Navbar styling */
        nav {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black background */
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #4CAF50; /* Green color on hover */
        }

        nav ul li a.active {
            color: #4CAF50; /* Green color for active link */
        }

        .logo {
            width: 50px; /* Adjust logo size */
            height: auto; /* Maintain aspect ratio */
        }

        /* Content styling */
        .content {
            flex: 1; /* Takes up remaining space */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            padding: 20px;
        }

        .content h2 {
            font-size: 36px;
            margin-bottom: 20px;
            animation: fadeIn 2s ease-in; /* Fade-in animation */
        }

        .content p {
            font-size: 18px;
            margin-bottom: 30px;
            animation: fadeIn 2.5s ease-in; /* Fade-in animation */
        }

        .content button {
            padding: 12px 24px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            animation: fadeIn 3s ease-in; /* Fade-in animation */
        }

        .content button:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        /* Bottom section styling */
        .bottom-section {
            background-color: #fff; /* White background */
            padding: 40px 20px; /* Increased padding */
            text-align: center;
        }

        /* Cards container styling */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between cards */
            max-width: 1200px; /* Maximum width for the cards container */
            margin: 0 auto; /* Center the grid */
        }

        /* Card styling */
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 40px;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        /* Style for <a> tags as buttons */
        a.button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        a.button:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        /* Footer styling */
        footer {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black background */
            color: #fff;
            text-align: center;
            padding: 12px 0; /* Increased padding */
            font-size: 14px;
            animation: fadeIn 4s ease-in; /* Fade-in animation */
        }

        footer p {
            margin: 0;
        }

        /* Keyframes for animations */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <img src="logo.png" alt="AMU Logo" class="logo">
        <h2> AMU SRAMS</h2>
        <ul>
            <li><a class="active" href="index.php">Home</a></li>
            <li><a href="start.php#about">About us</a></li>
            <li><a href="admin\log.php">Login</a></li>
        </ul>
    </nav>

    <!-- Content Section -->
    <div class="content">
        <h2>Welcome to AMU Staff Residence and Office Allocation Management System</h2>
        <p>"Efficient and Fair Housing & Office Allocation for AMU Staff!"</p>
        <button class="link-button" onclick="redirectToStartPage()">Get Started</button>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <div class="cards">
            <div class="card" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i>
                <h3>Home</h3>
                <a href="index.php" class="button">Amu Staff Residence and Office allocation home.</a>
            </div>
            <div class="card" onclick="window.location.href='start.php#about'">
                 <i class="bi bi-info-circle-fill"></i> <!-- About Us icon -->
                <h3>About Us</h3>
                <a href="start.php#about" class="button">Learn more about AMU SRAMS.</a>
           </div>
            <div class="card" onclick="window.location.href='admin/log.php'">
                  <i class="bi bi-person-lines-fill"></i> <!-- Staff Member Dashboard icon -->
                  <h3>Staff Member Dashboard</h3>
                  <a href="admin/log.php" class="button">Access staff member dashboard.</a>
           </div>
            <div class="card" onclick="window.location.href='admin/log.php'">
                <i class="bi bi-box-arrow-in-right"></i> <!-- Bootstrap login icon -->
                <h3>Login</h3>
                <a href="admin/log.php" class="button">User login into system.</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 AMU. All rights reserved.</p>
    </footer>

    <!-- Script for Button Redirection -->
    <script>
        function redirectToStartPage() {
            window.location.href = 'start.php';
        }
    </script>

    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>