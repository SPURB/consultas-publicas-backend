<?php
require_once "GenericDAO.php";
require_once "Member.php";

class Consulta extends GenericDAO{
	
	private $id_consulta;
	private $nome;
	private $dataCadastro;
	private $ativo;
	private $nomePublico;
	private $dataFinal;
	private $nContribuicoes;
	private $textoIntro;
	private $urlConsulta;
	private $urlCapa;
	private $urlDevolutiva;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "consultas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id_consulta" => "id_consulta",
			"nome" => "nome",
			"data_cadastro" => "dataCadastro",
			"ativo" => "ativo",
			"nome_publico" => "nomePublico",
			"data_final" => "dataFinal",
			"texto_intro" => "textoIntro",
			"url_consulta" => "urlConsulta",
			"url_capa" => "urlCapa",
			"url_devolutiva" => "urlDevolutiva"
		);
	}
	
	public function __get($campo) {
		if($campo == "nContribuicoes"){
			return $this->getNContribuicoes();
		}
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}

	public function listarPadrao($conditions = NULL, $orderColumns = NULL, $orderType = NULL, $selectColumns = NULL){
		return $this->lista();
	}
	
	public function listar($filtro = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
		try{
			$lista = $this->select($filtro);
			foreach ($lista as $consulta) {
				$consulta->nContribuicoes = $this->getNContribuicoes($consulta->id_consulta);
			}
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function obter($id){
		try{
			$consulta = $this->getById($id);
			$consulta->nContribuicoes = $this->getNContribuicoes($consulta->id_consulta);
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
			$this->dataCadastro = date("Y-m-d H:i:s");
			$this->ativo = "1";

			return $this->insert();
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function atualizar($campos = NULL, $filtro = NULL){
		try{
			if($campos == NULL){
				return $this->selfUpdate($this->id_consulta);
			}
			return $this->update($campos, $filtro);
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function desativar($id){
		$colunas = array("ativo" => "=0");
		$filtros = array("id_consulta" => $id);
		return $this->atualizar($colunas, $filtros);
	}

	public function getNContribuicoes($idConsulta = NULL){
		if($idConsulta == NULL){
			$idConsulta = $this->id_consulta;
		}
		try{
			$m = new Member();
			$filtro = array("public" => "=1");
			$mConsulta = $m->listarPorConsulta($idConsulta, $filtro);
			return count($mConsulta);
		}catch(Exception $ex){
			return -1;
		}
	}
	
}