<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header('location: log.php');
    exit(); // Ensure no further code is executed after the redirect
}

// Initialize variables with default values
$message = ""; // Default message
$is_form_allowed = false; // Default form submission status

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user has already submitted their details
$user_id = $_SESSION['user_id'];
$check_sql = "SELECT * FROM user_detail WHERE user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // User has already submitted their details
    $is_form_allowed = false;
    $message = "<div class='alert alert-warning'>You have already submitted your details.</div>";
} else {
    // Check if the form submission is allowed based on the date and time
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Fetch the current form permission
    $sql = "SELECT * FROM form_permissions ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $permission = $result->fetch_assoc();
        $start_date = $permission['start_date'];
        $end_date = $permission['end_date'];
        $end_time = "23:59:59"; // End of the day

        // Check if the current date and time are within the allowed interval
        if ($current_date >= $start_date && $current_date <= $end_date) {
            if ($current_date == $end_date && $current_time > $end_time) {
                $is_form_allowed = false; // Lock the form if the time is up
            } else {
                $is_form_allowed = true; // Allow the form
            }
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_form_allowed) {
    // Sanitize and validate input data
    $name = htmlspecialchars($_POST['name']);
    $gender = htmlspecialchars($_POST['gender']);
    $college = htmlspecialchars($_POST['college']);
    $department = htmlspecialchars($_POST['department']);
    $employment_date = htmlspecialchars($_POST['employment_date']);
    $academic_rank = htmlspecialchars($_POST['academic_rank']);
    $work_range = htmlspecialchars($_POST['work_range']);
    $marital_status = htmlspecialchars($_POST['marital_status']);
    $children = intval($_POST['children']);
    $spouse = htmlspecialchars($_POST['spouse']);
    $spouse_name = htmlspecialchars($_POST['spouse_name']);
    $disability = htmlspecialchars($_POST['disability']);
    $soamu = htmlspecialchars($_POST['soamu']);
    $current_address = htmlspecialchars($_POST['current_address']);
    $unit_type = htmlspecialchars($_POST['unit_type']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $alt_email = filter_var($_POST['alt_email'], FILTER_SANITIZE_EMAIL);
    $phone_home = htmlspecialchars($_POST['phone_home']);
    $phone_mobile = htmlspecialchars($_POST['phone_mobile']);

    // Insert data into the database using prepared statements
    $sql = "INSERT INTO user_detail(user_id, name, gender, college, department, employment_date, academic_rank, work_range, marital_status, children, spouse, spouse_name, disability, soamu, current_address, unit_type, email, alt_email, phone_home, phone_mobile)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssssissssssssss", $user_id, $name, $gender, $college, $department, $employment_date, $academic_rank, $work_range, $marital_status, $children, $spouse, $spouse_name, $disability, $soamu, $current_address, $unit_type, $email, $alt_email, $phone_home, $phone_mobile);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Your details have been submitted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Fetch submitted information for the logged-in user only
$submitted_info = [];
$fetch_sql = "SELECT * FROM user_detail WHERE user_id = ?";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$fetch_result = $fetch_stmt->get_result();
if ($fetch_result->num_rows > 0) {
    while ($row = $fetch_result->fetch_assoc()) {
        $submitted_info[] = $row;
    }
}

$fetch_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Member Dashboard</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
    --primary-color: #1e3a8a;
    --secondary-color: #f4f4f4;
    --accent-color: rgb(239, 243, 240);
    --text-color: #333;
    --sidebar-bg: linear-gradient(135deg, #1e3a8a, #3b82f6);
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --hover-bg: rgba(255, 255, 255, 0.1);
    --transition-speed: 0.3s;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--secondary-color);
    color: var(--text-color);
    margin: 0;
    padding: 0;
}

/* Sidebar Navigation */
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, #1e3a8a, #0d1b2a);
    color: white;
    padding-top: 20px;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000; /* Ensure sidebar is above other content */
}

.sidebar .logo {
    text-align: center;
    margin-bottom: 20px;
}

.sidebar .logo img {
    width: 80px;
    border-radius: 50%;
    animation: flip 4s infinite ease-in-out; /* Add flipping animation */
}

@keyframes flip {
    0% {
        transform: perspective(600px) rotateY(0deg); /* Front view */
    }
    50% {
        transform: perspective(600px) rotateY(180deg); /* Back view */
    }
    100% {
        transform: perspective(600px) rotateY(360deg); /* Front view */
    }
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
    margin-left: 250px; /* Align with sidebar width */
    padding: 20px; /* Add padding to avoid overlap */
    width: calc(100% - 250px); /* Ensure content takes up remaining space */
    box-sizing: border-box; /* Include padding in width calculation */
}

/* Navbar */
.navbar {
    background: var(--sidebar-bg);
    box-shadow: var(--shadow);
    padding: 5px 0px;
    position: fixed;
    top: 0;
    left: 250px; /* Align with sidebar width */
    width: calc(100% - 250px); /* Ensure navbar takes up remaining space */
    z-index: 999; /* Ensure navbar is below sidebar */
}

.navbar-brand {
    font-weight: 600;
    color: white !important;
}

.profile-dropdown .dropdown-toggle {
    color: blue;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-dropdown .dropdown-menu {
    border: none;
    box-shadow: var(--shadow);
    background: var(--sidebar-bg);
}

.profile-dropdown .dropdown-item {
    color: white;
    transition: background var(--transition-speed) ease;
}

.profile-dropdown .dropdown-item:hover {
    background: var(--hover-bg);
}

/* Form Container */
.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
    padding: 40px 0px 5px 180px;/* Adjusted padding */
    margin-top: 80px; /* Add margin to avoid overlap with navbar */
    transition: box-shadow var(--transition-speed) ease;
}

.form-container:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.form-container h2 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 20px;
}

.form-label {
    font-weight: 500;
    color: var(--text-color);
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 10px;
    transition: border-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 5px rgba(30, 58, 138, 0.2);
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    font-weight: 500;
    transition: background-color var(--transition-speed) ease, transform var(--transition-speed) ease;
}

.btn-primary:hover {
    background-color: #152c5b;
    transform: translateY(-2px);
}

.alert {
    border-radius: 8px;
    padding: 10px 20px; /* Adjusted padding */
    margin-bottom: 20px;
    transition: opacity var(--transition-speed) ease;
}

.alert:hover {
    opacity: 0.9;
}

/* Table Styling */
.table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    box-shadow: var(--shadow);
}

.table th, .table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

.table th {
    background-color: var(--primary-color);
    color: white;
}

.table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.table tr:hover {
    background-color: #f1f1f1;
}

/* Back Button Styles */
.btn-back {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    transition: all var(--transition-speed) ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.btn-back:hover {
    background: linear-gradient(135deg, #3b82f6, #1e3a8a);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.btn-back i {
    font-size: 16px;
    transition: transform var(--transition-speed) ease;
}

.btn-back:hover i {
    transform: translateX(-5px);
}

.btn-back::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: transform 0.5s ease, opacity 0.5s ease;
    opacity: 0;
}

.btn-back:hover::after {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
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
        width: 100%;
    }

    .navbar {
        left: 0;
        width: 100%;
    }

    .form-container {
        padding: 20px;
        margin-top: 20px;
    }

    .table th, .table td {
        padding: 8px;
    }

    .sidebar ul li {
        text-align: center;
    }
}
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #1e3a8a;
            color: white;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .card-body p {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .card-body p strong {
            color: #1e3a8a;
        }

        .alert {
            border-radius: 8px;
            padding: 10px 20px;
            margin-bottom: 20px;
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
                    <li><a href="office_allocate.php"><i class="fas fa-building"></i>Office allocation request</a></li>
                </ul>
                </li>
        </ul>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Staff Dashboard</a>
            <div class="profile-dropdown ms-auto">
                <a class="btn btn-light dropdown-toggle d-flex align-items-center" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
                <a href="staff_member_dashboard.php" class="btn btn-back me-2">
    <i class="bi bi-arrow-left"></i> Back
</a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person me-2"></i>View Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="form-container">
            <h2>Staff Member Dashboard</h2>
            <?= $message; ?>

            <?php if ($is_form_allowed): ?>
                <!-- Form is allowed -->
                <form id="staff-form" action="" method="POST">
                <div class="row">
                    <!-- Left Side -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender:</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="college" class="form-label">College/Institute:</label>
                            <input type="text" class="form-control" id="college" name="college" required>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department of Education:</label>
                            <input type="text" class="form-control" id="department" name="department" required>
                        </div>

                        <div class="mb-3">
                            <label for="employment_date" class="form-label">Assigned Location (Date):</label>
                            <input type="date" class="form-control" id="employment_date" name="employment_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="academic_rank" class="form-label">Academic Position (Title):</label>
                            <select class="form-select" id="academic_rank" name="academic_rank" required>
                                <option value="professor">Professor</option>
                                <option value="researcher">Researcher</option>
                                <option value="phd">PhD</option>
                                <option value="msc">MSc</option>
                                <option value="bsc">BSc</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="work_range" class="form-label">Work Range in University:</label>
                            <select class="form-select" id="work_range" name="work_range" required>
                                <option value=">8">More than 8 years</option>
                                <option value="5-8">5-8 years</option>
                                <option value="3-5">3-5 years</option>
                                <option value="1-3">1-3 years</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status:</label>
                            <select class="form-select" id="marital_status" name="marital_status" required>
                                <option value="married">Married</option>
                                <option value="unmarried">Unmarried</option>
                                <option value="divorced">Divorced/Widowed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="children" class="form-label">Number of Children:</label>
                            <input type="number" class="form-control" id="children" name="children" min="0">
                        </div>

                        <div class="mb-3">
                            <label for="spouse" class="form-label">Spouse:</label>
                            <select class="form-select" id="spouse" name="spouse" required>
                                <option value="no">No</option>
                                <option value="yes">Yes (Enter Name Below)</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="spouse_name" name="spouse_name">
                        </div>

                        <div class="mb-3">
                            <label for="disability" class="form-label">Disability Status:</label>
                            <select class="form-select" id="disability" name="disability" required>
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="soamu" class="form-label">Service Outside AMU (SOAMU):</label>
                            <select class="form-select" id="soamu" name="soamu" required>
                                <option value="1-4">1-4 years</option>
                                <option value=">4">More than 4 years</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="current_address" class="form-label">Current Address:</label>
                            <select class="form-select" id="current_address" name="current_address" required>
                                <option value="private">Private Residence Outside the University</option>
                                <option value="university">University-Provided Housing</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="unit_type" class="form-label">Type of Unit:</label>
                            <select class="form-select" id="unit_type" name="unit_type" required>
                                <option value="three_bedroom">Three-bedroom</option>
                                <option value="two_bedroom">Two-bedroom</option>
                                <option value="one_bedroom">One-bedroom</option>
                                <option value="studio">Studio</option>
                                <option value="service">Service Quarters</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="alt_email" class="form-label">Alternative Email:</label>
                            <input type="email" class="form-control" id="alt_email" name="alt_email">
                        </div>

                        <div class="mb-3">
                            <label for="phone_home" class="form-label">Phone (Home):</label>
                            <input type="text" class="form-control" id="phone_home" name="phone_home">
                        </div>

                        <div class="mb-3">
                            <label for="phone_mobile" class="form-label">Phone (Mobile):</label>
                            <input type="text" class="form-control" id="phone_mobile" name="phone_mobile" required>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <!-- Form is not allowed -->
                <div class="alert alert-warning">
                    The form is currently locked. You cannot submit your details at this time.
                </div>
            <?php endif; ?>


            <!-- Display Submitted Information Using Cards -->
            <?php if (!empty($submitted_info)): ?>
                <h3 class="mt-5">Your Submitted Information</h3>
                <div class="row">
                    <?php foreach ($submitted_info as $info): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <i class="bi bi-person-circle me-2"></i> Personal Information
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($info['name']); ?></p>
                                    <p><strong>Gender:</strong> <?= htmlspecialchars($info['gender']); ?></p>
                                    <p><strong>College:</strong> <?= htmlspecialchars($info['college']); ?></p>
                                    <p><strong>Department:</strong> <?= htmlspecialchars($info['department']); ?></p>
                                    <p><strong>Employment Date:</strong> <?= htmlspecialchars($info['employment_date']); ?></p>
                                    <p><strong>Academic Rank:</strong> <?= htmlspecialchars($info['academic_rank']); ?></p>
                                    <p><strong>Work Range:</strong> <?= htmlspecialchars($info['work_range']); ?></p>
                                    <p><strong>Marital Status:</strong> <?= htmlspecialchars($info['marital_status']); ?></p>
                                    <p><strong>Children:</strong> <?= htmlspecialchars($info['children']); ?></p>
                                    <p><strong>Spouse:</strong> <?= htmlspecialchars($info['spouse']); ?></p>
                                    <p><strong>Disability:</strong> <?= htmlspecialchars($info['disability']); ?></p>
                                    <p><strong>SOAMU:</strong> <?= htmlspecialchars($info['soamu']); ?></p>
                                    <p><strong>Current Address:</strong> <?= htmlspecialchars($info['current_address']); ?></p>
                                    <p><strong>Unit Type:</strong> <?= htmlspecialchars($info['unit_type']); ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($info['email']); ?></p>
                                    <p><strong>Phone (Mobile):</strong> <?= htmlspecialchars($info['phone_mobile']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-5">
                    No submitted information found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>