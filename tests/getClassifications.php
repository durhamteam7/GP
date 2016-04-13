<?php

// require database connection code
require_once('../dbConnectExternal.php');

###########################################
// QUERY
$sql = "SELECT * FROM Classification;";

// execute query
$result = $mysqli->query($sql);

$data = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 results";
}

echo count($data) . " entries retrieved";
echo "\n";
echo "\n";
print_r($data);
echo "\n";

###########################################

?>