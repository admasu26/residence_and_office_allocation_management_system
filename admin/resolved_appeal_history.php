<?php
session_start();

// Check if the user is logged in and is part of the allocation committee
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

// Fetch all resolved appeals
$resolved_sql = "SELECT * FROM appeals WHERE status IN ('Approved', 'Rejected')";
$resolved_result = $conn->query($resolved_sql);

if (!$resolved_result) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolved Appeals</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 250px;
            height: 100%;
            background: #1e3a8a;
            color: white;
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

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #1e3a8a;
        }

        .table thead {
            background-color: #1e3a8a;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2>Allocation Committee</h2>
        <ul>
            <li><a href="allocation_committee_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
           
            <li><a href="solve_appeal.php"><i class="fas fa-gavel"></i> Solve Appeal</a></li>
           
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1><i class="fas fa-history"></i> Resolved Appeals</h1>
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

            <!-- Resolved Appeals Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-home"></i> Allocation Type</th>
                            <th><i class="fas fa-comment"></i> Appeal Reason</th>
                            <th><i class="fas fa-check"></i> Status</th>
                            <th><i class="fas fa-comment-dots"></i> Resolution Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resolved_result->num_rows > 0) {
                            while ($row = $resolved_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['username']) . "</td>
                                        <td>" . htmlspecialchars($row['allocation_type']) . "</td>
                                        <td>" . htmlspecialchars($row['appeal_reason']) . "</td>
                                        <td>" . htmlspecialchars($row['status']) . "</td>
                                        <td>" . htmlspecialchars($row['resolution_message']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No resolved appeals found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>