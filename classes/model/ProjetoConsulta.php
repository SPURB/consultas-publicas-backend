<?php
require_once "GenericDAO.php";
require_once "Consulta.php";

class ProjetoConsulta extends GenericDAO{
	
	private $id;
	private $idConsulta;
	private $idProjeto;
	private $consulta;
	private $projeto;
	
	public function __construct(){
		parent::__construct();
	
		$this->tableName = "projetos_consultas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"fk_projeto" => "idProjeto",
			"fk_consulta" => "idConsulta"
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
			/*
			foreach ($lista as $prco) {
				$this->getFKs($prco);
			}
			*/
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	private function getFKs($projetoConsulta){
		//include_once "Projeto.class.php";

/*
		$DAO = new Projeto();
		$filtro = array("id" => "=".$projetoConsulta->idProjeto);
		$this->log->write("ID PR ".$projetoConsulta->idProjeto);
		$projeto = $DAO->listar($filtro);
		$projetoConsulta->projeto = ($projeto != NULL && is_array($projeto)) ? $projeto[0] : NULL; 
*/
		$DAO = new Consulta();
		$filtro = array("id_consulta" => "=".$projetoConsulta->idConsulta);
		$consulta = $DAO->listar($filtro);
		$projetoConsulta->consulta = ($consulta != NULL && is_array($consulta)) ? $this->encodeObject($consulta[0]) : NULL;

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
/*
	public function obter($id){
		$filtroId = array("id" => "= $id");
		$result = $this->listar($filtroId);
		reset($result);
		return current($result);
	}
*/
}