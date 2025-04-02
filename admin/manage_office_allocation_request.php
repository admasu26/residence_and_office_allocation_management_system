<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine which requests to show (Pending, Approved, or Rejected)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Pending';

// Fetch allocation requests based on filter, ordered by office type (private first) then rank
$sql = "SELECT * FROM office_allocation_requests WHERE status = ? 
        ORDER BY 
        CASE WHEN office_type = 'private' THEN 1 
             WHEN office_type = 'shared' THEN 2 
             ELSE 3 END,
        rank ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status_filter);
$stmt->execute();
$result = $stmt->get_result();

// Calculate scores and rank requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rank_score'])) {
    // First, get all pending requests
    $pending_sql = "SELECT * FROM office_allocation_requests WHERE status = 'Pending'";
    $pending_result = $conn->query($pending_sql);
    
    while ($row = $pending_result->fetch_assoc()) {
        $id = $row['id'];
        $work_range = $row['work_range'];
        $academic_rank = $row['academic_rank'];
        $disability = $row['disability'];
        $gender = $row['gender'];

        // Calculate score based on criteria
        $score = 0;

        // Work range (25%)
        switch ($work_range) {
            case '>8':
                $score += 25;
                break;
            case '5-8':
                $score += 23;
                break;
            case '3-5':
                $score += 20;
                break;
            case '1-3':
                $score += 17;
                break;
        }

        // Academic rank (65%)
        switch ($academic_rank) {
            case 'professor':
                $score += 65;
                break;
            case 'researcher':
                $score += 63;
                break;
            case 'phd':
                $score += 58;
                break;
            case 'msc':
                $score += 52;
                break;
            case 'bsc':
                $score += 47;
                break;
        }

        // Disability (5%)
        if ($disability == 'yes') {
            $score += 5;
        }

        // Gender (5%)
        if ($gender == 'female') {
            $score += 5;
        }

        // Update the score in the database
        $update_sql = "UPDATE office_allocation_requests SET score = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $score, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Rank private and shared office types separately
    $office_types = ['private', 'shared']; // Private first as requested
    
    foreach ($office_types as $type) {
        // Get requests for this office type ordered by score
        $rank_sql = "SELECT id, score FROM office_allocation_requests 
                    WHERE status = 'Pending' AND office_type = ?
                    ORDER BY score DESC";
        
        $stmt = $conn->prepare($rank_sql);
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $rank_result = $stmt->get_result();
        
        $rank = 1;
        $previous_score = null;
        $previous_rank = 1;
        
        while ($rank_row = $rank_result->fetch_assoc()) {
            $id = $rank_row['id'];
            $current_score = $rank_row['score'];
            
            // If current score is same as previous, use the same rank
            if ($previous_score !== null && $current_score == $previous_score) {
                $current_rank = $previous_rank;
            } else {
                $current_rank = $rank;
                $previous_rank = $rank;
            }
            
            $update_rank_sql = "UPDATE office_allocation_requests SET rank = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_rank_sql);
            $stmt_update->bind_param("ii", $current_rank, $id);
            $stmt_update->execute();
            $stmt_update->close();
            
            $previous_score = $current_score;
            $rank++;
        }
    }

    $_SESSION["message"] = "Scores calculated and ranks assigned successfully.";
    header("Location: manage_office_allocation_request.php");
    exit();
}

// Update status of selected requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status_single'])) {
        // Handle single request update
        $id = $_POST['request_id'];
        $status = $_POST['status'];
        $current_status = $_POST['current_status'] ?? '';
        
        // Only proceed if the status is actually changing
        if ($status !== $current_status) {
            // Update the status of the request
            $update_sql = "UPDATE office_allocation_requests SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();

            // Send notification if status changed to Rejected or Approved
            if ($status === 'Rejected' || $status === 'Approved') {
                // Fetch the staff member's username
                $fetch_username_sql = "SELECT username FROM office_allocation_requests WHERE id = ?";
                $stmt = $conn->prepare($fetch_username_sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $username_result = $stmt->get_result();
                $username_row = $username_result->fetch_assoc();
                $username = $username_row['username'];
                $stmt->close();

                // Insert a notification into the `notifications` table
                $notification_message = "Your office allocation request has been " . strtolower($status) . ".";
                $notification_sql = "INSERT INTO notifications (username, message) VALUES (?, ?)";
                $stmt = $conn->prepare($notification_sql);
                $stmt->bind_param("ss", $username, $notification_message);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION["message"] = "Request status updated successfully.";
        } else {
            $_SESSION["message"] = "No change in request status.";
        }
        header("Location: manage_office_allocation_request.php?status=" . urlencode($status_filter));
        exit();
    }
    elseif (isset($_POST['update_status'])) {
        // Handle multiple requests update
        if (!empty($_POST['selected_requests'])) {
            $status = $_POST['status'];
            $selected_requests = $_POST['selected_requests'];

            foreach ($selected_requests as $id) {
                // Update the status of the request
                $update_sql = "UPDATE office_allocation_requests SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $status, $id);
                $stmt->execute();
                $stmt->close();

                // If the request is rejected, send a notification
                if ($status === 'Rejected') {
                    // Fetch the staff member's username
                    $fetch_username_sql = "SELECT username FROM office_allocation_requests WHERE id = ?";
                    $stmt = $conn->prepare($fetch_username_sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $username_result = $stmt->get_result();
                    $username_row = $username_result->fetch_assoc();
                    $username = $username_row['username'];
                    $stmt->close();

                    // Insert a notification into the `notifications` table
                    $notification_message = "Your office allocation request has been rejected.";
                    $notification_sql = "INSERT INTO notifications (username, message) VALUES (?, ?)";
                    $stmt = $conn->prepare($notification_sql);
                    $stmt->bind_param("ss", $username, $notification_message);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $_SESSION["message"] = "Selected requests updated successfully.";
        } else {
            $_SESSION["error"] = "No requests selected.";
        }
        header("Location: manage_office_allocation_request.php?status=" . urlencode($status_filter));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Allocation Requests</title>
    <link rel="icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
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
            position: relative;
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
            display: block;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .table-responsive {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #1e3a8a;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
        
        .status-filter {
            margin-bottom: 20px;
        }
        
        .status-filter .btn {
            margin-right: 5px;
        }
        
        .btn-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-approved {
            background-color: #28a745;
            color: white;
        }
        
        .btn-rejected {
            background-color: #dc3545;
            color: white;
        }
        
        .active-status {
            font-weight: bold;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .action-buttons {
            white-space: nowrap;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
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
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-tasks"></i> Manage Office Allocation Requests
            </h2>
            <?php
            if (isset($_SESSION["message"])) {
                echo "<div class='alert alert-success'>" . $_SESSION["message"] . "</div>";
                unset($_SESSION["message"]);
            }
            if (isset($_SESSION["error"])) {
                echo "<div class='alert alert-danger'>" . $_SESSION["error"] . "</div>";
                unset($_SESSION["error"]);
            }
            ?>
            
            <!-- Status Filter Buttons -->
            <div class="status-filter">
                <a href="?status=Pending" class="btn btn-pending <?php echo $status_filter == 'Pending' ? 'active-status' : ''; ?>">
                    <i class="fas fa-clock"></i> Pending Requests
                </a>
                <a href="?status=Approved" class="btn btn-approved <?php echo $status_filter == 'Approved' ? 'active-status' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Approved Requests
                </a>
                <a href="?status=Rejected" class="btn btn-rejected <?php echo $status_filter == 'Rejected' ? 'active-status' : ''; ?>">
                    <i class="fas fa-times-circle"></i> Rejected Requests
                </a>
            </div>
            
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Gender</th>
                                <th>Academic Rank</th>
                                <th>Work Range</th>
                                <th>Disability</th>
                                <th>Office Type</th>
                                <th>Campus</th>
                                <th>Score</th>
                                <th>Rank</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $counter = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><input type='checkbox' name='selected_requests[]' value='" . $row["id"] . "'></td>";
                                    echo "<td>" . $row["name"] . "</td>
                                          <td>" . $row["username"] . "</td>
                                          <td>" . $row["gender"] . "</td>
                                          <td>" . $row["academic_rank"] . "</td>
                                          <td>" . $row["work_range"] . "</td>
                                          <td>" . $row["disability"] . "</td>
                                          <td>" . $row["office_type"] . "</td>
                                          <td>" . $row["campus"] . "</td>
                                          <td>" . ($row["score"] ?? 'N/A') . "</td>
                                          <td>" . ($row["rank"] ?? 'N/A') . "</td>
                                          <td>" . ($row["status"] ?? 'Pending') . "</td>";
                                    
                                    // Add action buttons for all statuses
                                    echo "<td class='action-buttons'>";
                                    echo "<div class='btn-group'>";
                                    
                                    // Approve button (show unless already approved)
                                    if ($row["status"] !== 'Approved') {
                                        echo "<form method='POST' action='' style='display: inline;'>
                                                <input type='hidden' name='request_id' value='" . $row["id"] . "'>
                                                <input type='hidden' name='status' value='Approved'>
                                                <input type='hidden' name='current_status' value='" . $row["status"] . "'>
                                                <button type='submit' name='update_status_single' class='btn btn-success btn-sm'>
                                                    <i class='fas fa-check'></i> Approve
                                                </button>
                                              </form>";
                                    }
                                    
                                    // Reject button (show unless already rejected)
                                    if ($row["status"] !== 'Rejected') {
                                        echo "<form method='POST' action='' style='display: inline; margin-left: 5px;'>
                                                <input type='hidden' name='request_id' value='" . $row["id"] . "'>
                                                <input type='hidden' name='status' value='Rejected'>
                                                <input type='hidden' name='current_status' value='" . $row["status"] . "'>
                                                <button type='submit' name='update_status_single' class='btn btn-danger btn-sm'>
                                                    <i class='fas fa-times'></i> Reject
                                                </button>
                                              </form>";
                                    }
                                    
                                    // Pending button (show unless already pending)
                                    if ($row["status"] !== 'Pending') {
                                        echo "<form method='POST' action='' style='display: inline; margin-left: 5px;'>
                                                <input type='hidden' name='request_id' value='" . $row["id"] . "'>
                                                <input type='hidden' name='status' value='Pending'>
                                                <input type='hidden' name='current_status' value='" . $row["status"] . "'>
                                                <button type='submit' name='update_status_single' class='btn btn-warning btn-sm'>
                                                    <i class='fas fa-clock'></i> Set Pending
                                                </button>
                                              </form>";
                                    }
                                    
                                    echo "</div></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='13' class='text-center'>No " . strtolower($status_filter) . " allocation requests found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <?php if ($status_filter == 'Pending'): ?>
                        <button type="submit" name="rank_score" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Calculate Rank and Score
                        </button>
                    <?php endif; ?>
                    
                    <select name="status" class="form-select d-inline-block w-auto">
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Reject</option>
                        <option value="Pending">Set to Pending</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-success">
                        <i class="fas fa-check"></i> Update Selected
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        // Select All checkbox functionality
        document.getElementById('select-all')?.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_requests[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
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
    </script>
</body>
</html>
<?php
// Close the database connection after all operations are complete
$conn->close();
?>