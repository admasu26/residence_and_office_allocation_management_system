<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch reports sent to the Managing Director
$sql = "SELECT id, title, created_at FROM reports WHERE sent_to_director = TRUE ORDER BY created_at DESC";
$result = $conn->query($sql);
$reports = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
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
        }

        .sidebar ul li a i {
            font-size: 18px;
        }

        .sidebar ul li:hover {
            background: rgb(9, 0, 0);
        }

        .sidebar ul li:hover a {
            color: #fff;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e3a8a;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #1e3a8a;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: #1e3a8a;
            border: none;
        }

        .btn-primary:hover {
            background-color: #152c5b;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2>SRAM</h2>
        <ul>
            <li><a href="managing_director_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="view_report.php"><i class="fas fa-file-alt"></i> View Reports</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1><i class="fas fa-file-alt"></i> Reports Sent to Managing Director</h1>

            <?php if (!empty($reports)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Report Title</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= $report['title'] ?></td>
                                <td><?= date('M d, Y H:i:s', strtotime($report['created_at'])) ?></td>
                                <td>
                                    <a href="view_report_details.php?id=<?= $report['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No reports found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>