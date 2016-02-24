<?php


require_once './algorithm.php';

class SwansonTest extends PHPUnit_Framework_TestCase
{
	
    // contains the object handle of the string class
    var $s;

    // constructor of the test suite
    function StringTest($name) {
       $this->PHPUnit_Framework_TestCase($name);
	$this->$s = new Swanson();
    }
    function tearDown() {
        // delete your instance
        unset($this->s);
    }

    public function testCompare_by_classifiaction()
    {
        $this->assertEquals($this->$s->get_species_counts(), 2);
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
