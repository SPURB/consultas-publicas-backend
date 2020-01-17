<?php

require_once 'exceptions/APIException.php';
require_once APP_PATH.'/classes/model/APICallableModel.php';
require_once APP_PATH.'/classes/base/main/Logger.php';

abstract class APIMethod {
	abstract static function load($request);
    
    protected static function getFunctionClass(){
        /*
            key = nome da função na url
            val = nome da classe
        */
		$functions = array(
			"members" => "Member",
			"consultas" => "Consulta",
			"etapas" => "Etapa",
			"subetapas" => "SubEtapa",
			"arquivos" => "Arquivo",
			"projetos" => "Projeto",
			"extensoes" => "Extensao",
            "projetoConsulta" => "ProjetoConsulta"
		);
        return $functions;
    }
    
    
    /*
    * Obter instancia da classe correspondente a tabela requisitada
    */
	protected static function getTable($function){
        $functions = self::getFunctionClass();
		if(!array_key_exists($function, $functions)){
			throw new Exception("Erro! $function - Requisicao incorreta.", 400);
		}
        $className = $functions[$function];
        $classPath = APP_PATH.'/classes/model/'.$className.'.php';
        if(!file_exists($classPath)){
            throw new Exception("APIMethod Classe não encontrada! $className", 500);
        }
        require_once $classPath;
		$model = new $className();
        
        if(!$model instanceof APICallableModel){
            throw new Exception("APIMethod Classe inválida! $className", 500);
        }
        
        return $model;
	}

	protected static function getConsulta($table){
        require_once APP_PATH.'/classes/model/Consulta.php';
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
}

?>