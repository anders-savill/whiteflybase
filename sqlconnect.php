<?php
$SV = '';
$UN = '';
$PW = '';
$DB = '';
	
$mysqli = new mysqli($SV, $UN, $PW, $DB);
	if($mysqli->connect_errno)
	{
		echo "Failed to connect to SQL Database: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
?>