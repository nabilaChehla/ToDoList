<?php

// MySQLi connection parameters
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statement to list all databases
$sql = "SHOW DATABASES";

// Execute the SQL query
$result = $conn->query($sql);



// Name of the database to be created
$dbname = "task_db";

// Check if the database already exists
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");

//close connection 
$conn->close();

if ($result->num_rows == 0) {
    //if not create the database and fill it
    require './conn/install.php';
    header("Location: signup.php");
    exit();
} else {
    //if yes connect to db
    require './conn/conn.php';
    header("Location: login.php");
    exit();
}

?>