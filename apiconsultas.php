<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Content-type: application/json");

define('APP_PATH', realpath(dirname(__FILE__)));

require_once APP_PATH.'/classes/api/APIFactory.php';

$method = $_SERVER['REQUEST_METHOD'];


$info = (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != "") ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

if($info == NULL || $info == ""){
	http_response_code(404);
	$message = "Requisição inválida"; //array("message" => "Requisição inválida");
	echo json_encode($message);
} else{
	$requestPaths = explode('/', trim($info,'/'));

	 /*
	 * Remove versão de url de 0 até 255. Exemplos:
	 * /v1/consultas => /consultas
	 * v255/members => /members
	*/
	function removeVersion($pathItem) {
		preg_match('/[v][1-255]/', $pathItem, $matches); // v1, v2, ..., v255
		if ( count($matches) > 0 ) return;
		else return $pathItem;
	}

	$request = array_filter($requestPaths, 'removeVersion');
	$result = NULL;
	$result = APIFactory::executeRequest($method, $request, TRUE);
	echo $result;
}

?>