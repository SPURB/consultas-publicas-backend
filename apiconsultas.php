<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";
include_once "classes/base/Logger.class.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Content-type: application/json");

$allowed = FALSE;
if(isset($_SERVER['REMOTE_ADDR'])){
	$allow = array("10.");
	foreach($allow as $a){
		if(stripos($_SERVER['REMOTE_ADDR'], $a) !== FALSE){
			$allowed = TRUE;
			break;
		}
	}
}
if($allowed === FALSE){
	http_response_code(403);
	echo json_encode("Nao autorizado ".$_SERVER['REMOTE_ADDR']);
}
else{
	$method = $_SERVER['REQUEST_METHOD'];
	if(!isset($_SERVER['REQUEST_URI'])){
		http_response_code(404);
		echo json_encode("Requisicao invalida");
	}else{
		$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
		$result = NULL;
		switch ($method) {
			case 'GET':
				$result = encodeObject(get($request));
			break;
			case 'PUT':
				$result = put($request);
			break;
			case 'POST':
				$result = post($request);
			break;
			case 'DELETE':
				$result = del($request);
			break;
		}
		$result = json_encode($result);
		if(json_last_error() != JSON_ERROR_NONE){
			$result = json_last_error_msg();
			logErro($result);
			http_response_code(500);
		}
		echo $result;
	}
}



function get($request){
	$memberDAO = new Member();
	$consultaDAO = new Consulta();
	$result = NULL;
	//array_shift extrai o primeiro elemento do array
	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));
	
	try{
		$consulta = getConsulta($table);
		if($consulta === FALSE){
			if($table == "members"){
				$result = ($id > 0) ? $memberDAO->obter($id) : $memberDAO->listarAtivos();
			}else if($table == "consultas"){
				$result = ($id > 0) ? $consultaDAO->obter($id) : $consultaDAO->listar();
			}else if($table == "pagedmembers"){
				$result = ($id > 0) ? $memberDAO->listarAtivos($id) : $memberDAO->listarAtivos(1);
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
	
	$member = new Member();
	$result = NULL;
	$actions = array("search", "pagedsearch");
	try{
		if(array_search($action, $actions) !== FALSE){
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
			if($action == "pagedsearch"){
				$page = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
				$page = (intval($page) > 0) ? intval($page) : 1;
				$result = $member->listarAtivos($page, $filtro);
			}else{
				$result = $member->listar($filtro);
			}
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
			$novaConsulta->dataCadastro = date("Y-m-d H:i:s");
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
			if($member->isComentarioRepetido($member->content, $member->idConsulta )){
				throw new Exception("Texto repetido nao autorizado.", 403);
			}
			$member->commentdate = date("Y-m-d");
			$member->content = trim($member->content);
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
	$tables = array("members", "consultas", "pagedmembers");
	$consultaDAO = new Consulta();
	if(array_search($table, $tables) !== FALSE){
		return FALSE;
	}
	$consulta = $consultaDAO->obterPeloNome($table);
	if($consulta === FALSE){
		throw new Exception("$table recurso nao encontrado", 404);
	}
	return $consulta;
}

function logErro($msg){
	$log = new Logger();
	$log->write($msg);
}

function encodeObject($obj){
	if(is_array($obj)){
		foreach($obj as $item){
			$item = encodeObject($item);
		}
	}else if(is_object($obj)){
		foreach($obj as $key => $val){
			if($val != NULL){
				$obj->$key = utf8_encode($val);
			}
		}
	}
	return $obj;
}


?>