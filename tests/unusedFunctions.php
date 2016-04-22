<?php


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

?>