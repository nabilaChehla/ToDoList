<?php
session_start() or trigger_error("", E_USER_ERROR);
$msg1 = "";
$msg2 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    require './conn/conn.php';

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row["password"] == (md5($password))) {
            $_SESSION['userid'] = $row["id"];
            $_SESSION['username'] = $username;
            $conn->close();
            header("Location: index.php");
            exit();
        } else {
            $msg2 = "Incorrect password";
        }
    } else {
        $msg1 = "Incorrect Username";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="./css/loginStyle.css">
</head>

<body>
    <div class="main-div">
        <img class="login-img" src="images/login_image.jpg">
        <div class="account-container">
            <h1>Welcome Back!</h1>
            <p>Login to continue </p>
            <br> 
            <form id="user-info-form" method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <?php
                echo '<div class="error-message">' . $msg1 . '</div>';
                ?>
                <br>
                <input type="password" name="password" placeholder="Password" required>
                <?php
                echo '<div class="error-message">' . $msg2 . '</div>';
                ?>
                <br>
                <div>
                    <button type="submit">LOGIN</button>
                    <a href="./signup.php">Sign Up</a>
                </div>   
                
            </form>
            
            
        </div>
    </div>
    <script src="./src/js/script.js"></script>

</body>

</html>
