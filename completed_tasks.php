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

function formatDate($date) {
    $today = new DateTime();
    $taskDate = new DateTime($date);
    if ($today->format('Y-m-d') === $taskDate->format('Y-m-d')) {
        return "Today";
    }
    return $taskDate->format('F j, Y');
}

$lastDate = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Completed Tasks</title>
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
        <h2>Your Completed Tasks</h2>
        <?php foreach ($tasks as $task): ?>
            <?php
            $currentDate = formatDate($task['DATE_TIME']);
            if ($currentDate !== $lastDate) {
                if ($lastDate !== null) {
                    echo "<hr>";
                }
                echo "<h3>{$currentDate}</h3>";
                $lastDate = $currentDate;
            }
            ?>
            <div class="task-container">
            <input class='checkbox-custom' type="checkbox" disabled checked>
                <label ><del> <?=  htmlspecialchars($task['TITLE']); ?></del></label>
                <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="task_id" value="<?= $task['ID']; ?>">
                    <button class="delete-btn" type="submit">❌</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
