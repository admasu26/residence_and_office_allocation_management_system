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

// Handle form submission to add or edit a resource
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add_resource"])) {
        // Add new resource
        $campus = $_POST['campus'] ?? '';
        $building = $_POST['building'] ?? '';
        $floor = $_POST['floor'] ?? '';
        $room_start = $_POST['room_start'] ?? '';
        $room_end = $_POST['room_end'] ?? '';
        $resource_type = $_POST['resource_type'] ?? '';

        if (!empty($campus) && !empty($building) && !empty($floor) && !empty($room_start) && !empty($room_end) && !empty($resource_type)) {
            // Loop through the range of rooms and add each one
            for ($room_number = $room_start; $room_number <= $room_end; $room_number++) {
                // Check if the room already exists
                $check_sql = "SELECT * FROM resources WHERE campus = ? AND building = ? AND floor = ? AND room_number = ?";
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param("ssii", $campus, $building, $floor, $room_number);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $_SESSION["error"] = "Room $room_number in building $building already exists.";
                } else {
                    // Insert the new resource
                    $sql = "INSERT INTO resources (campus, building, floor, room_number, resource_type, status) 
                            VALUES (?, ?, ?, ?, ?, 'Available')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssiis", $campus, $building, $floor, $room_number, $resource_type);

                    if (!$stmt->execute()) {
                        $_SESSION["error"] = "Error: " . $conn->error;
                        break;
                    }
                }
                $stmt->close();
            }
            if (!isset($_SESSION["error"])) {
                $_SESSION["message"] = "Resources added successfully.";
            }
        } else {
            $_SESSION["error"] = "All fields are required.";
        }
    } elseif (isset($_POST["edit_resource"])) {
        // Edit existing resource
        $id = $_POST['id'] ?? '';
        $campus = $_POST['campus'] ?? '';
        $building = $_POST['building'] ?? '';
        $floor = $_POST['floor'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $resource_type = $_POST['resource_type'] ?? '';
        $status = $_POST['status'] ?? '';

        if (!empty($id) && !empty($campus) && !empty($building) && !empty($floor) && !empty($room_number) && !empty($resource_type) && !empty($status)) {
            // Update the resource
            $sql = "UPDATE resources SET campus = ?, building = ?, floor = ?, room_number = ?, resource_type = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiissi", $campus, $building, $floor, $room_number, $resource_type, $status, $id);

            if ($stmt->execute()) {
                $_SESSION["message"] = "Resource updated successfully.";
            } else {
                $_SESSION["error"] = "Error: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION["error"] = "All fields are required.";
        }
    }
}

// Fetch all campuses, buildings, and floors for dropdowns
$campuses_sql = "SELECT DISTINCT campus FROM resources";
$campuses_result = $conn->query($campuses_sql);

$buildings_sql = "SELECT DISTINCT building FROM resources";
$buildings_result = $conn->query($buildings_sql);

$floors_sql = "SELECT DISTINCT floor FROM resources";
$floors_result = $conn->query($floors_sql);

// Handle filtering
$filter_campus = $_GET['campus'] ?? '';
$filter_building = $_GET['building'] ?? '';
$filter_floor = $_GET['floor'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build the SQL query based on filters
$resources_sql = "SELECT * FROM resources WHERE 1=1";
if (!empty($filter_campus)) {
    $resources_sql .= " AND campus = '$filter_campus'";
}
if (!empty($filter_building)) {
    $resources_sql .= " AND building = '$filter_building'";
}
if (!empty($filter_floor)) {
    $resources_sql .= " AND floor = '$filter_floor'";
}
if (!empty($filter_status)) {
    $resources_sql .= " AND status = '$filter_status'";
}
$resources_result = $conn->query($resources_sql);

// Fetch resource details for editing
$edit_id = $_GET['edit_id'] ?? '';
$edit_resource = null;
if (!empty($edit_id)) {
    $edit_sql = "SELECT * FROM resources WHERE id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_resource = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Insert notification
$message = "The allocation committee added a new resource.";
$username = $_SESSION['username'];
$sql = "INSERT INTO notifications (username, message) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $message);
$stmt->execute();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Resource Usage</title>
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

        .card i {
            font-size: 24px; /* Smaller icons in cards */
            color: #1e3a8a;
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
            color: #666;
        }

        .form-label i {
            font-size: 14px; /* Smaller icons in form labels */
            margin-right: 5px;
        }

        .table th i {
            font-size: 14px; /* Smaller icons in table headers */
            margin-right: 5px;
        }
        
        /* Hide the add resource form by default */
        #addResourceForm {
            display: none;
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
            <h1 class="mb-4"><i class="fas fa-building"></i> Track Resource Usage</h1>

            <!-- Add Resource Button -->
            <div class="mb-4">
                <button id="showAddResourceForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Resource
                </button>
            </div>

            <!-- Add Resource Form (Hidden by default) -->
            <div class="card mb-4" id="addResourceForm">
                <div class="card-body">
                    <h2 class="card-title"><i class="fas fa-plus"></i> Add Resource</h2>
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
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="campus" class="form-label"><i class="fas fa-university"></i> Campus:</label>
                            <select name="campus" class="form-select" required>
                                <option value="Main">Main</option>
                                <option value="Chamo">Chamo</option>
                                <option value="Abaya">Abaya</option>
                                <option value="Kulfo">Kulfo</option>
                                <option value="Nechisar">Nechisar</option>
                                <option value="Sawula">Sawula</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="building" class="form-label"><i class="fas fa-building"></i> Building:</label>
                            <input type="text" name="building" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="floor" class="form-label"><i class="fas fa-layer-group"></i> Floor:</label>
                            <input type="number" name="floor" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="room_start" class="form-label"><i class="fas fa-door-open"></i> Room Range:</label>
                            <input type="number" name="room_start" class="form-control" placeholder="Start Room Number" required>
                            <input type="number" name="room_end" class="form-control" placeholder="End Room Number" required>
                        </div>
                        <div class="mb-3">
                            <label for="resource_type" class="form-label"><i class="fas fa-home"></i> Resource Type:</label>
                            <select name="resource_type" class="form-select" required>
                                <option value="three_bedroom">Three-Bedroom</option>
                                <option value="two_bedroom">Two-Bedroom</option>
                                <option value="one_bedroom">One-Bedroom</option>
                                <option value="studio">Studio</option>
                                <option value="service">Service Quarters</option>
                            </select>
                        </div>
                        <button type="submit" name="add_resource" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Resource
                        </button>
                        <button type="button" id="cancelAddResource" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </form>
                </div>
            </div>

            <!-- Edit Resource Form (Only shown when editing) -->
            <?php if (!empty($edit_resource)): ?>
            <div class="card mb-4" id="editResourceForm">
                <div class="card-body">
                    <h2 class="card-title"><i class="fas fa-edit"></i> Edit Resource</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= $edit_resource['id'] ?>">
                        <div class="mb-3">
                            <label for="campus" class="form-label"><i class="fas fa-university"></i> Campus:</label>
                            <select name="campus" class="form-select" required>
                                <option value="Main" <?= $edit_resource['campus'] == 'Main' ? 'selected' : '' ?>>Main</option>
                                <option value="Chamo" <?= $edit_resource['campus'] == 'Chamo' ? 'selected' : '' ?>>Chamo</option>
                                <option value="Abaya" <?= $edit_resource['campus'] == 'Abaya' ? 'selected' : '' ?>>Abaya</option>
                                <option value="Kulfo" <?= $edit_resource['campus'] == 'Kulfo' ? 'selected' : '' ?>>Kulfo</option>
                                <option value="Nechisar" <?= $edit_resource['campus'] == 'Nechisar' ? 'selected' : '' ?>>Nechisar</option>
                                <option value="Sawula" <?= $edit_resource['campus'] == 'Sawula' ? 'selected' : '' ?>>Sawula</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="building" class="form-label"><i class="fas fa-building"></i> Building:</label>
                            <input type="text" name="building" class="form-control" value="<?= $edit_resource['building'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="floor" class="form-label"><i class="fas fa-layer-group"></i> Floor:</label>
                            <input type="number" name="floor" class="form-control" value="<?= $edit_resource['floor'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="room_number" class="form-label"><i class="fas fa-door-open"></i> Room Number:</label>
                            <input type="number" name="room_number" class="form-control" value="<?= $edit_resource['room_number'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="resource_type" class="form-label"><i class="fas fa-home"></i> Resource Type:</label>
                            <select name="resource_type" class="form-select" required>
                                <option value="three_bedroom" <?= $edit_resource['resource_type'] == 'three_bedroom' ? 'selected' : '' ?>>Three-Bedroom</option>
                                <option value="two_bedroom" <?= $edit_resource['resource_type'] == 'two_bedroom' ? 'selected' : '' ?>>Two-Bedroom</option>
                                <option value="one_bedroom" <?= $edit_resource['resource_type'] == 'one_bedroom' ? 'selected' : '' ?>>One-Bedroom</option>
                                <option value="studio" <?= $edit_resource['resource_type'] == 'studio' ? 'selected' : '' ?>>Studio</option>
                                <option value="service" <?= $edit_resource['resource_type'] == 'service' ? 'selected' : '' ?>>Service Quarters</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label"><i class="fas fa-info-circle"></i> Status:</label>
                            <select name="status" class="form-select" required>
                                <option value="Available" <?= $edit_resource['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Allocated" <?= $edit_resource['status'] == 'Allocated' ? 'selected' : '' ?>>Allocated</option>
                            </select>
                        </div>
                        <button type="submit" name="edit_resource" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Resource
                        </button>
                        <a href="track_usage.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Resource List -->
            <h2><i class="fas fa-list"></i> Resource List</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-university"></i> Campus</th>
                            <th><i class="fas fa-building"></i> Building</th>
                            <th><i class="fas fa-layer-group"></i> Floor</th>
                            <th><i class="fas fa-door-open"></i> Room Number</th>
                            <th><i class="fas fa-home"></i> Resource Type</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-edit"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resources_result->num_rows > 0) {
                            while ($row = $resources_result->fetch_assoc()) {
                                $status_class = ($row['status'] == 'Allocated') ? 'text-danger' : 'text-success';
                                echo "<tr>
                                        <td>{$row['campus']}</td>
                                        <td>{$row['building']}</td>
                                        <td>{$row['floor']}</td>
                                        <td>{$row['room_number']}</td>
                                        <td>{$row['resource_type']}</td>
                                        <td class='{$status_class}'>{$row['status']}</td>
                                        <td><a href='track_usage.php?edit_id={$row['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Edit</a></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No resources found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
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
        // Toggle Add Resource Form
        document.getElementById('showAddResourceForm').addEventListener('click', function() {
            document.getElementById('addResourceForm').style.display = 'block';
            this.style.display = 'none';
        });
        
        document.getElementById('cancelAddResource').addEventListener('click', function() {
            document.getElementById('addResourceForm').style.display = 'none';
            document.getElementById('showAddResourceForm').style.display = 'block';
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

        function applyFilters() {
            const campus = document.getElementById('campus').value;
            const building = document.getElementById('building').value;
            const floor = document.getElementById('floor').value;
            const status = document.getElementById('status').value;

            let url = 'track_usage.php?';
            if (campus) url += `campus=${campus}&`;
            if (building) url += `building=${building}&`;
            if (floor) url += `floor=${floor}&`;
            if (status) url += `status=${status}`;

            window.location.href = url;
        }
    </script>
</body>
</html>