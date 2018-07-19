<?php
require_once "GenericDAO.class.php";
require_once "Member.class.php";

class Consulta extends GenericDAO{
	
	private $nome;
	private $dataCadastro;
	private $ativo;
	private $nomePublico;
	private $dataFinal;
	private $nContribuicoes;
	private $textoIntro;
	private $urlConsulta;
	private $urlCapa;

	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "consultas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id_consulta" => "id",
			"nome" => "nome",
			"data_cadastro" => "dataCadastro",
			"ativo" => "ativo",
			"nome_publico" => "nomePublico",
			"data_final" => "dataFinal",
			"n_contribuicoes" => "nContribuicoes",
			"texto_intro" => "textoIntro",
			"url_consulta" => "urlConsulta",
			"url_capa" => "urlCapa"
		);
	}
	
	public function __get($campo) {
		if($campo == "ativo"){
			return $this->parseBoolean($this->$campo);
		}
		else if($campo == "nContribuicoes"){
			return $this->getNContribuicoes();
		}
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
			return $this->select($filtro);
		}catch(Exception $ex){
			error_log($ex->getMessage());
			return FALSE;
		}
	}
	
	public function obter($id){
		try{
			return $this->getById($id);
		}catch(Exception $ex){
			error_log($ex->getMessage());
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
	
	public function cadastrar(){
		try{
			return $this->insert();
		}catch(Exception $ex){
			error_log($ex->getMessage());
			return FALSE;
		}
	}

	public function atualizar($campos = NULL, $filtro = NULL){
		try{
			if($campos == NULL){
				return $this->selfUpdate($this->memid);
			}
			return $this->update($campos, $filtro);
		}catch(Exception $ex){
			error_log($ex->getMessage());
			return FALSE;
		}
	}

	public function desativar($id){
		$colunas = array("ativo" => "=0");
		$filtros = array("id" => $id);
		return $this->atualizar($colunas, $filtros);
	}

	public function getNContribuicoes(){
		try{
			$m = new Member();
			$filtro = array("public" => "=1");
			$mConsulta = $m->listarPorConsulta($this->id, $filtro);
			return count($mConsulta);
		}catch(Exception $ex){
			return -1;
		}

	}
	
}