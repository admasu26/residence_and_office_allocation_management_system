<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Access Denied. Please <a href='login.php'>Login</a>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "resource_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM resources WHERE status = 'available'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Resources</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Available Resources</h2>
<table>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Location</th>
        <th>Size</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo ucfirst($row['type']); ?></td>
        <td><?php echo $row['location']; ?></td>
        <td><?php echo $row['size']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php if ($result->num_rows === 0): ?>
    <p>No available resources at the moment.</p>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
