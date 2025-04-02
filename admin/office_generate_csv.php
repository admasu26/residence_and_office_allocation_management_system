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

// Fetch report details
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($report_id <= 0) {
    die("Invalid report ID.");
}

$sql = "SELECT title, data FROM office_reports WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();
$report = $result->fetch_assoc();
$stmt->close();

if (!$report) {
    die("Report not found.");
}

$report_title = $report['title'];
$report_data = json_decode($report['data'], true);

if (empty($report_data)) {
    die("No data found for this report.");
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $report_title . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, array_keys($report_data[0]));

// Write CSV rows
foreach ($report_data as $row) {
    fputcsv($output, $row);
}

// Close output stream
fclose($output);
exit;
?>