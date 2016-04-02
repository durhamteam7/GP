<?php

// import algorithm
require_once("algorithm.php");

class SwansonTest extends PHPUnit_Framework_TestCase
{
	
    // contains the object handle of the string class
    private $s;

    // constructor of the test suite
    function SwansonTest() {
        $this->s = new Swanson();
    }
    function tearDown() {
        // delete your instance
        unset($this->s);
    }

    // compare by classification test
    public function testCompare_by_classification()
    {
    	$array = array(1, 2, 3, 4);
    	$array2 = array(2, 3, 4, 5);
    	$array3 = array(2, 9, 4, 1);

        $this->assertLessThan(0, $this->s->compare_by_classification($array, $array2));
    	$this->assertGreaterThan(0, $this->s->compare_by_classification($array2, $array));
    	$this->assertEquals(0, $this->s->compare_by_classification($array2, $array3));
    }

    public function testGet_species_counts()
    {
        $empty_array = array();

        $array = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "")));
        $res = array(0);

        $array2 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
        $res2 = array(1);

        $array3 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")),
                              array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
        $res3 = array(1, 1);

        $array4 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")),
                              array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                                    array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
        $res4 = array(1, 2);


        $this->assertEquals(array(), $this->s->get_species_counts($empty_array));
        $this->assertEquals($res, $this->s->get_species_counts($array));
        $this->assertEquals($res2, $this->s->get_species_counts($array2));
        $this->assertEquals($res3, $this->s->get_species_counts($array3));
        $this->assertEquals($res4, $this->s->get_species_counts($array4));
    }
    public function testTally_spp_votes()
    {
        $empty_array = array();

        $array = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ""));
        $res = array();

        $array2 = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"));
        $res2 = array("animal" => 1);

        $array3 = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"));
        $res3 = array("animal" => 2);

        $array4 = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "species"));
        $res4 = array("animal" => 1, "species" => 1);

        $array5 = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "species"));
        $res5 = array("animal" => 2, "species" => 1);


        $this->assertEquals(array(), $this->s->tally_spp_votes($empty_array));
        $this->assertEquals($res, $this->s->tally_spp_votes($array));
        $this->assertEquals($res2, $this->s->tally_spp_votes($array2));
        $this->assertEquals($res3, $this->s->tally_spp_votes($array3));
        $this->assertEquals($res4, $this->s->tally_spp_votes($array4));
        $this->assertEquals($res5, $this->s->tally_spp_votes($array5));
    }
    public function testCalculate_pielou()
    {
        $empty_array = array();
        $this->assertEquals(0, $this->s->calculate_pielou($empty_array));

        $array = array(0);
        $this->assertEquals(0, $this->s->calculate_pielou($array));

        $array2 = array(2, 3, 4, 2);
        $lns = log(4);
        $plnplist = array((2/11) * log(2/11),
                          (3/11) * log(3/11),
                          (4/11) * log(4/11),
                          (2/11) * log(2/11));
        $r = -array_sum($plnplist);
        $res = $r / $lns;
        $this->assertEquals($res, $this->s->calculate_pielou($array2));

        $array3 = array(2, 2, 2, 2);
        $lns2 = log(4);
        $plnplist2 = array((2/8) * log(2/8),
                           (2/8) * log(2/8),
                           (2/8) * log(2/8),
                           (2/8) * log(2/8));
        $r2 = -array_sum($plnplist2);
        $res2 = $r2 / $lns2;
        $this->assertEquals(1, $this->s->calculate_pielou($array3));

    }
    public function testChoose_winners()
    {
        $this->assertEquals(1, 2);
    }
    public function testCalculate_num_animals()
    {
        $array = array();
        $array2 = array(0);
        $array3 = array(5, 2, 1, 4, 3);

        $this->assertEquals(array(), $this->s->calculate_num_animals($array));
        $this->assertEquals(array(0, 0, 0), $this->s->calculate_num_animals($array2));
        $this->assertEquals(array(1, 3, 5), $this->s->calculate_num_animals($array3));
    }
    public function testCalculate_TF_perc()
    {
        $empty_array = array();
        $array = array("true");
        $array2 = array("false");
        $array3 = array("true", "false");
        $array4 = array("true", 25);

        $this->assertEquals(0, $this->s->calculate_TF_perc($empty_array));
        $this->assertEquals(1, $this->s->calculate_TF_perc($array));
        $this->assertEquals(0, $this->s->calculate_TF_perc($array2));
        $this->assertEquals(0.5, $this->s->calculate_TF_perc($array3));
        $this->assertEquals(0.5, $this->s->calculate_TF_perc($array4));
    }
    public function testWinner_info()
    {
        $this->assertEquals(1, 2);
    }
    public function testProcess_subject()
    {
        $this->assertEquals(1, 2);
    }
    public function testArray_count_values_of()
    {
        $value = 0;

        $empty_array = array();
        $array = array(0);
        $array2 = array(1);
        $array3 = array(0, 1, "element", 0);

        $this->assertEquals(0, $this->s->array_count_values_of($value, $empty_array));
        $this->assertEquals(1, $this->s->array_count_values_of($value, $array));
        $this->assertEquals(0, $this->s->array_count_values_of($value, $array2));
        $this->assertEquals(2, $this->s->array_count_values_of($value, $array3));
    }
    public function testCalculate_median()
    {
        $empty_array = array();
        $array = array(0);
        $array2 = array(0, 1, 2);
        $array3 = array(1, 2, 4, 5);

        $this->assertEquals(0, $this->s->calculate_median($empty_array));
        $this->assertEquals(0, $this->s->calculate_median($array));
        $this->assertEquals(1, $this->s->calculate_median($array2));
        $this->assertEquals(3, $this->s->calculate_median($array3));
    }
}
?>
