<?php
// Start the session
session_start() or trigger_error("", E_USER_ERROR);

// Database credentials
require './conn/conn.php';

echo "<h1> Welcome " . $_SESSION["username"] . "</h1>";
// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}

// Initialize variables
$project_name = "";
$message = "";
$task_title = "";
$task_message = "";
$task_update_message = "";

// If project creation form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_project'])) {
    // Get the user ID from the session
    $manager_id = $_SESSION['userid'];

    // Get the project name from the form
    $project_name = $_POST['project_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO PROJECT (PROJECT_NAME, MANAGER_ID) VALUES (?, ?)");
    $stmt->bind_param("si", $project_name, $manager_id);

    // Execute the statement
    if ($stmt->execute()) {
        $message = "New project created successfully";
    } else {
        $message = "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// If task creation form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_task'])) {
    // Get the user ID from the session
    $manager_id = $_SESSION['userid'];

    // Get the task details from the form
    $task_title = $_POST['task_title'];
    $project_id = $_POST['project_id'];
    $assigned_user_id = $_POST['assigned_user_id'];

    // Prepare and bind to insert the task
    $stmt = $conn->prepare("INSERT INTO TODOS (TITLE) VALUES (?)");
    $stmt->bind_param("s", $task_title);

    // Execute the statement
    if ($stmt->execute()) {
        $task_id = $stmt->insert_id;

        // Insert into USER_TASK
        $stmt_user_task = $conn->prepare("INSERT INTO USER_TASK (USER_ID, TASK_ID) VALUES (?, ?)");
        $stmt_user_task->bind_param("ii", $assigned_user_id, $task_id);
        $stmt_user_task->execute();
        $stmt_user_task->close();

        // Insert into PROJECT_USER_TASK
        $stmt_project_user_task = $conn->prepare("INSERT INTO PROJECT_USER_TASK (PROJECT_ID, USER_ID, TASK_ID) VALUES (?, ?, ?)");
        $stmt_project_user_task->bind_param("iii", $project_id, $assigned_user_id, $task_id);
        $stmt_project_user_task->execute();
        $stmt_project_user_task->close();

        $task_message = "New task created and assigned successfully";
    } else {
        $task_message = "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// If task checkbox state change is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task'])) {
    // Get the user ID from the session
    $user_id = $_SESSION['userid'];

    // Get the task ID and checked state from the form
    $task_id = $_POST['task_id'];
    $checked = isset($_POST['checked']) ? 1 : 0;

    // Verify if the task belongs to the user
    $stmt_verify = $conn->prepare("SELECT USER_ID FROM USER_TASK WHERE USER_ID = ? AND TASK_ID = ?");
    $stmt_verify->bind_param("ii", $user_id, $task_id);
    $stmt_verify->execute();
    $stmt_verify->store_result();

    if ($stmt_verify->num_rows > 0) {
        // Update the task's checked state
        $stmt_update = $conn->prepare("UPDATE TODOS SET CHECKED = ? WHERE ID = ?");
        $stmt_update->bind_param("ii", $checked, $task_id);

        if ($stmt_update->execute()) {
            $task_update_message = "Task updated successfully";
        } else {
            $task_update_message = "Error updating task: " . $stmt_update->error;
        }

        $stmt_update->close();
    } else {
        $task_update_message = "You are not authorized to update this task.";
    }

    $stmt_verify->close();
}

// Retrieve projects where the user is a manager or has tasks assigned and not all tasks are completed
$user_id = $_SESSION['userid'];
$projects_result = $conn->query("
    SELECT DISTINCT PROJECT.ID, PROJECT.PROJECT_NAME, PROJECT.MANAGER_ID, PROJECT.CREATED_AT
    FROM PROJECT 
    LEFT JOIN PROJECT_USER_TASK ON PROJECT.ID = PROJECT_USER_TASK.PROJECT_ID 
    LEFT JOIN TODOS ON PROJECT_USER_TASK.TASK_ID = TODOS.ID
    WHERE (PROJECT.MANAGER_ID = $user_id OR PROJECT_USER_TASK.USER_ID = $user_id OR PROJECT_USER_TASK.USER_ID IS NULL)
    AND (TODOS.CHECKED = 0 OR TODOS.CHECKED IS NULL)
    AND (PROJECT_USER_TASK.USER_ID IS NOT NULL OR PROJECT.MANAGER_ID = $user_id)
    ORDER BY PROJECT.CREATED_AT DESC
");

// Retrieve all users for task assignment
$users_result = $conn->query("SELECT ID, USERNAME FROM USERS");

// Fetch users in an array to reuse
$users = [];
while ($user_row = $users_result->fetch_assoc()) {
    $users[$user_row['ID']] = $user_row['USERNAME'];
}

// Fetch tasks and assigned users for each project
$projects = [];
while ($project_row = $projects_result->fetch_assoc()) {
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
    $projects[] = $project_row;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project and Tasks</title>
    <link rel="stylesheet" href="./css/project.css">

</head>
<body>
        <header class="light-header">
            <nav class="nav-list">
                <button class="home-btn"><a href="index.php">Projects</a></button>
                <button><a href="completed_projects.php">Completed Projects</a></button>
                <button><a href="login.php">Change User</a></button>
                <button><a href="completed_tasks.php">Completed Tasks</a></button>
                <button>Statistiques</button>
            </nav>
           
        </header>
    <h1>Create New Project and Tasks</h1>
    
    <!-- Display project creation message -->
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- Project creation form -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="project_name">Project Name:</label>
        <input type="text" id="project_name" name="project_name" required>
        <br><br>
        <button type="submit" name="create_project">Create Project</button>
    </form>

    <h2>Your Projects</h2>
    <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $project): ?>
            <div class="project-container">
                <h3>Project Name: <?php echo htmlspecialchars($project['PROJECT_NAME']); ?></h3>

                <!-- Task creation form for each project (only for managers) -->
                <?php if ($project['MANAGER_ID'] == $_SESSION['userid']): ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <label for="task_title">Task Title:</label>
                        <input type="text" id="task_title" name="task_title" required>
                        <br>
                        <label  for="assigned_user_id">Assign to User:</label>
                        <select id="assigned_user_id" name="assigned_user_id" required>
                            <?php foreach ($users as $user_id => $username): ?>
                                <option value="<?php echo $user_id; ?>"><?php echo htmlspecialchars($username); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="project_id" value="<?php echo $project['ID']; ?>">
                        <br><br>
                        <button type="submit" name="create_task">Add Task</button>
                    </form>
                <?php endif; ?>

                <!-- Display task creation message -->
                <?php if (!empty($task_message)): ?>
                    <p><?php echo $task_message; ?></p>
                <?php endif; ?>

                <!-- Display task update message -->
                <?php if (!empty($task_update_message)): ?>
                    <p><?php echo $task_update_message; ?></p>
                <?php endif; ?>

                <!-- Display tasks for the project -->
                <?php if (!empty($project['TASKS'])): ?>
                    <h4>Tasks</h4>
                    <ul>
                        <?php foreach ($project['TASKS'] as $task): ?>
                            <?php echo htmlspecialchars($task['USERNAME'])." "; ?>
                            <li>
                                <?php echo htmlspecialchars($task['TITLE']); ?> 
                                <?php if ($task['USER_ID'] == $_SESSION['userid']): ?>
                                    <form class="checkbox-container"method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                        <input class="ckeckbox-input" type="checkbox" name="checked" value="1" <?php echo $task['CHECKED'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="checkbox-custom"></span>
                                        <input type="hidden" name="task_id" value="<?php echo $task['TASK_ID']; ?>">
                                        <input type="hidden" name="update_task" value="1">
                                    </form>
                                <?php else: ?>
                                    <input type="checkbox" disabled <?php echo $task['CHECKED'] ? 'checked' : ''; ?>>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No tasks found for this project.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</body>
</html>
