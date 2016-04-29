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

    // tests if the database connection is set up
      function testSetupDB() {
      $connected = true;
  		// Check connection
  		if ($this->s->mysqli->connect_error) {
          $connected = false;
  		}
      $this->assertEquals(true, $connected);
    }


   	function testHighest_vote()
   	{
        $empty_array = array();

        $array = array(array("species" => ""));
        $res = array("" => 1);

        $array2 = array(array("species" => "animal"));
        $res2 = array("animal" => 1);

        $array3 = array(array("species" => "animal"),
                        array("species" => "animal"));
        $res3 = array("animal" => 2);

        $array4 = array(array("species" => "animal"),
                        array("species" => "species"));
        $res4 = array("animal" => 1, "species" => 1);

        $array5 = array(array("species" => "animal"),
                        array("species" => "animal"),
                        array("species" => "species"));
        $res5 = array("animal" => 2, "species" => 1);

        $this->assertEquals(0, $this->s->highest_vote("species", $empty_array));
        $this->assertEquals(1, $this->s->highest_vote("species", $array));
        $this->assertEquals(1, $this->s->highest_vote("species", $array2));
        $this->assertEquals(2, $this->s->highest_vote("species", $array3));
        $this->assertEquals(1, $this->s->highest_vote("species", $array4));
        $this->assertEquals(2, $this->s->highest_vote("species", $array5));
   	}

    public function testTally_votes()
    {
        $empty_array = array();

        $array = array(array("species" => ""));
        $res = array("" => 1);

        $array2 = array(array("species" => "animal"));
        $res2 = array("animal" => 1);

        $array3 = array(array("species" => "animal"),
                        array("species" => "animal"));
        $res3 = array("animal" => 2);

        $array4 = array(array("species" => "animal"),
                        array("species" => "species"));
        $res4 = array("animal" => 1, "species" => 1);

        $array5 = array(array("species" => "animal"),
                        array("species" => "animal"),
                        array("species" => "species"));
        $res5 = array("animal" => 2, "species" => 1);

        $this->assertEquals(array(), $this->s->tally_votes("species", $empty_array));
        $this->assertEquals($res, $this->s->tally_votes("species", $array));
        $this->assertEquals($res2, $this->s->tally_votes("species", $array2));
        $this->assertEquals($res3, $this->s->tally_votes("species", $array3));
        $this->assertEquals($res4, $this->s->tally_votes("species", $array4));
        $this->assertEquals($res5, $this->s->tally_votes("species", $array5));
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

    public function testFraction_blanks()
    {
        $empty_array = array();
        $array = array(0 => 1);
        $array2 = array(86 => 1);
        $array3 = array(0 => 1, 1 => 1, 86 => 2);

        $this->assertEquals(0, $this->s->fraction_blanks($empty_array));
        $this->assertEquals(0, $this->s->fraction_blanks($array));
        $this->assertEquals(1, $this->s->fraction_blanks($array2));
        $this->assertEquals(0.5, $this->s->fraction_blanks($array3));
    }
    public function testFraction_support()
    {
        $empty_array = array();
        $array = array(0 => 1);
        $array2 = array(0 => 1, 1 => 1, 2 => 1);
        $array3 = array(1 => 1, 2 => 1, 3 => 2);

        $this->assertEquals(0, $this->s->fraction_support($empty_array));
        $this->assertEquals(1, $this->s->fraction_support($array));
        $this->assertEquals(1/3, $this->s->fraction_support($array2));
        $this->assertEquals(0.5, $this->s->fraction_support($array3));
    }
}
?>
