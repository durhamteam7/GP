<?php

# Gold standard data set from Pen's classifications

// require database connection code
require('../dbConnectExternal.php');

###########################################
// SAMPLE QUERY
$sql = "SELECT * FROM Animal WHERE person_id = 311 ORDER BY photo_id ASC;";

// execute query
$result = $mysqli->query($sql);

$goldStandard = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $goldStandard[] = $row;
    }
} else {
    echo "0 results";
}
###########################################

echo '<pre>';
print_r($goldStandard);
echo '</pre>';

# RUN ALGORITHM ON $goldStandard;

?>