<?php
session_start();
//print_r($_SESSION);
if(!isset($_SESSION['Auth']))
	{
		$_SESSION['Auth'] = 0;
		$_SESSION['Admin'] = 0;
		}
if(!isset($_SESSION['Admin']))
		{
		
		}
include_once("sqlconnect.php");
error_reporting(-1);

$sql = $mysqli;

if ($sql->connect_error)
	{
		die($sql->connect_errno . ' ' . $sql->connect_error);
		}

if(isset($_POST['lcommand']))
	{
	//Commands are routed via $_POST. Any application with the right format can submit a request	
	call_user_func($_POST['lcommand']);	

	
	}
?>


