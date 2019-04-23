<?php

require_once 'exceptions/APIException.php';

require_once APP_PATH.'/classes/model/Member.php';
require_once APP_PATH.'/classes/model/Consulta.php';
require_once APP_PATH.'/classes/model/Arquivo.php';
require_once APP_PATH.'/classes/model/Etapa.php';
require_once APP_PATH.'/classes/model/Projeto.php';
require_once APP_PATH.'/classes/model/Url.php';
require_once APP_PATH.'/classes/model/ProjetoConsulta.php';
require_once APP_PATH.'/classes/model/Consulta.php';

abstract class APIMethod{
	public abstract static function load($request);

	protected static $ext_versao = "_v1";

	protected static function removerVersao($nome){
		if(strripos($nome, APIMethod::$ext_versao) !== FALSE){
			$nome = substr($nome, 0, strlen(APIMethod::$ext_versao) * -1);
		}
		return $nome;
	}

	protected function getTable($function){
		$function = APIMethod::removerVersao($function);

		$functions = array(
			"members" => new Member(),
			"consultas" => new Consulta(),
			"arquivos" => new Arquivo(),
			"etapas" => new Etapa(),
			"projetos" => new Projeto(),
			"urls" => new Url(),
			"projetoConsulta" => new ProjetoConsulta()
		);

		if(!array_key_exists($function, $functions)){
			throw new Exception("Oops! $function - Requisicao incorreta.", 400);
		}
		return $functions[$function];
	}

	protected function getConsulta($table){
		$table = APIMethod::removerVersao($table);

		$tables = array("members", "consultas", "arquivos", "etapas", "projetos", "urls", "pagedmembers", "projetoConsulta");
		$consultaDAO = new Consulta();
		if(array_search($table, $tables) !== FALSE){
			return FALSE;
		}
		$consulta = $consultaDAO->obterPeloNome($table);
		if($consulta === FALSE){
			throw new Exception("Oops! $table recurso nao encontrado", 404);
		}
		return $consulta;
	}

	protected function allow($token){
		$key = "SPurbanismo";
		return (md5($key) == $token);
	}

	protected function getAllHeaders(){
		if(!function_exists('getallheaders')){
			$headers = [];
			foreach($_SERVER as $name => $value){
				if (substr($name, 0, 5) == 'HTTP_'){
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			return $headers;
		}
		return getallheaders();
	}

}

?>
