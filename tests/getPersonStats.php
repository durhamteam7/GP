<?php

// require database connection code
require_once('../dbConnectExternal.php');

###########################################
// QUERY
$sql = "SELECT * FROM PersonStats;";

// execute query
$result = $mysqli->query($sql);

$personStats = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $personStats[] = $row;
    }
} else {
    echo "0 results";
    echo "\n";
}

echo count($personStats) . " person stats retrieved";
echo "\n";
echo "\n";
print_r($personStats);
echo "\n";

###########################################

?>