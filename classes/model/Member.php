<?php
require_once "GenericDAO.php";

class Member extends GenericDAO {
	
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
	private $proposta;
	private $justificativa;
	private $opiniao;

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
			"id_consulta" => "idConsulta",
			"proposta" => "proposta",
			"justificativa" => "justificativa",
			"opiniao" => "opiniao"
		);
	}
	
	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
    
    function getTableName(){
        return "members";
    }
    
    function getColumns(){
		/*
			key = coluna do banco => value = property da classe
		*/
        return array(
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
			"id_consulta" => "idConsulta",
			"proposta" => "idProposta",
			"justificativa" => "justificativa",
			"opiniao" => "idOpiniao"
		);
    }
    
    function getOneMany(){
        return array();
    }
    
    function getManyOne(){
        return array();
    }
    
    function getManyOneId(){
        return "";
    }
/*
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
	*/

	public function getList($filtro = NULL, $limite = 0, $pagina = 1){
		if($filtro == NULL){
			$filtro = array();
		}
		$filtro['trash'] = "=0";
		return parent::getList($filtro, $limite, $pagina);
	}
	
	public function listarPorConsulta($idConsulta, $filtro = NULL){
		if($filtro != NULL && is_array($filtro)){
			$filtro["idConsulta"] = "=".$idConsulta;
		}else{
			$filtro = array("idConsulta" => "= $idConsulta");
		}
		return $this->getList($filtro);
	}
	
	public function obterPorConsulta($idConsulta, $idMember){
		$filtro = array("memid" => "= $idMember");
		return $this->listarPorConsulta($idConsulta, $filtro);
	}
	/*
	protected function beforeInsert($input){
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
    */
	
	private function isComentarioRepetido($comentario, $idConsulta){
		$comentario = trim($comentario);
		$filtro = array(
			"content" => "= $comentario",
		);
		$result = $this->listarPorConsulta($idConsulta, $filtro);		
		return (count($result) > 0);
	}
    
    protected function beforeInsert($input){
        parent::beforeInsert($input);
        $consulta = $this->getConsulta($this->idConsulta);
        if($consulta !== FALSE){
            if($consulta->ativo == '0'){
                throw new Exception("Consulta encerrada. Periodo de participacao terminado.", 403);
            }
            $this->idConsulta = $consulta->idConsulta;
        }
        if($this->isComentarioRepetido($this->content, $this->idConsulta)){
            throw new Exception("Texto repetido nao autorizado.", 403);
        }
        $this->commentdate = date("Y-m-d H:i:s");
        $this->content = trim($this->content);
    }
    
    private function getConsulta(){
        require_once APP_PATH.'/classes/model/Consulta.php';
		$consultaDAO = new Consulta();
		$consulta = $consultaDAO->get($this->idConsulta);
		if($consulta === FALSE){
			throw new Exception("Erro ao obter a consulta em Member.", 404);
		}
		return $consulta;
	}
	
}