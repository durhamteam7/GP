<?php


// require database connection code
require('../dbConnectExternal.php');

###########################################
// QUERY
$sql = "SELECT photo_id FROM Classification;";

// execute query
$result = $mysqli->query($sql);

$classified = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $classified[] = $row["photo_id"];
    }
} else {
    echo "0 results";
}

echo "Getting already classified photo_ids";
echo "\n";
echo count($classified) . " entries retrieved";
echo "\n";
print_r($classified);
echo "\n";

?>