<?php
session_start();
require 'conn/conn.php';

$categoriesByDate = [];

if (isset($_SESSION['userid'])) {
    $userId = $_SESSION['userid'];

    $sql = "SELECT c.id AS cat_id, c.cat_name, c.created_at, t.id AS task_id, t.title, t.checked
            FROM category c
            LEFT JOIN user_category_task uct ON c.id = uct.cat_id
            LEFT JOIN todos t ON uct.task_id = t.id
            WHERE uct.user_id = $userId
            GROUP BY c.id
            HAVING COUNT(*) = SUM(t.checked)
            ORDER BY c.created_at DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $currentDate = date('Y-m-d', strtotime($row['created_at']));
            $categoriesByDate[$currentDate][] = [
                'cat_name' => $row['cat_name'],
                'cat_id' => $row['cat_id']
            ];
        }
    } else {
        echo "<p>No categories found with all tasks completed.</p>";
    }
} else {
    echo "<p>User ID not found in session.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Completed Tasks</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    .category-container {
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 20px;
        padding: 10px;
    }
    .category-name {
        color: #333;
        margin-bottom: 10px;
    }
    .task-list {
        list-style-type: none;
        padding: 0;
    }
    .task-item {
        margin-bottom: 5px;
    }
    input[type="checkbox"] {
        margin-right: 5px;
    }
    del {
        color: #999;
    }
    hr {
        border: 1px solid #ccc;
        margin: 20px 0;
    }
    .date-header {
        font-weight: bold;
        font-size: 1.2em;
        margin-top: 20px;
    }
</style>
<nav class="nav-list">
                <button class="home-btn"><a href="index.php">tasks</a></button>
                <button class="home-btn"><a href="projects.php">projects</a></button>
                <button><a href="category.php">category</a></button>
                <button><a href="login.php">Change User</a></button>

            </nav>
</head>
<body>
    <h1>Completed categories</h1>
<?php
if (!empty($categoriesByDate)) {
    $today = date('Y-m-d');
    foreach ($categoriesByDate as $date => $categories) {
        $displayDate = ($date === $today) ? 'Today' : $date;
        echo "<div class='date-header'>Created on: $displayDate</div><hr>";
        foreach ($categories as $category) {
            echo "<div class='category-container'>";
            echo "<div class='category-name'>" . $category['cat_name'] . "</div>";
            echo "<ul class='task-list'>";
            $tasks_sql = "SELECT * FROM todos WHERE id IN (SELECT task_id FROM user_category_task WHERE cat_id = " . $category['cat_id'] . ")";
            $tasks_result = $conn->query($tasks_sql);
            while ($task_row = $tasks_result->fetch_assoc()) {
                echo "<li class='task-item'>";
                if ($task_row['checked'] == 1) {
                    echo "<input type='checkbox' checked disabled> <del>" . $task_row['title'] . "</del>";
                } else {
                    echo "<input type='checkbox' disabled> " . $task_row['title'];
                }
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>"; // .category-container
        }
    }
} else {
    echo "<p>No categories found with all tasks completed.</p>";
}
$conn->close();
?>
</body>
</html>
