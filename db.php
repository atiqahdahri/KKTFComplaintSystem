<?php

// Database configuration
$dbHost = "localhost"; // replace with your database host
$dbUser = "root"; // replace with your database username
$dbPass = ""; // replace with your database password
$dbName = "psm"; // replace with your database name

// Establish database connection
$db = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
} 

?>
