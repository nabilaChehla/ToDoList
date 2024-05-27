<?php
session_start();

$msg1 = "";
$msg2 = "";
$msg3 = "";

require_once './zxcvbn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $confirm_password = htmlspecialchars($confirm_password);

    $valid = true;

    if (strlen($username) < 3 || strlen($username) > 30) {
        $msg1 = "Username must be between 3 and 30 characters long.";
        $valid = false;
    }

    $score = p($password);
    if ($score[0] < 3) {
        $msg2 = '<p>'.$score[1].'</p><p>'.$score[2][0].'</p>';
        $valid = false;
    }
    
    if (strlen($password) < 5) {
        $msg2 = $msg2.'<p>Passwords must be at least 5 characters long</p>';
        $valid = false;
    } elseif ($password !== $confirm_password) {
        $msg3 = "Passwords do not match.";
        $valid = false;
    }

    if ($valid) {
        require './conn/conn.php';

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $msg1 = "Username already exists. Please choose another.";
        } else {
            $hashed_password = md5($password);

            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $user_id = $stmt->insert_id;

                $_SESSION['userid'] = $user_id;
                $_SESSION['username'] = $username;

                header("Location: index.php");
                exit();
            } else {
                echo "Error: " . $stmt->error; // Output error message
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/loginStyle.css">

</head>
<body>

    <div class="main-div">
        <img class="login-img" src="images/login_image.jpg">
        <div class="account-container">
            <h1>Welcome  !</h1>
            <p>You dont have an account? join us </p>
            <br> 
            <form id="user-info-form" method="POST" action="" style="display:block;">
                <input class="signup-input" type="text" name="username" placeholder="Username" required>
                <?php echo "<p>".$msg1."</p>"; ?>
                <br>
                <input class="signup-pw" type="password" name="password" placeholder="Password" required>
                <?php echo "<p>".$msg2."</p>"; ?>
                <br>
                <input class="signup-pw" type="password" name="confirm-password" placeholder="Confirm Password" required>
                <?php echo "<p>".$msg3."</p>"; ?>
                <br>
                <div>
                    <button type="submit">Sign Up</button>
                    <a class="href" href="./login.php">Log In</a>
                </div>  
            </form> 
        </div>
    </div>
    <br>
</div>

<script src="./src/js/script.js"></script>
</body>
</html>
