<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";
include_once "classes/base/Logger.class.php";
/*
if(isset($_SERVER['HTTP_ORIGIN'])){
	$origin = $_SERVER['HTTP_ORIGIN'];
	$allow = array("localhost", "spurbcp", "gestao", "10.");
	foreach($allow as $a){
		if(stripos($origin, $a) !== FALSE){
			header('Access-Control-Allow-Origin: '.$origin);
			break;
		}
	}
}
*/

header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Content-type: application/json");

$allowed = FALSE;
if(isset($_SERVER['HTTP_HOST'])){
	$allow = array("localhost", "spurbcp", "prefeitura.sp.gov.br");
	foreach($allow as $a){
		if(stripos($_SERVER['HTTP_HOST'], $a) !== FALSE){
			$allowed = TRUE;
			break;
		}
	}
}
if($allowed === FALSE){
	http_response_code(403);
	echo json_encode("Sem permissao");
}
else{
	$method = $_SERVER['REQUEST_METHOD'];
	if(!isset($_SERVER['REQUEST_URI'])){
		http_response_code(404);
		echo json_encode("Requisicao invalida");
	}else{
		$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
		if(isset($request[0]) &&  $request[0] == "apiconsultas"){
			array_shift($request);
		}else{
			$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
		}
		switch ($method) {
			case 'GET':
				echo json_encode(get($request));
			break;
			case 'PUT':
				echo json_encode(put($request));
			break;
			case 'POST':
				echo json_encode(post($request));
			break;
			case 'DELETE':
				echo json_encode(del($request));
			break;
		}
	}
}



function get($request){
	$memberDAO = new Member();
	$consultaDAO = new Consulta();
	$result = NULL;
	//array_shift extrai o primeiro elemento do array
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	logErro("Request ".$table."/".$id);
	
	try{
		$consulta = getConsulta($table);
		if($consulta === FALSE){
			if($table == "members"){
				$result = ($id > 0) ? $memberDAO->obter($id) : $memberDAO->listarAtivos();
			}else if($table == "consultas"){
				$result = ($id > 0) ? $consultaDAO->obter($id) : $consultaDAO->listar();
			}
		}else{
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}
		if($result == NULL){
			throw new Exception("$id nao encontrado", 404);
		}
		http_response_code(200);
	}catch(Exception $ex){
		logErro($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function post($request){
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$action = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$input = json_decode(file_get_contents('php://input'),true);
	$log = new Logger();
	$log->write("Request ".$table."/".$action);
	
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
			if(!$result || $result == NULL){
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
			$novaConsulta->dataCadastro = date("Y-m-d");
			$novaConsulta->ativo = "1";
			$result = $novaConsulta->cadastrar();
			if($result == NULL || $result === FALSE){
				$msg = "";
				foreach($novaConsulta as $key => $val){
					$msg.=" ".$key." | ".$val;
				}
				throw new Exception("Erro ao gravar no banco de dados.", 500);
			}
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
			$member->commentdate = date("Y-m-d");
			$result = $member->cadastrar();
			if($result == NULL || $result === FALSE){
				$msg = "";
				foreach($member as $key => $val){
					$msg.=" ".$key." | ".$val;
				}
				throw new Exception("$msg Erro ao gravar no banco de dados.", 500);
			}
		}else{
			throw new Exception("Recurso nao encontrado", 404);
		}
		http_response_code(200);
	}catch(Exception $ex){
		logErro($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function put($request){
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	$input = json_decode(file_get_contents('php://input'),true);
	$member = new Member();
	$result = NULL;
	
	try{
		if($id <= 0){
			throw new Exception("$id nao encontrado", 404);
		}
		
		$memberOrig = $member->obter($id);
		if($memberOrig === FALSE){
			throw new Exception("$id nao encontrado", 404);
		}
		
		foreach($member->columns as $key){
			if(isset($input[$key]) && isset($memberOrig->$key) && $memberOrig->$key != $input[$key]){
				$member->$key = $input[$key];
			}else if(isset($memberOrig->$key)){
				$member->$key = $memberOrig->$key;
			}else{
				throw new Exception("Parametro incorreto $key", 400);
			}
	
		}
		
		$member->memid = $id;
		$result = $member->atualizar();
		if(!$result || $result == 0){
			$msg = "";
			foreach($member as $key => $val){
				$msg.=" ".$key." | ".$val;
			}
			throw new Exception("$msg Erro na atualização.", 500);
		}
	}catch(Exception $ex){
		logErro($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function del($request){
	$memberDAO = new Member();
	$result = NULL;
	
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
		logErro($ex->getMessage());
		http_response_code($ex->getCode());
		$result=$ex->getMessage();
	}
	return $result;
}

function getConsulta($table){
	$consultaDAO = new Consulta();
	if($table == "members" || $table == "consultas"){
		return FALSE;
	}else{
		$consulta = $consultaDAO->obterPeloNome($table);
		if($consulta === FALSE){
			throw new Exception("$table recurso nao encontrado", 404);
		}
		return $consulta;
	}
}

function logErro($msg){
	$log = new Logger();
	$log->write($msg);
}


?>