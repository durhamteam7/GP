<?php


require('../../dbConnect.php');

$sql = "SELECT * FROM Options";

$result = $mysqli->query($sql);
$mysqli->close();


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




?>
