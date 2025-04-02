<?php
session_start(); // Start the session

// db_connection.php
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

if (isset($_POST["submit"])) {
    $uname = $_POST["username"];
    $pass = $_POST["passwords"];

    // Fix the SQL query to use 'username' instead of 'uname'
    $sql = "SELECT * FROM users WHERE username = '$uname' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) { 
        $row = $result->fetch_assoc();
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user information in session variables
        $_SESSION['user_id'] = $row['id']; // Assuming 'id' is the primary key of the users table
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // Redirect based on role
        if ($row['role'] == 'admin') {
            header('location: admin_dashboard.php');
        } elseif ($row['role'] == 'allocation_committee') {
            header('location: allocation_committee_dashboard.php');
        } elseif ($row['role'] == 'staff_member') {
            header('location: staff_member_dashboard.php');
        } elseif ($row['role'] == 'managing_director') {
            header('location: managing_director_dashboard.php');
        }
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* General Styling */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    height: 100vh;
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

/* Top section styling */
.top-section {
    height: 50vh; /* Half of the viewport height */
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 100px; /* Padding on the sides */
}

/* Left content styling */
.left-content {
    color: #fff; /* White text for better visibility on background */
}

/* Logo styling */
.logo {
    width: 150px; /* Adjust logo size */
    margin-bottom: 10px; /* Space below logo */
    animation: fadeIn 2s ease-in; /* Fade-in animation */
}

/* Heading styling */
.left-content h1 {
    font-size: 28px;
    font-weight: bold;
    margin-top: 0px; /* Reduced margin */
    margin-bottom: 10px; /* Space below heading */
    animation: fadeIn 2.5s ease-in; /* Fade-in animation */
}

/* Text styling */
.left-content p {
    font-size: 16px;
    line-height: 1.6; /* Improved readability */
    margin-top: 0px; /* Reduced margin */
    animation: fadeIn 3s ease-in; /* Fade-in animation */
}

/* Login container styling */
.login-container {
    background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white background */
    padding: 40px; /* Increased padding for larger size */
    border-radius: 10px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2); /* Enhanced shadow */
    width: 100%;
    max-width: 400px; /* Increased width */
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth hover effect */
    animation: slideInRight 1s ease-out; /* Slide-in animation */
}

/* Hover effect for login container */
.login-container:hover {
    transform: translateY(-5px); /* Slight lift on hover */
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
}

.input-group {
    margin-bottom: 20px; /* Increased margin */
}

.input-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500; /* Slightly bold labels */
}

.input-group input {
    width: 100%;
    padding: 10px; /* Increased padding */
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px; /* Larger font size */
    transition: border-color 0.3s ease; /* Smooth input hover effect */
}

.input-group input:hover {
    border-color: #4CAF50; /* Green border on hover */
}

.button-group {
    text-align: center;
}

button {
    width: 100%;
    padding: 12px; /* Increased padding */
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px; /* Larger font size */
    transition: background-color 0.3s ease; /* Smooth button hover effect */
}

button:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Bottom section styling */
.bottom-section {
    height: auto; /* Adjust height to fit content */
    background-color: #fff; /* White background */
    display: flex;
    justify-content: center;
    padding: 40px 20px; /* Increased padding */
}

/* Cards container styling */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
    gap: 20px; /* Space between cards */
    max-width: 1200px; /* Maximum width for the cards container */
    width: 100%;
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

/* Footer styling */
footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
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
@keyframes slideInLeft {
    0% {
        transform: translateX(-100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInRight {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

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
    <!-- Top Section -->
    <div class="top-section">
        <!-- Left Content (Logo and Text) -->
        <div class="left-content">
            <img src="logo.png" alt="University Logo" class="logo">
            <h1>AMU | Staff Residence and Office Allocation Management system </h1>
            <p>This system serves as a portal for university staff to request and manage residence and office allocations efficiently."</p>
        </div>

        <!-- Login Form -->
        <div class="login-container">
            <h2>Login</h2>
            <form action="log.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="input-group">
                    <label for="passwords">Password</label>
                    <input type="password" id="passwords" name="passwords" class="form-control" required>
                </div>
                <div class="button-group">
                    <button type="submit" name="submit" class="btn btn-success">Login</button>
                </div>
            </form>
            <!-- Back to Home Button -->
            <div class="text-center mt-3">
                <a href="../index.php" class="btn btn-outline-primary">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <div class="cards">
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-home"></i>
                <h3>Staff Member Dashboard</h3>
                <p>Staff Members Submit Request,view Allocation,view resources,log maintenance issue.</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-tools"></i>
                <h3>Allocation Committee Dashboard</h3>
                <p>The Allocation committee View Request , Allocate , track resource  .</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-box"></i>
                <h3>Admin Dahboard</h3>
                <p>The Admin Manage The System Delete , Update , Create User Account</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-envelope"></i>
                <h3>Managing Director Dashboard</h3> 
                <p>The Managing Director View and Approve Critical Allocation.</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-check-circle"></i>
                <h3>View Allocation Status</h3>
                <p>Check the status of your allocations.</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-clipboard"></i>
                <h3>Submit Allocation Request</h3>
                <p>Request resource allocations from the committee.</p>
            </div>
            <div class="card" onclick="window.location.href='log.php'">
                <i class="fas fa-home"></i>
                <h3>Residence Allocation</h3>
                <a><p>Allocate residential resources to staff members.</p></a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 AMU. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>