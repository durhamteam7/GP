<?php

  #######################################
 ##                                     ##
### implementation of swanson algorithm ###
 ##                                     ##
  #######################################

class Swanson {

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
	        $all_blank = true;
	        if ($number_of_classifications == 5) {
	            foreach ($subject as $c) {
	                if ($c["species"] != 86) {
	                    $all_blank = false;
	                }
	            }
	        }
	        if ($all_blank) {
	            $retired = true;
	        }
	        # Second Retirement Condition - Consensus
	        # Are there 10 agreeing classifications? (Including blanks)
	        if ($species >= 10) {
	            $retired = true;
	        }
	        # Third Retirement Condition - Complete
	        # Are there 25 or more classifications?
	        if ($number_of_classifications >= 25) {
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

	    foreach ($all_outputs as $output) 
	    {

	        if ($output["retired"]) 
	        {
	        	$photo_id = $output["photo_id"];
	        	$number_of_classifications = $output["number_of_classifications"];
	        	$species = $output["species"];
	        	$gender = $output["gender"];
	        	$age = $output["age"];
	        	$number = $output["number"];
	        	$evenness = $output["evenness"];
	        	$fraction_support = $output["fraction_support"];
	        	$fraction_blanks = $output["fraction_blanks"];

	            $updateQuery = "INSERT INTO Classification " .
	                            "(photo_id, number_of_classifications, species, gender, age, number, evenness, fraction_support, fraction_blanks, timestamp) " .
	                            "VALUES ('$photo_id', '$number_of_classifications', '$species', '$gender', '$age', '$number', '$evenness', '$fraction_support', '$fraction_blanks', now());";
	            if ($mysqli->query($updateQuery) === TRUE) {
	                echo "Record updated successfully";
	            } else {
	                echo "Error updating record: " . $mysqli->error;
	            }
	        }

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
	        if ($s[$key] == $c[$key]) {
	            $correct += 1;
	        }
	        $all += 1;
	    }

	    $rate = 0;
	    if ($all > 0) {
	        $rate = $correct / $all;
	    }
	    return $rate;
	}


	###################################################################
	# The following functions are not currently used in the algorithm #
	###################################################################

	# Calculates the median of an array
	function calculate_median($arr)
	{
		if (count($arr) <= 0) {
			return 0;
		}
	    $count = count($arr); //total numbers in array
	    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
	    if($count % 2) 
	    { // odd number, middle is the median
	        $median = $arr[$middleval];
	    } 
	    else { // even number, calculate avg of 2 medians
	        $low = $arr[$middleval];
	        $high = $arr[$middleval+1];
	        $median = ceil(($low+$high)/2); // Rounds the value up if fraction
	    }
	    return $median;
	}

	# Counts number of ocurrences of a specified item in an array.
	function array_count_values_of($value, $array) 
	{
		if (count($array) <= 0) {
			return 0;
		}
	    $counts = array_count_values($array);
	    if (array_key_exists($value, $counts)) {
	    	return $counts[$value];
	    }
	    else {
	    	return 0;
	    }
	}

	//compares two lists by looking at the first value from each
	//negative if a[0]<b[0] zero if a[0]==b[0] else positive
	function compare_by_classification($a,$b)
	{
		return $a[0] - $b[0];
	}
	/* ALTERNATE VERSION
	function compare_by_classification($a, $b)
	{
		if ($a[0]<$b[0]) 
		{
			echo -1;
		}
		elseif ($a[0]==$b[0]) 
		{
			echo 0;
		}
		else
		{
			echo 1;
		}
	}
	*/

	//return the number of species in classifications for a given subject
	//input a list of classifications, wherein each classification is a list of species (with associated data)
	//output a list with the number of species per classificaition
	function get_species_counts($classifications)
	{
		if (count($classifications) == 0)
		{
			return array();
		}
		$spp = array();
		for($x = 0; $x < count($classifications); $x++)
		{
			$key = "species";
			$entry = $classifications[$x];
			if ($entry[$key] != "")
			{
				$spp[] = count($entry);
			}
			else
			{
				$spp[] = 0;
			}
		}
		return $spp;
	}

	//choose the winners from the vote as the top vote-getters
	//input the number of winners
	//input a dictionary of votes
	//output a list of the winning species
	function choose_winners($numwin, $sppvotes)
	{
		if (count($sppvotes) <= 0) {
			return array();
		}

		if ($numwin <= 0) {
			return array();
		}

		# sort by votes
		arsort($sppvotes);

		$winners = array_slice($sppvotes, 0, $numwin);

		# Don't think we need this
		/*
		# check for ties
		if (count($sppvotes)>$numwin)
		{
			$keys = array_keys($sppvotes);
			if ($sppvotes[$keys[($numwin-1)]] == $sppvotes[$keys[$numwin]])
			{
				$votes = $sppvotes[$keys[($numwin-1)]];
				$ties = array(array());
				# get all the tied species
	            foreach ($sppvotes as $spp) 
	            {
	             	if ($spp[1] == $votes)
	             	{
	                    array_push($ties, $spp);
	             	}
	            }
	            # choose one at random
	            $tiewinner = array_rand($ties, 1);
	            $winners[$numwin-1] = $tiewinner;
	                
			}
		}
		*/

		return $winners;
	}

	//calculate the number of individuals within a species based on bins (1,2,3,4,5,6,7,8,9,10,11-50,51+)
	//input a list of number of individuals given for a species
	//output a list giving the minimum, median and maximum bin
	function calculate_num_animals($noa)
	{
		if(count($noa) == 0)
		{
			return array();
		}
		$nums = array();
		$tens = array();
		$meds = array();
		$many = array();
		for($x=0; $x<count($noa); $x++)
		{
			if($noa[$x] < 10)
			{
				$nums[] = $noa[$x];
			}
			elseif($noa[$x] == 10)
			{
				$tens[] = $noa[$x];
			}
			elseif($noa[$x] < 51)
			{
				$meds[] = $noa[$x];
			}
			else
			{
				$many[] = $noa[$x];
			}
		}
		sort($nums);
		$sorted_list = array_merge($nums, $tens, $meds, $many);
		$median = ceil((count($sorted_list)+1)/2)-1;
		return array($sorted_list[0],$sorted_list[$median],end($sorted_list));
	}

	//calculate the percentage of true items given a list of true and false
	//input a list of true and false items
	//output the fraction of true items in the list expressed as a decimal
	function calculate_TF_perc($items)
	{
	    $ctr = 0;

	    foreach ($items as $ea)
	    {
	    	if ($ea == "true") 
	    	{
	    		$ctr = $ctr + 1;
	    	}
	    }

	    if (count($items) <= 0) {
	    	return 0;
	    }

	    return floatval($ctr)/count($items);
	}

	//return metadata associated with the winning species
	//input a list of species winners, each of which is a list
	//input total number of classifications
	//input total number of blanks
	//input a list of classification lines,each of which is a list
	//output a list containing statistics for each species provided
	function winner_info($sppwinners, $numclass, $numblanks, $subject)
	{
		if(count($sppwinners) == 0 || count($subject) == 0)
		{
			return array();
		}
		//CAN'T TEST THIS FUNCTION YET AS USES ANOTHER FUNCTION THAT HASN'T BEEN ADDED YET
		$info = array();
		for($x=0; $x<count($sppwinners); $x++)
		{
			//the fraction of people who voted for this spp
			$fracpeople = (0.0+$wppwinners[$x][1])/($numclass-$numblanks);

			$noa = array();
			$stand = array();
			$rest = array();
			$move = array();
			$eat = array();
			$interact = array();
			$baby = array();
			for($x=0; $y<count($subject); $y++)
			{
				if($subject[$y][10] == $sppwinners[$x][0])
				{
					$noa[] = $subject[$y][11];
					$stand[] = $subject[$y][12];
					$rest[] = $subject[$y][13];
					$move[] = $subject[$y][14];
					$eat[] = $subject[$y][15];
					$interact[] = $subject[$y][16];
					$baby[] = $subject[$y][17];
				}
			}

			//get the number of animals
			$numanimals = $this->calculate_num_animals($noa);

			$stand_frac = $this->calculate_TF_perc($stand);
			$rest_frac = $this->calculate_TF_perc($rest);
			$move_frac = $this->calculate_TF_perc($move);
			$eat_frac = $this->calculate_TF_perc($eat);
			$interact_frac = $this->calculate_TF_perc($interact);
			$baby_frac = $this->calculate_TF_perc($baby);

			$info[] = array_merge([$sppwinners[$x][0],$sppwinners[$x][0],$fracpeople],$numanimals,[$stand_frac,$rest_frac,$move_frac,$eat_frac,$interact_frac,$baby_frac]);
		}
		return info;
	}

	//process all the classifications for one subject and write the plurality consensus vote for that subject to the output file
	//input a list that contains classification lines from the flat file
	//each classification line is itself a list, with each item in the list a datum from the input flat file
	//no output
	function process_subject($subject, $filewriter)
	{
		# sort by classification so that multiple lines within
	    # one classification are adjacent

	    # NB This line is potentially quite buggy! :(
		usort($subject, "compare_by_classification");

		# create a 2D list: first by classification, then by species
		$scals = array(array());
		$lastclas = "";
		$subcl = array(array());

		foreach ($subject as $entry) 
		{
			if ($entry[0]==$lastclas) 
			{
				array_push($subcl, $entry);
			}
			else if (count($subcl)>0)
			{
				array_push($scals, $subcl);
				$subcl = array($entry);
				$lastclas = $entry[0];
			}
		}
		array_push($scals, $subcl);


	    # count total number of classifications done
	    $numclass = count($scals);

	    # count unique species per classification, ignoring blanks
	    $sppcount = $this->get_species_counts($scals);

	    # count and remove the blanks
	    $numblanks = $this->array_count_values_of(0, $sppcount);
		$sppcount_noblanks = array(array());
		foreach ($sppcount as $val) 
		{
			if ($val != 0) 
			{
				array_push($sppcount_noblanks, $val);
			}
		}


	    # take median (rounded up) of the number of individuals in the subject
	    sort($sppcount_noblanks);
	    $medianspp = $this->calculate_median($sppcount_noblanks);

	    # count up votes for each species
	    $sppvotes = $this->tally_spp_votes($subject);

	    # total number of (non-blank) votes
	    $totalvotes = array_sum($sppvotes);
	    # Pielou evenness index
	    $pielou = $this->calculate_pielou($sppvotes); # Potential bug... may need to # enumerate values

	    # choose winners based on most votes
	    $sppwinners = $this->choose_winners($medianspp,$sppvotes);

	    # get winner info
	    $winnerstats = $this->winner_info($sppwinners,$numclass,$numblanks,$subject);

	    # output to file

	    $multi_1 = array(array());

	    foreach (array_slice($subject, 2, 4) as $value) 
	    {

	  		array_push($multi_1, $value * $subject[0]);

		}

		$multi_2 = array(array());

	    foreach (array_slice($subject, 5, 10) as $value) 
	    {

	  		array_push($multi_2, $value * $subject[0]);

		}	

	    $add_to = array($numclass,$totalvotes,$numblanks,$pielou,$medianspp);

	   	$basic_info = $multi_1 + $multi_2 + $add_to;

	    $ctr = 1;

	    foreach ($winnerstats as $winner)
	    {
	     	$spp_info = $basic_info + array($ctr) + $winner;
	     	fputcsv($filewriter, $spp_info);
	        $ctr = $ctr + 1;
	    }

	    return;
	}

}
?>
