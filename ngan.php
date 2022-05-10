<?php
$link = mysqli_connect("vip","elo","ELO@2022","elolms");
if($link === false){
	die("ERROR: Could not connect." . mysqli_connect_error());
}
$sql = "SELECT * from mdl_context limit 20";
if ($result = mysqli_query($link, $sql)){
	if(mysqli_num_rows($result) > 0){
		echo "<table>";
			echo "<tr>";
				echo "<th>w02 context</th>";
				//echo "<th>name</th>";
				//echo "<th>intro</th>";
				//echo "<th>nosubmissions</th>";
		while($row = mysqli_fetch_array($result)){
			echo "<tr>";
				echo "<td>" . $row['path'] . "</td>";
				//echo "<td>" . $row['name'] . "</td>";
				//echo "<td>" . $row['intro'] . "</td>";
				//echo "<td>" . $row['nosubmissions'] . "</td>";
			echo "</tr>";
		}
		echo "</table>";
		mysqli_free_result($result);
	} else{
		echo "No records were found";
	}
} else{
	echo "ERROR: khono co $sql. " .mysqli_error($link);
}
mysqli_close($link);
?>
