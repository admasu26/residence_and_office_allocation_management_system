<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the maintenance issue details by ID
if (isset($_GET['id'])) {
    $issue_id = $_GET['id'];
    $sql = "SELECT * FROM maintenance WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $issue = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Maintenance Issue</title>
    <link rel="icon" href="logo.png">
    <style>
        body { font-family: Arial, sans-serif; }
        .issue-details { width: 80%; margin: 50px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { background: #1e3a8a; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
        button:hover { background: #00264d; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Maintenance Issue Details</h2>
    <div class="issue-details">
        <table>
            <tr>
                <th>BF No.</th>
                <td><?php echo $issue["bfno"]; ?></td>
            </tr>
            <tr>
                <th>Requested By</th>
                <td><?php echo $issue["request_by"]; ?></td>
            </tr>
            <tr>
                <th>Work Required</th>
                <td><?php echo $issue["work_required"]; ?></td>
            </tr>
            <tr>
                <th>Location</th>
                <td><?php echo $issue["location"]; ?></td>
            </tr>
            <tr>
                <th>Date</th>
                <td><?php echo $issue["date"]; ?></td>
            </tr>
            <tr>
                <th>Work Type</th>
                <td><?php echo $issue["work_type"]; ?></td>
            </tr>
            <tr>
                <th>Materials Required</th>
                <td><?php echo $issue["material_list"]; ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo $issue["status"]; ?></td>
            </tr>
            <?php if ($issue["status"] == "Resolved") { ?>
            <tr>
                <th>Resolution Details</th>
                <td><?php echo $issue["resolution_details"]; ?></td>
            </tr>
            <tr>
                <th>Resolved By</th>
                <td><?php echo $issue["resolved_by"]; ?></td> <!-- You can map this to the user's name later -->
            </tr>
            <tr>
                <th>Resolution Date</th>
                <td><?php echo $issue["resolution_date"]; ?></td>
            </tr>
            <?php } ?>
        </table>
        <a href="track_maintenance.php">Back to Issue List</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
