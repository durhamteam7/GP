<?php

###########################################
// QUERY
$sql = "SELECT * FROM Animal ORDER BY photo_id DESC";

// execute query
$result = $mysqli->query($sql);

$data = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (!in_array($row["photo_id"], $classified)) {
            $data[] = $row;
        }
    }
} else {
    echo "0 results";
}

echo count($data) . " entries retrieved";
echo "\n";
echo "\n";

?>