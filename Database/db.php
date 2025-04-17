<!-- Connect to db and return db -->

<?php
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "scholarship_db";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
