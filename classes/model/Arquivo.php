<?php
require_once "GenericDAO.php";
require_once "Url.php";

class Arquivo extends GenericDAO{
	
	private $nome;
	private $id;
	private $idEtapa;
	private $posicao;
	
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
			"id_subetapa" => "idSubEtapa",
			"id_projeto" => "idProjeto",
			"url" => "url",
			"id_extensao" => "idExtensao",
			"atualizacao" => "atualizacao",
			"posicao" => "posicao",
			"fonte" => "fonte"
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
		$order = array("posicao");
		try{
			$lista = $this->select($filtro, $order);
			foreach ($lista as $arquivo) {
				$this->getLists($arquivo);
				unset($arquivo->posicao);
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
			$arquivo = $this->getById($id);
			$this->getLists($arquivo);
			return $arquivo;
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
		if($arquivo != NULL){
			$DAO = new Url();
			$filtro = array("idArquivo" => "=".$arquivo->id);
			// $urls = $DAO->listar($filtro);
			// if($urls != NULL && is_array($urls)){
			// 	$arquivo->urls = array();
			// 	foreach ($urls as $url) {
			// 		array_push($arquivo->urls, $this->encodeObject($url));
			// 	}
			// }
		}
	}
	
}