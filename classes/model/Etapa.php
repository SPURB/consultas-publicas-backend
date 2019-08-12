<?php
require_once "GenericDAO.php";

class Etapa extends GenericDAO {
	
	private $id;
	private $nome;
	private $idProjeto;
	private $filtro;
	private $filtros;

	public function __construct(){
	
		$tableName = "etapas";
		
		$columns = array(
			"id" => "id",
			"nome" => "nome"
		);
        
		parent::__construct($tableName, $columns);

	}
	
	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}

	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->listar($filtro);

		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
	
}
