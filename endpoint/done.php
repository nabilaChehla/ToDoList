<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start() or trigger_error("", E_USER_ERROR);


if (isset($_POST['id'])) {
    
    if (!isset($_SESSION['userid'])) {
        var_dump("User is not logged in");  // Debugging output
        header("Location: ../login.php");
        exit();
    }

    require "../conn/conn.php"; 

    $id = $_POST['id'];
    $user_id = $_SESSION['userid'];

    if (empty($id)) {
        echo 'error';
        exit();
    } else {
        $stmt = $conn->prepare("SELECT todos.id, todos.checked FROM todos INNER JOIN user_task ON todos.id = user_task.task_id WHERE todos.id=? AND user_task.user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $todo = $result->fetch_assoc();
            $uId = $todo['id'];
            $checked = $todo['checked'];

            $uChecked = $checked ? 0 : 1;

            $updateStmt = $conn->prepare("UPDATE todos SET checked=? WHERE id=?");
            $updateStmt->bind_param("ii", $uChecked, $uId);
            $updateRes = $updateStmt->execute();

            if ($updateRes) {
                echo $checked;
            } else {
                echo "error";
            }
        } else {
            echo "error";
        }

        $stmt->close();
        $updateStmt->close();
        $conn->close();
        exit();
    }
} else {
    echo "error";
    exit();
}
?>
