<?php
    // Database credentials
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";

    // Create connection
    $conn = new mysqli($servername, $dbusername, $dbpassword);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database
    $dbname = "task_db";
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
    } else {
        echo "Error creating database: " . $conn->error;
    }

    // Select the database
    $conn->select_db($dbname);

    $conn->close();

