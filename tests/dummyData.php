<?php

# Sample retrieval of data
$sql = "SELECT * FROM Animal ORDER BY photo_id ASC;";
# Might be a problem to retrieve all rows in the db (86 000 entries)

# Hard coded values to be used for testing our functions
# and creating an output
# INPUT FORMAT
$animal1 = array(
    "animal_id" => 0, # int(11)
    "photo_id" => 1,  # int(11)
    "person_id" => 0, # int(11)
    "species" => 0,   # int(11)
    "gender" => 0,    # int(11)
    "age" => 0,       # int(11)
    "number" => 0,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal2 = array(
    "animal_id" => 0, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 0, # int(11)
    "species" => 0,   # int(11)
    "gender" => 0,    # int(11)
    "age" => 0,       # int(11)
    "number" => 0,    # int(4)
    "timestamp" => 0  # timestamp
);

# Imagine this is sorted based on the photo_id
# So the 10 classifications for photo 1 are the 10 first/last elements
$data = array($animal1, $animal1, $animal2, $animal2);

while (count($data) > 0) {

    # populate the subject variable with all classifications for one photo
    # subject will contain all rows with that photo_id
    $subject = array(array_pop($data));
    while ($data[count($data) - 1]["photo_id"] == $subject[0]["photo_id"]) {
        $subject[] = array_pop($data);
    }

    #echo '<pre>';
    print_r($subject);
    #echo '</pre>';

    # --------------- #
    # process subject #
    # --------------- #

}

# OUTPUT FORMAT
$output = array(
    "classification_id" => 0,  # int(11)
    "photo_id" => 0,           # int(11)
    "species" => 0,            # int(11)
    "gender" => 0,             # int(11)
    "age" => 0,                # int(11)
    "number" => 0,             # int(4)
    "evenness" => 0,           # decimal(10,9)
    "fraction_support" => 0,   # decimal(10,9)
    "fraction_blanks" => 0,    # decimal(10,9)
    "timestamp" => 0           # timestamp
);

?>