<?php

// import algorithm
require_once '../algorithm.php';

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
        $this->assertEquals(1, 2);
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
