<?php
require 'conn/conn.php';
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task']) && !empty($_POST['task_title'])) {
        // Add task
        $task_title = $conn->real_escape_string($_POST['task_title']);
        
        // Insert task into todos table
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->bind_param("s", $task_title);
        $stmt->execute();
        $task_id = $stmt->insert_id;
        $stmt->close();

        // Link task with user
        $stmt = $conn->prepare("INSERT INTO user_task (user_id, task_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['toggle_task']) && isset($_POST['task_id'])) {
        // Toggle task completion status
        $task_id = intval($_POST['task_id']);
        
        // Fetch current checked status
        $stmt = $conn->prepare("SELECT checked FROM todos WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->bind_result($checked);
        $stmt->fetch();
        $stmt->close();
        
        // Toggle the checked status
        $new_checked_status = $checked ? 0 : 1;

        $stmt = $conn->prepare("UPDATE todos SET checked = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_checked_status, $task_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['delete_task']) && isset($_POST['task_id'])) {
        // Delete task
        $task_id = intval($_POST['task_id']);
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Retrieve the undone tasks for the logged-in user that are not part of any project or category
$tasks_result = $conn->query("SELECT t.id, t.title, t.checked, DATE(t.date_time) AS task_date
                             FROM todos t
                             JOIN user_task ut ON t.id = ut.task_id
                             WHERE ut.user_id = $user_id
                             AND t.checked = 0
                             AND t.id NOT IN (SELECT task_id FROM user_category_task)
                             AND t.id NOT IN (SELECT task_id FROM project_user_task)
                             ORDER BY t.date_time DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Your Tasks</title>
    <style>
        .task-completed {
            text-decoration: line-through;
        }
        .task-container {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<nav class="nav-list">
                <button class="home-btn"><a href="index.php">tasks</a></button>
                <button class="home-btn"><a href="projects.php">projects</a></button>
                <button><a href="category.php">category</a></button>
                <button><a href="login.php">Change User</a></button>

            </nav>

    <h1>Manage Your Tasks</h1>

    <h2>Add a New Task</h2>
    <form method="POST" action="">
        <label for="task_title">Task Title:</label>
        <input type="text" id="task_title" name="task_title" required>
        <button type="submit" name="add_task">Add Task</button>
    </form>

    <h2>Your Tasks</h2>
    <?php
    $current_date = null; // Variable to keep track of the current date

    while ($task = $tasks_result->fetch_assoc()) {
        $task_date = $task['task_date'];
        $display_date = $task_date === date('Y-m-d') ? 'Today' : $task_date; // Check if date is today

        // Check if the task date is different from the current date
        if ($task_date !== $current_date) {
            // If so, display a line separator and the date
            if ($current_date !== null) {
                echo '<hr>'; // Line separator
            }
            echo "<h3>$display_date</h3>"; // Display the date or "Today"
            $current_date = $task_date; // Update current date
        }

        // Display task
        $task_id = $task['id'];
        $task_title = htmlspecialchars($task['title']);
        $task_completed_class = $task['checked'] ? 'task-completed' : '';
        echo "<div class='task-container'>";
        echo "<form method='POST' action='' style='display:inline;'>";
        echo "<input type='hidden' name='task_id' value='$task_id'>";
        echo "<input type='checkbox' name='toggle_task' onchange='this.form.submit()'>";
        echo "<span class='$task_completed_class'>$task_title</span>";
        echo "</form>";
        echo "<form method='POST' action='' style='display:inline;'>";
        echo "<input type='hidden' name='task_id' value='$task_id'>";
        echo "<button type='submit' name='delete_task'>❌</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
        <a href="completed_tasks.php">SEE COMPLETED PROJECTS</a>

</body>
</html>
