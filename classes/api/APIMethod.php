<?php

require_once 'exceptions/APIException.php';

require_once APP_PATH.'/classes/model/Member.php';
require_once APP_PATH.'/classes/model/Consulta.php';
require_once APP_PATH.'/classes/model/Etapa.php';
require_once APP_PATH.'/classes/model/SubEtapa.php';
require_once APP_PATH.'/classes/model/Arquivo.php';
require_once APP_PATH.'/classes/model/Projeto.php';
require_once APP_PATH.'/classes/model/Extensao.php';
// require_once APP_PATH.'/classes/model/ProjetoConsulta.php';

abstract class APIMethod {
	public abstract static function load($request);

	protected function getTable($function){
		
		$functions = array(
			"members" => new Member(),
			"consultas" => new Consulta(),
			"etapas" => new Etapa(),
			"subetapas" => new SubEtapa(),
			"arquivos" => new Arquivo(),
			"projetos" => new Projeto(),
			"extensoes" => new Extensao(),

			// "projetoConsulta" => new ProjetoConsulta()
		);

		if(!array_key_exists($function, $functions)){
			throw new Exception("Oops! $function - Requisicao incorreta.", 400);
		}
		return $functions[$function];
	}

	protected function getConsulta($table){
		$tables = array(
			"members",
			"consultas",
			"etapas",
			"subetapas",
			"arquivos",
			"projetos",
			"extensoes"
			// "pagedmembers", 
			// "projetoConsulta"
		);
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
}

?>