<?php
require_once "classes/Member.class.php";
require_once "classes/Consulta.class.php";
require_once "classes/Arquivo.class.php";
include_once "classes/base/Logger.class.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Content-type: application/json");

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

function getTable($function){
	$functions = array(
		"members" => new Member(),
		"consultas" => new Consulta(),
		"arquivos" => new Arquivo()
	);

	if(!array_key_exists($function, $functions)){
		throw new Exception("Requisicao incorreta.");
	}
	return $functions[$function];
}

function getConsulta($table){
	$consultaDAO = new Consulta();
	$consulta = $consultaDAO->obterPeloNome($table);
	if($consulta === FALSE){
		throw new Exception("$table consulta nao encontrada", 404);
	}
	return $consulta;
}

function get($request){
	$result = NULL;

	$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
	$id = intval(array_shift($request));

	try{
		$consulta = getConsulta($table);
		if($consulta !== FALSE){
			$memberDAO = new Member();
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}else{
			$obj = getTable($table);
			$result = ($id > 0) ? $obj->obter($id) : $obj->listarPadrao();
		}		
		
		if(!$result || $result == NULL){
			throw new Exception("Nada encontrado.", 200);
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

	$result = NULL;
	$actions = array("search", "pagedsearch");
	try{
		$member = new Member();
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
			$result = encodeObject($result);
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
		//Insert
		else if($action == ""){	
			foreach($input as $key => $val){
				if(array_search($key, $member->columns) === FALSE){
					throw new Exception("$key parametro incorreto", 400);
				}
				$member->$key = $val;
			}
			$consulta = getConsulta($table);
			if($consulta !== FALSE){
				if($consulta->ativo == '0'){
					throw new Exception("Consulta encerrada. Periodo de participacao terminado.", 403);
				}
				$member->idConsulta = $consulta->id;
			}
			if($member->isComentarioRepetido($member->content, $member->idConsulta )){
				throw new Exception("Texto repetido nao autorizado.", 403);
			}
			$member->commentdate = date("Y-m-d H:i:s");
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