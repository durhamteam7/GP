<?php

$start = microtime(true);

# import algorithm
require_once 'Swanson.php';
$s = new Swanson();

# creates the new tables Classification and PersonStats
# only needs to run once, comment out when they've been created
$photo_ids = $s->createTables();

# get the photo_ids of all available photos
$photo_ids = $s->getPhotos();

# get the photo_ids of the already classified photos
$classified = $s->getClassified();
# $classified - will hold the data from all classified and retired photos

# retrieve the animal data
$d = $s->getAnimals($classified, $photo_ids);
$data = $d[0];
$all_data = $d[1];
# Might be a problem to retrieve all rows in the db (over 120 000 entries)

$s->main($data);

# print out the rows of the classification table
# i.e. the data for all images that have been retired
$classifications = $s->getClassifications();

# loop through every users classifications and compare to the classified values
$s->rateUsers($all_data, $classifications);

# print out the rows of the person stats table
$person_stats = $s->getPersonStats();

# compare against fold standard set
$s->goldClassifiedComparison();

$end = microtime(true);
echo "algorithm finished in " . ($end - $start) . " seconds\n";
