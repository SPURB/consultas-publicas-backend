<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Content-type: application/json");

define('APP_PATH', realpath(dirname(__FILE__)));

require_once APP_PATH.'/classes/api/APIFactory.php';

$method = $_SERVER['REQUEST_METHOD'];
$info = 
	(isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != "") ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
if($info == NULL || $info == ""){
	http_response_code(404);
	echo json_encode("Requisicao invalida");
}else{
	$request = explode('/', trim($info,'/'));
	$result = NULL;
	$result = APIFactory::executeRequest($method, $request, TRUE);
	echo $result;
}


?>