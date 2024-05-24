<?php
session_start();

if(isset($_POST['title'])){
    // Check if user is logged in
    if (!isset($_SESSION['userid'])) {
        header("Location: ../login.php");
        exit();
    }

    require '../conn/conn.php';

    $title = $_POST['title'];
    $user_id = $_SESSION['userid'];

    if(empty($title)){
        header("Location: ../index.php?mess=error");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $res = $stmt->execute();

        if($res){
            // Get the ID of the inserted task
            $task_id = $stmt->insert_id;

            // Associate the task with the current user
            $stmt = $conn->prepare("INSERT INTO user_task (user_id, task_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $task_id);
            $stmt->execute();

            header("Location: ../index.php?mess=success");
            exit();
        } else {
            header("Location: ../index.php");
            exit();
        }
        $stmt->close();
        $conn->close();
    }
} else {
    header("Location: ../index.php?mess=error");
    exit();
}
?>
