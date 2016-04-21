<?php


// require database connection code
require_once('../dbConnectExternal.php');

// requires the gold standard data set
require_once('goldStandard.php');

// querey to get the species and photo_id for each classified image
$sql = "SELECT species, photo_id FROM Classification;";

// execute query
$result = $mysqli->query($sql);

$classifications = [];

// process result into an array with the photo_id as the key and the species as the value
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$classifications[$row[photo_id]] = $row[species];
	}
}
else
{
	echo "0 results <br>";
	echo "\n";
}

/*
echo '<pre>';
print_r($classifications);
echo '</pre>';
*/

//compare the gold standard and the classified species in each photo
$same = 0;
$different = 0;
$notClassified = 0;

for($x=0; $x<count($goldStandard); $x++)
{
	$photo_id = $goldStandard[$x][photo_id];
	if (array_key_exists($photo_id, $classifications))
	{
		if ($classifications[$photo_id] == $goldStandard[$x][species])
		{
			$same++;
		}
		else
		{
			$different++;
		}
	}
	else
	{
		$notClassified++;
	}
}

echo "same results = ".$same;
echo "<br>";
echo "\n";
echo "different results = ".$different;
echo "<br>";
echo "\n";
echo "not classified = ".$notClassified;
echo "<br>";
echo "\n";
echo "\n";
?>
