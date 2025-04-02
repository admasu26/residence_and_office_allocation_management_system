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

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $maintenance_id = $_POST['maintenance_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE maintenance SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $maintenance_id);

    if ($stmt->execute()) {
        echo "<script>alert('Status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating status: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Fetch all maintenance issues
$sql = "SELECT * FROM maintenance ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track & Resolve Maintenance Issues</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
    position: relative; /* Required for positioning the submenu */
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
    background: rgb(9, 0, 0);
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #1e3a8a;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-form select {
            padding: 5px;
            border-radius: 4px;
        }

        .status-form button {
            padding: 5px 10px;
            background-color: #1e3a8a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .status-form button:hover {
            background-color: #152c5b;
        }

        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .status.pending {
            background-color: #ffd700;
            color: #000;
        }

        .status.in-progress {
            background-color: #87CEEB;
            color: #000;
        }

        .status.completed {
            background-color: #90EE90;
            color: #000;
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
        <div class="container">
            <h1>Track & Resolve Maintenance Issues</h1>
            
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>BF No</th>
                            <th>Requested By</th>
                            <th>Work Required</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Work Type</th>
                            <th>Material List</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['bfno']) ?></td>
                            <td><?= htmlspecialchars($row['request_by']) ?></td>
                            <td><?= htmlspecialchars($row['work_required']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['work_type']) ?></td>
                            <td><?= htmlspecialchars($row['material_list']) ?></td>
                            <td>
                                <span class="status <?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td>
                                <form class="status-form" method="POST" action="">
                                    <input type="hidden" name="maintenance_id" value="<?= $row['id'] ?>">
                                    <select name="status">
                                        <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="In Progress" <?= $row['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No maintenance issues found.</p>
            <?php endif; ?>
        </div>
        <!-- Add this button inside the container div -->
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="generate_pdf.php" target="_blank">
                <button style="padding: 10px 20px; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Download PDF
                </button>
            </a>
        </div>
    </div>

<script>
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>