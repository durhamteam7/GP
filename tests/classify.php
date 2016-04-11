<?php

// import algorithm
require_once("algorithm.php");

$s = new Swanson();

// require database connection code
require('../dbConnectExternal.php');

###########################################
// QUERY
$sql = "SELECT * FROM Animal ORDER BY photo_id DESC;";

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

###########################################
# Might be a problem to retrieve all rows in the db (86 000 entries)

# Hard coded values to be used for testing our functions
# and creating an output
# INPUT FORMAT
$animal1 = array(
    "animal_id" => 0, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 1, # int(11)
    "species" => 5,   # int(11)
    "gender" => 0,    # int(11)
    "age" => 5,       # int(11)
    "number" => 1,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal2 = array(
    "animal_id" => 1, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 2, # int(11)
    "species" => 86,  # int(11)
    "gender" => 1,    # int(11)
    "age" => 5,       # int(11)
    "number" => 1,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal3 = array(
    "animal_id" => 2, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 3, # int(11)
    "species" => 86,  # int(11)
    "gender" => 1,    # int(11)
    "age" => 5,       # int(11)
    "number" => 1,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal4 = array(
    "animal_id" => 3, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 4, # int(11)
    "species" => 5,   # int(11)
    "gender" => 2,    # int(11)
    "age" => 0,       # int(11)
    "number" => 0,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal5 = array(
    "animal_id" => 4, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 5, # int(11)
    "species" => 2,   # int(11)
    "gender" => 0,    # int(11)
    "age" => 10,      # int(11)
    "number" => 0,    # int(4)
    "timestamp" => 0  # timestamp
);
$animal6 = array(
    "animal_id" => 5, # int(11)
    "photo_id" => 2,  # int(11)
    "person_id" => 6, # int(11)
    "species" => 5,   # int(11)
    "gender" => 0,    # int(11)
    "age" => 5,       # int(11)
    "number" => 0,    # int(4)
    "timestamp" => 0  # timestamp
);

# Imagine this is sorted based on the photo_id
# So the 10 classifications for photo 1 are the 10 first/last elements
#$data = array($animal1, $animal2, $animal3, $animal4, $animal5, $animal6);

while (count($data) > 0) {

    # populate the subject variable with all classifications for one photo
    # subject will contain all rows with that photo_id
    $subject = array(array_pop($data));
    while ($data[count($data) - 1]["photo_id"] == $subject[0]["photo_id"]) {
        $subject[] = array_pop($data);
    }

    # OUTPUT FORMAT
    $output = array(
        "classification_id" => 0,  # int(11)
        "photo_id" => 0,   # int(11)
        "retired" => false,        # tinyint(1)
        "species" => 0,            # int(11)
        "gender" => 0,             # int(11)
        "age" => 0,                # int(11)
        "number" => 0,             # int(4)
        "evenness" => 0,           # decimal(10,9)
        "fraction_support" => 0,   # decimal(10,9)
        "fraction_blanks" => 0,    # decimal(10,9)
        "timestamp" => 0           # timestamp
    );

    $output["photo_id"] = $subject[0]["photo_id"];

    $number_of_classifications = count($subject);

    echo "Subject " . $photo_id;
    echo "\n";
    print_r($subject);
    echo "\n";

    # Decide the winners
    $output["species"] = $s->decide_on("species", $subject);
    $output["gender"] = $s->decide_on("gender", $subject);
    $output["age"] = $s->decide_on("age", $subject);
    $output["number"] = $s->decide_on("number", $subject);

    # First Retirement Condition - Blank
    # Are the 5 first classifications blank?
    $all_blank = true;
    if ($number_of_classifications == 5) {
        foreach ($subject as $c) {
            if ($c["species"] != 86) {
                $all_blank = false;
            }
        }
    }
    if ($all_blank) {
        $output["retired"] = true;
    }

    # Second Retirement Condition - Consensus
    # Are there 10 agreeing classifications? (Including blanks)
    if ($output["species"] >= 10) {
        $output["retired"] = true;
    }

    # Third Retirement Condition - Complete
    # Are there 25 or more classifications?
    if ($number_of_classifications >= 25) {
        $output["retired"] = true;
    }

    echo "Evenness Index";
    echo "\n";
    $votes = $s->tally_votes("species", $subject);
    $nlist = array_values($votes);
    $evenness = $s->calculate_pielou($nlist);
    print_r($evenness);
    echo "\n";
    echo "\n";
    $output["evenness"] = $evenness;

    echo "Fraction Support";
    echo "\n";
    $fraction_support = $s->fraction_support($votes);
    print_r($fraction_support);
    echo "\n";
    echo "\n";
    $output["fraction_support"] = $fraction_support;

    echo "Fraction Blanks";
    echo "\n";
    $fraction_blanks = $s->fraction_blanks($votes);
    print_r($fraction_blanks);
    echo "\n";
    echo "\n";
    $output["fraction_blanks"] = $fraction_blanks;

    echo "Output";
    echo "\n";
    print_r($output);
    echo "\n";

    $photo_id = $output["photo_id"];
    $retired = $output["retired"];
    $species = $output["species"];
    $gender = $output["gender"];
    $age = $output["age"];
    $number = $output["number"];
    $evenness = $output["evenness"];
    $fraction_support = $output["fraction_support"];
    $fraction_blanks = $output["fraction_blanks"];

    # RAM'S UPDATE FUNCTION - this isn't quite working. getting constraint error
    $updateQuery = "INSERT INTO Classification " .
                    "(photo_id, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) " .
                    "VALUES ('$photo_id', '$species', '$gender', '$age', '$number', '$evenness', '$fraction_support', '$fraction_blanks', now()) " .
                    "ON DUPLICATE KEY UPDATE " .
                    "timestamp=now()";

    if ($mysqli->query($updateQuery) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $mysqli->error;
    }
}

require_once("getClassifications.php");

$mysqli->close();
?>