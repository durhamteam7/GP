<?php

require "vendor/autoload.php";

class SwansonTest extends PHPUnit_Framework_TestCase
{

    // contains the object handle of the string class
    private $s;

    private $classified;
    private $photo_ids;

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
      $this->assertEquals(true, $this->s->setupDB());
    }

    public function testTally_votes()
    {
        $empty_array = array();

        $array = array(array("species" => ""));
        $res = array("" => 1);

        $array2 = array(array("species" => "badger"));
        $res2 = array("badger" => 1);

        $array3 = array(array("species" => "dog"),
                        array("species" => "dog"));
        $res3 = array("dog" => 2);

        $array4 = array(array("species" => "deer"),
                        array("species" => "cat"));
        $res4 = array("deer" => 1, "cat" => 1);

        $array5 = array(array("species" => "deer"),
                        array("species" => "deer"),
                        array("species" => "badger"));
        $res5 = array("deer" => 2, "badger" => 1);

        $this->assertEquals(array(), $this->s->tally_votes("species", $empty_array));
        $this->assertEquals($res, $this->s->tally_votes("species", $array));
        $this->assertEquals($res2, $this->s->tally_votes("species", $array2));
        $this->assertEquals($res3, $this->s->tally_votes("species", $array3));
        $this->assertEquals($res4, $this->s->tally_votes("species", $array4));
        $this->assertEquals($res5, $this->s->tally_votes("species", $array5));
    }

   	function testHighest_vote()
   	{
        $empty_array = array();

        $array = array(array("species" => ""));
        $res = array("" => 1);

        $array2 = array(array("species" => "badger"));
        $res2 = array("badger" => 1);

        $array3 = array(array("species" => "dog"),
                        array("species" => "dog"));
        $res3 = array("dog" => 2);

        $array4 = array(array("species" => "deer"),
                        array("species" => "cat"));
        $res4 = array("deer" => 1, "cat" => 1);

        $array5 = array(array("species" => "deer"),
                        array("species" => "deer"),
                        array("species" => "badger"));
        $res5 = array("deer" => 2, "badger" => 1);

        $this->assertEquals(0, $this->s->highest_vote("species", $empty_array));
        $this->assertEquals(1, $this->s->highest_vote("species", $array));
        $this->assertEquals(1, $this->s->highest_vote("species", $array2));
        $this->assertEquals(2, $this->s->highest_vote("species", $array3));
        $this->assertEquals(1, $this->s->highest_vote("species", $array4));
        $this->assertEquals(2, $this->s->highest_vote("species", $array5));
   	}

    function testDecide_on()
  	{
        $empty_array = array();

        $array = array(array("species" => ""));
        $res = array("" => 1);

        $array2 = array(array("species" => "badger"));
        $res2 = array("badger" => 1);

        $array3 = array(array("species" => "dog"),
                        array("species" => "dog"));
        $res3 = array("dog" => 2);

        $array4 = array(array("species" => "deer"),
                        array("species" => "cat"));
        $res4 = array("deer" => 1, "cat" => 1);

        $array5 = array(array("species" => "deer"),
                        array("species" => "deer"),
                        array("species" => "badger"));
        $res5 = array("deer" => 2, "badger" => 1);

        $this->assertEquals("", $this->s->decide_on("species", $empty_array));
        $this->assertEquals("", $this->s->decide_on("species", $array));
        $this->assertEquals("badger", $this->s->decide_on("species", $array2));
        $this->assertEquals("dog", $this->s->decide_on("species", $array3));
        $this->assertEquals("cat", $this->s->decide_on("species", $array4));
        $this->assertEquals("deer", $this->s->decide_on("species", $array5));
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

    public function testGetClassified() {
      $this->classified = $this->s->getClassified();
      $this->assertTrue(count($this->classified) > 0);
    }

    public function testGetClassifications() {
      $this->classifications = $this->s->getClassifications();
      $this->assertTrue(count($this->classifications) > 0);
    }

    public function testGetPhotos() {
      $this->photo_ids = $this->s->getPhotos();
      $this->assertTrue(count($this->photo_ids) > 0);
    }

    public function testGetPersonStats() {
      $this->person_stats = $this->s->getPersonStats();
      $this->assertTrue(count($this->person_stats) > 0);
    }

    public function testGetAnimals() {
      $d = $this->s->getAnimals($this->classified, $this->photo_ids);
      $this->assertTrue(count($d) > 0);
      $this->data = $d[0];
      $this->all_data = $d[1];
      $this->assertTrue(count($this->data) > 0);
      $this->assertTrue(count($this->all_data) > 0);
    }

    public function testGetGoldStandard() {
      $a = $this->s->getGoldStandard();
      $this->assertTrue(count($a) > 0);
    }

    public function testGoldClassifiedComparison() {
      //$this->assertEquals(2, 1);
    }
}
?>
