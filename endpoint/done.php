<?php

if(isset($_POST['id'])){
    require '../conn/conn.php';

    $id = $_POST['id'];

    if(empty($id)){
       echo 'error';
       exit();
    }else {
        $stmt = $conn->prepare("SELECT id, checked FROM todos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $todo = $result->fetch_assoc();
            $uId = $todo['id'];
            $checked = $todo['checked'];

            $uChecked = $checked ? 0 : 1;

            $updateStmt = $conn->prepare("UPDATE todos SET checked=? WHERE id=?");
            $updateStmt->bind_param("ii", $uChecked, $uId);
            $updateRes = $updateStmt->execute();

            if($updateRes){
                echo $checked;
            }else {
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
}else {
    header("Location: ../index.php?mess=error");
    exit();
}
