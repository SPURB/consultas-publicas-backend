<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";

if(isset($_SERVER['HTTP_ORIGIN'])){
	$origin = $_SERVER['HTTP_ORIGIN'];
	$allow = array("localhost", "spurbcp", "10.");
	foreach($allow as $a){
		if(stripos($origin, $a) !== FALSE){
			header('Access-Control-Allow-Origin: '.$origin);
			break;
		}
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
		$consulta = getConsulta($table);
		if($consulta === FALSE){
			$result = ($id > 0) ? $memberDAO->obter($id) : $memberDAO->listar();
		}else{
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}
		if($result == NULL){
			throw new Exception("$id nao encontrado", 404);
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
					throw new Exception("$key parametro incorreto", 400);
				}
				$filtro[$key] = $val;
			}
			if(array_count_values($filtro) == 0){
				throw new Exception("Parametros de busca incorretos", 400);
			}
			$result = $member->listar($filtro);
			if(!$result){
				throw new Exception("Nenhum resultado encontrado", 404);
			}
		}else if($table == "consultas"){
			$novaConsulta = new Consulta();
			foreach($input as $key => $val){
				if(array_search($key, $novaConsulta->columns) === FALSE){
					throw new Exception("$key parametro incorreto", 400);
				}
				$novaConsulta->$key = $val;
			}
			$novaConsulta->dataCadastro = date();
			$novaConsulta->ativo = "1";
			$result = $novaConsulta->cadastrar();
		}
		else if($action == ""){	
			foreach($input as $key => $val){
				if(array_search($key, $member->columns) === FALSE){
					throw new Exception("$key parametro incorreto", 400);
				}
				$member->$key = $val;
			}
			$consulta = getConsulta($table);
			if($consulta !== FALSE){
				$member->idConsulta = $consulta->id;
			}
			$member->commentDate = date();
			$result = $member->cadastrar();
		}else{
			throw new Exception("Recurso nao encontrado", 404);
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
			throw new Exception("$id nao encontrado", 404);
		}
		
		$member = $memberDAO->obter($id);
		
		foreach($input as $key => $val){
			if(array_search($key, $member->columns) === FALSE){
				throw new Exception("Parametro incorreto $key", 400);
			}
			$member->$key = $val;
		}
		$member->memid = $id;
		$result = $member->atualizar();
		if(!$result || $result == 0){
			throw new Exception("Erro. Nenhuma linha atualizada", 500);
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
			throw new Exception("$id parametro invalido");
		}
		
		$result = $memberDAO->desativar($id);
		
		if($result == NULL || $result === FALSE || $result == 0){
			throw new Exception("$id gerou um erro. Nenhuma linha atualizada.", 500);
		}
		http_response_code(200);
	}catch(Exception $ex){
		error_log($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function getConsulta($table){
	if($table == "members"){
		return FALSE;
	}else{
		$consulta = $consultaDAO->obterPeloNome($table);
		if($consulta === FALSE){
			throw new Exception("$table recurso nao encontrado", 404);
		}
		return $consulta;
	}
}


?>