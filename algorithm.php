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

// process result
/*if ($result->num_rows > 0) {
    // output data of each row
    echo '<pre>';
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo '</pre>';
} else {
    echo "0 results";
}*/
###########################################



// close connection
//$mysqli->close();

class Swanson{

	//compares two lists by looking at the first value from each
	//negative if a[0]<b[0] zero if a[0]==b[0] else positive
	function compare_by_classifiaction($a,$b)
	{
		return $a[0]-$b[0];
	}

	//return the number of species in classifications for a given subject
	//input a list of classifications, wherein each classification is a list of soecies (with associated data)
	//output a list with the number of species per classificaition
	function get_species_counts($scals)
	{
		$spp=array();
		for($x=0; $x<count($scals); $x++)
		{
			if ($scals[$x][0][10] != "")
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
	
	}

	//calculate the pielou evenness index
	//input a list giving the distribution of votes
	//output the pielou evenness indes or 0 for unanimous vote
	function calculate_pielou($nlist)
	{
		if (count($nlist)<2)
		{
			return 0;
		}
		// denominator
		$lns = log(count($nlist));
		// neumerator
		$sumList = array_sum($nlist);
		$plist = array();
		for($x=0; $x<$nlist; $x++)
		{
			$plist[] = $nlist[$x]/$sumList;
		}
		$plnplist = array();
		for($x=0; $x<$plist; $x++)
		{
			$plnplist[] = $plist[$x]*log($plist[$x]);
		}
		$sumplnp = -array_sum($plnplist);
		return $sumplnp/$lns;
	}

	//choose the winners from the vote as the top vote-getters
	//input the number of winners
	//input a dictionary of votes
	//output a list of the winning species
	function choose_winners($numwin,$sppvotes)
	{

	}

	//calculate the number of individuals within a species based on bins (1,2,3,4,5,6,7,8,9,10,11-50,51+)
	//input a list of number of individuals given for a species
	//output a list giving the minimum, median and maximum bin
	function calculate_num_animals($noa)
	{

	}

	//calculate the percentage of true items given a list of true and false
	//input a list of true and false items
	//output the fraction of true items in the list expressed as a decimal
	function calculate_TF_perc($items)
	{

	}

	//return metadata associated with the winning species
	//input a list of species winners, each of which is a list
	//input total number of classifications
	//input total number of blanks
	//input a list of classification lines,each of which is a list
	//output a list containing statistics for each species provided
	function winner_info($sppwinners,$numclass,$numblanks,$subject)
	{
	
	}

	//process all the classifications for one subject and write the plurality consensus vote for that subject to the output file
	//input a list that contains classification lines from the flat file
	//each classification line is itself a list, with each item in the list a datum from the input flat file
	//no output
	function process_subject($subject,$filewriter)
	{

	}


}
?>
