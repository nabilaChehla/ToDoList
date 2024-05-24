<?php
include 'config.php'; // Make sure this file establishes a database connection and initializes $conn

// Initialize variables
$name = $_POST['task_name'];
$description = $_POST['task_description'];

// Prepare the SQL statement
$sql = "INSERT INTO tasks (task_name, task_description,task_due_date) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $description); // Bind parameters

// Execute the query
if ($stmt->execute() === TRUE) {
    echo "<h1>Task added successfully</h1>"; // Corrected opening tag for h1
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>
