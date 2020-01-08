<?php
require_once "GenericDAO.php";

class Arquivo extends GenericDAO{
	
	private $nome;
	private $id;
	private $url;
	private $idEtapa;
    private $idSubEtapa;
    private $idProjeto;
    private $atualizacao;
    private $idExtensao;
	private $posicao;
    private $fonte;
	
	public function __construct(){

	
		$tableName = "arquivos";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$columns = array(
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

		parent::__construct($tableName, $columns);
	}
	
	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
	
	public function getList($filtro = NULL, $limite = 0, $pagina = 1, $orderColumns = NULL, $orderType = "ASC", $selectColumns = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
		$order = array("posicao");
		try{
			$lista = parent::getList($filtro, NULL, NULL, $order);
			foreach ($lista as $arquivo) {
				unset($arquivo->posicao);
			}
			return $lista;
		}catch(Exception $ex){
			Logger::write($ex->getMessage());
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

	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->listar($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
}
