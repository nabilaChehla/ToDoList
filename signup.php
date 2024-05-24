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

        //initiate the session 
        session_start();

        //initialise messages
        $msg1 = "";
        $msg2 = "";
        $msg3 = "";

        //Check if the form is posted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            //Get username and password
            //'isset()' is unnecessary because of the 'required' attribute in the html form 
            $username = $_POST['username'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm-password'];
            
            //Insure html-safety
            $username = htmlspecialchars($username);
            $password = htmlspecialchars($password);
            $confirm_password = htmlspecialchars($confirm_password);

            //initialise connection boolean
            $b = True;

            //Make sure the username and password are of length
            if( strlen( $username ) < 3 OR strlen( $username ) > 30){
                $msg1 = "Username has to be at least 3 characters long and at max 30 characters long";
                //connection unnecessary
                $b = False;
            }
            if( strlen( $password ) < 5){
                $msg2 = "Password has to be at least 5 characters long";
                //connection unnecessary
                $b = False;
            //Check whether the confimation is valid
            }elseif( strcmp( $password , $confirm_password ) ){
                $msg3 = "Password doesn't match";
                //connection unnecessary
                $b = False;
            }

            if($b){
                //Connect to the db
                $local = "localhost";
                $dbusername = "root";
                $dbpassword = "";
                $dbname = "ToDo";
                $connect = new mysqli($local, $dbusername, $dbpassword, $dbname);
                //check connection
                if (!$connect) {
                    die("Connection failed: " . $connect->connect_error);
                }

                //Check whether Username exists
                $sql = "SELECT id FROM users WHERE username = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();

                $res = $stmt->get_result();
                if( $res->num_rows > 0){
                    //username already exist
                    $msg1 = "Username already exists, choose another";
                    $res->close();
                }else{
                    //Add Credentials to the db and redirect to homepage
                    $sql = "INSERT INTO users ( username , password ) VALUES ( ? , ? )";
                    $stmt = $connect->prepare($sql);
                    $hashed_password = md5($password);
                    $stmt->bind_param("ss", $username, $hashed_password);
                    $stmt->execute();
                    
                    $res = $stmt->get_result();

                    $sql = "SELECT id FROM users WHERE username = ?";
                    $stmt = $connect->prepare($sql);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();

                    $res = $stmt->get_result();
                    if( $res->num_rows > 0){
                        $row = $res->fetch_assoc();
                        $id = $row["id"];
                    }
                    $_SESSION['userid'] = $id;
                    $_SESSION['username'] = $username;
                    $connect->close();

                    header("Location: todo.php");
                    exit();
                }
            }
        }

    ?>

    <div class="account-container">
        <h1>Sign Up</h1>
        <form id="user-info-form" method="POST" action="" style="display:block;">
            <input type="text" name="username" placeholder="Username" required>
            <?php 
                echo "<p>".$msg1."</p>";
            ?>
            </br>
            <input type="password" name="password" placeholder="Password" required>
            <?php 
                echo "<p>".$msg2."</p>";
            ?>
            </br>
            <input type="password" name="confirm-password" placeholder="Confirm Password" required>
            <?php 
                echo "<p>".$msg3."</p>";
            ?>
            </br>
            <button type="submit">Sign Up</button>
            </br>
        </form>
        <a class="href" href="./login.php">Log In</a>
        </br>
    </div>

    <script src="./src/js/script.js"></script>
</body>
</html>