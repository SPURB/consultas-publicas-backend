<?php
require_once "GenericDAO.class.php";

class Url extends GenericDAO{
	
	private $id;
	private $url;
	private $extensao;
	private $idArquivo;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "urls";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"url" => "url",
			"extensao" => "extensao",
			"id_arquivo" => "idArquivo"
		);
	}
	
	public function __get($campo) {
		return $this->$campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
	
	public function listar($filtro = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
		try{
			$lista = $this->select($filtro);
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function listarPorProjeto($idProjeto, $filtro=NULL){
		if($filtro != NULL && is_array($filtro)){
			$filtro["idProjeto"] = "=".$idProjeto;
		}else{
			$filtro = array("idProjeto" => "= $idProjeto");
		}
		return $this->listar($filtro);
	}
	
	public function obter($id){
		try{
			$consulta = $this->getById($id);
			return $consulta;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function cadastrar($input = NULL){
		try{
			if($input != NULL){
				foreach($input as $key => $val){
					if(array_search($key, $this->columns) === FALSE){
						throw new Exception("$key parametro incorreto", 400);
					}
					$this->$key = $val;
				}
			}
			return $this->insert();
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function atualizar($campos = NULL, $filtro = NULL){
		try{
			if($campos == NULL){
				return $this->selfUpdate($this->id);
			}
			return $this->update($campos, $filtro);
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
}