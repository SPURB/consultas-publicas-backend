<?php
require_once "GenericDAO.php";

class Extensao extends GenericDAO {
	private $id;
	private $nome;
	
	public function __construct(){
	
		$tableName = "extensoes";
		
		/*
			key = coluna do banco => value = property da classe
		*/
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
		$result = $this->getList($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
}
