<?php
include 'dbconnection.php';
session_start();

// Ensure the user is logged in and is an allocation committee member
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'allocation_committee') {
    header("Location: log.php");
    exit();
}

// Check if a request is being approved or rejected
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = null;
    $status = '';

    if (isset($_POST['approve'])) {
        $request_id = $_POST['approve'];
        $status = 'approved';
    } elseif (isset($_POST['reject'])) {
        $request_id = $_POST['reject'];
        $status = 'rejected';
    }

    if ($request_id) {
        // Update the request status in the database
        $update_sql = "UPDATE allocation_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $request_id);

        if ($stmt->execute()) {
            // Fetch applicant's email for notification (if needed)
            $query = "SELECT email FROM allocation_requests WHERE id = ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("i", $request_id);
            $stmt2->execute();
            $stmt2->bind_result($email);
            $stmt2->fetch();
            $stmt2->close();

            // Optional: Send an email notification to the staff member
            if (!empty($email)) {
                $subject = "Allocation Request Status Update";
                $message = "Dear Applicant,\n\nYour housing allocation request has been $status.\n\nBest regards,\nAllocation Committee";
                $headers = "From: no-reply@amu.edu.et";

                mail($email, $subject, $message, $headers);
            }

            echo "<script>alert('Request has been " . $status . " successfully.'); window.location.href='view_requests.php';</script>";
        } else {
            echo "<script>alert('Error updating the request.'); window.location.href='view_requests.php';</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
