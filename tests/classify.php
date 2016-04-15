<?php

# require database connection code
require_once('../dbConnectExternal.php');

# import algorithm
require_once("algorithm.php");
$s = new Swanson();

# get the photo_ids of the already classified photos
require_once('getClassified.php');
# $classified - will hold the data from all classified and retired photos

# retrieve the animal data
require_once('getAnimals.php');
# $data - will hold all classifications
# Might be a problem to retrieve all rows in the db (over 120 000 entries)

$s->main($data, $mysqli);

# A better update query would be something like this
# because we would update all rows simultaneously instead
# of one row at a time with a separate query.
/*
INSERT INTO mytable (id, a, b, c)
VALUES (1, 'a1', 'b1', 'c1'),
(2, 'a2', 'b2', 'c2'),
(3, 'a3', 'b3', 'c3'),
(4, 'a4', 'b4', 'c4'),
(5, 'a5', 'b5', 'c5'),
(6, 'a6', 'b6', 'c6')
ON DUPLICATE KEY UPDATE id=VALUES(id),
a=VALUES(a),
b=VALUES(b),
c=VALUES(c);
*/

# print out the rows of the classification table
# i.e. the data for all images that have been retired
require_once("getClassifications.php");

# loop through every users classifications and compare to the classified values
require_once("flagUsers.php");

# close connection
$mysqli->close();

?>