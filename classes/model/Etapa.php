<?php
require_once "GenericDAO.php";
require_once "Arquivo.php";

class Etapa extends GenericDAO{
	
	private $id;
	private $nome;
	private $idProjeto;
	private $arquivos;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "etapas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"nome" => "nome",
			"fk_projeto" => "idProjeto"
		);
	}
	
	public function __get($campo) {
		return $this -> $campo;
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
			foreach ($lista as $etapa) {
				$this->getLists($etapa);
			}
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
	
	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->listar($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
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

	private function getLists($etapa){
		if($etapa != NULL){
			$DAO = new Arquivo();
			$filtro = array("idEtapa" => "=".$etapa->id);
			$arquivos = $DAO->listar($filtro);
			if($arquivos != NULL && is_array($arquivos)){
				$etapa->arquivos = array();
				foreach ($arquivos as $arquivo) {
					array_push($etapa->arquivos, $this->encodeObject($arquivo));
				}
			}
		}
	}
	
}