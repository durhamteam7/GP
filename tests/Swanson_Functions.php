<?php

# Returns a dictionary giving the vote tallies for a subject
# Input: a list of classifications lines, each of which is a list
# Output: a dictionary with species as the key and the number of votes
# the species received as value

function tally_spp_votes($subject)
{
	$vote_table = array(array());

	foreach ($subject as $entry) 
	{
		$spp = $entry[10];
		
		if ($spp != "") # ignore blanks
		{
			# already in table
			if (in_array($spp, $vote_table))
			{
				$vote_table[spp] = $vote_table[spp] + 1;
			}
			# not in table yet
			else
			{
				$vote_table[spp] = 1;
			}
		}
	}

	echo $vote_table;
}

############################################################################

# Calculate the percentage of true items given a list of true and false
# Input: a list of true and false items
# Output: the fraction of true items in the list expressed as a decimal

function calculate_TF_perc($items)
{
    $ctr = 0

    foreach ($items as $ea)
    {
    	if ($ea == "true") 
    	{
    		$ctr = $ctr + 1;
    	}
    }

    echo floatval($ctr)/count($items);
}

###########################################################################

# Compare two lists by comparing their first item
# Input: two lists (lines from the input file)
# Output: negative if a[0] < b[0], zero if a[0] == b[0] and
# strictly positive if a[0] > b[0].

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

############################################################################

# Counts number of ocurrences of a specified item in an array.

function array_count_values_of($value, $array) 
{
    $counts = array_count_values($array);
    echo $counts[$value];
    return $counts[$value];
}

###########################################################################
# Calculates the median of an array

function calculate_median($arr) 
{
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if($count % 2) 
    { // odd number, middle is the median
        $median = $arr[$middleval];
    } 
    else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = ceil(($low+$high)/2)); // Rounds the value up if fraction
    }
    return $median;
}

###########################################################################

# Process all the classifications for one subject and write the
# plurality consensus vote for that subject to the output file.
# Input: a list that contains classification lines from the flat file.
#        Each classification line is itself a list, with each item in
#        the list a datum from the input flat file.
# Output: none

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
    $numblanks = array_count_values_of(0, $sppcount));
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

############################################################

# Choose the winners from the vote as the top vote-getters.
# Input: number of winners
# Input: a dictionary of votes
# Output: a list of the winning species

function choose_winners($numwin, $sppvotes)
{
	# sort by votes
	$sorted_sppvotes = asort($sppvotes); // Very dissimilar to original code...

	$winners = array_slice($sorted_sppvotes, 0, $numwin);

	# check for ties
	if (count($sorted_sppvotes)>$numwin) 
	{
		if ($sorted_sppvotes[($numwin-1)]*[1] == $sorted_sppvotes[$numwin]*[1]) 
		{
			$votes = $sorted_sppvotes[$numwin-1]*[1];
			$ties = array(array());
			# get all the tied species
            foreach ($sorted_sppvotes as $spp) 
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

	echo $winners;
	return $winners;
}

############################################################


?>
