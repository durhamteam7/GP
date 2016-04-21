<?php

###########################################
// QUERY
$sql = "SELECT * FROM Animal ORDER BY photo_id DESC LIMIT 10000;";

// execute query
$result = $mysqli->query($sql);

$data = [];
$all_data = [];

// process result
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (!in_array($row["photo_id"], $classified)) {
            #if (in_array($row["photo_id"], $photo_ids)) {
                $data[] = $row;
            #}
        }
        $all_data[] = $row;
    }
} else {
    echo "0 results";
    echo "\n";
}

echo count($data) . " animals retrieved";
echo "\n";
echo "\n";

?>