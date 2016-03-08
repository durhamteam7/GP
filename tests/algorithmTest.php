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

        $this->assertLessThan(0, $this->s->compare_by_classification($array,$array2));
    	$this->assertGreaterThan(0, $this->s->compare_by_classification($array2,$array));
    	$this->assertEquals(0, $this->s->compare_by_classification($array2,$array3));
    }

    public function testGet_species_counts()
    {
        $array = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "" => "animal")));
        $res = array();
        $res[] = 1;
        $array2 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "" => "")));
        $res2 = array();
        $res2[] = 0;
        $array3 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "" => "animal")), array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "" => "animal")));
        $res3 = array();
        $res3[] = 2;

        $this->assertEquals($res, $this->s->get_species_counts($array));
        $this->assertEquals($res2, $this->s->get_species_counts($array2));
        $this->assertEquals($res3, $this->s->get_species_counts($array3));
    }
    public function testTally_spp_votes()
    {
        $this->assertEquals(1, 2);
    }
    public function testChoose_winners()
    {
        $this->assertEquals(1, 2);
    }
    public function testCalculate_num_animals()
    {
        $this->assertEquals(1, 2);
    }
    public function testCalculate_TF_perc()
    {
        $this->assertEquals(1, 2);
    }
    public function testWinner_info()
    {
        $this->assertEquals(1, 2);
    }
    public function testProcess_subject()
    {
        $this->assertEquals(1, 2);
    }
}
?>
