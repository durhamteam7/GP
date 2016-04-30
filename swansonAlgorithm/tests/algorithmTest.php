<?php

# to run from root
# phpunit swansonAlgorithm/tests --bootstrap vendor/autoload.php

# auto load classes
require "vendor/autoload.php";

class SwansonTest extends PHPUnit_Framework_TestCase
{

    // contains the object handle of the string class
    private static $s;

    private static $classified;
    private static $photo_ids;
    private static $d;
    private static $classifications;

    // set up the test suite
    static function setUpBeforeClass() {
        echo "\n\n";
        self::$s = new Swanson();
        echo "class loaded\n";
        self::$classified = self::$s->getClassified();
        echo "get classified done\n";
        self::$photo_ids = self::$s->getPhotos();
        echo "get photos done\n";
        self::$d = self::$s->getAnimals(self::$classified, self::$photo_ids);
        echo "get animals done\n";
        self::$classifications = self::$s->getClassifications();
        echo "get classifications done\n";
        echo "\n";
    }

    static function tearDownAfterClass() {
        // delete your instance
        unset($s);
    }

    function getEnv() {
      $this->assertEquals(1, self::$s->getEnv());
    }

    function setEnv() {
      self::$s->setEnv(0);
      $this->assertEquals(0, self::$s->getEnv());
    }

    // tests if the database connection is set up
    function testSetupDB() {
        self::$s->setEnv(0);
        $this->assertEquals(true, self::$s->setupDB());
        self::$s->setEnv(1);
        $this->assertEquals(true, self::$s->setupDB());
        self::$s->setEnv(2);
        $this->assertEquals(false, self::$s->setupDB());
        self::$s->setEnv(1);
    }

    function testMain() {
        self::$s->main(self::$d[0]);
        $this->assertEquals(true, true);

        $data = array(
          array(
  				  "animal_id" => 1,
  				  "photo_id" => 999999,
  				  "species" => 86,
  				  "gender" => 0,
  				  "age" => 0,
  				  "number" => 1,
  				  "timestamp" => ""
          ),
          array(
  				  "animal_id" => 2,
  				  "photo_id" => 999999,
  				  "species" => 86,
  				  "gender" => 1,
  				  "age" => 5,
  				  "number" => 1,
  				  "timestamp" => ""
          ),
          array(
  				  "animal_id" => 3,
  				  "photo_id" => 999999,
  				  "species" => 86,
  				  "gender" => 5,
  				  "age" => 0,
  				  "number" => 1,
  				  "timestamp" => ""
          ),
          array(
  				  "animal_id" => 4,
  				  "photo_id" => 999999,
  				  "species" => 86,
  				  "gender" => 1,
  				  "age" => 5,
  				  "number" => 1,
  				  "timestamp" => ""
          ),
          array(
  				  "animal_id" => 5,
  				  "photo_id" => 999999,
  				  "species" => 86,
  				  "gender" => 1,
  				  "age" => 5,
  				  "number" => 1,
  				  "timestamp" => ""
          )
        );
        self::$s->main($data);
        $this->assertEquals(true, true);
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

        $this->assertEquals(array(), self::$s->tally_votes("species", $empty_array));
        $this->assertEquals($res, self::$s->tally_votes("species", $array));
        $this->assertEquals($res2, self::$s->tally_votes("species", $array2));
        $this->assertEquals($res3, self::$s->tally_votes("species", $array3));
        $this->assertEquals($res4, self::$s->tally_votes("species", $array4));
        $this->assertEquals($res5, self::$s->tally_votes("species", $array5));
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

        $this->assertEquals(0, self::$s->highest_vote("species", $empty_array));
        $this->assertEquals(1, self::$s->highest_vote("species", $array));
        $this->assertEquals(1, self::$s->highest_vote("species", $array2));
        $this->assertEquals(2, self::$s->highest_vote("species", $array3));
        $this->assertEquals(1, self::$s->highest_vote("species", $array4));
        $this->assertEquals(2, self::$s->highest_vote("species", $array5));
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

        $this->assertEquals("", self::$s->decide_on("species", $empty_array));
        $this->assertEquals("", self::$s->decide_on("species", $array));
        $this->assertEquals("badger", self::$s->decide_on("species", $array2));
        $this->assertEquals("dog", self::$s->decide_on("species", $array3));
        $this->assertEquals("cat", self::$s->decide_on("species", $array4));
        $this->assertEquals("deer", self::$s->decide_on("species", $array5));
  	}

    public function testCalculate_pielou()
    {
        $empty_array = array();
        $this->assertEquals(0, self::$s->calculate_pielou($empty_array));

        $array = array(0);
        $this->assertEquals(0, self::$s->calculate_pielou($array));

        $array2 = array(2, 3, 4, 2);
        $lns = log(4);
        $plnplist = array((2/11) * log(2/11),
                          (3/11) * log(3/11),
                          (4/11) * log(4/11),
                          (2/11) * log(2/11));
        $r = -array_sum($plnplist);
        $res = $r / $lns;
        $this->assertEquals($res, self::$s->calculate_pielou($array2));

        $array3 = array(2, 2, 2, 2);
        $lns2 = log(4);
        $plnplist2 = array((2/8) * log(2/8),
                           (2/8) * log(2/8),
                           (2/8) * log(2/8),
                           (2/8) * log(2/8));
        $r2 = -array_sum($plnplist2);
        $res2 = $r2 / $lns2;
        $this->assertEquals(1, self::$s->calculate_pielou($array3));
    }

    public function testFraction_support()
    {
        $empty_array = array();
        $array = array(0 => 1);
        $array2 = array(0 => 1, 1 => 1, 2 => 1);
        $array3 = array(1 => 1, 2 => 1, 3 => 2);

        $this->assertEquals(0, self::$s->fraction_support($empty_array));
        $this->assertEquals(1, self::$s->fraction_support($array));
        $this->assertEquals(1/3, self::$s->fraction_support($array2));
        $this->assertEquals(0.5, self::$s->fraction_support($array3));
    }

    public function testFraction_blanks()
    {
        $empty_array = array();
        $array = array(0 => 1);
        $array2 = array(86 => 1);
        $array3 = array(0 => 1, 1 => 1, 86 => 2);

        $this->assertEquals(0, self::$s->fraction_blanks($empty_array));
        $this->assertEquals(0, self::$s->fraction_blanks($array));
        $this->assertEquals(1, self::$s->fraction_blanks($array2));
        $this->assertEquals(0.5, self::$s->fraction_blanks($array3));
    }

    public function testGetUserCorrectnessRate() {
      $subject = array();
      $subject2 = array();
      $subject3 = array();
      $this->assertEquals(0, self::$s->getUserCorrectnessRate("species", $subject, self::$classifications));
      $this->assertEquals(0, self::$s->getUserCorrectnessRate("species", $subject2, self::$classifications));
      $this->assertEquals(0, self::$s->getUserCorrectnessRate("species", $subject3, self::$classifications));
    }

    public function testGetClassified() {
        self::$s->getClassified();
        $this->assertTrue(count(self::$classified) > 0);
    }

    public function testGetClassifications() {
        self::$s->getClassifications();
        $this->assertTrue(count(self::$classifications) > 0);
    }

    public function testGetPhotos() {
        self::$s->getPhotos();
        $this->assertTrue(count(self::$photo_ids) > 0);
    }

    public function testGetPersonStats() {
        $person_stats = self::$s->getPersonStats();
        $this->assertTrue(count($person_stats) > 0);
    }

    public function testGetAnimals() {
        self::$s->getAnimals(self::$classified, self::$photo_ids);
        $this->assertTrue(self::$d !== NULL);
        #$this->data = $d[0];
        #$this->all_data = $d[1];
        #$this->assertTrue(count($this->data) > 0);
        #$this->assertTrue(count($this->all_data) > 0);
    }

    public function testGetGoldStandard() {
        $a = self::$s->getGoldStandard();
        $this->assertTrue(count($a) > 0);
    }

    public function testGoldClassifiedComparison() {
        self::$s->goldClassifiedComparison();
        $this->assertEquals(true, true);
    }

    public function testRateUsers() {
        //self::$s->rateUsers(self::$d[1], self::$classifications);
        $this->assertEquals(true, true);
    }

    public function testCreateTables() {
        self::$s->createTables();
        $this->assertEquals(true, true);
    }

    public function testEmptyTable() {
        self::$s->emptyTable("asd");
        $this->assertEquals(true, true);
    }
}
?>
