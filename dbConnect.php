<?php

$servername = "mysql.dur.ac.uk";
$username = "ljdw32";
$password = "boston38";
$db = "Cljdw32_team7";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} 

echo "Connected successfully";

?>