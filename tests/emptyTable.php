<?php

// require database connection code
require('../dbConnectExternal.php');

$emptyTable = "TRUNCATE Classification;";

if ($mysqli->query($emptyTable) === TRUE) {
    echo "Record updated successfully";
    echo "\n";
} else {
    echo "Error updating record: " . $mysqli->error;
    echo "\n";
}

$emptyTable = "TRUNCATE PersonStats;";

if ($mysqli->query($emptyTable) === TRUE) {
    echo "Record updated successfully";
    echo "\n";
} else {
    echo "Error updating record: " . $mysqli->error;
    echo "\n";
}

?>