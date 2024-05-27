

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Your Tasks</title>
    <link rel="stylesheet" href="./css/project.css">
    <link rel="stylesheet" href="./css/checkboxStyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body >
    
    
<header class="light-header">
       
        <nav class="nav-list">
            <div>
                <img src="./images/icons8-profile-picture-96.png">
                <h2 class="header-title"><?php 
                 require 'conn/conn.php';
                 session_start();
                 if (!isset($_SESSION['userid'])) {
                     header("Location: login.php");
                     exit();
                 }
                echo $_SESSION['username']; ?></h2>
            </div>
            <li class='nav-link'><a href="task.php">Tasks</a> <img src='./images/icons8-to-do-48.png'></li>   
            <li class='nav-link'><a href="projects.php">Projects</a><img src='./images/icons8-project-64.png'></li>    
            <li class='nav-link'><a href="category.php">Category</a><img src='./images/icons8-category-48.png'></li>    
            <li class='nav-link'><a href="login.php">Change User</a><img src='./images/icons8-user-48.png'></li>   

        </nav>

    </header>
    <div class='container'>
        <h2>Add a New Task</h2>
        <form  method="POST" action="">
            <input class="task-input" type="text" id="task_title" name="task_title" required>
            <button class='add-btn' type="submit" name="add_task"></button>
        </form>

        
        <?php




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
        <?php
        $current_date = null; // Variable to keep track of the current date

        if($tasks_result->num_rows>0){
            echo '<div class=\'tasks\'><h2>Your Tasks</h2>';
        }

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
            echo "<form method='POST' action=''>";
            echo "<input type='hidden' name='task_id' value='$task_id'>";
            echo "<input class='checkbox-custom' type='checkbox' name='toggle_task' onchange='this.form.submit()'>";
            echo "<label class='$task_completed_class'>$task_title</label>";
            echo "</form>";
            echo "<form method='POST' action=''>";
            echo "<input type='hidden' name='task_id' value='$task_id'>";
            echo "<button class=\"delete-btn\" type='submit' name='delete_task'>❌</button>";
            echo "</form>";
            echo "</div>";
        }
        ?>
                <a class='link-completed-task' href="completed_tasks.php">SEE COMPLETED TASKS</a>

        </div>
    </div>
</body>
</html>
