<?php

require_once "GenericDAO.php";

class Consulta extends GenericDAO{
	
	private $idConsulta;
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
	
		$tableName = "consultas";
		
		/*
			key = coluna do banco => value = property da classe
		*/
        
		$columns = array(
			"id_consulta" => "idConsulta",
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
        parent::__construct($tableName, $columns);

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
	
	public function getList($filtro = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
        $lista = parent::getList($filtro);
        foreach ($lista as $consulta) {
            $consulta->nContribuicoes = $this->getNContribuicoes($consulta->idConsulta);
        }
        return $lista;
	}
	
	public function get($id){
        $consulta = parent::get($id);
        if(empty($consulta)){
            throw new Exception("Consulta $id não encontrada", 400);
        }
        $consulta->nContribuicoes = $this->getNContribuicoes($consulta->idConsulta);
        return $consulta;
	}
	
	public function obterPeloNome($nome){
		$filtro = array("nome" => "= $nome");
		$result = $this->getList($filtro);
		if($result === FALSE || count($result) != 1){
			return FALSE;
		}
		return $result[0];
	}
    
    public function beforeInsert($input){
        parent::beforeInsert($input);
        $this->dataCadastro = date("Y-m-d H:i:s");
        $this->ativo = "1";
    }

	public function remove($id){
		$colunas = array("ativo" => "=0");
		$filtros = array("id_consulta" => $id);
		return $this->update($colunas, $filtros);
	}

	public function getNContribuicoes($idConsulta = NULL){
		if($idConsulta == NULL){
			$idConsulta = $this->idConsulta;
		}
        require_once "Member.php";
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
