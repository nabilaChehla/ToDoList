<?php
include 'db_config.php';

header('Content-Type: application/json'); // Set response header to JSON

$response = array(); // Initialize response array

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description'])) {
    $description = $mysqli->real_escape_string($_POST['description']);
    $query = "INSERT INTO tasks (description) VALUES ('$description')";
    if ($mysqli->query($query) === TRUE) {
        $response['success'] = true;
        $response['message'] = "New record created successfully";
    } else {
        $response['success'] = false;
        $response['error'] = "Error: " . $query . "<br>" . $mysqli->error;
    }
} else {
    $result = $mysqli->query("SELECT * FROM tasks ORDER BY created_at DESC");
    if ($result) {
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        $response['success'] = true;
        $response['tasks'] = $tasks;
    } else {
        $response['success'] = false;
        $response['error'] = "Error fetching tasks: " . $mysqli->error;
    }
}

echo json_encode($response); // Output JSON response

$mysqli->close();
?>
