<?php

  #######################################
 ##                                     ##
### implementation of swanson algorithm ###
 ##                                     ##
  #######################################


// require database connection code
require('dbConnect.php');


// sample query
$query = "SELECT * FROM Animal LIMIT(25);";

// execute query
$result = $mysqli->query($sql);

// process result
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo $row . "<br>";
    }
} else {
    echo "0 results";
}


// close connection
$mysqli->close();

?>