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

function generatePassword($role, $conn) {
    $prefix = '';
    switch ($role) {
        case 'admin': $prefix = 'AMU/AD'; break;
        case 'allocation_committee': $prefix = 'AMU/A'; break;
        case 'staff_member': $prefix = 'AMUS/S'; break;
        case 'managing_director': $prefix = 'AMU/D'; break;
        default: $prefix = 'AMU/G'; break;
    }

    $sql = "SELECT MAX(id) as max_id FROM users WHERE role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] ? $row['max_id'] + 1 : 1;

    return $prefix . str_pad($next_id, 3, '0', STR_PAD_LEFT);
}

// Display generated password from session if available
if (isset($_SESSION['new_user_password'])) {
    $temp_password = $_SESSION['new_user_password'];
    echo "<script>alert('User created successfully! Password: $temp_password');</script>";
    unset($_SESSION['new_user_password']);
}

if (isset($_POST["submit"])) {
    $username = trim($_POST["username"]);
    $role = $_POST["role"];
    $manual_password = trim($_POST["manual_password"] ?? '');
    $is_generated = empty($manual_password);

    if (empty($username)) {
        echo "<script>alert('Username cannot be empty!');</script>";
    } else {
        // Check if username exists
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Username already exists!');</script>";
        } else {
            // Generate or use manual password
            $plain_password = $is_generated ? generatePassword($role, $conn) : $manual_password;
            
            // Hash the password for storage
            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
            
            // Store plain password in session to show once (only for generated passwords)
            if ($is_generated) {
                $_SESSION['new_user_password'] = $plain_password;
            }
            
            // Insert user with hashed password
            $insert_sql = "INSERT INTO users (username, password, role, is_generated) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssi", $username, $hashed_password, $role, $is_generated);
            
            if ($stmt->execute()) {
                // Redirect to prevent form resubmission
                header("Location: create.php");
                exit();
            } else {
                echo "<script>alert('Error creating user: " . $conn->error . "');</script>";
            }
        }
    }
}

// Fetch all users
$users_result = $conn->query("
    SELECT id, username, role, created_at, is_generated 
    FROM users 
    ORDER BY created_at DESC
");

if (!$users_result) {
    die("Error fetching users: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="icon" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Your existing CSS styles */
        body { padding: 20px; margin-left: 250px; background-color: #f8f9fa; color: #333; }
        .sidebar { width: 250px; height: 100vh; background: #1e3a8a; color: white; position: fixed; left: 0; top: 0; }
        /* General Styles */
        body {
            padding: 20px;
            margin-left: 250px;
            position: relative;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
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
            color: var(--sidebar-text);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            cursor: pointer;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .container {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: auto;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-container, .table-container {
            background-color: var(--card-bg);
            color: black;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            width: 48%;
            flex: 1 1 45%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            width: 100%;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--table-text);
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--table-border-color);
            color: var(--table-text);
        }

        th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
        }

        tbody tr {
            background-color: var(--card-bg);
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        td.text-center {
            text-align: center;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .profile-dropdown .dropdown-toggle {
            background: var(--dropdown-bg);
            color: var(--dropdown-text);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .profile-dropdown .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-dropdown .dropdown-menu {
            background: var(--dropdown-bg);
            border: none;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-dropdown .dropdown-item {
            color: var(--dropdown-text);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .profile-dropdown .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Dark Mode Styles */
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --sidebar-bg: #0d1b2a;
            --sidebar-text: #e0e0e0;
            --card-bg: rgb(241, 232, 232);
            --card-text: #e0e0e0;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
            --dropdown-bg: #1e1e1e;
            --dropdown-text: #e0e0e0;
            --table-text: rgb(6, 6, 6);
            --table-border-color: white;
            --table-header-bg: rgb(234, 241, 236);
            --table-header-text: rgb(224, 224, 224);
        }

        /* Light Mode (Default) */
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --sidebar-bg: #1e3a8a;
            --sidebar-text: white;
            --card-bg: white;
            --card-text: #333;
            --card-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            --dropdown-bg: white;
            --dropdown-text: #333;
            --table-text: #333;
            --table-border-color: #ddd;
            --table-header-bg: rgb(3, 45, 193);
            --table-header-text: #333;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .container {
                flex-direction: column;
            }

            .form-container, .table-container {
                width: 100%;
            }

            .profile-dropdown {
                position: static;
                text-align: center;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (same as before) -->
    <nav class="sidebar">
        <!-- ... your existing sidebar code ... -->
    </nav>

    <!-- Profile Dropdown (same as before) -->
    <div class="profile-dropdown dropdown">
        <!-- ... your existing profile dropdown code ... -->
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Create User Form -->
        <div class="form-container">
            <h2><i class="bi bi-person-plus"></i> Create User</h2>
            <form action="create.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="manual_password" class="form-label">Password (Optional)</label>
                    <input type="password" class="form-control" id="manual_password" name="manual_password">
                    <small class="text-muted">Leave blank to auto-generate a password (visible to admin)</small>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="managing_director">Managing Director</option>
                        <option value="allocation_committee">Allocation Committee</option>
                        <option value="staff_member">Staff Member</option>
                    </select>
                </div>
                <button type="submit" name="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create User</button>
            </form>
        </div>

        <!-- User List -->
        <div class="table-container">
            <h2><i class="bi bi-people"></i> User List</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Password Type</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while ($row = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['role']))); ?></td>
                                    <td>
                                        <?php if ($row['is_generated']): ?>
                                            <span class="badge bg-info">System Generated</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y g:i a', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>