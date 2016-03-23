<?php


//https://community.dur.ac.uk/php.myadmin/password/phpMyAdmin/index.php?token=49768bff20a7a435e85700551e9ffe92&db=Cljdw32_MammalWeb
// require database connection code
require('../../dbConnect.php');
//access query parameter
$q = $_GET["q"];
if ($_POST['q']){
	$q = $_POST['q'];
}
//echo $q;
$q = '{"contains_human":false}';
$jsonQ = json_decode($q,true);
//var_dump($jsonQ);
//echo "<br><br>";
$result = getPhotoFromQuery($jsonQ);

// process result


if ($result->num_rows > 0) {
	// output data of each row
	$outputString =  "[";
	while($row = $result->fetch_assoc()) {
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
	if ($query["id"] != null){
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
	if ($query["age_id"] != null){
		$qString .= "AND Photo.age_id=".$query["age_id"];
	}
	
	//Time period
	//Date - seperate?
	
	
	//Site
	if ($query["site_id"] != null){
		$qString .= "AND Photo.site_id=".$query["site_id"];
	}
	
	//Sequence
	
	//Habitat type
	if ($query["habitat_id"] != null){
		$qString .= "AND Site.habitat_id=".$query["habitat_id"];
	}
	
	//Human presence
	if ($query["contains_human"] != null){
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
	
	//echo $sql;
	return $result;

}

//GET photos with query
?>
