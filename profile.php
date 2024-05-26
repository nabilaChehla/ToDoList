<?php
require 'conn/conn.php';
session_start();

// Check if the user is logged in
if(isset($_SESSION['userid'])) {
    $userId = $_SESSION['userid'];
    
    // Query to count completed tasks for the user
    $query_completed_tasks = "SELECT COUNT(*) AS num_completed_tasks FROM todos 
    WHERE id IN (
        SELECT task_id FROM user_task WHERE user_id = ?
        UNION
        SELECT task_id FROM user_category_task WHERE user_id = ?
    ) AND checked = 1";     

// Prepare the statement for completed tasks
$statement_completed_tasks = $conn->prepare($query_completed_tasks);

// Bind the parameter for completed tasks
$statement_completed_tasks->bind_param("ii", $userId, $userId);

// Execute the query for completed tasks
$statement_completed_tasks->execute();

    
    // Bind the result for completed tasks
    $statement_completed_tasks->bind_result($numCompletedTasks);
    
    // Fetch the result for completed tasks
    $statement_completed_tasks->fetch();
    
    // Close the statement for completed tasks
    $statement_completed_tasks->close();





    // Query to count incomplete tasks for the user
    $query_incomplete_tasks = "SELECT COUNT(*) AS num_completed_tasks FROM todos 
    WHERE id IN (
        SELECT task_id FROM user_task WHERE user_id = ?
        UNION
        SELECT task_id FROM user_category_task WHERE user_id = ?
    ) AND checked = 0";   
    
    // Prepare the statement for incomplete tasks
    $statement_incomplete_tasks = $conn->prepare($query_incomplete_tasks);
    
// Bind the parameter for completed tasks
$statement_incomplete_tasks->bind_param("ii", $userId, $userId);

// Execute the query for completed tasks
$statement_incomplete_tasks->execute();
    
    // Bind the result for incomplete tasks
    $statement_incomplete_tasks->bind_result($numIncompleteTasks);
    
    // Fetch the result for incomplete tasks
    $statement_incomplete_tasks->fetch();
    
    // Close the statement for incomplete tasks
    $statement_incomplete_tasks->close();
    
    // Query to count projects user is participating in
    $query_projects = "SELECT COUNT(*) AS num_projects FROM project_user_task 
                       WHERE user_id = ?";
    
    // Prepare the statement for projects
    $statement_projects = $conn->prepare($query_projects);
    
    // Bind the parameter for projects
    $statement_projects->bind_param("i", $userId);
    
    // Execute the query for projects
    $statement_projects->execute();
    
    // Bind the result for projects
    $statement_projects->bind_result($numProjects);
    
    // Fetch the result for projects
    $statement_projects->fetch();
    
    // Close the statement for projects
    $statement_projects->close();
    
    // Query to count categories user has
    $query_categories = "SELECT COUNT(*) AS num_categories FROM category 
                         WHERE user_id = ?";
    
    // Prepare the statement for categories
    $statement_categories = $conn->prepare($query_categories);
    
    // Bind the parameter for categories
    $statement_categories->bind_param("i", $userId);
    
    // Execute the query for categories
    $statement_categories->execute();
    
    // Bind the result for categories
    $statement_categories->bind_result($numCategories);
    
    // Fetch the result for categories
    $statement_categories->fetch();
    
    // Close the statement for categories
    $statement_categories->close();
    
    // Query to count categories where all tasks are done
    $query_categories_all_done = "SELECT COUNT(*) AS num_categories_all_done FROM (
                                    SELECT c.id
                                    FROM category c
                                    LEFT JOIN user_category_task uct ON c.id = uct.cat_id
                                    LEFT JOIN todos t ON uct.task_id = t.id
                                    WHERE c.user_id = ?
                                    GROUP BY c.id
                                    HAVING COUNT(*) = SUM(t.checked)
                                ) AS categories_all_done";



    
    // Prepare the statement for categories where all tasks are done
    $statement_categories_all_done = $conn->prepare($query_categories_all_done);
    
    // Bind the parameter for categories where all tasks are done
    $statement_categories_all_done->bind_param("i", $userId);
    
    // Execute the query for categories where all tasks are done
    $statement_categories_all_done->execute();
    
    // Bind the result for categories where all tasks are done
    $statement_categories_all_done->bind_result($numCategoriesAllDone);
    
    // Fetch the result for categories where all tasks are done
    $statement_categories_all_done->fetch();
    
    // Close the statement for categories where all tasks are done
    $statement_categories_all_done->close();
    
    // Calculate the number of categories where not all tasks are done
    $numCategoriesNotAllDone = $numCategories - $numCategoriesAllDone;
    
    $username = $_SESSION["username"];
    $statisticsHTML = "
        <div class='statistics'>
            <p>Welcome, $username!</p>
            <p>Tasks Completed: <span class='completed'>$numCompletedTasks</span></p>
            <p>Tasks Incomplete: <span class='incomplete'>$numIncompleteTasks</span></p>
            <p>Projects Participating In: $numProjects</p>
            <p>Categories: $numCategories</p>
            <p>Categories with All Tasks Done: $numCategoriesAllDone</p>
            <p>Categories with Incomplete Tasks: $numCategoriesNotAllDone</p>
        </div>";
} else {
    // User not logged in
    $statisticsHTML = "<p>Please log in to view completed tasks.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Your Tasks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="nav-list">
        <button class="home-btn"><a href="index.php">Tasks</a></button>
        <button class="home-btn"><a href="projects.php">Projects</a></button>
        <button><a href="completed_projects.php">Completed Projects</a></button>
        <button><a href="login.php">Change User</a></button>
        <button><a href="completed_tasks.php">Completed Tasks</a></button>
        <button><a href="category.php">Category</a></button>
        <button><a href="profile.php">Profile</a></button>
    </nav>

    <?php echo $statisticsHTML; ?>
</body>
</html>