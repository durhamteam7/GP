<?php
  class mysql_resultset
  {
    var $results, $map;

    function mysql_resultset($results)
    {
      $this->results = $results;
      $this->map = array();

      $index = 0;
      while ($column = $results->fetch_field())
      {
        $this->map[$index++] = array($column->table, $column->name);
      }
    }

    function fetch()
    {
      if ($row = $this->results->fetch_row())
      {
        $drow = array();
        foreach ($row as $index => $field)
        {
          list($table, $column) = $this->map[$index];
          if(!isset($drow[$table][$column])){
          	$drow[$table][$column] = $row[$index];
          }
          else{
          	echo "SET";
          	$drow[$table."1"][$column] = $row[$index];
          }
        }

        return $drow;
      }
      else
        return false;
    }
  }
?>



<?php

//https://community.dur.ac.uk/php.myadmin/password/phpMyAdmin/index.php?token=49768bff20a7a435e85700551e9ffe92&db=Cljdw32_MammalWeb

// require database connection code
require('../../dbConnect.php');

//access query parameter
$query = json_decode(file_get_contents("php://input"),true);


/********* FORM SQL *************/
global $mysqli;

$qString = "1 ";
$havingQString = " 1 ";

//ID
if (array_key_exists("id", $query) && $query["id"] != null){
		$qString .= " AND Photo.photo_id=".$query["id"];
}

//species
foreach($query["species"]["value"] as $species){
	$qString .= " AND Classification.species=".$species;
}

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

$sql = "SELECT *
FROM (Classification
INNER JOIN (SELECT Photo.*, COUNT(Animal.photo_id) AS numClassifications 
FROM (Photo INNER JOIN Animal ON Animal.photo_id = Photo.photo_id)	
GROUP BY Photo.photo_id
HAVING ".$havingQString.") as Photo ON Photo.photo_id = Classification.photo_id)
WHERE ".$qString."
LIMIT ".$query["pageSize"].";";

//echo $sql;

/********* EXECUTE QUERY *************/
$result = $mysqli->query($sql);

$mysqli->close();

	
/********* PROCESS RESULT *************/

//var_dump($fieldinfo);

echo "\n\n\n";

$resultset = new mysql_resultset($result);


echo "\n";

$rows = array();

if ($result->num_rows > 0) {
	// output data of each row
	while($row = $resultset->fetch()) {
		unset($row["PhotoMeta"]["exif"]);
		if (!isset($rows[$row["photo_id"]])){
			$row["Classification"] = array($row["Classification"]);
			$rows[$row["photo_id"]] = $row;
		}
		else{
			array_push($rows[$row["photo_id"]]["Classification"],$row["Classification"]);
		}
	}
}

$outputString =  "[";
foreach($rows as $row){
	$outputString.= json_encode($row).",";
}
$outputString = rtrim($outputString, ",");
$outputString.= "]";
echo $outputString;

?>
