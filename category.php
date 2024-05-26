<?php
require 'conn/conn.php';
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['new_category']) && !empty($_POST['category_name'])) {
        // Create new category
        $category_name = $conn->real_escape_string($_POST['category_name']);
        $stmt = $conn->prepare("INSERT INTO category (cat_name, user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("si", $category_name, $user_id);
        $stmt->execute();
        $stmt->close();
        echo "New category created successfully.<br>";
    }

    if (isset($_POST['add_task']) && !empty($_POST['task_title']) && !empty($_POST['category_id'])) {
        // Add task to a category
        $task_title = $conn->real_escape_string($_POST['task_title']);
        $category_id = intval($_POST['category_id']);

        // Insert task into todos table
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->bind_param("s", $task_title);
        $stmt->execute();
        $task_id = $stmt->insert_id;
        $stmt->close();

        // Link task with user and category
        $stmt = $conn->prepare("INSERT INTO user_category_task (user_id, cat_id, task_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $category_id, $task_id);
        $stmt->execute();
        $stmt->close();

        echo "Task added to category successfully.<br>";
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

    if (isset($_POST['delete_category']) && isset($_POST['category_id'])) {
        // Delete category
        $category_id = intval($_POST['category_id']);
        $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch existing categories for the current user, ordered by creation date
$categories_result = $conn->query("SELECT id, cat_name FROM category WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch tasks for the user
$tasks_result = $conn->query("SELECT t.id, t.title, t.checked, c.id AS category_id, c.cat_name 
                             FROM todos t 
                             JOIN user_category_task uct ON t.id = uct.task_id 
                             JOIN category c ON uct.cat_id = c.id 
                             WHERE uct.user_id = $user_id");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories and Tasks</title>
    <style>
        .task-completed {
            text-decoration: line-through;
        }
        .category-container {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
        }
        .category-title {
            font-size: 1.2em;
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
                <button><a href="profile.php">profile</a></button>
                <button><a href="completedCategories.php">completedCategories</a></button>
            </nav>
</head>
<body>
    <h1>Manage Categories and Tasks</h1>

    <h2>Create a New Category</h2>
    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <button type="submit" name="new_category">Create Category</button>
    </form>

    <?php
    $categories = [];
    while ($row = $tasks_result->fetch_assoc()) {
        $categories[$row['category_id']]['cat_name'] = $row['cat_name'];
        $categories[$row['category_id']]['tasks'][] = $row;
    }

    foreach ($categories_result as $category) {
        $category_id = $category['id'];
        $cat_name = $category['cat_name'];
        echo "<div class='category-container'>";
        echo "<div class='category-title'>" . htmlspecialchars($cat_name) . "</div>";

        // Button to delete category
        echo "<form method='POST' action='' style='display:inline;'>";
        echo "<input type='hidden' name='category_id' value='$category_id'>";
        echo "<button type='submit' name='delete_category'>Delete Category</button>";
        echo "</form>";

        // Form to add task to this category
        echo "<form method='POST' action=''>";
        echo "<input type='hidden' name='category_id' value='$category_id'>";
        echo "<label for='task_title_$category_id'>Task Title:</label>";
        echo "<input type='text' id='task_title_$category_id' name='task_title' required>";
        echo "<button type='submit' name='add_task'>Add Task</button>";
        echo "</form>";

        if (isset($categories[$category_id]['tasks'])) {
            echo "<ul>";
            foreach ($categories[$category_id]['tasks'] as $task) {
                $task_id = $task['id'];
                $task_title = htmlspecialchars($task['title']);
                $checked = $task['checked'] ? 'checked' : '';
                $task_completed_class = $task['checked'] ? 'task-completed' : '';
                echo "<li>";
                echo "<form method='POST' action='' style='display:inline;'>";
                echo "<input type='hidden' name='task_id' value='$task_id'>";
                echo "<input type='checkbox' name='toggle_task' onchange='this.form.submit()' $checked>";
                echo "<span class='$task_completed_class'>$task_title</span>";
                echo "</form>";
                echo "<form method='POST' action='' style='display:inline;'>";
                echo "<input type='hidden' name='task_id' value='$task_id'>";
                echo "<button type='submit' name='delete_task'>❌</button>";
                echo "</form>";
                echo "</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
    ?>
</body>
</html>
