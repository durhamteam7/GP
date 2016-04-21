<?php

###########################################
// QUERY
$sql = "SELECT * FROM Photo ORDER BY photo_id DESC LIMIT 10000;";

// execute query
$result = $mysqli->query($sql);

$photo_ids = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $photo_ids[] = $row["photo_id"];
    }
} else {
    echo "0 results";
    echo "\n";
}

echo count($photo_ids) . " photo_ids retrieved";
echo "\n";
print_r($photo_ids);
echo "\n";
echo "\n";

?>