<?php

require_once 'exceptions\APIException.php';

require_once APP_PATH.'\classes\model\Member.php';
require_once APP_PATH.'\classes\model\Consulta.php';
require_once APP_PATH.'\classes\model\Arquivo.php';
require_once APP_PATH.'\classes\model\Etapa.php';
require_once APP_PATH.'\classes\model\Projeto.php';
require_once APP_PATH.'\classes\model\Url.php';
require_once APP_PATH.'\classes\model\ProjetoConsulta.php';
require_once APP_PATH.'\classes\model\Consulta.php';

abstract class APIMethod{
	public abstract static function load($request);

	protected function getTable($function){
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
			throw new Exception("Requisicao incorreta.");
		}
		return $functions[$function];
	}

	protected function getConsulta($table){
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

	protected function allow($token){
		$key = "SPurbanismo";
		return (md5($key) == $token);
	}

}

?>