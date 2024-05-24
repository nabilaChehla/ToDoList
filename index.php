<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>To-Do List</h2>
        <form id="taskForm" action="task.php" method="post">
            <input type="text" id="taskInput" placeholder="Enter task" name="task_name">
            <input type="text" id="taskInput" placeholder="Enter task" name="task_description">

            <input type="submit" value="submit">
        </form>
        <ul id="taskList"></ul>
        <a href="get_tasks.php">View Past Tasks</a>
    </div>
    <script src="script.js"></script>
</body>
</html>
