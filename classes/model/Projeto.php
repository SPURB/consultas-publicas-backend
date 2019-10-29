<?php
require_once "GenericDAO.php";
require_once "Etapa.php";
require_once "Consulta.php";
require_once "ProjetoConsulta.php";

class Projeto extends GenericDAO{
	
	private $id;
	private $nome;
	private $ativo;
	private $atualizacao;
	private $wordpress_user_id;
	private $consultas;
	// private $etapas;
	
	public function __construct(){	

	
		$tableName = "projetos";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$columns = array(
			"id" => "id",
			"nome" => "nome",
			"ativo" => "ativo",
			"atualizacao" => "atualizacao",
			"autor_wp_admin_id" => "wordpressUserId",
			"id_etapa" => "idEtapa",
			"piu" => "piu"
		);

        parent::__construct($tableName, $columns);

		$this->consultas = array();
		// $this->etapas = array();
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
			FILL LIST ONLY WHEN RETURNS A SINGLE OBJECT
			foreach ($lista as $projeto) {
				$this->getLists($projeto);
			}
			*/
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function getById($id){
		try{
			$projeto = $this->getById($id);
			//$this->getLists($projeto);
			return $projeto;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->getList($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
    
    public function beforeSelfUpdate($input, $id){
        parent::beforeSelfUpdate($input, $id);
        $this->atualizacao = date("Y-m-d H:i:s");
    }

	private function getLists($projeto){
		if($projeto != NULL){
			$DAO = new Etapa();
			$idProjeto = $projeto->id;
			$filtro = array("idProjeto" => "=".$idProjeto);
			// $etapas = $DAO->listar($filtro);
			// $projeto->etapas = ($etapas != NULL) ? $this->encodeObject($etapas) : array();

			$DAO = new ProjetoConsulta();
			$filtro = array("idProjeto" => "=".$idProjeto);
			$prcons = $DAO->listar($filtro);
			if($prcons != NULL && is_array($prcons)){
				$DAO = new Consulta();
				foreach ($prcons as $prcon) {
					$filtro = array("id_consulta" => "=".$prcon->idConsulta);
					$consultas = $DAO->listar($filtro);
					if($consultas != null && is_array($consultas)){
						$projeto->consultas = array();
						foreach ($consultas as $con) {
							array_push($projeto->consultas, $this->encodeObject($con));
						}
					}
				}
			}
		}
	}
	
}
