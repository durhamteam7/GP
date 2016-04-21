<?php

ini_set('memory_limit', '4096M');

# require database connection code
require_once('../dbConnectExternal.php');

# import algorithm
require_once("algorithm.php");
$s = new Swanson();

# get the photo_ids of the already classified photos
#require_once('getPhotos.php');

# get the photo_ids of the already classified photos
require_once('getClassified.php');
# $classified - will hold the data from all classified and retired photos

# retrieve the animal data
require_once('getAnimals.php');
# $data - will hold all classifications
# Might be a problem to retrieve all rows in the db (over 120 000 entries)

$s->main($data, $mysqli);

# print out the rows of the classification table
# i.e. the data for all images that have been retired
require_once("getClassifications.php");

# loop through every users classifications and compare to the classified values
require_once("rateUsers.php");

# print out the rows of the person stats table
require_once("getPersonStats.php");

require_once("goldClassifiedComparison.php");

# close connection
$mysqli->close();

?>