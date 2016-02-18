<?php

  #######################################
 ##                                     ##
### implementation of swanson algorithm ###
 ##                                     ##
  #######################################


// require database connection code
require('dbConnect.php');



###########################################
// SAMPLE QUERY
$sql = "SELECT * FROM Animal LIMIT 25;";

// execute query
$result = $mysqli->query($sql);

// process result
if ($result->num_rows > 0) {
    // output data of each row
    echo '<pre>';
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo '</pre>';
} else {
    echo "0 results";
}
###########################################



// close connection
$mysqli->close();

?>