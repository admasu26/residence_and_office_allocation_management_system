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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campus = $_POST['campus'];
    $building = $_POST['building'];
    $floor = $_POST['floor'];
    $room_id = $_POST['room_id'];
    $unit_type = $_POST['unit_type'];

    // Insert new resource into the database
    $sql = "INSERT INTO residence_resources (campus, building, floor, room_id, unit_type, status)
            VALUES ('$campus', '$building', '$floor', '$room_id', '$unit_type', 'Available')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION["message"] = "Resource added successfully!";
    } else {
        $_SESSION["error"] = "Error adding resource: " . $conn->error;
    }

    // Redirect to avoid form resubmission
    header("Location: add_resource.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #4a90e2;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        /* Form Styles */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #357abd, #4a90e2);
        }

        /* Success and Error Messages */
        .success {
            color: #28a745;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 2rem;
            }

            input, select, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Resource</h1>

        <!-- Display Success or Error Messages -->
        <?php
        if (isset($_SESSION["message"])) {
            echo "<p class='success'>" . $_SESSION["message"] . "</p>";
            unset($_SESSION["message"]);
        }
        if (isset($_SESSION["error"])) {
            echo "<p class='error'>" . $_SESSION["error"] . "</p>";
            unset($_SESSION["error"]);
        }
        ?>

        <!-- Add Resource Form -->
        <form method="POST" action="">
            <label for="campus">Campus:</label>
            <select id="campus" name="campus" required>
                <option value="Main">Main</option>
                <option value="Abaya">Abaya</option>
                <option value="Nechisar">Nechisar</option>
                <option value="Chamo">Chamo</option>
                <option value="Kulfo">Kulfo</option>
                <option value="Sawula">Sawula</option>
            </select>

            <label for="building">Building:</label>
            <input type="text" id="building" name="building" required>

            <label for="floor">Floor:</label>
            <input type="number" id="floor" name="floor" required>

            <label for="room_id">Room ID:</label>
            <input type="number" id="room_id" name="room_id" required>

            <label for="unit_type">Unit Type:</label>
            <select id="unit_type" name="unit_type" required>
                <option value="three_bedroom">Three-Bedroom</option>
                <option value="two_bedroom">Two-Bedroom</option>
                <option value="one_bedroom">One-Bedroom</option>
                <option value="studio">Studio</option>
                <option value="service">Service Quarters</option>
            </select>

            <button type="submit">Add Resource</button>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>