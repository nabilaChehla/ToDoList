<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

require './conn/conn.php';

$user_id = $_SESSION['userid'];

// Handle task deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // Ensure the task belongs to the logged-in user and is not part of any project
    $sql = "DELETE TODOS FROM TODOS 
            JOIN USER_TASK ON TODOS.ID = USER_TASK.TASK_ID 
            LEFT JOIN PROJECT_USER_TASK ON TODOS.ID = PROJECT_USER_TASK.TASK_ID
            WHERE TODOS.ID = ? AND USER_TASK.USER_ID = ? AND PROJECT_USER_TASK.TASK_ID IS NULL";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Retrieve the checked tasks for the logged-in user that are not part of any project
$sql = "SELECT TODOS.ID, TODOS.TITLE, TODOS.DATE_TIME 
        FROM TODOS 
        JOIN USER_TASK ON TODOS.ID = USER_TASK.TASK_ID 
        LEFT JOIN PROJECT_USER_TASK ON TODOS.ID = PROJECT_USER_TASK.TASK_ID
        WHERE USER_TASK.USER_ID = ? AND TODOS.CHECKED = 1 AND PROJECT_USER_TASK.TASK_ID IS NULL
        ORDER BY TODOS.DATE_TIME DESC";

$tasks = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Completed Tasks</title>
    <link rel="stylesheet" href="./css/complited.css">

</head>
<body>
<header class="light-header">
<nav class="nav-list">
                <button class="home-btn"><a href="index.php">tasks</a></button>
                <button class="home-btn"><a href="projects.php">projects</a></button>
                <button><a href="completed_projects.php">Completed Projects</a></button>
                <button><a href="login.php">Change User</a></button>
                <button><a href="completed_tasks.php">Completed Tasks</a></button>
                <button><a href="category.php">category</a></button>
                <button><a href="profile.php">profile</a></button>
            </nav>
           
        </header>
    <h1>Your Completed Tasks</h1>
    <?php foreach ($tasks as $task): ?>
        <div class="task-container">
            <p class="strikethrough"><strong>Title:</strong> <?= htmlspecialchars($task['TITLE']); ?></p>
            <p class="strikethrough"><strong>Date:</strong> <?= htmlspecialchars($task['DATE_TIME']); ?></p>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" name="task_id" value="<?= $task['ID']; ?>">
                <button type="submit">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
