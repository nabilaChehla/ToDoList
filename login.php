<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php

        session_start();
        $msg1 = "";
        $msg2 = "";

        //Check if the form is posted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            //Get username and password
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            //Strip of special chars
            $username = htmlspecialchars($username);
            $password = htmlspecialchars($password);

            //conn to db and check username and password
            //conn to the db
            require './conn/conn.php';

            //Check whether account exists
            $sql = "SELECT id,password FROM users WHERE username = ?";           
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            $res = $stmt->get_result();
            if( $res->num_rows > 0){
                //Username already exists check if password is correct
                $row = $res->fetch_assoc();
                
                //credentials correct: redirect to homepage
                if( $row["password"] == ($password) ){
                    echo "correct password";
                    //correct password
                    $_SESSION['userid'] = $row["id"];
                    $_SESSION['username'] = $username;
                    $conn->close();
                    header("Location: index.php");

                }else{
                    //incorrect password
                    //password incorrect
                    $msg2 = "Incorrect password";
                    $conn->close();
                } 
                
            }else{
                //Username does not exit
                $msg1 = "Incorrect Username";
                $conn->close();
            }
        }
    ?>

    <div class="account-container">
        <h1>Log In</h1>
        <br>
        <form id="user-info-form" method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <?php
                echo $msg1;
            ?>
            <br>
            <input type="password" name="password" placeholder="Password" required>
            <?php
                echo $msg2;
            ?>
            <br>
            <button type="submit">Log In</button>
            <br>
        </form>
        <a href="./signup.php">Sign Up</a>

    </div>

    <script src="./src/js/script.js"></script>
</body>
</html>