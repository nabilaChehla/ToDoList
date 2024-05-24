<?php
include 'db_config.php';

// Insert new task into the database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description'])) {
    $description = $mysqli->real_escape_string($_POST['description']);
    $query = "INSERT INTO tasks (description) VALUES ('$description')";
    if ($mysqli->query($query) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $query . "<br>" . $mysqli->error;
    }
}

// Delete a task from the database
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM tasks WHERE id = $id";
    if ($mysqli->query($query) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error: " . $query . "<br>" . $mysqli->error;
    }
}

// Toggle the state of a task (completed/incomplete)
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $query = "UPDATE tasks SET state = !state WHERE id = $id";
    if ($mysqli->query($query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $query . "<br>" . $mysqli->error;
    }
}

// Fetch all tasks from the database
$result = $mysqli->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = [];

while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);

$mysqli->close();
?>
