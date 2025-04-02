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

// Fetch approved allocation requests
$sql = "SELECT * FROM allocation_requests WHERE status = 'Approved' ORDER BY rank ASC";
$result = $conn->query($sql);

// Handle allocation process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['allocate'])) {
    if (!empty($_POST['selected_requests'])) {
        $selected_requests = $_POST['selected_requests'];

        // Loop through selected requests and allocate resources
        foreach ($selected_requests as $request_id) {
            // Fetch request details
            $request_sql = "SELECT * FROM allocation_requests WHERE id = ?";
            $stmt = $conn->prepare($request_sql);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($request) {
                $preferred_type = $request['prefered_type'];
                $campus = $request['campus'];
                $allocated_to_name = $request['name']; // Staff member's name
                $allocated_to_username = $request['username']; // Staff member's username

                // Find an available resource matching the preferred type and campus
                $resource_sql = "SELECT * FROM resources WHERE resource_type = ? AND campus = ? AND status = 'Available' LIMIT 1";
                $stmt = $conn->prepare($resource_sql);
                $stmt->bind_param("ss", $preferred_type, $campus);
                $stmt->execute();
                $resource = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($resource) {
                    // Allocate the resource
                    $allocate_sql = "UPDATE resources SET status = 'Allocated' WHERE id = ?";
                    $stmt = $conn->prepare($allocate_sql);
                    $stmt->bind_param("i", $resource['id']);
                    $stmt->execute();
                    $stmt->close();

                    // Update the allocation request status to 'Allocated'
                    $update_request_sql = "UPDATE allocation_requests SET status = 'Allocated' WHERE id = ?";
                    $stmt = $conn->prepare($update_request_sql);
                    $stmt->bind_param("i", $request_id);
                    $stmt->execute();
                    $stmt->close();

                    // Store allocation details in the `allocations` table
                    $insert_allocation_sql = "INSERT INTO allocations (request_id, resource_id, allocated_to_name, allocated_to_username, campus, building, floor, room_number) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_allocation_sql);
                    $stmt->bind_param("iissssii", $request_id, $resource['id'], $allocated_to_name, $allocated_to_username, $resource['campus'], $resource['building'], $resource['floor'], $resource['room_number']);
                    $stmt->execute();
                    $stmt->close();

                    // Send notification to the staff member
                    $notification_message = "Your residence allocation request has been successfully processed.";
                    $notification_sql = "INSERT INTO notifications (username, message) VALUES (?, ?)";
                    $stmt = $conn->prepare($notification_sql);
                    $stmt->bind_param("ss", $allocated_to_username, $notification_message);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION["message"] = "Resources allocated successfully.";
                } else {
                    $_SESSION["error"] = "No available resources found for the selected requests.";
                }
            }
        }
    } else {
        $_SESSION["error"] = "No requests selected for allocation.";
    }
    // Redirect to the same page to display updated allocation details
    header("Location: residence_allocate.php");
    exit();
}

// Handle deallocation process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deallocate'])) {
    if (!empty($_POST['selected_allocations'])) {
        $selected_allocations = $_POST['selected_allocations'];

        // Loop through selected allocations and deallocate resources
        foreach ($selected_allocations as $allocation_id) {
            // Fetch allocation details
            $allocation_sql = "SELECT a.*, r.id as resource_id 
                              FROM allocations a
                              JOIN resources r ON a.resource_id = r.id
                              WHERE a.request_id = ?";
            $stmt = $conn->prepare($allocation_sql);
            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();
            $allocation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($allocation) {
                // Update the resource status back to 'Available'
                $update_resource_sql = "UPDATE resources SET status = 'Available' WHERE id = ?";
                $stmt = $conn->prepare($update_resource_sql);
                $stmt->bind_param("i", $allocation['resource_id']);
                $stmt->execute();
                $stmt->close();

                // Update the allocation request status back to 'Approved'
                $update_request_sql = "UPDATE allocation_requests SET status = 'Approved' WHERE id = ?";
                $stmt = $conn->prepare($update_request_sql);
                $stmt->bind_param("i", $allocation_id);
                $stmt->execute();
                $stmt->close();

                // Remove the allocation record
                $delete_allocation_sql = "DELETE FROM allocations WHERE request_id = ?";
                $stmt = $conn->prepare($delete_allocation_sql);
                $stmt->bind_param("i", $allocation_id);
                $stmt->execute();
                $stmt->close();

                // Send notification to the staff member
                $notification_message = "Your residence allocation has been deallocated.";
                $notification_sql = "INSERT INTO notifications (username, message) VALUES (?, ?)";
                $stmt = $conn->prepare($notification_sql);
                $stmt->bind_param("ss", $allocation['allocated_to_username'], $notification_message);
                $stmt->execute();
                $stmt->close();

                $_SESSION["message"] = "Resources deallocated successfully.";
            }
        }
    } else {
        $_SESSION["error"] = "No allocations selected for deallocation.";
    }
    // Redirect to the same page to display updated allocation details
    header("Location: residence_allocate.php");
    exit();
}

// Fetch allocation details to display after allocation
$allocation_sql = "SELECT a.request_id, a.allocated_to_name, a.allocated_to_username, a.campus, a.building, a.floor, a.room_number, 
                          r.resource_type, ar.status
                   FROM allocations a
                   JOIN resources r ON a.resource_id = r.id
                   JOIN allocation_requests ar ON a.request_id = ar.id";
$allocation_result = $conn->query($allocation_sql);

if (!$allocation_result) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residence Allocation</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
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

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .allocate-btn {
            margin-top: 20px;
            text-align: center;
        }

        .footer {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            padding: 10px 20px;
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            z-index: 1000;
        }

        .back-btn {
            margin-bottom: 20px;
        }
        
        .deallocate-btn {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .deallocate-btn:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        /* New styles for toggle view functionality */
        .toggle-view-container {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
        
        .toggle-view-btn {
            padding: 8px 20px;
            font-weight: 600;
            border: 2px solid #1e3a8a;
            background: #1e3a8a;
            color: white;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-size: 14px;
        }
        
        .toggle-view-btn:hover {
            background: white;
            color: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .view-section {
            transition: all 0.3s ease;
        }
        
        .hidden-section {
            display: none;
            opacity: 0;
            height: 0;
            overflow: hidden;
        }
        
        .visible-section {
            display: block;
            opacity: 1;
            height: auto;
        }
        
        .section-card {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
            <h1><i class="fas fa-home"></i> Residence Allocation</h1>
            
            <!-- Toggle View Button - Bottom Right -->
            <div class="toggle-view-container">
                <button id="toggleViewBtn" class="toggle-view-btn">
                    <i class="fas fa-exchange-alt"></i> Show Allocation Details
                </button>
            </div>

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

            <!-- Approved Requests Section -->
            <div id="approvedRequestsSection" class="view-section visible-section">
                <div class="section-card">
                    <h2><i class="fas fa-list"></i> Approved Requests</h2>
                    <form method="POST" action="">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-requests"></th>
                                        <th><i class="fas fa-user"></i> Name</th>
                                        <th><i class="fas fa-user"></i> Username</th>
                                        <th><i class="fas fa-home"></i> Preferred Type</th>
                                        <th><i class="fas fa-university"></i> Campus</th>
                                        <th><i class="fas fa-star"></i> Score</th>
                                        <th><i class="fas fa-trophy"></i> Rank</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td><input type='checkbox' name='selected_requests[]' value='" . $row["id"] . "'></td>
                                                    <td>" . $row["name"] . "</td>
                                                    <td>" . $row["username"] . "</td>
                                                    <td>" . $row["prefered_type"] . "</td>
                                                    <td>" . $row["campus"] . "</td>
                                                    <td>" . $row["score"] . "</td>
                                                    <td>" . $row["rank"] . "</td>
                                                    <td>" . $row["status"] . "</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No approved requests found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="allocate-btn">
                            <button type="submit" name="allocate" class="btn btn-primary">
                                <i class="fas fa-check"></i> Allocate Selected Requests
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Allocation Details Section -->
            <div id="allocationDetailsSection" class="view-section hidden-section">
                <div class="section-card">
                    <h2><i class="fas fa-list"></i> Allocation Details</h2>
                    <form method="POST" action="">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-allocations"></th>
                                        <th><i class="fas fa-user"></i> Name</th>
                                        <th><i class="fas fa-user"></i> Username</th>
                                        <th><i class="fas fa-university"></i> Campus</th>
                                        <th><i class="fas fa-building"></i> Building</th>
                                        <th><i class="fas fa-layer-group"></i> Floor</th>
                                        <th><i class="fas fa-door-open"></i> Room Number</th>
                                        <th><i class="fas fa-home"></i> Resource Type</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($allocation_result->num_rows > 0) {
                                        // Reset pointer to beginning of result set
                                        $allocation_result->data_seek(0);
                                        while ($row = $allocation_result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td><input type='checkbox' name='selected_allocations[]' value='" . $row["request_id"] . "'></td>
                                                    <td>" . $row["allocated_to_name"] . "</td>
                                                    <td>" . $row["allocated_to_username"] . "</td>
                                                    <td>" . $row["campus"] . "</td>
                                                    <td>" . $row["building"] . "</td>
                                                    <td>" . $row["floor"] . "</td>
                                                    <td>" . $row["room_number"] . "</td>
                                                    <td>" . $row["resource_type"] . "</td>
                                                    <td>" . $row["status"] . "</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>No allocations found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="allocate-btn">
                            <button type="submit" name="deallocate" class="btn deallocate-btn">
                                <i class="fas fa-times"></i> Deallocate Selected
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?= date('Y') ?> Resource Management System. All rights reserved.
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        // Select All checkbox functionality
        document.getElementById('select-all-requests').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_requests[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
        
        // Select All allocations checkbox functionality
        document.getElementById('select-all-allocations').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_allocations[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Toggle between views
        document.getElementById('toggleViewBtn').addEventListener('click', function() {
            const approvedSection = document.getElementById('approvedRequestsSection');
            const allocationSection = document.getElementById('allocationDetailsSection');
            const icon = this.querySelector('i');
            
            if (approvedSection.classList.contains('visible-section')) {
                // Switch to allocation details view
                approvedSection.classList.remove('visible-section');
                approvedSection.classList.add('hidden-section');
                allocationSection.classList.remove('hidden-section');
                allocationSection.classList.add('visible-section');
                icon.classList.replace('fa-exchange-alt', 'fa-list');
                this.innerHTML = '<i class="fas fa-list"></i> Show Approved Requests';
            } else {
                // Switch back to approved requests view
                allocationSection.classList.remove('visible-section');
                allocationSection.classList.add('hidden-section');
                approvedSection.classList.remove('hidden-section');
                approvedSection.classList.add('visible-section');
                icon.classList.replace('fa-list', 'fa-exchange-alt');
                this.innerHTML = '<i class="fas fa-exchange-alt"></i> Show Allocation Details';
            }
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