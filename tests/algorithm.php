<?php

  #######################################
 ##                                     ##
### implementation of swanson algorithm ###
 ##                                     ##
  #######################################


// require database connection code
//require('dbConnect.php');

###########################################
// SAMPLE QUERY
//$sql = "SELECT * FROM Animal LIMIT 25;";

// execute query
//$result = $mysqli->query($sql);

//$data = [];

// process result
/*if ($result->num_rows > 0) {
    // output data of each row
    echo '<pre>';
    while($row = $result->fetch_assoc()) {
    	$data[] = $row;
        print_r($row);
    }
    echo '</pre>';
} else {
    echo "0 results";
}*/
###########################################

// close connection
//$mysqli->close();

class Swanson {

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
	function get_species_counts($scals)
	{
		if (count($scals) == 0)
		{
			return array();
		}
		$spp = array();
		for($x = 0; $x < count($scals); $x++)
		{
			if ($scals[$x][0][10] != "") // number 10 refers to species in the array
			{
				$spp[] = count($scals[$x]);
			}
			else
			{
				$spp[] = 0;
			}
		}
		return $spp;
	}

	//returns a dictionary giving the vote tallies for a subject
	//input a list of classifications lines, each of wich is a list
	//output a dictionary with species as the key and the number of votes the species received as value
	function tally_spp_votes($subject)
	{
		$vote_table = array();

		foreach ($subject as $entry) 
		{
			$spp = $entry[10];
			
			if ($spp != "") # ignore blanks
			{
				# already in table
				if (array_key_exists($spp, $vote_table))
				{
					$vote_table[$spp] = $vote_table[$spp] + 1;
				}
				# not in table yet
				else
				{
					$vote_table[$spp] = 1;
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
			$numanimals = calculate_num_animals($noa);

			$stand_frac = calculate_TF_perc($stand);
			$rest_frac = calculate_TF_perc($rest);
			$move_frac = calculate_TF_perc($move);
			$eat_frac = calculate_TF_perc($eat);
			$interact_frac = calculate_TF_perc($interact);
			$baby_frac = calculate_TF_perc($baby);

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
			elseif (count($subcl)>0)
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
	    $sppcount = get_species_counts($scals);

	    # count and remove the blanks
	    $numblanks = array_count_values_of(0, $sppcount);
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
	    $medianspp = calculate_median($sppcount_noblanks);

	    # count up votes for each species
	    $sppvotes = tally_spp_votes($subject);

	    # total number of (non-blank) votes
	    $totalvotes = array_sum($sppvotes);
	    # Pielou evenness index
	    $pielou = calculate_pielou($sppvotes); # Potential bug... may need to # enumerate values

	    # choose winners based on most votes
	    $sppwinners = choose_winners($medianspp,$sppvotes);

	    # get winner info
	    $winnerstats = winner_info($sppwinners,$numclass,$numblanks,$subject);

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

	############################################################################

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

	# Fraction blanks is calculated as the fraction of classifiers who reported “nothing here”
	# for an image that is ultimately classified as containing an animal.
	function fraction_blanks($classifications)
	{
		if (count($classifications) <= 0) {
			return 0;
		}

		$nothing = 86; # 86 - noanimal - Nothing <span class='fa fa-ban'/>	
		$n = array_count_values_of($nothing, $classifications);
		return $n/count($classifications);
		
	}

	# Fraction support is calculated as the fraction of classifications supporting the
	# aggregated answer (i.e. fraction support of 1.0 indicates unanimous support).
	function fraction_support($classifications)
	{
		if (count($classifications) <= 0) {
			return 0;
		}

		$count_values = array_count_values($classifications);
		arsort($count_values);
		$keys = array_keys($count_values);
		$first_value = $count_values[$keys[0]];
		return $first_value/count($classifications);
	}

	###########################################################################


}
?>
