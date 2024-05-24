<?php
include 'config.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM tasks"; // Replace 'your_table' with your actual table name
$result = $conn->query($sql);

if ($result !== false) {
    if ($result->num_rows > 0) {
        // Output data of each row
        "<br>"
        while ($row = $result->fetch_assoc()) {
            echo "Task name: " . $row["task_name"] . "<br>";
            echo "Task des: " . $row["task_description"] . "<br><br><br>";
        }
    } else {
        echo "No past tasks found";
    }
} else {
    echo "Error executing query: " . $conn->error;
}

$conn->close();
?>
