<?php


//https://community.dur.ac.uk/php.myadmin/password/phpMyAdmin/index.php?token=49768bff20a7a435e85700551e9ffe92&db=Cljdw32_MammalWeb
// require database connection code
require('../../dbConnect.php');
//access query parameter
$q = json_decode(file_get_contents("php://input"),true);
$result = getPhotoFromQuery($q);

// process result

$outputString =  "[";
if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
		unset($row["exif"]);
		$outputString.= json_encode($row).",";
	}
	$outputString = rtrim($outputString, ",");
}
$outputString.= "]";
echo $outputString;

function getPhotoFromQuery($query){

	// SAMPLE QUERY
	global $mysqli;
	
	$qString = "1 ";
	$havingQString = " 1 ";
	
	//ID
	if (array_key_exists("id", $query) && $query["id"] != null){
			$qString .= " AND Photo.photo_id=".$query["id"];
	}
	
	//species
	
	//evenness
	
	//Number of classifications
	if ($query["numClassifications"]["minValue"] != $query["numClassifications"]["options"]["floor"]){
		 $havingQString .= "AND COUNT(Animal.photo_id) >= ".$query["numClassifications"]["minValue"];
	}
	if ($query["numClassifications"]["maxValue"] != $query["numClassifications"]["options"]["ceil"]){
		 $havingQString .= "AND COUNT(Animal.photo_id) <= ".$query["numClassifications"]["maxValue"];
	}
	
	//num animals
	
	//location
	
	//users
	
	//Gender
	
	//Age
	if (sizeof($query["age"]["value"]) != 0){
		//$qString .= "AND Photo.age_id=".$query["age"]["value"];
	}
	
	//Time period
	//Date - seperate?
	
	
	//Site
	if (array_key_exists("site_id", $query) && $query["site_id"] != null){
		$qString .= " AND Photo.site_id=".$query["site_id"];
	}
	
	//Sequence
	
	//Habitat type
	if (array_key_exists("habitat_id", $query) && $query["habitat_id"] != null){
		$qString .= " AND Site.habitat_id=".$query["habitat_id"];
	}
	
	//Human presence
	if (array_key_exists("contains_human", $query) && $query["contains_human"] != null){
		$qString .= " AND Photo.contains_human=".$query["contains_human"];
	}
	
	//Blank Images
	
	
	$sql = "SELECT Photo.*
	FROM Photo,Animal
	WHERE ".$qString."
	LIMIT 100;";
	
	
	$sql = "SELECT Photo.*,COUNT(Animal.photo_id) FROM (Animal
	INNER JOIN Photo
	ON Animal.photo_id=Photo.photo_id)
	WHERE ".$qString."
	GROUP BY Photo.photo_id
	HAVING ".$havingQString."
	LIMIT 100;";
	
	//echo $sql;

	// execute query
	$result = $mysqli->query($sql);
	$mysqli->close();
	return $result;

}

//GET photos with query
?>
