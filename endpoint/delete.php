<?php

if(isset($_POST['id'])){
    require '../conn/conn.php';

    $id = $_POST['id'];

    if(empty($id)){
       echo 0;
       exit();
    }else {
        $stmt = $conn->prepare("DELETE FROM todos WHERE id=?");
        $stmt->bind_param("i", $id); // "i" indicates the type of the parameter (integer)
        $res = $stmt->execute();

        if($res){
            echo 1;
        }else {
            echo 0;
        }
        $stmt->close();
        $conn->close();
        exit();
    }
}else {
    header("Location: ../index.php?mess=error");
    exit();
}
