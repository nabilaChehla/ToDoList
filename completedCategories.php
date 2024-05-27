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

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $currentDate = date('Y-m-d', strtotime($row['created_at']));
            $categoriesByDate[$currentDate][] = [
                'cat_name' => $row['cat_name'],
                'cat_id' => $row['cat_id']
            ];
        }
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
    <link rel="stylesheet" href="css/completed_projects.css">
    <link rel="stylesheet" href="./css/checkboxStyle.css">
    <link rel="stylesheet" href="./css/project.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
<header>
        <nav class="nav-list">
            <div>
                <img src="./images/icons8-profile-picture-96.png">
                <h2 class="header-title"><?php echo $_SESSION['username']; ?></h2>

            </div>
            <li class='nav-link'><a href="task.php">Tasks</a> <img src='./images/icons8-to-do-48.png'></li>   
            <li class='nav-link'><a href="projects.php">Projects</a><img src='./images/icons8-project-64.png'></li>    
            <li class='nav-link'><a href="category.php">Category</a><img src='./images/icons8-category-48.png'></li>    
            <li class='nav-link'><a href="login.php">Change User</a><img src='./images/icons8-user-48.png'></li>   
        </nav>
    </header>
    <div class="container">
    <h2>Completed categories</h2>
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
                        echo "<label><del>".$task_row['title']."</del></label>";

                            echo "<input class='checkbox-custom' type='checkbox' checked disabled> "  ;


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
    </div>
</body>
</html>
