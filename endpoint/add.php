<?php

if(isset($_POST['title'])){
    require '../conn/conn.php';

    $title = $_POST['title'];

    if(empty($title)){
        header("Location: ../index.php?mess=error");
        exit();
    }else {
        $stmt = $conn->prepare("INSERT INTO todos(title) VALUES(?)");
        $stmt->bind_param("s", $title); // "s" indicates the type of the parameter (string)
        $res = $stmt->execute();

        if($res){
            header("Location: ../index.php?mess=success"); 
        }else {
            header("Location: ../index.php");
        }
        $stmt->close();
        $conn->close();
        exit();
    }
}else {
    header("Location: ../index.php?mess=error");
    exit();
}
