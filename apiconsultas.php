<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";
require_once "classes/Arquivo.class.php";
require_once "classes/Etapa.class.php";
require_once "classes/Projeto.class.php";
require_once "classes/Url.class.php";
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
	$info = 
		(isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != "") ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
	if($info == NULL || $info == ""){
		http_response_code(404);
		echo json_encode("Requisicao invalida");
	}else{
		$request = explode('/', trim($info,'/'));
		$result = NULL;
		switch ($method) {
			case 'GET':
				$result = get($request);
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
		$resultEnc = json_encode($result);
		if(json_last_error() != JSON_ERROR_NONE){
			logErro(json_last_error_msg());
		}else{
			$result = $resultEnc;
		}
		echo $result;
	}
}

function get($request){
	$memberDAO = new Member();
	$consultaDAO = new Consulta();
	$arquivoDAO = new Arquivo();
	$etapaDAO = new Etapa();
	$projetoDAO = new Projeto();
	$urlDAO = new Url();
	$result = NULL;
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
			}else if($table == "arquivos"){
				$result = ($id > 0) ? $arquivoDAO->obter($id) : $arquivoDAO->listar();
			}else if($table == "etapas"){
				$result = ($id > 0) ? $etapaDAO->obter($id) : $etapaDAO->listar();
			}else if($table == "projetos"){
				$result = ($id > 0) ? $projetoDAO->obter($id) : $projetoDAO->listar();
			}else if($table == "urls"){
				$result = ($id > 0) ? $urlDAO->obter($id) : $urlDAO->listar();
			}
		}else{
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}
		if($result == NULL){
			throw new Exception("$id nao encontrado", 404);
		}
		cleanEmail($result);
		$result = encodeObject($result);
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
	
	try{
		if($table == "members"){
			$member = new Member();
			$result = NULL;
			$actions = array("search", "pagedsearch");		
			if(array_search($action, $actions) !== FALSE){
				//SEARCH MEMBER
				if($action == "pagedsearch"){
					$page = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
					$page = (intval($page) > 0) ? intval($page) : 1;
				}else{
					$page = NULL;
				}
				$result = $member->buscar($input, $page);

				if(!$result || $result == NULL){
					throw new Exception("Nenhum resultado encontrado", 200);
				}
				$result = encodeObject($result);
			}else if($action == ""){
				//INSERT MEMBER
				$consulta = getConsulta($table);
				if($consulta !== FALSE){
					if($consulta->ativo == '0'){
						throw new Exception("Consulta encerrada. Periodo de participacao terminado.", 403);
					}
					$member->idConsulta = $consulta->id;
				}

				$result = $member->cadastrar($input);
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
		}else{
			//INSERT
			$obj = getTable($table);
			$result = $obj->cadastrar($input);
			if($result == NULL || $result === FALSE){
				throw new Exception("Erro ao gravar no banco de dados.", 500);
			}
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

	$obj = getTable($table);

	$result = NULL;
	try{
		if($id <= 0){
			throw new Exception("$id nao encontrado", 404);
		}
		$objOrig = $obj->obter($id);
		if($objOrig === FALSE){
			throw new Exception("$id nao encontrado", 404);
		}
		
		foreach($obj->columns as $key){
			if(isset($input[$key]) && $objOrig->$key != $input[$key]){
				$obj->$key = $input[$key];
			}else if(isset($objOrig->$key)){
				$obj->$key = $objOrig->$key;
			}else{
				$obj->$key = NULL;
			}
		}
		$result = $obj->atualizar();
		if(!$result || $result == 0){
			$msg = "";
			foreach($obj as $key => $val){
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
	$tables = array("members", "consultas", "arquivos", "etapas", "projetos", "urls", "pagedmembers");
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

function cleanEmail($obj){
	if(is_array($obj)){
		foreach($obj as $item){
			$item = cleanEmail($item);
		}
	}else if(is_object($obj) && isset($obj->email)){
		$obj->email = "";
	}
	return $obj;
}

function getTable($function){
	$functions = array(
		"members" => new Member(),
		"consultas" => new Consulta(),
		"arquivos" => new Arquivo(),
		"etapas" => new Etapa(),
		"projetos" => new Projeto(),
		"urls" => new Url()
	);

	if(!array_key_exists($function, $functions)){
		throw new Exception("Requisicao incorreta.");
	}
	return $functions[$function];
}	


?>