<?php
require_once "GenericDAO.php";

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
			key = nome da coluna no banco => value = propriedade da classe
			A primeira deve ser a Primary Key da tabela
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

	public function buscar($input, $page=NULL){
		$filtro = array();
		foreach($input as $key => $val){
			if(array_search($key, $this->columns) === FALSE){
				throw new Exception("$key parametro incorreto", 400);
			}
			$filtro[$key] = $val;
		}
		if(array_count_values($filtro) == 0){
			throw new Exception("Parametros de busca incorretos", 400);
		}

		return ($page != NULL) ? $this->listarAtivos($page, $filtro) : $this->listar($filtro);
	}
	
	public function listar($filtro = NULL){
		try{
			return $this->select($filtro);
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}

	public function listarAtivos($pagina = NULL, $filtro = NULL){
		if($filtro == NULL){
			$filtro = array();
		}
		$filtro['trash'] = "=0";
		$result = $this->select($filtro);
		if($pagina != NULL){
			$limite = 10;
			$primeiro = ($pagina * $limite) - $limite;
			$result = array_slice($result, $primeiro, $limite);
		}
		return $result;
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
		$result = $this->listar($filtroId);
		reset($result);
		return current($result);
	}
	
	public function obterPorConsulta($idConsulta, $idMember){
		$filtro = array("memid" => "= $idMember");
		return $this->listarPorConsulta($idConsulta, $filtro);
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

			if($this->isComentarioRepetido($this->content, $this->idConsulta)){
				throw new Exception("Texto repetido nao autorizado.", 403);
			}
			$this->commentdate = date("Y-m-d H:i:s");
			$this->content = trim($this->content);
			return $this->insert();
		}catch(Exception $ex){
			$this->log->write($ex->getMessage());
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
			$this->log->write($ex->getMessage());
			return FALSE;
		}
	}
	
	public function desativar($id){
		$colunas = array("trash" => "=1");
		$filtros = array("memid" => "=".$id);
		return $this->atualizar($colunas, $filtros);
	}
	
	public function isComentarioRepetido($comentario, $idConsulta){
		$comentario = trim($comentario);
		$filtro = array(
			"content" => "= $comentario",
		);
		$result = $this->listarPorConsulta($idConsulta, $filtro);		
		return (count($result) > 0);
	}
	
}