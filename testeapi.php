<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";

$origin = $_SERVER['HTTP_ORIGIN'];
$allow = array("localhost", "spurbcp");
foreach($allow as $a){
	if(stripos($origin, $a) !== FALSE){
		header('Access-Control-Allow-Origin: '.$origin);
	}
}

header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

header("Content-type: application/json");
 
// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
 
// print results, insert id or affected row count
switch ($method) {
	case 'GET':
		echo json_encode(get());
	break;
	case 'PUT':
		echo json_encode(put());
	break;
	case 'POST':
		echo json_encode(post());
	break;
	case 'DELETE':
		echo json_encode(del());
	break;
}

function get(){
	$memberDAO = new Member();
	$consultaDAO = new Consulta();
	$result = NULL;
	
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	
	try{
		if($table == "members"){
			$result = ($id > 0) ? $memberDAO->obter($id) : $memberDAO->listar();
		}else{
			$consulta = $consultaDAO->obterPeloNome($table);
			if($consulta === FALSE){
				throw new Exception("$table Resource not found", 404);
			}
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}
		
		if($result == NULL){
			throw new Exception("$id Resource not found", 404);
		}
		http_response_code(200);
	}catch(Exception $ex){
		error_log($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function post(){
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$action = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$input = json_decode(file_get_contents('php://input'),true);
	
	$member = new Member();
	$result = NULL;
	try{
		if($action == "search"){
			$filtro = array();
			foreach($input as $key => $val){
				if(array_search($key, $member->columns) === FALSE){
					throw new Exception("$key column invalid", 400);
				}
				$filtro[$key] = $val;
			}
			if(array_count_values($filtro) == 0){
				throw new Exception("Invalid search conditions", 400);
			}
			$result = $member->listar($filtro);
			if(!$result){
				throw new Exception("No data found", 404);
			}
		}else if($action == ""){
			foreach($input as $key => $val){
				if(array_search($key, $member->columns) === FALSE){
					throw new Exception("Internal Server Error", 500);
				}
				$member->$key = $val;
			}
			$result = $member->cadastrar();
		}else{
			throw new Exception("Resource not found", 404);
		}
		http_response_code(200);
	}catch(Exception $ex){
		error_log($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function put(){
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	$input = json_decode(file_get_contents('php://input'),true);
	
	$member = new Member();
	$result = NULL;
	
	try{
		if($id <= 0){
			throw new Exception("$id resource not found", 404);
		}
		
		foreach($input as $key => $val){
			if(array_search($key, $member->columns) === FALSE){
				throw new Exception("Incorrect input $key", 400);
			}
			$member->$key = $val;
		}
		$member->memid = $id;
		$result = $member->selfUpdate();
		if(!$result || $result == 0){
			throw new Exception("Internal Server Error", 500);
		}
	}catch(Exception $ex){
		error_log($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function del(){
	$memberDAO = new Member();
	$result = NULL;
	
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	
	try{
		if($id <= 0){
			throw new Exception("$id invalid parameter");
		}
		
		$result = $memberDAO->desativar($id);
		
		if($result == NULL || $result === FALSE || $result == 0){
			throw new Exception("$id Internal Server Error", 500);
		}
		http_response_code(200);
	}catch(Exception $ex){
		error_log($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}


?>