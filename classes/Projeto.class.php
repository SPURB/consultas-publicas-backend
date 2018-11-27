<?php
require_once "GenericDAO.class.php";

class Projeto extends GenericDAO{
	
	private $id;
	private $nome;
	private $ativo;
	private $atualizacao;
	private $wordpress_user_id;
	private $consultas;
	private $etapas;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "projetos";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"nome" => "nome",
			"ativo" => "ativo",
			"atualizacao" => "atualizacao",
			"autor_wp_admin_id" => "wordpress_user_id"
		);

		$this->consultas = array();
		$this->etapas = array();
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
			foreach ($lista as $projeto) {
				$this->getLists($projeto);
			}
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function obter($id){
		try{
			$projeto = $this->getById($id);
			$this->getLists($projeto);
			return $projeto;
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

	private function getLists($projeto){
		include_once "Etapa.class.php";
		include_once "Consulta.class.php";
		include_once "ProjetoConsulta.class.php";
		if($projeto != NULL){
			$DAO = new Etapa();
			$idProjeto = $projeto->id;
			$filtro = array("idProjeto" => "=".$idProjeto);
			$etapas = $DAO->listar($filtro);
			$projeto->etapas = ($etapas != NULL) ? $this->encodeObject($etapas) : array();

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