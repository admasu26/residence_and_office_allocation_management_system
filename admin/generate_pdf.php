<?php
session_start();

// Include FPDF library
require('fpdf/fpdf.php'); // Ensure this path is correct

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if report data exists in the session
if (!isset($_SESSION['report_data']) || empty($_SESSION['report_data'])) {
    die("No report data found. Please generate a report first.");
}

// Fetch report data from session
$report_data = $_SESSION['report_data'];
$report_title = $_SESSION['report_title'];

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, $report_title, 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$headers = array_keys($report_data[0]); // Dynamically get headers from the first row of data

// Calculate column widths based on content
$column_widths = [];
$total_width = 0; // Total width of all columns
foreach ($headers as $header) {
    $max_width = $pdf->GetStringWidth($header); // Initialize with header width
    foreach ($report_data as $row) {
        $cell_width = $pdf->GetStringWidth($row[$header]);
        if ($cell_width > $max_width) {
            $max_width = $cell_width;
        }
    }
    $column_widths[$header] = $max_width + 6; // Add minimal padding (reduced from 10 to 6)
    $total_width += $column_widths[$header];
}

// Adjust column widths if the total width exceeds the page width
$page_width = 190; // Default page width (A4 size minus margins)
if ($total_width > $page_width) {
    $scale_factor = $page_width / $total_width;
    foreach ($headers as $header) {
        $column_widths[$header] *= $scale_factor;
    }
}

// Print headers
foreach ($headers as $header) {
    $pdf->Cell($column_widths[$header], 10, ucwords(str_replace('_', ' ', $header)), 1, 0, 'C');
}
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial', '', 12);
foreach ($report_data as $row) {
    foreach ($headers as $header) {
        $pdf->Cell($column_widths[$header], 10, $row[$header], 1, 0, 'C');
    }
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D', $report_title . '.pdf'); // 'D' forces download

$conn->close();
?>