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

// Handle report generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];

    // Fetch data based on report type
    switch ($report_type) {
        case 'maintenance_issue':
            $sql = "SELECT bfno, request_by, work_required, location, date, work_type, material_list, status FROM maintenance ORDER BY date DESC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $report_data = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $report_data = []; // Set to empty array if no data is found
            }
            $report_title = "Maintenance Issue Report";
            break;

        case 'track_resource':
            $sql = "SELECT campus, building, floor, room_number, resource_type, status FROM office_resource ORDER BY campus, building, floor, room_number";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $report_data = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $report_data = []; // Set to empty array if no data is found
            }
            $report_title = "Track Resource Report";
            break;

        case 'about_allocation':
            $sql = "SELECT a.allocated_to_name, a.campus, a.building, a.floor, a.room_number, 
                           r.resource_type, ar.status
                    FROM office_allocation a
                    JOIN office_resource r ON a.resource_id = r.id
                    JOIN office_allocation_requests ar ON a.request_id = ar.id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $report_data = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $report_data = []; // Set to empty array if no data is found
            }
            $report_title = "About Allocation Report";
            break;

        default:
            $_SESSION["error"] = "Invalid report type selected.";
            header("Location: send_office_report.php");
            exit();
    }

    // Store report data in session for PDF generation
    $_SESSION['report_data'] = $report_data;
    $_SESSION['report_title'] = $report_title;

    // Save report data to the database
    $data_json = json_encode($report_data); // Convert report data to JSON
    $sql = "INSERT INTO office_reports (title, data) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $report_title, $data_json);
    $stmt->execute();
    $report_id = $stmt->insert_id; // Get the ID of the inserted report
    $stmt->close();

    $_SESSION['report_id'] = $report_id; // Store report ID in session
}

// Handle sending report to Managing Director
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_report'])) {
    $report_id = $_POST['report_id'];

    // Update the report to mark it as sent to the Managing Director
    $sql = "UPDATE office_reports SET sent_to_director = TRUE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION["message"] = "Report sent to Managing Director successfully.";
    header("Location: send_office_report.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
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

        .form-label {
            font-weight: bold;
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
            <li><a href="allocation_committee_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="send_office_report.php"><i class="fas fa-id-card"></i> Generate Report</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1><i class="fas fa-file-alt"></i> Generate Office Report</h1>

            <?php
            if (isset($_SESSION["error"])) {
                echo "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> " . $_SESSION["error"] . "</div>";
                unset($_SESSION["error"]);
            }
            if (isset($_SESSION["message"])) {
                echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> " . $_SESSION["message"] . "</div>";
                unset($_SESSION["message"]);
            }
            ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="report_type" class="form-label"><i class="fas fa-chart-bar"></i> Select Report Type:</label>
                    <select name="report_type" class="form-select" required>
                        <option value="maintenance_issue">Maintenance Issue Report</option>
                        <option value="track_resource">Track Resource Report</option>
                        <option value="about_allocation">About Allocation Report</option>
                    </select>
                </div>
                <button type="submit" name="generate_report" class="btn btn-primary">
                    <i class="fas fa-download"></i> Generate Report
                </button>
            </form>

            <?php if (isset($_SESSION['report_data']) && !empty($_SESSION['report_data'])): ?>
                <div class="mt-4">
                    <h2><?= $_SESSION['report_title'] ?></h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($_SESSION['report_data'][0]) as $column): ?>
                                    <th><?= ucwords(str_replace('_', ' ', $column)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['report_data'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?= $value ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="generate_pdf.php" target="_blank" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    <form method="POST" action="send_office_report.php" class="mt-3">
                        <input type="hidden" name="report_id" value="<?= $_SESSION['report_id'] ?>">
                        <button type="submit" name="send_report" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Send to Managing Director
                        </button>
                    </form>
                </div>
            <?php elseif (isset($_SESSION['report_data']) && empty($_SESSION['report_data'])): ?>
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-circle"></i> No data found for the selected report.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>