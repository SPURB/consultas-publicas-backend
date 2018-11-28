<?php
require_once "GenericDAO.class.php";

class Arquivo extends GenericDAO{
	
	private $id;
	private $nome;
	private $idEtapa;
	private $atualizacao;
	private $autor;
	private $descricao;
	private $posicao;
	private $urls;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "arquivos";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"id" => "id",
			"nome" => "nome",
			"id_etapa" => "idEtapa",
			"atualizacao" => "atualizacao",
			"autor" => "autor",
			"descricao" => "descricao",
			"posicao" => "posicao"
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
			foreach ($lista as $arquivo) {
				$this->getLists($arquivo);
			}
			return $lista;
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function listarPorEtapa($idEtapa, $filtro=NULL){
		if($filtro != NULL && is_array($filtro)){
			$filtro["idEtapa"] = "=".$idEtapa;
		}else{
			$filtro = array("idEtapa" => "= $idEtapa");
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

	public function getLists($arquivo){
		include_once "Url.class.php";
		if($arquivo != NULL){
			$DAO = new Url();
			$filtro = array("idArquivo" => "=".$arquivo->id);
			$urls = $DAO->listar($filtro);
			if($urls != NULL && is_array($urls)){
				$arquivo->urls = array();
				foreach ($urls as $url) {
					array_push($arquivo->urls, $this->encodeObject($url));
				}
			}
		}
	}
	
}