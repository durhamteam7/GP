<?php

/**
 * Allocate memory to the execution
 */
ini_set('memory_limit', '4096M');

  #######################################
 ##                                     ##
### Implementation of Swanson Algorithm ###
 ##                                     ##
  #######################################

/**
 * Implementation of Swanson Algorithm.
 *
 * Range of methods to perform the algorithm described
 * by Ali Swanson to classify photos in citizen science
 * projects.
 *
 * @author     Team7
 */
class Swanson
{
    /**
     * The object that handles the mysql database connection.
     *
     * @var mysqli
     */
    private $mysqli;
    /**
     * specifies the environment, i.e. which database server to connect to.
     *
     * @var int
     */
    private $env = 1;

		/**
     * These variables control which photos get retired.
     * As the number of user classifications grow,
     * these variables will need to be updated to values
     * similar to those in Swanson
		 */

    /**
     * The streak of first classifications that need to be blank to retire photo.
     *
     * @var int
     */
    private $blank_condition = 1;        /* 5 in Swanson */
    /**
     * The number of classifications that need to agree to retire photo.
     *
     * @var int
     */
    private $consensus_condition = 1;    /* 10 in Swanson */
    /**
     * The maximum number of classifications needed before we retire photo.
     *
     * @var int
     */
    private $complete_condition = 2;     /* 25 in Swanson */
    /**
     * Defines a minimum evenness value: any photo with a greater than or equal evenness will be retired.
     *
     * @var int
     */
    private $agreement_condition = 1;    /* 1 in Swanson */

    /**
     * The database value for a blank classification.
     *
     * @var int
     */
    private $blank_animal = 86;

    /**
     * Determines whether we want to limit our animal select statement or not.
     *
     * @var bool
     */
    private $animal_limiting = false; /* will be false in the end */
    /**
     * The limit on the animal select query.
     *
     * @var int
     */
    private $get_animal_limit = 1;
    /**
     * Determines whether we want to limit our photo select statement or not.
     *
     * @var bool
     */
    private $photo_limiting = false; /* will be false in the end */
    /**
     * The limit on the photo select query.
     *
     * @var int
     */
    private $get_photo_limit = 1;

    public function __construct()
    {
        $this->setupDB();
    }

    public function __destruct()
    {
        $this->closeDB();
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function setEnv($e)
    {
        $this->env = $e;
    }

    public function getConn()
    {
        return $this->mysqli;
    }

    /**
     * Initialized database connection.
     */
    public function setupDB()
    {
        if ($this->env == 0) {
            $servername = 'mysql.dur.ac.uk';
            $username = 'nobody';
            $password = '';
            $db = 'Cljdw32_MammalWeb';

            /* Create connection */
            $this->mysqli = new mysqli($servername, $username, $password, $db);
        } else if ($this->env == 1) {
            $servername = 'db4free.net';
            $username = 'mammalweb';
            $password = 'aliSwans0n';
            $db = 'mammalweb';

            /* Create connection */
            $this->mysqli = new mysqli($servername, $username, $password, $db);
        }

        /* Check connection */
        if ($this->mysqli->connect_error) {
            /* echo "Connection failed: " . $this->mysqli->connect_error; */
            return false;
        }
        return true;
    }

    public function closeDB()
    {
        $this->mysqli->close();
    }

    /**
     * Starting point for the algorithm.
     *
     * @param array[] data Array of database rows Returned by getAnimals()
     */
    public function main($data)
    {
        /**
				 * This array 'all_outputs' will contain all arrays of the image
         * values once the while loop below has completed.
				 */
        $all_outputs = array();

        while (count($data) > 0) {
            /* This loop populates the 'subject' variable with all classifications for one photo */
            /* The array 'subject' will contain all rows with that photo_id */
            if (count($data) > 0) {
                $subject = array(array_pop($data));
                while ($data[count($data) - 1]['photo_id'] == $subject[0]['photo_id']) {
                    $subject[] = array_pop($data);
                    if (count($data) <= 0) {
                        break;
                    }
                }
            }

            $photo_id = $subject[0]['photo_id'];
            $number_of_classifications = count($subject);

            /* Decide the winners */
            $species = $this->decide_on('species', $subject);
            $gender = $this->decide_on('gender', $subject);
            $age = $this->decide_on('age', $subject);
            $number = $this->decide_on('number', $subject);
            $retired = false;

						/**
             * First Retirement Condition - Blank
             * Are the 5 first classifications blank?
						 */
            if ($number_of_classifications == $this->blank_condition) {
                $all_blank = true;
                foreach ($subject as $c) {
                    if ($c['species'] != $this->blank_animal) {
                        $all_blank = false;
                    }
                }
                if ($all_blank) {
                    $retired = true;
                }
            }

						/**
             * Second Retirement Condition - Consensus
             * Are there 10 agreeing classifications? (Including blanks)
						 */
            if ($this->highest_vote('species', $subject) >= $this->consensus_condition) {
                $retired = true;
            }

						/**
             * Third Retirement Condition - Complete
             * Are there 25 or more classifications?
						 */
            if ($number_of_classifications >= $this->complete_condition) {
                $retired = true;
            }

            $votes = $this->tally_votes('species', $subject);
            $nlist = array_values($votes);
            $evenness = $this->calculate_pielou($nlist);

						/**
             * Fourth Retirement Condition - No Consensus
             * Is the agreement too low?
						 */
            if ($evenness >= $this->agreement_condition) {
                $retired = false;
            }

						/* calculate the fraction support */
						$fraction_support = $this->fraction_support($votes);

						/* calculate the fraction blanks */
						$fraction_blanks = $this->fraction_blanks($votes);

            /**
						 * The array 'output' will store all the specification values of the image
             * that have previously been calculated.
						 */
            $output = array(
                'photo_id' => $photo_id,
                'retired' => $retired,
                'number_of_classifications' => $number_of_classifications,
                'species' => $species,
                'gender' => $gender,
                'age' => $age,
                'number' => $number,
                'evenness' => $evenness,
                'fraction_support' => $fraction_support,
                'fraction_blanks' => $fraction_blanks,
            );
            /**
             * The array 'all_outputs' will be the container for each image and therefore its
             * properties. By keeping all the images and their respective properties in this array,
             * we will be able to access and tranfer all properties and values of each feature at once
             * and insert them into our database more efficiently.
             */
            array_push($all_outputs, $output);
        }

        /**
         * Finally, we loop through the array of all image's values and classify the photos all at once, row-by-row.
         * We will classify a photo if it has been retired (decided) and then transfer the values/properties etc. into the
         * database via the 'updateClassifications' variable.
         * The consequence of only classfying retired photos is that we do not store evenness values etc.
         * for the photos which have yet to be retired (decided).
         */

        $i = 0; /* A counter to keep track of the number of images we classify. */
        $updateClassifications = 'INSERT INTO Classification '.
                                '(photo_id, number_of_classifications, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) '.
                                'VALUES ';
        foreach ($all_outputs as $output) {
        /**
         * Retired images will have all their properties stored in local variables and then contatenated into the
         * 'updateClassifications' variable's contents to be stored in the database.
         */
            if ($output['retired']) {
                /* Will only classify 'retired' photos */

                $Cphoto_id = $output['photo_id'];
                $Cnumber_of_classifications = $output['number_of_classifications'];
                $Cspecies = $output['species'];
                $Cgender = $output['gender'];
                $Cage = $output['age'];
                $Cnumber = $output['number'];
                $Cevenness = $output['evenness'];
                $Cfraction_support = $output['fraction_support'];
                $Cfraction_blanks = $output['fraction_blanks'];

                /**
                 * Concatenating properties of image (including ID) with the current contents of the database.
                 */
                $updateClassifications .= "('$Cphoto_id', '$Cnumber_of_classifications', '$Cspecies', '$Cgender', '$Cage', '$Cnumber', '$Cevenness', '$Cfraction_support', '$Cfraction_blanks', now()),";

								/* Increment after every classification of image */
                ++$i;
            }
        }

        /* replace the last character with a semicolon -> ; */
        $updateClassifications = substr($updateClassifications, 0, -1).';';

        /**
         * i.e. A test of if there were images that were retired and so needed to be classified
         * We will check if the update of the classifcations with the image properties was successful or
         * if it wasn't, and echo the appropriate message depending on the answer.
         */

        if ($i > 0) {
            if ($this->mysqli->query($updateClassifications) === true) {
                /* echo "Record updated successfully\n"; */
            } else {
                /* echo "Error updating record: " . $this->mysqli->error . "\n"; */
            }
        }
    }

    /**
     * Calculate vote tallies for each subject.
     *
     * @param string  $key     One of species|age|gender
     * @param array[] $subject Array of classification rows
     *
     * @return array A dictionary with species as the key and the number of votes the species received as value
     */
    public function tally_votes($key, $subject)
    {
        $vote_table = array();

        foreach ($subject as $entry) {
            if (array_key_exists($key, $entry)) {
                $value = $entry[$key];

                /* already in table */
                if (array_key_exists($value, $vote_table)) {
                    $vote_table[$value] = $vote_table[$value] + 1;
                }
                /* not in table yet */
                else {
                    $vote_table[$value] = 1;
                }
            }
        }
        return $vote_table;
    }

    /**
     * Gets the count of the most popular value.
     *
     * @param string  $key     One of species|age|gender
     * @param array[] $subject Array of classification rows
     *
     * @return int The highest number of votes an element has received
     */
    public function highest_vote($key, $subject)
    {
        $votes = $this->tally_votes($key, $subject);
        if (count($votes) > 0) {
            arsort($votes);
            $keys = array_keys($votes);

            return $votes[$keys[0]];
        }
        return 0;
    }

    /**
     * Decides the winner based on the votes for a given key.
     *
     * @param string  $key     One of the species|age|gender|number
     * @param array[] $subject Array of Classification rows
     *
     * @return string of the winning species|gender|age|number for said key
     */
    public function decide_on($key, $subject)
    {
        $votes = $this->tally_votes($key, $subject);
        $winner = '';
        if (count($votes) > 0) {
            arsort($votes);

            $keys = array_keys($votes);
            $winner = $keys[0];
        }

        return $winner;
    }

    /**
     * Calculate the pielou evenness index of a list.
     *
     * @param int[] nlist A list of vote distributions
     *
     * @return float pielou evenness index or 0 for unanimous vote
     */
    public function calculate_pielou($nlist)
    {
        if (count($nlist) < 2) {
            return 0;
        }
        /* denominator */
        $lns = log(count($nlist));
        /* numerator */
        $sumList = array_sum($nlist);
        $plist = array();
        for ($x = 0; $x < count($nlist); ++$x) {
            $plist[] = $nlist[$x] / $sumList;
        }
        $plnplist = array();
        for ($x = 0; $x < count($plist); ++$x) {
            $plnplist[] = $plist[$x] * log($plist[$x]);
        }
        $sumplnp = -array_sum($plnplist);

        return $sumplnp / $lns;
    }

    /**
     * Calulates Fraction support
     * Fraction support is calculated as the fraction of classifications supporting the
     * aggregated answer (i.e. fraction support of 1.0 indicates unanimous support).
     *
     * @param int[] $votes Array of values representing the classifications of a subject
     *
     * @return float The fraction of support for the most voted answer
     */
    public function fraction_support($votes)
    {
        if (count($votes) <= 0) {
            return 0;
        }

        $sum = array_sum(array_values($votes));

        arsort($votes);
        $keys = array_keys($votes);
        $first_value = $votes[$keys[0]];
        if ($sum != 0) {
            return $first_value / $sum;
        }
				return 0;
    }

    /**
     * Calculates the amount of votes that have classified the image as blank
     * for an image that is ultimatly classified as containing an animal.
     *
     * @param int[] $votes Array of values representing the classifications of a subject
     *
     * @return float the fraction of votes that are blank
     */
    public function fraction_blanks($votes)
    {
        if (count($votes) <= 0) {
            return 0;
        }

        $sum = array_sum(array_values($votes));

        if (array_key_exists($this->blank_animal, $votes)) {
            $n = $votes[$this->blank_animal];
        } else {
            $n = 0;
        }

        if ($sum != 0) {
            return $n / $sum;
        }
        return 0;
    }

    /**
     * Gives the number of how many images have been classified into the database
     * so far and lists each classified photos ID.
     *
     * @return array[] of the classified photos ID
     */
    public function getClassified()
    {
        /* QUERY */
        $sql = 'SELECT photo_id FROM Classification;';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $classified = [];

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classified[] = $row['photo_id'];
            }
        }
        return $classified;
    }

    /**
     * Lists each classified photo's entire properties.
     * If no photos have been classified, '0 Results' will be printed.
     *
     * @return array[] of all the classified photos properties
     */
    public function getClassifications()
    {
        /* QUERY */
        $sql = 'SELECT * FROM Classification;';

        /* execute query */
        $result = $this->mysqli->query($sql);

    		$classifications = [];

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classifications[$row['photo_id']] = $row;
            }
        }
        return $classifications;
    }

    /**
     * Get all of the photos IDs.
     *
     * @return array[] of all the photo IDs from the database
     */
    public function getPhotos()
    {
        /* QUERY */
        $sql = 'SELECT * FROM Photo ORDER BY photo_id ASC';
        if ($this->photo_limiting) {
            $sql .= " LIMIT $this->get_photo_limit";
        }
        $sql .= ';';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $photo_ids = [];

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $photo_ids[] = $row['photo_id'];
            }
        }
        return $photo_ids;
    }

    /**
     * Gets the statistics of each person
     * including how often they are correct for species, gender, age and Number.
     *
     * @return array[] of the statistics for each person
     */
    public function getPersonStats()
    {
        /* QUERY */
        $sql = 'SELECT * FROM PersonStats;';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $person_stats = [];

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $person_stats[] = $row;
            }
        }
        return $person_stats;
    }

    /**
     * Gets information about all the animals.
     *
     * @param array[] $classified all the classified information
     * @param array[] $photo_ids  photo IDs of the images
     *
     * @return array[] containing all of the information about the animlas
     */
    public function getAnimals($classified, $photo_ids)
    {
        /* QUERY */
            $sql = 'SELECT * FROM Animal ORDER BY photo_id ASC';
        if ($this->animal_limiting) {
            $sql .= " LIMIT $this->get_animal_limit";
        }
        $sql .= ';';

        /* execute query */
        $result = $this->mysqli->query($sql);

        if (count($result) > 0) {
            $data = [];
            $all_data = [];

            /* process result */
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if (!in_array($row['photo_id'], $classified)) {
                        if (in_array($row['photo_id'], $photo_ids)) {
                            $data[] = $row;
                        }
                    }
                    $all_data[] = $row;
                }
            }
            return [$data, $all_data];
        }

        return;
    }

    /**
     * Gets all of the correct classifications for each photo
     * by getting the classifications done by Penn (person_id 311).
     *
     * @return array[] all of the classifications done by person_id 311 (Penn's classificaitons)
     */
    public function getGoldStandard()
    {
        /* SAMPLE QUERY */
        $sql = 'SELECT * FROM Animal WHERE person_id = 311 ORDER BY photo_id ASC;';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $gold_standard = [];

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gold_standard[] = $row;
            }
        } else {
            /* echo "0 results"; */
            /* echo "\n"; */
        }

        return $gold_standard;
    }

    /**
     * compares that classified photos produced by the algorithm to
     * the classifications got by the GoldStandard displaying
     * a decimal showing the number correctly classified over
     * the correctly classified plus the wrongly classified members.
     * it ignores all the classifications that are seeing no animals.
     */
    public function goldClassifiedComparison()
    {
        $gold_standard = $this->getGoldStandard();

        /* query to get the species and photo_id for each classified image */
        $sql = 'SELECT species, photo_id FROM Classification;';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $classifications = [];

        /* process result into an array with the photo_id as the key and the species as the value */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classifications[$row['photo_id']] = $row['species'];
            }
        } else {
            echo '0 results <br>';
            echo "\n";
        }

        /**
         * echo '<pre>';
         * print_r($classifications);
         * echo '</pre>';
         */

        /* compare the gold standard and the classified species in each photo */
        $same = 0;
        $different = 0;
        $notClassified = 0;

        $different_classifications = array();

        for ($x = 0; $x < count($gold_standard); ++$x) {
            $photo_id = $gold_standard[$x]['photo_id'];
            /* Ignore "Don't know" classifications */
            if (!in_array($gold_standard[$x]['species'], array(96, 97))) {
                if (array_key_exists($photo_id, $classifications)) {
                    if ($classifications[$photo_id] == $gold_standard[$x]['species']) {
                        ++$same;
                    } else {
                        ++$different;
                        $different_classifications[] = $photo_id;
                    }
                } else {
                    ++$notClassified;
                }
            }
        }
        if (($same + $different) > 0) {
            /* echo "Correctness against gold standard = " . ($same / ($same + $different)); */
        }
    }

    /**
     * Populates the PersonStats for each user using all of the classifications.
     *
     * @param array[] $all_data        contains all the information from the Animal table
     * @param array[] $classifications is an array of all the classifications
     */
    public function rateUsers($all_data, $classifications)
    {
				/**
         *  Sorts the all_data array based on person_id
				 */
        usort($all_data, function ($item1, $item2) {
            if ($item1['person_id'] == $item2['person_id']) {
                return 0;
            }

            return $item1['person_id'] < $item2['person_id'] ? -1 : 1;
        });

        /* This array 'all_outputs' will contain all arrays of the $subject once the while loop below has completed. */
        $all_outputs = array();

        while (count($all_data) > 0) {
						/**
             * populate the subject variable with all classifications for one photo
             * subject will contain all rows with that photo_id
						 */
            $subject = array(array_pop($all_data));
            if (count($all_data) > 0) {
                while ($all_data[count($all_data) - 1]['person_id'] == $subject[0]['person_id']) {
                    $subject[] = array_pop($all_data);
                    if (count($all_data) <= 0) {
                        break;
                    }
                }
            }
            /* sorts the subject based no the photo_id */
            /*
             * usort($subject, function ($item1, $item2) {
             *     if ($item1['photo_id'] == $item2['photo_id']) return 0;
             *     return $item1['photo_id'] < $item2['photo_id'] ? -1 : 1;
             * });
    				 */

            $person_id = $subject[0]['person_id'];
            $species_rate = $this->getUserCorrectnessRate('species', $subject, $classifications);
            $gender_rate = $this->getUserCorrectnessRate('gender', $subject, $classifications);
            $age_rate = $this->getUserCorrectnessRate('age', $subject, $classifications);
            $number_rate = $this->getUserCorrectnessRate('number', $subject, $classifications);
            $number_of_classifications = count($subject);

						/**
             * echo "$person_id has $species_rate, $gender_rate, $age_rate, $number_rate";
             * echo "\n";
             * echo "on " . $number_of_classifications . " classifications";
             * echo "\n";
						 */

            /**
             * The array 'output' will store all the subject values of the classifications
             * for the photo that has previously been calculated.
             */
            $output = array(
                    'person_id' => $person_id,
                    'species_rate' => $species_rate,
                    'gender_rate' => $gender_rate,
                    'age_rate' => $age_rate,
                    'number_rate' => $number_rate,
                    'number_of_classifications' => $number_of_classifications,
            );

            /**
             * The array 'all_outputs' will be the container for each $output and therefore its
             * properties. By keeping all the $output arrays and their respective properties in this array,
             * we will be able to access and tranfer all properties and values of each feature at once
             * and insert them into our database more efficiently.
             */
            array_push($all_outputs, $output);
        }

        /**
         * Finally, we loop through the array of all subjects' values and update all stats at once, per person all, row-by-row.
         * We will transfer the values/properties etc. into the
         * database via the 'updatePersonStats' variable.
         */

        $i = 0;

        $updatePersonStats = 'INSERT INTO PersonStats '.
                              '(person_id, species_rate, gender_rate, age_rate, number_rate, number_of_classifications) '.
                              'VALUES ';

        foreach ($all_outputs as $output) {
            /**
             * Outputs will have all their properties stored in local variables and then contatenated into the
             * 'updatePersonStats' variable's contents to be stored in the database.
             */
            $thePerson_id = $output['person_id'];
            $theSpecies_rate = $output['species_rate'];
            $theGender_rate = $output['gender_rate'];
            $theAge_rate = $output['age_rate'];
            $theNumber_rate = $output['number_rate'];
            $theNumber_of_classifications = $output['number_of_classifications'];

            /**
             * Concatenating properties of subject (including person_id) with the current contents of the database.
             */
            $updatePersonStats .= "('$thePerson_id', '$theSpecies_rate', '$theGender_rate', '$theAge_rate', '$theNumber_rate', '$theNumber_of_classifications'),";
            ++$i;
        }
        /* Remove the last comma */
        $updatePersonStats = substr($updatePersonStats, 0, -1).'';
        $updatePersonStats .= ' ON DUPLICATE KEY UPDATE'.
                              ' person_id=VALUES(person_id), species_rate=VALUES(species_rate), gender_rate=VALUES(gender_rate), age_rate=VALUES(age_rate),'.
                              ' number_rate=VALUES(number_rate), number_of_classifications=VALUES(number_of_classifications);';

        /**
         * We will check if the update of the person stats with the subject properties was successful or
         * if it wasn't, and echo the appropriate message depending on the answer.
         */

        if ($i > 0) {
            if ($this->mysqli->query($updatePersonStats) === true) {
                /* echo "Record updated successfully\n"; */
            } else {
                /* echo 'Error updating record: '.$this->mysqli->error."\n"; */
            }
        }
    }

    /**
     * Calculates the amount of votes that the user made that were correct
     * as a fraction of all of the votes that were produced.
     *
     * @param string  $key             One of the species|age|gender|number
     * @param array[] $subject         Array of Classification rows
     * @param array[] $classifications the final classification given to things
     *
     * @return float the fraction of votes that the user has correctly identifies
     */
    public function getUserCorrectnessRate($key, $subject, $classifications)
    {
        $correct = 0;
        $all = 0;

        foreach ($subject as $s) {
            $c = null;

            $photo_id = $s['photo_id'];
            if (array_key_exists($photo_id, $classifications)) {
                $c = $classifications[$photo_id];
            }
            $sSpecies = $s[$key];
            $cSpecies = $c[$key];
            if ($c != null) {
                if ($s[$key] == $c[$key]) {
                    $correct += 1;
                }
                $all += 1;
            }
        }

        $rate = 0;
        if ($all > 0) {
            $rate = $correct / $all;
        }

        return $rate;
    }

    /**
     * Creates the Classification and PersonStats tables
     * in the SQL database if they don't already exist.
     *
     * Classifications contains: classifications_id, photo_id, species, gender, age, number,
     * evenness, fraction_support, fraction_blanks, timestamp, number_of_classifications
     *
     * PersonStats contains: person_stats_id, person_id, species_rate, gender_rate, age_rate, number_rate
     */
    public function createTables()
    {
        /* Creating Classification table */
        $createTable = 'CREATE TABLE IF NOT EXISTS `Classification` ('.
            '`classification_id` int(11) NOT NULL AUTO_INCREMENT,'.
            '`photo_id` int(11) NOT NULL,'.
            '`species` int(11) NOT NULL,'.
            '`gender` int(11) NOT NULL,'.
            '`age` int(11) NOT NULL,'.
            '`number` int(4) NOT NULL,'.
            '`evenness` decimal(10,9) NOT NULL,'.
            '`fraction_support` decimal(10,9) NOT NULL,'.
            '`fraction_blanks` decimal(10,9) NOT NULL,'.
            '`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'.
            '`number_of_classifications` int(11) NOT NULL,'.
            'PRIMARY KEY (`classification_id`),'.
            'KEY `photo_id` (`photo_id`)'.
      	') ENGINE=InnoDB DEFAULT CHARSET=latin1;';
        if ($this->mysqli->query($createTable) === true) {
            /* echo "Classification table created successfully\n"; */
        } else {
            /* echo "Error creating Classification table: " . $this->mysqli->error . "\n"; */
        }

        $alterTable = 'ALTER TABLE `Classification` '.
                'ADD CONSTRAINT `Classification_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `Photo` (`photo_id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        if ($this->mysqli->query($alterTable) === true) {
            /* echo "Classification table altered successfully\n"; */
        } else {
            /* echo "Error altering Classification table: " . $this->mysqli->error . "\n"; */
        }

        /* Creating PersonStats table */
        $createTable = 'CREATE TABLE IF NOT EXISTS PersonStats ('.
            'person_id INT NOT NULL PRIMARY KEY,'.
            'species_rate DECIMAL(10, 9) NOT NULL,'.
            'gender_rate DECIMAL(10, 9) NOT NULL,'.
            'age_rate DECIMAL(10, 9) NOT NULL,'.
            'number_rate DECIMAL(10, 9) NOT NULL'.
        ');';
        if ($this->mysqli->query($createTable) === true) {
            /* echo "PersonStats table created successfully\n"; */
        } else {
            /* echo "Error creating PersonStats table: " . $this->mysqli->error . "\n"; */
        }
    }

    /**
     * Clears all data from the table specified in $table_name
     * and informs you of whether or not it has been successful.
     *
     * @param string $table_name the name of the table to empty
     */
    public function emptyTable($table_name)
    {
        $emptyTable = "TRUNCATE $table_name;";

        if ($this->mysqli->query($emptyTable) === true) {
            /* echo "Record updated successfully"; */
            /* echo "\n"; */
        } else {
            /* echo "Error updating record: " . $this->mysqli->error; */
            /* echo "\n"; */
        }
    }
}
