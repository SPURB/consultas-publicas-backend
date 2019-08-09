<?php
require_once "GenericDAO.php";

class SubEtapa extends GenericDAO {
	private $id;
	private $nome;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "subetapas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"nome" => "nome"
		);
	}
	
	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
    
    public function setTableName(){
        return "subetapas";
    }
	/*
	public function listar($filtro = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
		try{
			$lista = $this->select($filtro);
			foreach ($lista as $subetapa) {
				$this->getLists($subetapa);
			}
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	*/
	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->getList($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
	/*
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

	private function getLists($subetapa){
		if($subetapa != NULL){
			$filtro = array("idSubEtapa" => "=".$subetapa->id);
		}
	}
    */
}