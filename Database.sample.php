<?php
// Database.sample.php
// Copy this file as Database.php and fill in your own details

// Database connection settings
$host = "localhost";    // Database host (usually 'localhost')
$user = "Database_username";         // Database username
$password = "";         // Database password
$dbname = "Datbase_name"; // Database name

// Create connection
$con = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>