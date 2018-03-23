<?php
require_once "GenericDAO.class.php";

class Member extends GenericDAO{
	
	private $memid;
	private $name;
	private $email;
	private $content;
	private $commentdate;
	private $public;
	private $postid;
	private $trash;
	private $commentid;
	private $commentcontext;
	private $idConsulta;
	
	public function __construct(){	
		parent::__construct();
	
		$this->tableName = "members";
		
		/*
			key = coluna do banco => value = property da classe
		*/
		$this->columns = array(
			"memid" => "memid",
			"name" => "name",
			"email" => "email",
			"content" => "content",
			"commentdate" => "commentdate",
			"public" => "public",
			"postid" => "postid",
			"trash" => "trash",
			"commentid" => "commentid",
			"commentcontext" => "commentcontext",
			"id_consulta" => "idConsulta"
		);
	}
	
	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
	
	public function listar($filtro = NULL){
		try{
			return $this->select($filtro);
		}catch(Exception $ex){
			error_log($ex->getMessage());
			return FALSE;
		}
	}
	
	public function listarPorConsulta($idConsulta, $filtro = NULL){
		
		if($filtro != NULL && is_array($filtro)){
			$filtro["idConsulta"] = "=".$idConsulta;
		}else{
			$filtro = array("idConsulta" => "= $idConsulta");
		}
		return $this->listar($filtro);
	}
	
	public function obter($id){
		$filtroId = array("memid" => "= $id");
		return $this->listar($filtroId);
	}
	
	public function obterPorConsulta($idConsulta, $idMember){
		$filtro = array("memid" => "= $idMember");
		return $this->listarPorConsulta($idConsulta, $filtro);
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
		$colunas = array("trash" => "=1");
		$filtros = array("memid" => $id);
		return $this->atualizar($colunas, $filtros);
	}
	
}