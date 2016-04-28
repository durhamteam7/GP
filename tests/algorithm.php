<?php

  #######################################
 ##                                     ##
### Implementation of Swanson Algorithm ###
 ##                                     ##
  #######################################

class Swanson {

	private $mysqli;
	private $env = 1;

	private $blank_condition = 1;		#5
	private $consensus_condition = 1;	#10
	private $complete_condition = 2;	#25
	private $agreement_condition = 1;	#1

	private $blank_animal = 86;

	private $animal_limiting = false; # will be false in the end
	private $get_animal_limit = 1;
	private $photo_limiting = false; # will be false in the end
	private $get_photo_limit = 1;

	function __construct() {
		$this->setupDB();
	}

	function __destruct() {
		$this->mysqli->close();
	}

	function setupDB() {
		if ($this->env == 0) {
			$servername = "mysql.dur.ac.uk";
			$username = "nobody";
			$password = "";
			$db = "Cljdw32_MammalWeb";
		}
		else {
			$servername = "db4free.net";
			$username = "mammalweb";
			$password = "aliSwans0n";
			$db = "mammalweb";
		}

		// Create connection
		$this->mysqli = new mysqli($servername, $username, $password, $db);

		// Check connection
		if ($this->mysqli->connect_error) {
		    die("Connection failed: " . $this->mysqli->connect_error);
		}
	}

	function main($data) {
		/* This array 'all_outputs' will contain all arrays of the image
		 values once the while loop below has completed. */
		$all_outputs = array();

	    while (count($data) > 0) {
	        # This loop populates the 'subject' variable with all classifications for one photo
	        # The array 'subject' will contain all rows with that photo_id
	        $subject = array(array_pop($data));
	        while ($data[count($data) - 1]["photo_id"] == $subject[0]["photo_id"]) {
	            $subject[] = array_pop($data);
	        }
	        $photo_id = $subject[0]["photo_id"];
	        $number_of_classifications = count($subject);
	        echo "Subject " . $photo_id;
	        echo "\n";
	        print_r($subject);
	        echo "\n";
	        # Decide the winners
	        $species = $this->decide_on("species", $subject);
	        $gender = $this->decide_on("gender", $subject);
	        $age = $this->decide_on("age", $subject);
	        $number = $this->decide_on("number", $subject);
	        $retired = false;
	        # First Retirement Condition - Blank
	        # Are the 5 first classifications blank?
	        if ($number_of_classifications == $this->blank_condition) {
	        	$all_blank = true;
	            foreach ($subject as $c) {
	                if ($c["species"] != $this->blank_animal) {
	                    $all_blank = false;
	                }
	            }
		        if ($all_blank) {
		            $retired = true;
		        }
	        }
	        # Second Retirement Condition - Consensus
	        # Are there 10 agreeing classifications? (Including blanks)
	        if ($this->highest_vote("species", $subject) >= $this->consensus_condition) {
	            $retired = true;
	        }
	        # Third Retirement Condition - Complete
	        # Are there 25 or more classifications?
	        if ($number_of_classifications >= $this->complete_condition) {
	            $retired = true;
	        }
	        echo "Evenness Index";
	        echo "\n";
	        $votes = $this->tally_votes("species", $subject);
	        $nlist = array_values($votes);
	        $evenness = $this->calculate_pielou($nlist);
	        print_r($evenness);
	        echo "\n";
	        echo "\n";
	        # Fourth Retirement Condition - No Consensus
	        # Is the agreement too low?
	        if ($evenness >= $this->agreement_condition) {
	            $retired = false;
	        }
	        echo "Fraction Support";
	        echo "\n";
	        $fraction_support = $this->fraction_support($votes);
	        print_r($fraction_support);
	        echo "\n";
	        echo "\n";
	        echo "Fraction Blanks";
	        echo "\n";
	        $fraction_blanks = $this->fraction_blanks($votes);
	        print_r($fraction_blanks);
	        echo "\n";
	        echo "\n";
	        /* The array 'output' will store all the specification values of the image
	        that have previously been calculated. */
	        $output = array(
	            "photo_id" => $photo_id,
	            "retired" => $retired,
	            "number_of_classifications" => $number_of_classifications,
	            "species" => $species,
	            "gender" => $gender,
	            "age" => $age,
	            "number" => $number,
	            "evenness" => $evenness,
	            "fraction_support" => $fraction_support,
	            "fraction_blanks" => $fraction_blanks
	        );
	        /*
	        This will print the image values in human-readable form takne from the array
	        and illustrate the relationships in the array.
	        */
	        echo "\n";
	        print_r($output);
	        echo "\n";

	        # Adding into the array of all image's values
	        
	        /*
	        The array 'all_outputs' will be the container for each image and therefore its
	        properties. By keeping all the images and their respective properties in this array,
	        we will be able to access and tranfer all properties and values of each feature at once
	        and insert them into our database more efficiently.
	        */
	        array_push($all_outputs, $output);
	        
	    }
		/* 
		Finally, we loop through the array of all image's values and classify the photos all at once, row-by-row.
	        We will classify a photo if it has been retired (decided) and then transfer the values/properties etc. into the
	        database via the 'updateClassifications' variable.
	        The consequence of only classfying retired photos is that we do not store evenness values etc.
	        for the photos which have yet to be retired (decided).
	        */
	        
	    $i = 0; // A counter to keep track of the number of images we classify.
	    $updateClassifications = "INSERT INTO Classification " .
	                            "(photo_id, number_of_classifications, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) " . 
	                            "VALUES ";
	    foreach ($all_outputs as $output)
	    {
	    
	    /* 
	    Retired images will have all their properties stored in local variables and then contatenated into the
	    'updateClassifications' variable's contents to be stored in the database.
	    */
	    
	        if ($output["retired"]) // Will only classify 'retired' photos
	        {
	        	$Cphoto_id = $output["photo_id"];
	        	$Cnumber_of_classifications = $output["number_of_classifications"];
	        	$Cspecies = $output["species"];
	        	$Cgender = $output["gender"];
	        	$Cage = $output["age"];
	        	$Cnumber = $output["number"];
	        	$Cevenness = $output["evenness"];
	        	$Cfraction_support = $output["fraction_support"];
	        	$Cfraction_blanks = $output["fraction_blanks"];
				
				/*
				Concatenating properties of image (including ID) with the current contents of the database.
				*/
				$updateClassifications .= "('$Cphoto_id', '$Cnumber_of_classifications', '$Cspecies', '$Cgender', '$Cage', '$Cnumber', '$Cevenness', '$Cfraction_support', '$Cfraction_blanks', now()),";
				$i++; // Incremented after every classification of image
	        }
	    }
	    
	    # replace the last character with a semicolon -> ;
	    $updateClassifications = substr($updateClassifications, 0, -1) . ";";

	    #echo "Insert query\n";
	    #echo $updateClassifications;
	    #echo "\n";
	    
	    /* 
	    i.e. A test of if there were images that were retired and so needed to be classified
	    We will check if the update of the classifcations with the image properties was successful or
	    if it wasn't, and echo the appropriate message depending on the answer. 
	    */
	    
	    if ($i > 0) 
	    { 
		    if ($this->mysqli->query($updateClassifications) === TRUE)
		    {
		        echo "Record updated successfully\n";
		    } 
		    else 
		    {
		        echo "Error updating record: " . $this->mysqli->error . "\n";
		    }
	    }
	}


	//returns a dictionary giving the vote tallies for a subject
	//input the key to use,  a list of classifications lines, each of wich is a list
	//output a dictionary with species as the key and the number of votes the species received as value
	function tally_votes($key, $subject)
	{
		$vote_table = array();

		foreach ($subject as $entry) 
		{
			if (array_key_exists($key, $entry)) {
				$value = $entry[$key];

				# already in table
				if (array_key_exists($value, $vote_table))
				{
					$vote_table[$value] = $vote_table[$value] + 1;
				}
				# not in table yet
				else
				{
					$vote_table[$value] = 1;
				}
			}
		}

		return $vote_table;
	}

	//gets the number of the most popular alternative
	//input the key to use, a list of classifications lines, each of wich is a list
	//output the highest number of votes an element has received
	function highest_vote($key, $subject)
	{
		$votes = $this->tally_votes($key, $subject);
		sort($votes);
		return $votes[0];
	}

	//calculate the pielou evenness index
	//input a list giving the distribution of votes
	//output the pielou evenness index or 0 for unanimous vote
	function calculate_pielou($nlist)
	{
		if (count($nlist) < 2)
		{
			return 0;
		}
		// denominator
		$lns = log(count($nlist));
		// numerator
		$sumList = array_sum($nlist);
		$plist = array();
		for($x=0; $x<count($nlist); $x++)
		{
			$plist[] = $nlist[$x] / $sumList;
		}
		$plnplist = array();
		for($x=0; $x<count($plist); $x++)
		{
			$plnplist[] = $plist[$x] * log($plist[$x]);
		}
		$sumplnp = -array_sum($plnplist);
		return $sumplnp / $lns;
	}

	# Fraction support is calculated as the fraction of classifications supporting the
	# aggregated answer (i.e. fraction support of 1.0 indicates unanimous support).
	# INPUT: a list of values representing the classifications of a subject
	function fraction_support($votes)
	{
		if (count($votes) <= 0) {
			return 0;
		}

		$sum = array_sum(array_values($votes));
		
		arsort($votes);
		$keys = array_keys($votes);
		$first_value = $votes[$keys[0]];
		return $first_value/$sum;
	}

	# Fraction blanks is calculated as the fraction of classifiers who reported “nothing here”
	# for an image that is ultimately classified as containing an animal.
	# INPUT: a list of values representing the classifications of a subject
	# OUTPUT
	function fraction_blanks($votes)
	{
		if (count($votes) <= 0) {
			return 0;
		}

		$sum = array_sum(array_values($votes));

		$n = $votes[$this->blank_animal];
		return $n/$sum;
		
	}

	# Decides based on the votes for a given key
	function decide_on($key, $subject)
	{
	    $votes = $this->tally_votes($key, $subject);
	    arsort($votes);

	    echo "Votes Per $key";
	    echo "\n";
	    print_r($votes);
	    echo "\n";

	    $keys = array_keys($votes);
	    $winner = $keys[0];

	    echo "Winning " . ucfirst($key);
	    echo "\n";
	    print_r($winner);
	    echo "\n";
	    echo "\n";

	    return $winner;
	}

	# Takes a users classifications and all decided classifications
	# and compares how well the user classifies
	# INPUT: the key to check (species, gender, age, number), users classifications, all decided classifications
	# OUTPUT: the correctness rate the user has for that key
	function getUserCorrectnessRate($key, $subject, $classifications) 
	{
	    $correct = 0;
	    $all = 0;

	    foreach ($subject as $s) {
	        $c = null;
	        foreach ($classifications as $classification) {
	            if ($s["photo_id"] == $classification["photo_id"]) {
	                $c = $classification;
	            }
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
	
	/*
	
	*/
	function getAnimals($classified, $photo_ids) {
		// QUERY
		$sql = "SELECT * FROM Animal ORDER BY photo_id";
		if ($this->animal_limiting) {
			$sql .= " DESC LIMIT $this->get_animal_limit";
		}
		$sql .= ";";

		// execute query
		$result = $this->mysqli->query($sql);

		$data = [];
		$all_data = [];

		// process result
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        if (!in_array($row["photo_id"], $classified)) {
		            if (in_array($row["photo_id"], $photo_ids)) {
		                $data[] = $row;
		            }
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

		return [$data, $all_data];
	}
	
	/*
	Gives the number of how many images have been classified into the database
	so far and lists each classified photo's ID.
	If no photos have been classified, '0 Results' will be printed.
	*/
	function getClassified() {
		// QUERY
		$sql = "SELECT photo_id FROM Classification;";

		// execute query
		$result = $this->mysqli->query($sql);

		$classified = [];

		// process result
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        $classified[] = $row["photo_id"];
		    }
		} else {
		    echo "0 results";
		    echo "\n";
		}

		echo "Getting already classified photo_ids";
		echo "\n";
		echo count($classified) . " classified entries retrieved";
		echo "\n";
		print_r($classified);
		echo "\n";

		return $classified;
	}
	
	/*
	Gives the number of how many images have been classified into the database
	so far and lists each classified photo's entire properties.
	If no photos have been classified, '0 Results' will be printed.
	*/
	function getClassifications() {
		// QUERY
		$sql = "SELECT * FROM Classification;";

		// execute query
		$result = $this->mysqli->query($sql);

		$classifications = [];

		// process result
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        $classifications[] = $row;
		    }
		} else {
		    echo "0 results";
		    echo "\n";
		}

		echo count($classifications) . " classifications retrieved";
		echo "\n";
		echo "\n";
		print_r($classifications);
		echo "\n";

		return $classifications;
	}
	
	/*
        
	*/
	function getPhotos() {
		// QUERY
		$sql = "SELECT * FROM Photo ORDER BY photo_id";
		if ($this->photo_limiting) {
			$sql .= " DESC LIMIT $this->get_photo_limit";
		}
		$sql .= ";";

		// execute query
		$result = $this->mysqli->query($sql);

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

		return $photo_ids;
	}
	
	/*
	Prints the statistics of each Person and how many people there are with statistics in
	the database. 
	Statistics include: Species Rate, Gender Rate, Age Rate and Number Rate with respect to correctness.
	If there are no stats to print, '0 results' wil be printed.
	*/
	function getPersonStats() {
		// QUERY
		$sql = "SELECT * FROM PersonStats;";

		// execute query
		$result = $this->mysqli->query($sql);

		$person_stats = [];

		// process result
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        $person_stats[] = $row;
		    }
		} else {
		    echo "0 results";
		    echo "\n";
		}

		echo count($person_stats) . " person stats retrieved";
		echo "\n";
		echo "\n";
		print_r($person_stats);
		echo "\n";

		return $person_stats;
	}

	function getGoldStandard() {
		// SAMPLE QUERY
		$sql = "SELECT * FROM Animal WHERE person_id = 311 ORDER BY photo_id ASC;";

		// execute query
		$result = $this->mysqli->query($sql);

		$gold_standard = [];

		// process result
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        $gold_standard[] = $row;
		    }
		} else {
		    echo "0 results";
		    echo "\n";
		}

		return $gold_standard;
	}

	function goldClassifiedComparison() {
		$gold_standard = $this->getGoldStandard();

		// query to get the species and photo_id for each classified image
		$sql = "SELECT species, photo_id FROM Classification;";

		// execute query
		$result = $this->mysqli->query($sql);

		$classifications = [];

		// process result into an array with the photo_id as the key and the species as the value
		if($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$classifications[$row[photo_id]] = $row[species];
			}
		}
		else
		{
			echo "0 results <br>";
			echo "\n";
		}

		/*
		echo '<pre>';
		print_r($classifications);
		echo '</pre>';
		*/

		//compare the gold standard and the classified species in each photo
		$same = 0;
		$different = 0;
		$notClassified = 0;

		$different_classifications = array();

		for($x=0; $x<count($gold_standard); $x++)
		{
			$photo_id = $gold_standard[$x][photo_id];
			# Ignore "Don't know" classifications
			if (!in_array($gold_standard[$x][species], array(96, 97)))
			{
				if (array_key_exists($photo_id, $classifications))
				{
					if ($classifications[$photo_id] == $gold_standard[$x][species])
					{
						$same++;
					}
					else
					{
						$different++;
						$different_classifications[] = $photo_id;
					}
				}
				else
				{
					$notClassified++;
				}
			}
		}
		if (($same + $different) > 0) {
			echo "Correctness against gold standard = " . ($same / ($same + $different));
		}
		echo "\n";
		echo "<br>";
		echo "\n";
		echo "same results = ".$same;
		echo "\n";
		echo "<br>";
		echo "\n";
		echo "different results = ".$different;
		echo "\n";
		echo "<br>";
		echo "\n";
		echo "not classified = ".$notClassified;
		echo "\n";
		echo "<br>";
		echo "\n";
		echo "photo_ids where it differs:";
		echo "\n";
		print_r($different_classifications);
		echo "\n";
		echo "<br>";
		echo "\n";
		echo "\n";
	}

	function rateUsers($all_data, $classifications) {
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

		    $species_rate = $this->getUserCorrectnessRate("species", $subject, $classifications);
		    $gender_rate = $this->getUserCorrectnessRate("gender", $subject, $classifications);
		    $age_rate = $this->getUserCorrectnessRate("age", $subject, $classifications);
		    $number_rate = $this->getUserCorrectnessRate("number", $subject, $classifications);

		    $number_of_classifications = count($subject);

		    echo "$person_id has $species_rate, $gender_rate, $age_rate, $number_rate";
		    echo "\n";
		    echo "on " . $number_of_classifications . " classifications";
		    echo "\n";

		    #Output -- Needs to be made more efficient using the same method as in the Algorithm.PHP file.
		    $updatePersonStats = "INSERT INTO PersonStats (person_id, species_rate, gender_rate, age_rate, number_rate, number_of_classifications) " .
		    "VALUES ('$person_id', '$species_rate', '$gender_rate', '$age_rate', '$number_rate', '$number_of_classifications') " .
		    "ON DUPLICATE KEY UPDATE person_id=person_id," .
		    "species_rate='$species_rate'," .
		    "gender_rate='$gender_rate'," .
		    "age_rate='$age_rate'," .
		    "number_rate='$number_rate'," .
		    "number_of_classifications='$number_of_classifications';";

		    #echo $updatePersonStats . "\n";

		    if ($this->mysqli->query($updatePersonStats) === TRUE) {
		        echo "Record updated successfully";
		        echo "\n";
		    } else {
		        echo "Error updating record: " . $this->mysqli->error;
		        echo "\n";
		    }
		}
	}

	function createTables() {
		# Creating Classification table
		$createTable = "CREATE TABLE IF NOT EXISTS `Classification` (".
		  "`classification_id` int(11) NOT NULL AUTO_INCREMENT,".
		  "`photo_id` int(11) NOT NULL,".
		  "`species` int(11) NOT NULL,".
		  "`gender` int(11) NOT NULL,".
		  "`age` int(11) NOT NULL,".
		  "`number` int(4) NOT NULL,".
		  "`evenness` decimal(10,9) NOT NULL,".
		  "`fraction_support` decimal(10,9) NOT NULL,".
		  "`fraction_blanks` decimal(10,9) NOT NULL,".
		  "`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
		  "`number_of_classifications` int(11) NOT NULL,".
		  "PRIMARY KEY (`classification_id`),".
		  "KEY `photo_id` (`photo_id`)".
		") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		if ($this->mysqli->query($createTable) === TRUE) {
		    echo "Classification table created successfully\n";
		} else {
		    echo "Error creating Classification table: " . $this->mysqli->error . "\n";
		}

		$alterTable = "ALTER TABLE `Classification` ".
		  "ADD CONSTRAINT `Classification_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `Photo` (`photo_id`) ON DELETE CASCADE ON UPDATE CASCADE;";
		if ($this->mysqli->query($alterTable) === TRUE) {
		    echo "Classification table altered successfully\n";
		} else {
		    echo "Error altering Classification table: " . $this->mysqli->error . "\n";
		}

		# Creating PersonStats table
		$createTable = "CREATE TABLE IF NOT EXISTS PersonStats (".
		    "person_stats_id INT(11) AUTO_INCREMENT PRIMARY KEY,".
		    "person_id INT NOT NULL,".
		    "species_rate DECIMAL(10, 9) NOT NULL,".
		    "gender_rate DECIMAL(10, 9) NOT NULL,".
		    "age_rate DECIMAL(10, 9) NOT NULL,".
		    "number_rate DECIMAL(10, 9) NOT NULL".
		");";
		if ($this->mysqli->query($createTable) === TRUE) {
		    echo "PersonStats table created successfully\n";
		} else {
		    echo "Error creating PersonStats table: " . $this->mysqli->error . "\n";
		}

	}

	function emptyTable($table_name) {
		$emptyTable = "TRUNCATE $table_name;";

		if ($this->mysqli->query($emptyTable) === TRUE) {
		    echo "Record updated successfully";
		    echo "\n";
		} else {
		    echo "Error updating record: " . $this->mysqli->error;
		    echo "\n";
		}
	}
}
?>
