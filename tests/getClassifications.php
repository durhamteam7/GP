<?php

// require database connection code
require_once('../dbConnectExternal.php');

###########################################
// QUERY
$sql = "SELECT * FROM Classification WHERE evenness > 0.25;";

// execute query
$result = $mysqli->query($sql);

$classifications = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $classifications[] = $row;
    }
} else {
    echo "0 results";
    echo "\n";
}

echo count($classifications) . " classifications retrieved";
echo "\n";
echo "\n";
print_r($classifications);
echo "\n";

###########################################

?>