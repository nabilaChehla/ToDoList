<?php

// Database credentials
require './conn/conn.php';

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}


// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_project'])) {
    // Get the user ID from the session
    $manager_id = $_SESSION['userid'];

    // Get the project name from the form
    $project_name = $_POST['project_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO PROJECT (PROJECT_NAME, MANAGER_ID) VALUES (?, ?)");
    $stmt->bind_param("si", $project_name, $manager_id);

    // Execute the statement
    if ($stmt->execute()) {
        $message = "New project created successfully";
    } else {
        $message = "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Retrieve all projects created by the user
$manager_id = $_SESSION['userid'];
$result = $conn->query("SELECT PROJECT_NAME FROM PROJECT WHERE MANAGER_ID = $manager_id");

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project</title>
    <style>
        .project-container {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Create New Project</h1>
    
    <!-- Display message -->
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    
    <!-- Project creation form -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="project_name">Project Name:</label>
        <input type="text" id="project_name" name="project_name" required>
        <br><br>
        <button type="submit" name="create_project">Create Project</button>
    </form>

    <h2>Your Projects</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="project-container">
                <p>Project Name: <?php echo htmlspecialchars($row['PROJECT_NAME']); ?></p>
                <!-- Additional project details and tasks can be added here -->
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</body>
</html>
