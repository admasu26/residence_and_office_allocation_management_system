<?php


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle allocation form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = mysqli_real_escape_string($conn, $_POST['request_id']);
    $resource_type = mysqli_real_escape_string($conn, $_POST['resource_type']);
    $resource_name = mysqli_real_escape_string($conn, $_POST['resource_name']);
    $allocation_date = mysqli_real_escape_string($conn, $_POST['allocation_date']);

    // Update allocation status in the database
    $sql = "UPDATE AllocationRequests SET status='allocated', resource_type='$resource_type', resource_name='$resource_name', allocation_date='$allocation_date' WHERE request_id='$request_id'";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='alert alert-success'>Resource allocated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Calculate scores for all pending requests
function calculateScore($row) {
    $score = 0;

    // Years of service
    if ($row['work_range'] == ">8") $score += 10;
    elseif ($row['work_range'] == "5-8") $score += 7;
    elseif ($row['work_range'] == "3-5") $score += 5;
    elseif ($row['work_range'] == "1-3") $score += 3;

    // Academic rank
    if ($row['academic_rank'] == "Professor") $score += 10;
    elseif ($row['academic_rank'] == "Researcher") $score += 8;
    elseif ($row['academic_rank'] == "PhD") $score += 6;
    elseif ($row['academic_rank'] == "MSc") $score += 4;
    elseif ($row['academic_rank'] == "BSc") $score += 2;

    // Marital status
    if ($row['marital_status'] == "married") $score += 5;
    elseif ($row['marital_status'] == "divorced") $score += 3;

    // Disability
    if ($row['disability'] == "yes") $score += 5;

    // Number of children
    $score += min($row['children'], 5); // Max 5 points

    // Gender (e.g., female applicants get extra points)
    if ($row['gender'] == "female") $score += 5;

    // SOAMU (Service Outside AMU)
    if ($row['soamu'] == ">4") $score += 5; // More than 4 years
    elseif ($row['soamu'] == "1-4") $score += 3; // 1-4 years

    return $score;
}

// Fetch and rank pending allocation requests
$sql = "SELECT * FROM AllocationRequests WHERE status='pending'";
$result = $conn->query($sql);

$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['score'] = calculateScore($row);
        $requests[] = $row;
    }
}

// Group requests by desired unit type
$grouped_requests = [];
foreach ($requests as $request) {
    $unit_type = $request['unit_type'];
    if (!isset($grouped_requests[$unit_type])) {
        $grouped_requests[$unit_type] = [];
    }
    $grouped_requests[$unit_type][] = $request;
}

// Sort each group by score (descending) and assign ranks
foreach ($grouped_requests as $unit_type => $group) {
    usort($group, function ($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Assign ranks within the group
    $rank = 1;
    foreach ($group as &$request) {
        $request['rank'] = $rank++;
    }

    $grouped_requests[$unit_type] = $group;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competitive Allocation Page</title>
    <link rel="icon" href="logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #1e3a8a;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #1e3a8a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #152c5b;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-success {
            background-color: #28a745;
            color: white;
        }
        .alert-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Competitive Allocation Management</h2>
        <?= $message; ?>

        <!-- Allocation Form -->
        <h3>Allocate Resource</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="request_id">Select Request:</label>
                <select id="request_id" name="request_id" required>
                    <?php
                    if (!empty($requests)) {
                        foreach ($requests as $request) {
                            echo "<option value='{$request['request_id']}'>{$request['name']} - Score: {$request['score']}</option>";
                        }
                    } else {
                        echo "<option value=''>No pending requests</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="resource_type">Resource Type:</label>
                <select id="resource_type" name="resource_type" required>
                    <option value="residence">Residence</option>
                    <option value="office">Office</option>
                </select>
            </div>

            <div class="form-group">
                <label for="resource_name">Resource Name:</label>
                <input type="text" id="resource_name" name="resource_name" required>
            </div>

            <div class="form-group">
                <label for="allocation_date">Allocation Date:</label>
                <input type="date" id="allocation_date" name="allocation_date" required>
            </div>

            <button type="submit">Allocate Resource</button>
        </form>

        <!-- Ranked Requests Table -->
        <h3>Ranked Allocation Requests</h3>
        <?php
        foreach ($grouped_requests as $unit_type => $group) {
            echo "<h4>Unit Type: " . ucfirst(str_replace('_', ' ', $unit_type)) . "</h4>";
            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>Rank</th>
                        <th>Request ID</th>
                        <th>Name</th>
                        <th>Score</th>
                        <th>Gender</th>
                        <th>SOAMU</th>
                        <th>College</th>
                        <th>Department</th>
                        <th>Employment Date</th>
                    </tr>
                  </thead>
                  <tbody>";
            foreach ($group as $request) {
                echo "<tr>
                        <td>{$request['rank']}</td>
                        <td>{$request['request_id']}</td>
                        <td>{$request['name']}</td>
                        <td>{$request['score']}</td>
                        <td>{$request['gender']}</td>
                        <td>{$request['soamu']}</td>
                        <td>{$request['college']}</td>
                        <td>{$request['department']}</td>
                        <td>{$request['employment_date']}</td>
                      </tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>
</body>
</html>