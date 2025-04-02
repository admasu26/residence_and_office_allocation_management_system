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

// Handle appeal resolution
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_appeal'])) {
    $appeal_id = $_POST['appeal_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $resolution_message = $_POST['resolution_message'];

    // First, get the appeal details
    $get_appeal_sql = "SELECT username, allocation_type FROM appeals WHERE id = ?";
    $stmt = $conn->prepare($get_appeal_sql);
    $stmt->bind_param("i", $appeal_id);
    $stmt->execute();
    $appeal_result = $stmt->get_result();
    $appeal_data = $appeal_result->fetch_assoc();
    $stmt->close();

    // Update the appeal status
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    $update_sql = "UPDATE appeals SET status = ?, resolution_message = ?, resolved_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $resolution_message, $appeal_id);

    if ($stmt->execute()) {
        // Create notification message
        $notification_message = "Your appeal for " . $appeal_data['allocation_type'] . " has been " . strtolower($status) . ". " . $resolution_message;
        
        // Insert notification into database
        $notification_sql = "INSERT INTO notifications (username, message, created_at) VALUES (?, ?, NOW())";
        $stmt_notification = $conn->prepare($notification_sql);
        $stmt_notification->bind_param("ss", $appeal_data['username'], $notification_message);
        $stmt_notification->execute();
        $stmt_notification->close();
        
        $_SESSION["message"] = "Appeal resolved successfully and notification sent.";
    } else {
        $_SESSION["error"] = "Error: " . $conn->error;
    }
    $stmt->close();

    // Redirect to the same page to display updated appeal list
    header("Location: solve_appeal.php");
    exit();
}

// Fetch all pending appeals
$pending_sql = "SELECT * FROM appeals WHERE status = 'Pending' ORDER BY created_at DESC";
$pending_result = $conn->query($pending_sql);

if (!$pending_result) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solve Appeal</title>
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

        .btn-history {
            margin-bottom: 20px;
        }
        
        .appeal-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .timestamp {
            font-size: 0.8rem;
            color: #6c757d;
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
            <h1><i class="fas fa-gavel"></i> Solve Appeal</h1>
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

            <!-- Button to View Resolved Appeals -->
            <div class="btn-history">
                <a href="resolved_appeal_history.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i> View Resolved Appeals
                </a>
            </div>

            <!-- Pending Appeals Table -->
            <h2><i class="fas fa-list"></i> Pending Appeals</h2>
            <?php if ($pending_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Username</th>
                                <th><i class="fas fa-home"></i> Allocation Type</th>
                                <th><i class="fas fa-calendar"></i> Submitted</th>
                                <th><i class="fas fa-cogs"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['allocation_type']) ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $row['id'] ?>">
                                            <i class="fas fa-gavel"></i> Resolve
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal for resolving the appeal -->
                                <div class="modal fade" id="resolveModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="resolveModalLabel">Resolve Appeal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="appeal-details">
                                                    <h5>Appeal Details</h5>
                                                    <p><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
                                                    <p><strong>Allocation Type:</strong> <?= htmlspecialchars($row['allocation_type']) ?></p>
                                                    <p><strong>Submitted:</strong> <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></p>
                                                    <p><strong>Reason:</strong></p>
                                                    <p><?= htmlspecialchars($row['appeal_reason']) ?></p>
                                                </div>
                                                
                                                <form method="POST" action="">
                                                    <input type="hidden" name="appeal_id" value="<?= $row['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="action" class="form-label">Action:</label>
                                                        <select name="action" class="form-select" required>
                                                            <option value="">Select action...</option>
                                                            <option value="approve">Approve</option>
                                                            <option value="reject">Reject</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="resolution_message" class="form-label">Resolution Message:</label>
                                                        <textarea name="resolution_message" class="form-control" rows="4" required 
                                                                  placeholder="Enter detailed resolution message that will be sent to the staff member..."></textarea>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="resolve_appeal" class="btn btn-primary">
                                                            <i class="fas fa-check"></i> Submit Resolution
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No pending appeals found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>