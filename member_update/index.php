<?php
// $properties = "C:/xampp/htdocs/bd.properties";
$properties = "/var/www/properties/bd.properties";

// define $dbtype; $host; $port; $user; $password; $dbname 
$propFile = fopen($properties, "r");
while (!feof($propFile)) {
	$line = fgets($propFile);
	$split = explode(":", $line);
	switch ($split[0]) {
		case 'dbtype': $dbtype = removeSpaces($split[1]); break;
		case 'host': $host = removeSpaces($split[1]); break;
		case 'port': $port = removeSpaces($split[1]); break;
		case 'user': $user = removeSpaces($split[1]); break;
		case 'password': $password = removeSpaces($split[1]); break;
		case 'dbname': $dbname = removeSpaces($split[1]); break; // prod 
		// case 'dbname': $dbname = $split[1]; break; // local
	}
}

// corrige bug pois split[1] retorna com dois espaços no final 
function removeSpaces($str){
	return $str = substr($str, 0, -2);
}

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error){
	die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['memid'])){
	$memid = $_POST['memid'];
	if (isset($_POST)) {
		$public = $_POST['public'];

		$sql = "UPDATE members SET public='$public' WHERE memid='$memid'";
		$query = $conn->query($sql);

		if($query){
			$out['message'] = "Status alterado com sucesso";
		}
		else{
			$out['error'] = true;
			$out['message'] = "Não foi possível alterar status de publicação";
		}
	}
}

if (isset($_POST['count'])){
}


$out = array(
	'error' => false,
	'message' => 'em desenvolvimento'
);

$conn->close();
header("Content-type: application/json");
echo json_encode($out);
die();
?>