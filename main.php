<?php

require_once 'include/DB_Functions.php';
$db = new DB_Functions();

$response = array();

if (isset($_POST['Command'])) {

	$command = $_POST['Command'];
	
	if($command == "getUsers")
	{
		$response = $db->getUsers();
		echo json_encode($response);
	}
}
?>
