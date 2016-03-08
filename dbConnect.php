<?php

$servername = "mysql.dur.ac.uk";
$username = "nobody";
$password = "";
$db = "Cljdw32_MammalWeb";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} 

echo "Connected successfully";

?>
