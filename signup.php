<?php
session_start();

$msg1 = "";
$msg2 = "";
$msg3 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $confirm_password = htmlspecialchars($confirm_password);

    $b = true;

    if (strlen($username) < 3 || strlen($username) > 30) {
        $msg1 = "Username must be between 3 and 30 characters long.";
        $b = false;
    }
    if (strlen($password) < 5) {
        $msg2 = "Password must be at least 5 characters long.";
        $b = false;
    } elseif ($password !== $confirm_password) {
        $msg3 = "Passwords do not match.";
        $b = false;
    }

    if ($b) {
        require './conn/conn.php';

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $msg1 = "Username already exists. Please choose another.";
        } else {
            $hashed_password = ($password);;

            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $user_id = $stmt->insert_id;

                $_SESSION['userid'] = $user_id;
                $_SESSION['username'] = $username;

                header("Location: login.php");
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
</head>
<body>

<div class="account-container">
    <h1>Sign Up</h1>
    <form id="user-info-form" method="POST" action="" style="display:block;">
        <input type="text" name="username" placeholder="Username" required>
        <?php echo "<p>".$msg1."</p>"; ?>
        <br>
        <input type="password" name="password" placeholder="Password" required>
        <?php echo "<p>".$msg2."</p>"; ?>
        <br>
        <input type="password" name="confirm-password" placeholder="Confirm Password" required>
        <?php echo "<p>".$msg3."</p>"; ?>
        <br>
        <button type="submit">Sign Up</button>
        <br>
    </form>
    <a class="href" href="./login.php">Log In</a>
    <br>
</div>

<script src="./src/js/script.js"></script>
</body>
</html>
