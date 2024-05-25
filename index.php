<?php


require 'conn/conn.php';
session_start() or trigger_error("", E_USER_ERROR);
echo "<h1> Welcome " .$_SESSION["username"] . "</h1>";
// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}

// Fetch only the tasks associated with the currently logged-in user
$user_id = $_SESSION['userid'];
$todos = $conn->query("SELECT todos.* FROM todos INNER JOIN user_task ON todos.id = user_task.task_id WHERE user_task.user_id = $user_id ORDER BY todos.id DESC");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>To-Do List App</h2>
                    </div>
                    <div class="card-body">
                        <form action="endpoint/add.php" method="POST" autocomplete="off">
                            <div class="input-group mb-3">
                                <input type="text" name="title" class="form-control <?= isset($_GET['mess']) && $_GET['mess'] == 'error' ? 'is-invalid' : '' ?>" placeholder="<?= isset($_GET['mess']) && $_GET['mess'] == 'error' ? 'You must do something! Be Productive!' : 'What do you need to do?' ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if ($todos->num_rows <= 0) { ?>
                            <div class="alert alert-secondary text-center" role="alert">
                               <h3 class="text-center">No Task for Today!</h3>
                            </div>
                        <?php } ?>
                        
                        <div class="list-group">
                            <?php while ($todo = $todos->fetch_assoc()) { ?>
                                <div class="list-group-item">
                                <span id="<?php echo $todo['id']; ?>" class="remove-to-do">x</span>
                                <input type="checkbox" class="check-box" data-todo-id="<?php echo $todo['id']; ?>" <?php echo $todo['checked'] ? 'checked' : ''; ?>>
                                <h2 <?php echo $todo['checked'] ? 'class="checked"' : ''; ?>><?php echo $todo['title']; ?></h2>
                                <small class="date-finished">Created: <?php echo $todo['date_time']; ?></small>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<a href="login.php">change user</a>
<a href="projects.php">My projects</a>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.remove-to-do').click(function(){
                const id = $(this).attr('id');
                const parent = $(this).parent();

                $.post("endpoint/delete.php", { id: id }, function(data){
                    if (data) {
                        parent.hide(600);
                    }
                });
            });
            $(".check-box").click(function(){

                const id = $(this).attr('data-todo-id');
                const h2 = $(this).next();

                $.post('endpoint/done.php', { id: id }, function(data){
                    if (data !== 'error') {
                        h2.toggleClass('checked', data === '0');
                        console.log("hiiii"); 

                    }
                });
            });
        });
    </script>

</body>
</html>
