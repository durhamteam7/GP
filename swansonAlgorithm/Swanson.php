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
    private $blankCondition;        /* 5 in Swanson */
    /**
     * The number of classifications that need to agree to retire photo.
     *
     * @var int
     */
    private $consensusCondition;    /* 10 in Swanson */
    /**
     * The maximum number of classifications needed before we retire photo.
     *
     * @var int
     */
    private $completeCondition;     /* 25 in Swanson */
    /**
     * Defines a minimum evenness value: any photo with a greater than or equal evenness will be retired.
     *
     * @var float
     */
    private $agreementCondition;    /* 1.0 in Swanson */

    /**
     * The database value for a blank classification.
     *
     * @var int
     */
    private $blankAnimal = 86;

    /**
     * Determines whether we want to limit our animal select statement or not.
     *
     * @var bool
     */
    private $animalLimiting = false; /* will be false in the end */
    /**
     * The limit on the animal select query.
     *
     * @var int
     */
    private $getAnimalLimit = 1;
    /**
     * Determines whether we want to limit our photo select statement or not.
     *
     * @var bool
     */
    private $photoLimiting = false; /* will be false in the end */
    /**
     * The limit on the photo select query.
     *
     * @var int
     */
    private $getPhotoLimit = 1;

    public function __construct()
    {
        $this->setupDB();
        $this->loadAlgorithmSettings();
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
            $database = 'Cljdw32_MammalWeb';

            /* Create connection */
            $this->mysqli = new mysqli($servername, $username, $password, $database);
        } else if ($this->env == 1) {
            $servername = 'db4free.net';
            $username = 'mammalweb';
            $password = 'aliSwans0n';
            $database = 'mammalweb';

            /* Create connection */
            $this->mysqli = new mysqli($servername, $username, $password, $database);
        }

        /* Check connection */
        if ($this->mysqli->connect_error) {
            /* echo "Connection failed: " . $this->mysqli->connect_error; */
            return false;
        }
        return true;
    }

    public function loadAlgorithmSettings() {
          /* QUERY */
          $sql = 'SELECT * FROM AlgorithmSettings LIMIT 1;';

          /* execute query */
          $result = $this->mysqli->query($sql);

          $settings;

          /* process result */
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $settings = $row;
              }

              /* Set our variables to the values in the DB */
              $this->blankCondition = $settings['blank_condition'];
              $this->consensusCondition = $settings['consensus_condition'];
              $this->completeCondition = $settings['complete_condition'];
              $this->agreementCondition = $settings['agreement_condition'];
          }
    }

    public function getAlgorithmSettings() {
        return array($this->blankCondition, $this->consensusCondition, $this->completeCondition, $this->agreementCondition);
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
				 * This array 'allOutputs' will contain all arrays of the image
         * values once the while loop below has completed.
				 */
        $allOutputs = array();

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
            $numClassifications = count($subject);

            /* Decide the winners */
            $species = $this->decideOn('species', $subject);
            $gender = $this->decideOn('gender', $subject);
            $age = $this->decideOn('age', $subject);
            $number = $this->decideOn('number', $subject);
            $retired = false;

						/**
             * First Retirement Condition - Blank
             * Are the 5 first classifications blank?
						 */
            if ($numClassifications == $this->blankCondition) {
                $allBlank = true;
                foreach ($subject as $c) {
                    if ($c['species'] != $this->blankAnimal) {
                        $allBlank = false;
                    }
                }
                if ($allBlank) {
                    $retired = true;
                }
            }

						/**
             * Second Retirement Condition - Consensus
             * Are there 10 agreeing classifications? (Including blanks)
						 */
            if ($this->highestVote('species', $subject) >= $this->consensusCondition) {
                $retired = true;
            }

						/**
             * Third Retirement Condition - Complete
             * Are there 25 or more classifications?
						 */
            if ($numClassifications >= $this->completeCondition) {
                $retired = true;
            }

            $votes = $this->tallyVotes('species', $subject);
            $nlist = array_values($votes);
            $evenness = $this->calculatePielou($nlist);

						/**
             * Fourth Retirement Condition - No Consensus
             * Is the agreement too low?
						 */
            if ($evenness >= $this->agreementCondition) {
                $retired = false;
            }

						/* calculate the fraction support */
						$fraction_support = $this->fractionSupport($votes);

						/* calculate the fraction blanks */
						$fraction_blanks = $this->fractionBlanks($votes);

            /**
						 * The array 'output' will store all the specification values of the image
             * that have previously been calculated.
						 */
            $output = array(
                'photo_id' => $photo_id,
                'retired' => $retired,
                'number_of_classifications' => $numClassifications,
                'species' => $species,
                'gender' => $gender,
                'age' => $age,
                'number' => $number,
                'evenness' => $evenness,
                'fraction_support' => $fraction_support,
                'fraction_blanks' => $fraction_blanks,
            );
            /**
             * The array 'allOutputs' will be the container for each image and therefore its
             * properties. By keeping all the images and their respective properties in this array,
             * we will be able to access and tranfer all properties and values of each feature at once
             * and insert them into our database more efficiently.
             */
            array_push($allOutputs, $output);
        }

        /**
         * Finally, we loop through the array of all image's values and classify the photos all at once, row-by-row.
         * We will classify a photo if it has been retired (decided) and then transfer the values/properties etc. into the
         * database via the 'updateClassification' variable.
         * The consequence of only classfying retired photos is that we do not store evenness values etc.
         * for the photos which have yet to be retired (decided).
         */

        $count = 0; /* A counter to keep track of the number of images we classify. */
        $updateClassification = 'INSERT INTO Classification '.
                                '(photo_id, number_of_classifications, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) '.
                                'VALUES ';
        foreach ($allOutputs as $output) {
        /**
         * Retired images will have all their properties stored in local variables and then contatenated into the
         * 'updateClassification' variable's contents to be stored in the database.
         */
            if ($output['retired']) {
                /* Will only classify 'retired' photos */

                $CPhotoID = $output['photo_id'];
                $CNumClassifications = $output['number_of_classifications'];
                $CSpecies = $output['species'];
                $CGender = $output['gender'];
                $CAge = $output['age'];
                $CNumber = $output['number'];
                $CEvenness = $output['evenness'];
                $CFractionSupport = $output['fraction_support'];
                $CFractionBlanks = $output['fraction_blanks'];

                /**
                 * Concatenating properties of image (including ID) with the current contents of the database.
                 */
                $updateClassification .= "('$CPhotoID', '$CNumClassifications', '$CSpecies', '$CGender', '$CAge', '$CNumber', '$CEvenness', '$CFractionSupport', '$CFractionBlanks', now()),";

								/* Increment after every classification of image */
                ++$count;
            }
        }

        /* replace the last character with a semicolon -> ; */
        $updateClassification = substr($updateClassification, 0, -1).';';

        /**
         * i.e. A test of if there were images that were retired and so needed to be classified
         * We will check if the update of the classifcations with the image properties was successful or
         * if it wasn't, and echo the appropriate message depending on the answer.
         */

        if ($count > 0) {
            if ($this->mysqli->query($updateClassification) === true) {
                /* echo "Record updated successfully\n"; */
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
    public function tallyVotes($key, $subject)
    {
        $voteTable = array();

        foreach ($subject as $entry) {
            if (array_key_exists($key, $entry)) {
                $value = $entry[$key];

                if (!array_key_exists($value, $voteTable)) {
                    $voteTable[$value] = 0;
                }

                $voteTable[$value] = $voteTable[$value] + 1;
            }
        }
        return $voteTable;
    }

    /**
     * Gets the count of the most popular value.
     *
     * @param string  $key     One of species|age|gender
     * @param array[] $subject Array of classification rows
     *
     * @return int The highest number of votes an element has received
     */
    public function highestVote($key, $subject)
    {
        $votes = $this->tallyVotes($key, $subject);
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
    public function decideOn($key, $subject)
    {
        $votes = $this->tallyVotes($key, $subject);
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
    public function calculatePielou($nlist)
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
    public function fractionSupport($votes)
    {
        if (count($votes) <= 0) {
            return 0;
        }

        $sum = array_sum(array_values($votes));

        arsort($votes);
        $keys = array_keys($votes);
        $firstValue = $votes[$keys[0]];
        if ($sum != 0) {
            return $firstValue / $sum;
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
    public function fractionBlanks($votes)
    {
        if (count($votes) <= 0) {
            return 0;
        }

        $sum = array_sum(array_values($votes));
        $num = 0;

        if (array_key_exists($this->blankAnimal, $votes)) {
            $num = $votes[$this->blankAnimal];
        }

        if ($sum != 0) {
            return $num / $sum;
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

        $classified = array();

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

    		$classifications = array();

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
        if ($this->photoLimiting) {
            $sql .= " LIMIT $this->getPhotoLimit";
        }
        $sql .= ';';

        /* execute query */
        $result = $this->mysqli->query($sql);

        $photoIDs = array();

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $photoIDs[] = $row['photo_id'];
            }
        }
        return $photoIDs;
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

        $personStats = array();

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $personStats[] = $row;
            }
        }
        return $personStats;
    }

    /**
     * Gets information about all the animals.
     *
     * @param array[] $classified all the classified information
     * @param array[] $photoIDs  photo IDs of the images
     *
     * @return array[] containing all of the information about the animlas
     */
    public function getAnimals($classified, $photoIDs)
    {
        /* QUERY */
            $sql = 'SELECT * FROM Animal ORDER BY photo_id ASC';
        if ($this->animalLimiting) {
            $sql .= " LIMIT $this->getAnimalLimit";
        }
        $sql .= ';';

        /* execute query */
        $result = $this->mysqli->query($sql);

        if (count($result) > 0) {
            $data = array();
            $all_data = array();

            /* process result */
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if (!in_array($row['photo_id'], $classified)) {
                        if (in_array($row['photo_id'], $photoIDs)) {
                            $data[] = $row;
                        }
                    }
                    $all_data[] = $row;
                }
            }
            return array($data, $all_data);
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

        $gold_standard = array();

        /* process result */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gold_standard[] = $row;
            }
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

        $classifications = array();

        /* process result into an array with the photo_id as the key and the species as the value */
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classifications[$row['photo_id']] = $row['species'];
            }
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

        $dif_classifications = array();

        for ($x = 0; $x < count($gold_standard); ++$x) {
            $photo_id = $gold_standard[$x]['photo_id'];
            /* Ignore "Don't know" classifications */
            if (!in_array($gold_standard[$x]['species'], array(96, 97))) {
                if (array_key_exists($photo_id, $classifications)) {
                    if ($classifications[$photo_id] == $gold_standard[$x]['species']) {
                        ++$same;
                    } else if ($classifications[$photo_id] != $gold_standard[$x]['species']) {
                        # code...
                    } {
                        ++$different;
                        $dif_classifications[] = $photo_id;
                    }
                } else if (!array_key_exists($photo_id, $classifications)) {
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

        /* This array 'allOutputs' will contain all arrays of the $subject once the while loop below has completed. */
        $allOutputs = array();

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

            $person_id = $subject[0]['person_id'];
            $species_rate = $this->getUserCorrectnessRate('species', $subject, $classifications);
            $gender_rate = $this->getUserCorrectnessRate('gender', $subject, $classifications);
            $age_rate = $this->getUserCorrectnessRate('age', $subject, $classifications);
            $number_rate = $this->getUserCorrectnessRate('number', $subject, $classifications);
            $numClassifications = count($subject);

						/**
             * echo "$person_id has $species_rate, $gender_rate, $age_rate, $number_rate";
             * echo "\n";
             * echo "on " . $numClassifications . " classifications";
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
                    'number_of_classifications' => $numClassifications,
            );

            /**
             * The array 'allOutputs' will be the container for each $output and therefore its
             * properties. By keeping all the $output arrays and their respective properties in this array,
             * we will be able to access and tranfer all properties and values of each feature at once
             * and insert them into our database more efficiently.
             */
            array_push($allOutputs, $output);
        }

        /**
         * Finally, we loop through the array of all subjects' values and update all stats at once, per person all, row-by-row.
         * We will transfer the values/properties etc. into the
         * database via the 'updatePersonStats' variable.
         */

        $count = 0;

        $updatePersonStats = 'INSERT INTO PersonStats '.
                              '(person_id, species_rate, gender_rate, age_rate, number_rate, number_of_classifications) '.
                              'VALUES ';

        foreach ($allOutputs as $output) {
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
            ++$count;
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

        if ($count > 0) {
            if ($this->mysqli->query($updatePersonStats) === true) {
                /* echo "Record updated successfully\n"; */
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
     * PersonStats contains: personStats, person_id, species_rate, gender_rate, age_rate, number_rate
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
        }

        $alterTable = 'ALTER TABLE `Classification` '.
                'ADD CONSTRAINT `Classification_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `Photo` (`photo_id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        if ($this->mysqli->query($alterTable) === true) {
            /* echo "Classification table altered successfully\n"; */
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
        }

        /* Creating AlgorithmSettings table */
        $createTable = 'CREATE TABLE IF NOT EXISTS AlgorithmSettings ('.
            'settings_id INT NOT NULL PRIMARY KEY,'.
            'blank_condition INT NOT NULL,'.
            'consensus_condition INT NOT NULL,'.
            'complete_condition INT NOT NULL,'.
            'agreement_condition DECIMAL(2, 1) NOT NULL'.
        ');';
        if ($this->mysqli->query($createTable) === true) {
            /* echo "AlgorithmSettings table created successfully\n"; */
        }

        /* Creating Favourites table */
        $createTable = 'CREATE TABLE IF NOT EXISTS Favourites ('.
            'person_id INT NOT NULL PRIMARY KEY,'.
            'photo_id INT NOT NULL,'.
        ');';
        if ($this->mysqli->query($createTable) === true) {
            /* echo "Favourites table created successfully\n"; */
        }
    }

    /**
     * Clears all data from the table specified in $tableName
     * and informs you of whether or not it has been successful.
     *
     * @param string $tableName the name of the table to empty
     */
    public function emptyTable($tableName)
    {
        $emptyTable = "TRUNCATE $tableName;";

        if ($this->mysqli->query($emptyTable) === true) {
            /* echo "Record updated successfully"; */
            /* echo "\n"; */
        }
    }
}
