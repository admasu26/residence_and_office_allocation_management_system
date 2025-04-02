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

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql = "SELECT * FROM maintenance";

if ($status_filter !== 'all') {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}

$sql .= " ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History</title>
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
            margin: 0;
            padding: 0;
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

        .filters {
            margin-bottom: 15px;
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

        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .status.pending { background-color: #ffd700; color: #000; }
        .status.completed { background-color: #90EE90; color: #000; }
        .status.in-progress { background-color: #87CEEB; color: #000; }
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
            <h1>Maintenance History</h1>
            <div class="filters">
                <form method="GET">
                    <label>Filter by Status:</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $status_filter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </form>
            </div>
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
                            <th>Status</th>
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
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No maintenance records found.</p>
            <?php endif; ?>
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
});</script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>