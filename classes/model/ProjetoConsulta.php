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
	
		$tableName = "projetos_consultas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$columns = array(
			"id" => "id",
			"fk_projeto" => "idProjeto",
			"fk_consulta" => "idConsulta"
		);

        parent::__construct($tableName, $columns);
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

}
