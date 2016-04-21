<?php

/*
$createTable = "CREATE TABLE PersonStats (".
    "person_stats_id INT(11) AUTO_INCREMENT PRIMARY KEY,".
    "person_id INT NOT NULL,".
    "species_rate DECIMAL(10, 9) NOT NULL,".
    "gender_rate DECIMAL(10, 9) NOT NULL,".
    "age_rate DECIMAL(10, 9) NOT NULL,".
    "number_rate DECIMAL(10, 9) NOT NULL".
");";
if ($mysqli->query($createTable) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $mysqli->error;
}
*/

# assume we have $all_data

echo "Calculating the correctness rate of each user";
echo "\n";
echo "species, gender, age, number";
echo "\n";
echo "\n";

# Sorts the all_data array based on person_id
usort($all_data, function ($item1, $item2) {
    if ($item1['person_id'] == $item2['person_id']) return 0;
    return $item1['person_id'] < $item2['person_id'] ? -1 : 1;
});

while (count($all_data) > 0) {

    # populate the subject variable with all classifications for one photo
    # subject will contain all rows with that photo_id
    $subject = array(array_pop($all_data));
    while ($all_data[count($all_data) - 1]["person_id"] == $subject[0]["person_id"]) {
        $subject[] = array_pop($all_data);
    }

    usort($subject, function ($item1, $item2) {
        if ($item1['photo_id'] == $item2['photo_id']) return 0;
        return $item1['photo_id'] < $item2['photo_id'] ? -1 : 1;
    });

    $person_id = $subject[0]["person_id"];

    $species_rate = $s->getUserCorrectnessRate("species", $subject, $classifications);
    $gender_rate = $s->getUserCorrectnessRate("gender", $subject, $classifications);
    $age_rate = $s->getUserCorrectnessRate("age", $subject, $classifications);
    $number_rate = $s->getUserCorrectnessRate("number", $subject, $classifications);

    echo "$person_id has $species_rate, $gender_rate, $age_rate, $number_rate";
    echo "\n";
    echo "on " . count($subject) . " classifications";
    echo "\n";

    #Output -- Needs to be made more efficient using the same method as in the Algorithm.PHP file.
    $updatePersonStats = "INSERT INTO PersonStats (person_id, species_rate, gender_rate, age_rate, number_rate) " .
    "VALUES ('$person_id', '$species_rate', '$gender_rate', '$age_rate', '$number_rate') " .
    "ON DUPLICATE KEY UPDATE person_id=person_id," .
    "species_rate='$species_rate'," .
    "gender_rate='$gender_rate'," .
    "age_rate='$age_rate'," .
    "number_rate='$number_rate';";

    #echo $updatePersonStats . "\n";

    if ($mysqli->query($updatePersonStats) === TRUE) {
        echo "Record updated successfully";
        echo "\n";
    } else {
        echo "Error updating record: " . $mysqli->error;
        echo "\n";
    }
}
?>
