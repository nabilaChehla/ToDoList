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

        echo "Task added successfully.<br>";
    }

    if (isset($_POST['toggle_task']) && isset($_POST['task_id'])) {
        // Toggle task completion status
        $task_id = intval($_POST['task_id']);
        $stmt = $conn->prepare("UPDATE todos SET checked = NOT checked WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_task']) && isset($_POST['task_id'])) {
        // Delete task
        $task_id = intval($_POST['task_id']);
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch tasks for the user that are not in any project or category
$tasks_result = $conn->query("SELECT t.id, t.title, t.checked
                             FROM todos t
                             JOIN user_task ut ON t.id = ut.task_id
                             WHERE ut.user_id = $user_id
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
            <nav class="nav-list">
                <button class="home-btn"><a href="index.php">tasks</a></button>
                <button class="home-btn"><a href="projects.php">projects</a></button>
                <button><a href="completed_projects.php">Completed Projects</a></button>
                <button><a href="login.php">Change User</a></button>
                <button><a href="completed_tasks.php">Completed Tasks</a></button>
                <button><a href="category.php">category</a></button>
                <button>Statistiques</button>
            </nav>
</head>
<body>
    <h1>Manage Your Tasks</h1>

    <h2>Add a New Task</h2>
    <form method="POST" action="">
        <label for="task_title">Task Title:</label>
        <input type="text" id="task_title" name="task_title" required>
        <button type="submit" name="add_task">Add Task</button>
    </form>

    <h2>Your Tasks</h2>
    <?php
    while ($task = $tasks_result->fetch_assoc()) {
        $task_id = $task['id'];
        $task_title = htmlspecialchars($task['title']);
        $checked = $task['checked'] ? 'checked' : '';
        $task_completed_class = $task['checked'] ? 'task-completed' : '';
        echo "<div class='task-container'>";
        echo "<form method='POST' action='' style='display:inline;'>";
        echo "<input type='hidden' name='task_id' value='$task_id'>";
        echo "<input type='checkbox' name='toggle_task' onchange='this.form.submit()' $checked>";
        echo "<span class='$task_completed_class'>$task_title</span>";
        echo "</form>";
        echo "<form method='POST' action='' style='display:inline;'>";
        echo "<input type='hidden' name='task_id' value='$task_id'>";
        echo "<button type='submit' name='delete_task'>❌</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
</body>
</html>
