<?php
$host = 'localhost';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Create the database if it doesn't exist
$query = "CREATE DATABASE IF NOT EXISTS task_db";
if ($mysqli->query($query) === FALSE) {
    die("Error creating database: " . $mysqli->error);
}

$mysqli->select_db('task_db');

// Create the tasks table if it doesn't exist
$query = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    state BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($mysqli->query($query) === FALSE) {
    die("Error creating table: " . $mysqli->error);
}
?>