<?php

// require database connection code
require('../dbConnectExternal.php');

$emptyTable = "TRUNCATE Classification;";

if ($mysqli->query($emptyTable) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $mysqli->error;
}

?>