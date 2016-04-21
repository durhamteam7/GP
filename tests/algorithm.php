<?php

  #######################################
 ##                                     ##
### implementation of swanson algorithm ###
 ##                                     ##
  #######################################

class Swanson {

	private $blank_condition = 1;		#5
	private $consensus_condition = 1;	#10
	private $complete_condition = 2;	#25
	private $agreement_condition = 1;	#1

	function main($data, $mysqli) {

		# This array will contain all arrays of image
		# values once the while loop below has completed.
		$all_outputs = array();

	    while (count($data) > 0) {
	        # populate the subject variable with all classifications for one photo
	        # subject will contain all rows with that photo_id
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
	                if ($c["species"] != 86) {
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
	        echo "\n";
	        print_r($output);
	        echo "\n";

	        # Adding into the array of all image's values
	        array_push($all_outputs, $output);
	        
	    }

		# Finally, we loop through the array of all image's values and classify the photos all at once, row-by-row.
	    # For now, we only classify a photo if it has been retired.
	        # The consequence is that we do not store evenness values etc.
	        # for photos which have yet to be retired (decided).
	    $updateClassifications = "INSERT INTO Classification " .
	                            "(photo_id, number_of_classifications, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) " . 
	                            "VALUES ";
	    foreach ($all_outputs as $output)
	    {

	        if ($output["retired"]) 
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

				$updateClassifications .= "('$Cphoto_id', '$Cnumber_of_classifications', '$Cspecies', '$Cgender', '$Cage', '$Cnumber', '$Cevenness', '$Cfraction_support', '$Cfraction_blanks', now()), ";
	        }
	    }
	    $updateClassifications = substr($updateClassifications, 0, -2) . ";";

	    #echo "Insert query\n";
	    #echo $updateClassifications;
	    #echo "\n";

	    if ($mysqli->query($updateClassifications) === TRUE)
	    {
	        echo "Record updated successfully\n";
	    } 
	    else 
	    {
	        echo "Error updating record: " . $mysqli->error . "\n";
	    }
	}


	//returns a dictionary giving the vote tallies for a subject
	//input a list of classifications lines, each of wich is a list
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

	// Gets the number of the most popular alternative
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

		$blank = 86; # 86 - noanimal - Nothing <span class='fa fa-ban'/>	
		$n = $votes[$blank];
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
	function getUserCorrectnessRate($key, $subject, $classifications) {
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
}
?>
