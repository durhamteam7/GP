<?php
//https://community.dur.ac.uk/php.myadmin/password/phpMyAdmin/index.php?token=49768bff20a7a435e85700551e9ffe92&db=Cljdw32_MammalWeb
// require database connection code
require('../../dbConnect.php');
//access query parameter

//echo $q;
$q = '{"contains_human":false}';
$jsonQ = json_decode($q,true);
$result = getPhotoFromQuery($jsonQ);

// process result


if ($result->num_rows > 0) {
	// output data of each row
	$outputString =  "[";
	while($row = $result->fetch_assoc()) {
		unset($row["exif"]);
		$outputString.= json_encode($row).",";
	}
	$outputString = rtrim($outputString, ",");
	$outputString.= "]";
	} else {
		 echo "0 results";
	}
echo $outputString;

function getPhotoFromQuery($query){

	// SAMPLE QUERY
	global $mysqli;
	
	$qString = "1 ";
	
	//ID
	if (array_key_exists("id", $query) && $query["id"] != null){
		$qString .= "AND Photo.photo_id=".$query["id"];
	}
	
	//species
	
	//evenness
	
	//Number of classifications
	
	//num animals
	
	//location
	
	//users
	
	//Gender
	
	//Age
	if (array_key_exists("age_id", $query) && $query["age_id"] != null){
		$qString .= "AND Photo.age_id=".$query["age_id"];
	}
	
	//Time period
	
	
	//Site
	if (array_key_exists("site_id", $query) && $query["site_id"] != null){
		$qString .= "AND Photo.site_id=".$query["site_id"];
	}
	
	//Sequence
	
	//Habitat type
	if (array_key_exists("habitat_id", $query) && $query["habitat_id"] != null){
		$qString .= "AND Site.habitat_id=".$query["habitat_id"];
	}
	
	//Human presence
	if (array_key_exists("contains_human", $query) && $query["contains_human"] != null){
		$qString .= "AND Photo.contains_human=".$query["contains_human"];
	}
	
	//Blank Images
	
	
	$sql = "SELECT *
	FROM Photo
	WHERE ".$qString."
	LIMIT 100;";

	// execute query
	$result = $mysqli->query($sql);
	$mysqli->close();
	return $result;

}

//GET photos with query
?>
