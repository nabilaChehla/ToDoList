<?php
// Start session
require 'conn/conn.php';
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userid'];


// Count tasks done from user_task table
$sql_user_task_done = "SELECT COUNT(*) as count FROM user_task 
                       JOIN todos ON user_task.task_id = todos.id
                       WHERE user_task.user_id = $user_id AND todos.checked = 1";
$result_user_task_done = $conn->query($sql_user_task_done);
$row_user_task_done = $result_user_task_done->fetch_assoc();
$tasks_done_count_user_task = $row_user_task_done['count'];

// Count tasks not done from user_task table
$sql_user_task_undone = "SELECT COUNT(*) as count FROM user_task 
                         JOIN todos ON user_task.task_id = todos.id
                         WHERE user_task.user_id = $user_id AND todos.checked = 0";
$result_user_task_undone = $conn->query($sql_user_task_undone);
$row_user_task_undone = $result_user_task_undone->fetch_assoc();
$tasks_undone_count_user_task = $row_user_task_undone['count'];

// Count tasks done from user_category_task table
$sql_user_category_task_done = "SELECT COUNT(*) as count FROM user_category_task 
                                JOIN todos ON user_category_task.task_id = todos.id
                                WHERE user_category_task.user_id = $user_id AND todos.checked = 1";
$result_user_category_task_done = $conn->query($sql_user_category_task_done);
$row_user_category_task_done = $result_user_category_task_done->fetch_assoc();
$tasks_done_count_user_category_task = $row_user_category_task_done['count'];

// Count tasks not done from user_category_task table
$sql_user_category_task_undone = "SELECT COUNT(*) as count FROM user_category_task 
                                  JOIN todos ON user_category_task.task_id = todos.id
                                  WHERE user_category_task.user_id = $user_id AND todos.checked = 0";
$result_user_category_task_undone = $conn->query($sql_user_category_task_undone);
$row_user_category_task_undone = $result_user_category_task_undone->fetch_assoc();
$tasks_undone_count_user_category_task = $row_user_category_task_undone['count'];

// Total counts
$tasks_done_count_total = $tasks_done_count_user_task +  $tasks_done_count_user_category_task;
$tasks_undone_count_total = $tasks_undone_count_user_task  + $tasks_undone_count_user_category_task;


// Get user ID from session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query to get checked tasks in the past 10 days
    $query = "SELECT COUNT(*) AS task_count, DATE(date_time) AS task_date
              FROM todos
              WHERE checked = 1 AND user_id = $user_id AND date_time >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
              GROUP BY DATE(date_time)
              ORDER BY DATE(date_time)";

    $result = $mysqli->query($query);

    // Fetch result
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['task_date']] = $row['task_count'];
    }

    // Close result set
    $result->close();

    // Close connection
    $mysqli->close();
} else {
    echo "User ID not found in session.";
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Status</title>
    <nav class="nav-list">
                <button class="home-btn"><a href="index.php">tasks</a></button>
                <button class="home-btn"><a href="projects.php">projects</a></button>
                <button><a href="category.php">category</a></button>
                <button><a href="login.php">Change User</a></button>

            </nav>
</head>
<body>
    <h1>Task Status</h1>
    <p>Tasks done: <?php echo $tasks_done_count_total; ?></p>
    <p>Tasks not done: <?php echo $tasks_undone_count_total; ?></p>
</body>
</html>
