<?php

$servername = "db4free.net";
$username = "mammalweb";
$password = "aliSwans0n";
$db = "mammalweb";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} 

//echo "Connected successfully";

?>
