<?php

# assume we have $all_data

echo "Calculating the correctness rate of each user";
echo "\n";
echo "species, gender, age, number";
echo "\n";
echo "\n";

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

    $speciesRate = $s->getUserCorrectnessRate("species", $subject, $classifications);
    $genderRate = $s->getUserCorrectnessRate("gender", $subject, $classifications);
    $ageRate = $s->getUserCorrectnessRate("age", $subject, $classifications);
    $numberRate = $s->getUserCorrectnessRate("number", $subject, $classifications);

    echo "$person_id has $speciesRate, $genderRate, $ageRate, $numberRate";
    echo "\n";

    #OUTPUT FUNCTION

}
?>