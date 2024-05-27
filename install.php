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

// SQL statement to create a new database
$dbname = "task_db";
$sql = "CREATE DATABASE $dbname";

// Execute the SQL statement
if ($conn->query($sql) === TRUE) {
    // echo "Database created successfully<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->close();

//connect to tast_db
$conn = new mysqli($servername, $username, $password,$dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Path to your SQL file
$sqlFile = './tables.sql';

if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile");
}

// Read the SQL file into a string
$sql = file_get_contents($sqlFile);

// Split the file into individual SQL statements
$sqlStatements = explode(';', $sql);

//statement counter
$n = 1;

foreach ($sqlStatements as $statement) {
    // Trim any whitespace
    $statement = trim($statement);
    if (!empty($statement)) {
        
        if ($conn->query($statement) === TRUE) {
            echo "Query ".$n."/14 executed successfully<br>";
            $n = $n + 1;
        } else {
            echo "Error executing query ".$n."/14: " . $conn->error . "\n";
            exit();
        }
    }
}

// Close connection
$conn->close();
