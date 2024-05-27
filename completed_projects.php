<?php
// Start the session
session_start() or trigger_error("", E_USER_ERROR);

// Database credentials
require './conn/conn.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}

// Retrieve completed projects where the user is a manager or has tasks assigned
$user_id = $_SESSION['userid'];
$completed_projects_result = $conn->query("SELECT DISTINCT PROJECT.ID, PROJECT.PROJECT_NAME, PROJECT.MANAGER_ID, PROJECT.CREATED_AT
                                           FROM PROJECT 
                                           LEFT JOIN PROJECT_USER_TASK ON PROJECT.ID = PROJECT_USER_TASK.PROJECT_ID 
                                           LEFT JOIN TODOS ON PROJECT_USER_TASK.TASK_ID = TODOS.ID
                                           WHERE (PROJECT.MANAGER_ID = $user_id 
                                           OR PROJECT_USER_TASK.USER_ID = $user_id)
                                           AND PROJECT.ID NOT IN (
                                               SELECT PROJECT.ID 
                                               FROM PROJECT 
                                               LEFT JOIN PROJECT_USER_TASK ON PROJECT.ID = PROJECT_USER_TASK.PROJECT_ID 
                                               LEFT JOIN TODOS ON PROJECT_USER_TASK.TASK_ID = TODOS.ID
                                               WHERE TODOS.CHECKED = 0
                                           )
                                           AND PROJECT.ID IN (
                                               SELECT PROJECT_USER_TASK.PROJECT_ID
                                               FROM PROJECT_USER_TASK
                                           )
                                           ORDER BY PROJECT.CREATED_AT DESC");

// Fetch tasks and assigned users for each completed project
$completed_projects = [];
while ($project_row = $completed_projects_result->fetch_assoc()) {
    $project_id = $project_row['ID'];

    // Fetch tasks for the current project
    $tasks_result = $conn->query("SELECT TODOS.ID AS TASK_ID, TODOS.TITLE, TODOS.CHECKED, USERS.ID AS USER_ID, USERS.USERNAME 
                                  FROM TODOS
                                  JOIN PROJECT_USER_TASK ON TODOS.ID = PROJECT_USER_TASK.TASK_ID
                                  JOIN USERS ON PROJECT_USER_TASK.USER_ID = USERS.ID
                                  WHERE PROJECT_USER_TASK.PROJECT_ID = $project_id");

    $tasks = [];
    while ($task_row = $tasks_result->fetch_assoc()) {
        $tasks[] = $task_row;
    }

    $project_row['TASKS'] = $tasks;
    $completed_projects[] = $project_row;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Projects</title>
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
            <li class='nav-link'><a href="index.php">Tasks</a> <img src='./images/icons8-to-do-48.png'></li>   
            <li class='nav-link'><a href="projects.php">Projects</a><img src='./images/icons8-project-64.png'></li>    
            <li class='nav-link'><a href="category.php">Category</a><img src='./images/icons8-category-48.png'></li>    
            <li class='nav-link'><a href="login.php">Change User</a><img src='./images/icons8-user-48.png'></li>   
        </nav>
    </header>
    <div class='container'>
    
    <h2>Your Completed Projects</h2>
    <?php if (!empty($completed_projects)): ?>
        <?php foreach ($completed_projects as $project): ?>
            <div class="project-container">
                <h3>Project Name: <?php echo htmlspecialchars($project['PROJECT_NAME']); ?></h3>
                <h4>Tasks</h4>
                <ul>
                    <?php foreach ($project['TASKS'] as $task): ?>
                        <li class='task-container'>
                            <label>
                                <?php echo htmlspecialchars($task['USERNAME']); ?>
                            </label> 
                            <label>
                                <?php echo htmlspecialchars($task['TITLE']) ; ?> 
                            </label> 
                            <input class='checkbox-custom' type="checkbox" disabled <?php echo $task['CHECKED'] ? 'checked' : ''; ?>>
                            
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No completed projects found.</p>
    <?php endif; ?>
    </div>

</body>
</html>
