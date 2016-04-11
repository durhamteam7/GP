<?php

# Load in the data from the database
# run the algorithm on a number of rows at a time

// require database connection code
require('../dbConnectExternal.php');

# only deal with this many rows at a time
$limit = 100;

$data = [];

for ($i = 0; true; $i++) {

    ###########################################
    // SAMPLE QUERY
    $sql = "SELECT * FROM Animal LIMIT " . ($i * $limit) . ", " . (($i + 1) * $limit) . ";";

    // execute query
    $result = $mysqli->query($sql);

    // process result
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else if ($result->num_rows < $limit) {
        break;
    }
    ###########################################

}

echo '<pre>';
print_r($data);
echo '</pre>';

?>