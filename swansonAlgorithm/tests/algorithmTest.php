<?php

# to run from root
# phpunit swansonAlgorithm/tests --bootstrap vendor/autoload.php

# auto load classes
require 'vendor/autoload.php';

class SwansonTest extends PHPUnit_Framework_TestCase
{
    // contains the object handle of the string class
    private static $swanson;

    private static $classified;
    private static $photoIDs;
    private static $photoData;
    private static $classifications;

    // set up the test suite
    public static function setUpBeforeClass()
    {
        echo "\n\n";
        self::$swanson = new Swanson();
        echo "class loaded\n";
        self::$classified = self::$swanson->getClassified();
        echo "get classified done\n";
        self::$photoIDs = self::$swanson->getPhotos();
        echo "get photos done\n";
        self::$photoData = self::$swanson->getAnimals(self::$classified, self::$photoIDs);
        echo "get animals done\n";
        self::$classifications = self::$swanson->getClassifications();
        echo "get classifications done\n";
        echo "\n";
    }

    public static function tearDownAfterClass()
    {
        // delete your instance
        unset($swanson);
    }

    public function getEnv()
    {
        $this->assertEquals(1, self::$swanson->getEnv());
    }

    public function setEnv()
    {
        self::$swanson->setEnv(0);
        $this->assertEquals(0, self::$swanson->getEnv());
    }

    // tests if the database connection is set up
    public function testSetupDB()
    {
        #self::$swanson->setEnv(2);
        #echo "testing env = " . self::$swanson->getEnv();
        #$this->assertEquals(false, self::$swanson->setupDB());

        #self::$swanson->setEnv(0);
        #echo "testing env = " . self::$swanson->getEnv();
        #$this->assertEquals(true, self::$swanson->setupDB());

        self::$swanson->setEnv(1);
        #echo "testing env = " . self::$swanson->getEnv();
        $this->assertEquals(true, self::$swanson->setupDB());
    }

    public function testMain()
    {
        self::$swanson->main(self::$photoData[0]);
        $this->assertEquals(true, true);

        $data = array(
          array(
                  'animal_id' => 1,
                  'photo_id' => 999999,
                  'species' => 86,
                  'gender' => 0,
                  'age' => 0,
                  'number' => 1,
                  'timestamp' => '',
          ),
          array(
                  'animal_id' => 2,
                  'photo_id' => 999999,
                  'species' => 86,
                  'gender' => 1,
                  'age' => 5,
                  'number' => 1,
                  'timestamp' => '',
          ),
          array(
                  'animal_id' => 3,
                  'photo_id' => 999999,
                  'species' => 86,
                  'gender' => 5,
                  'age' => 0,
                  'number' => 1,
                  'timestamp' => '',
          ),
          array(
                  'animal_id' => 4,
                  'photo_id' => 999999,
                  'species' => 86,
                  'gender' => 1,
                  'age' => 5,
                  'number' => 1,
                  'timestamp' => '',
          ),
          array(
                  'animal_id' => 5,
                  'photo_id' => 999999,
                  'species' => 86,
                  'gender' => 1,
                  'age' => 5,
                  'number' => 1,
                  'timestamp' => '',
          ),
        );
        self::$swanson->main($data);
        $this->assertEquals(true, true);
    }

    public function testTallyVotes()
    {
        $emptyArray = array();

        $array = array(array('species' => ''));
        $res = array('' => 1);

        $array2 = array(array('species' => 'badger'));
        $res2 = array('badger' => 1);

        $array3 = array(array('species' => 'dog'),
                        array('species' => 'dog'), );
        $res3 = array('dog' => 2);

        $array4 = array(array('species' => 'deer'),
                        array('species' => 'cat'), );
        $res4 = array('deer' => 1, 'cat' => 1);

        $array5 = array(array('species' => 'deer'),
                        array('species' => 'deer'),
                        array('species' => 'badger'), );
        $res5 = array('deer' => 2, 'badger' => 1);

        $this->assertEquals(array(), self::$swanson->tallyVotes('species', $emptyArray));
        $this->assertEquals($res, self::$swanson->tallyVotes('species', $array));
        $this->assertEquals($res2, self::$swanson->tallyVotes('species', $array2));
        $this->assertEquals($res3, self::$swanson->tallyVotes('species', $array3));
        $this->assertEquals($res4, self::$swanson->tallyVotes('species', $array4));
        $this->assertEquals($res5, self::$swanson->tallyVotes('species', $array5));
    }

    public function testHighestVote()
    {
        $emptyArray = array();

        $array = array(array('species' => ''));

        $array2 = array(array('species' => 'badger'));

        $array3 = array(array('species' => 'dog'),
                        array('species' => 'dog'), );

        $array4 = array(array('species' => 'deer'),
                        array('species' => 'cat'), );

        $array5 = array(array('species' => 'deer'),
                        array('species' => 'deer'),
                        array('species' => 'badger'), );

        $this->assertEquals(0, self::$swanson->highestVote('species', $emptyArray));
        $this->assertEquals(1, self::$swanson->highestVote('species', $array));
        $this->assertEquals(1, self::$swanson->highestVote('species', $array2));
        $this->assertEquals(2, self::$swanson->highestVote('species', $array3));
        $this->assertEquals(1, self::$swanson->highestVote('species', $array4));
        $this->assertEquals(2, self::$swanson->highestVote('species', $array5));
    }

    public function testDecideOn()
    {
        $emptyArray = array();

        $array = array(array('species' => ''));

        $array2 = array(array('species' => 'badger'));

        $array3 = array(array('species' => 'dog'),
                        array('species' => 'dog'), );

        $array4 = array(array('species' => 'deer'),
                        array('species' => 'cat'), );

        $array5 = array(array('species' => 'deer'),
                        array('species' => 'deer'),
                        array('species' => 'badger'), );

        $this->assertEquals('', self::$swanson->decideOn('species', $emptyArray));
        $this->assertEquals('', self::$swanson->decideOn('species', $array));
        $this->assertEquals('badger', self::$swanson->decideOn('species', $array2));
        $this->assertEquals('dog', self::$swanson->decideOn('species', $array3));
        $this->assertEquals('cat', self::$swanson->decideOn('species', $array4));
        $this->assertEquals('deer', self::$swanson->decideOn('species', $array5));
    }

    public function testCalculatePielou()
    {
        $emptyArray = array();
        $this->assertEquals(0, self::$swanson->calculatePielou($emptyArray));

        $array = array(0);
        $this->assertEquals(0, self::$swanson->calculatePielou($array));

        $array2 = array(2, 3, 4, 2);
        $lns = log(4);
        $plnplist = array((2 / 11) * log(2 / 11),
                          (3 / 11) * log(3 / 11),
                          (4 / 11) * log(4 / 11),
                          (2 / 11) * log(2 / 11), );
        $rrr = -array_sum($plnplist);
        $res = $rrr / $lns;
        $this->assertEquals($res, self::$swanson->calculatePielou($array2));

        $array3 = array(2, 2, 2, 2);
        /*
        $lns2 = log(4);
        $plnplist2 = array((2 / 8) * log(2 / 8),
                           (2 / 8) * log(2 / 8),
                           (2 / 8) * log(2 / 8),
                           (2 / 8) * log(2 / 8), );
        $rr2 = -array_sum($plnplist2);
        $res2 = $rr2 / $lns2;
        */
        $this->assertEquals(1, self::$swanson->calculatePielou($array3));
    }

    public function testFractionSupport()
    {
        $emptyArray = array();
        $array = array(0 => 1);
        $array2 = array(0 => 1, 1 => 1, 2 => 1);
        $array3 = array(1 => 1, 2 => 1, 3 => 2);

        $this->assertEquals(0, self::$swanson->fractionSupport($emptyArray));
        $this->assertEquals(1, self::$swanson->fractionSupport($array));
        $this->assertEquals(1 / 3, self::$swanson->fractionSupport($array2));
        $this->assertEquals(0.5, self::$swanson->fractionSupport($array3));
    }

    public function testFractionBlanks()
    {
        $emptyArray = array();
        $array = array(0 => 1);
        $array2 = array(86 => 1);
        $array3 = array(0 => 1, 1 => 1, 86 => 2);

        $this->assertEquals(0, self::$swanson->fractionBlanks($emptyArray));
        $this->assertEquals(0, self::$swanson->fractionBlanks($array));
        $this->assertEquals(1, self::$swanson->fractionBlanks($array2));
        $this->assertEquals(0.5, self::$swanson->fractionBlanks($array3));
    }

    public function testGetUserCorrectnessRate()
    {
        $swansonubject = array();
        $swansonubject2 = array();
        $swansonubject3 = array();
        $this->assertEquals(0, self::$swanson->getUserCorrectnessRate('species', $swansonubject, self::$classifications));
        $this->assertEquals(0, self::$swanson->getUserCorrectnessRate('species', $swansonubject2, self::$classifications));
        $this->assertEquals(0, self::$swanson->getUserCorrectnessRate('species', $swansonubject3, self::$classifications));
    }

    public function testGetClassified()
    {
        self::$swanson->getClassified();
        $this->assertTrue(count(self::$classified) > 0);
    }

    public function testGetClassifications()
    {
        self::$swanson->getClassifications();
        $this->assertTrue(count(self::$classifications) > 0);
    }

    public function testGetPhotos()
    {
        self::$swanson->getPhotos();
        $this->assertTrue(count(self::$photoIDs) > 0);
    }

    public function testGetPersonStats()
    {
        $personStats = self::$swanson->getPersonStats();
        $this->assertTrue(count($personStats) > 0);
    }

    public function testGetAnimals()
    {
        self::$swanson->getAnimals(self::$classified, self::$photoIDs);
        $this->assertTrue(self::$photoData !== null);
        #$this->data = $photoData[0];
        #$this->all_data = $photoData[1];
        #$this->assertTrue(count($this->data) > 0);
        #$this->assertTrue(count($this->all_data) > 0);
    }

    public function testGetGoldStandard()
    {
        $gold = self::$swanson->getGoldStandard();
        $this->assertTrue(count($gold) > 0);
    }

    public function testGoldClassifiedComparison()
    {
        self::$swanson->goldClassifiedComparison();
        $this->assertEquals(true, true);
    }

    public function testRateUsers()
    {
        self::$swanson->rateUsers(self::$photoData[1], self::$classifications);
        $this->assertEquals(true, true);
    }

    public function testCreateTables()
    {
        self::$swanson->createTables();
        $this->assertEquals(true, true);
    }

    public function testEmptyTable()
    {
        # Creating Test table
        $createTable = 'CREATE TABLE IF NOT EXISTS Test ('.
            'test_id INT NOT NULL PRIMARY KEY'.
        ');';
        if (self::$swanson->getConn()->query($createTable) === false) {
            echo 'Error creating Test table: '.self::$swanson->getConn()->error."\n";
        }

        $insert = "INSERT INTO Test VALUES ('1');";
        if (self::$swanson->getConn()->query($insert) === false) {
            echo 'Error inserting: '.self::$swanson->getConn()->error."\n";
        }

        // empty table
        self::$swanson->emptyTable('Test');

        // select everything from our new table
        $swansonelect = 'SELECT * FROM Test;';

                // execute query
                $result = self::$swanson->getConn()->query($swansonelect);
                // process result

        // test to see if table was emptied
        $this->assertEquals(false, $result->num_rows > 0);
    }
}
