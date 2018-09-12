<?php
require_once "base/Base.class.php";
require_once "exceptions/DAOException.class.php";
include_once "base/Logger.class.php";

class GenericDAO{
	/**
	 * Efetua transação com bd
	 */
	private $base;
	
	private static $properties = "C:/xampp/htdocs/bd.properties";
	private static $propertiesLinux = "/var/www/properties/bd.properties";
	
	protected $tableName;
	
	protected $columns;

	protected $log;
	
	public function __construct() {
		if(file_exists(self::$properties)){
			$this->base = new Base(self::$properties);
		}else{
			if(file_exists(self::$propertiesLinux)){
				$this->base = new Base(self::$propertiesLinux);
			}
			else{
				die("Erro na conexao. Arquivo inexistente ".self::$properties. " ou ".self::$propertiesLinux);
			}
		}
		$this->log = new Logger();
	}

	public function __get($campo) {
		return $this->$campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}
	
	/*
	* A primeira coluna da classe filha deve ser referente ao ID / Primary Key da tabela
	*/
	private function getPKColName(){
		if(!is_array($this->columns) || count($this->columns) < 1){
			throw new DAOException("Verifique o mapeamento das propriedades da classe.");
		}
		reset($this->columns);
		return current($this->columns);
	}
	
	/**
	 * Método utilizado para trocar os campos do BD pelas propriedades da classe
	 * @param $columns:array de campos key=campo_do_bd value=propriedade_da_classe, 
	 * objetobd:um objeto já populado vindo por consulta do bd,
	 * objetoclass[opcional]: se for omitido retorna um novo objeto, se passado retorna um objeto da sua classe
	 *
	 */
	protected final function bind($objetobd, $objetoclass = NULL){
		if(! ($classe = get_class($objetoclass) ) )
			throw new DAOException("Falha no mapeamento do banco de dados. O parâmetro fornecido não é um objeto.");
		
		if(! (is_array($this->columns) ) )
			throw new DAOException("Falha no mapeamento do banco de dados. As colunas devem ser definidas em um array.");
		
		if($objetoclass === NULL){
			$objetoclass = new $classe();
		}
		
		foreach($this->columns as $campobd => $campoclass){
			if(property_exists($objetobd, $campobd)){
				$objetoclass->$campoclass = $objetobd->$campobd;
			}
		}
		return $objetoclass;
		
	}
	
	protected final function remove($id){
		if($id === NULL || $id <= 0){
			throw new DAOException("O objeto não tem o id da base e não poderá ser removido.");
		}
		$values = array();
		$campoId = $this->getPKColName();
		$sql = "DELETE FROM ".$this->tableName." WHERE ".$campoId." = ?";
		array_push($values, $id);
		
		$result = $this->base->deletar($sql, $values);
		if($result == 0){
			$errMsg = "$sql ";
			foreach($values as $v){
				$errMsg.=" | $v";
			}
			throw new DAOException("$errMsg O comando foi executado mas nenhum registro da base foi modificado para o ID $id.");
		}
		return $result;
	}
	
	protected final function selfUpdate($id){
		if($id === NULL || $id <= 0){
			throw new DAOException("O objeto não tem identificação na base e não poderá ser atualizado.");
		}
		
		$sqlColunas = "";
		$values = array();
		$first = true; 
		$campoId = $this->getPKColName();
		
		foreach($this->columns as $campobd => $campoClass){
			if($campoClass != $campoId){
				if(!$first){
					$sqlColunas.=",";
				}
				$sqlColunas.=$campobd."=?";
				array_push($values, $this->$campoClass);
				$first = false;
			}
		}
		$sql = "UPDATE ".$this->tableName." SET ".$sqlColunas." WHERE ".$campoId." = ?";
		array_push($values, $id);

		$result = $this->base->atualizar($sql, $values);
		if($result == 0){
			$errMsg = "$sql ";
			foreach($values as $v){
				$errMsg.=" | $v";
			}
			throw new DAOException("$errMsg O comando foi executado mas nenhum registro da base foi modificado para o ID $id.");
		}
		return $result;
	}
	
	protected final function update($columns, $conditions = NULL){
		
		$sqlColunas = "";
		if($columns == NULL){
			throw new DAOException("Valores para update não especificados.");
		}
		if(!is_array($columns)){
			throw new DAOException("Valores para update devem ser um array.");
		}
		
		$values = array();
		$first = true;
		foreach($columns as $column => $val){
			if(!$first){
				$sqlColunas.=",";
			}
			$sqlColunas.=$column."=?";
			array_push($values, $val);
			$first = false;
		}
		
		$sql = "UPDATE ".$this->tableName." SET ";
		
		if($conditions != NULL){
			if(!is_array($conditions)){
				throw new DAOException("Condicoes para update devem ser um array.");
			}
			$sqlConditions = " ";
			$validOper = array("=","<>","NOT LIKE","LIKE","IS");
			foreach($conditions as $property => $val){
				$column = array_search($property, $this->columns);
				if($column !== FALSE){
					foreach($validOper as $oper){
						$operPos = stripos($val,$oper);
						if($operPos !== FALSE){
							$param = trim(substr($val, $operPos+strlen($oper), strlen($val)));
							if($first === FALSE){
								$sqlConditions.=" AND ";
							}
							$sqlConditions.=$column." ".$oper." ?";
							$param = str_replace("'", "", $param);
							$param = str_replace("\"", "", $param);
							array_push($values, $param);
							break;
						}
					}
					$first = FALSE;
				}
			}
		}
		
		if(strlen($sqlConditions) > 1){
			$sql .= " WHERE ".$sqlConditions;
		}else{
			throw new DAOException("Filtro nao reconhecido. Operadores aceitos =,<>,NOT LIKE,LIKE,IS" );
		}
		$result = $this->base->atualizar($sql, $values);
		if($result == 0){
			$errMsg = "$sql ";
			foreach($values as $v){
				$errMsg.=" | $v";
			}
			throw new DAOException("$errMsg O comando foi executado mas nenhum registro da base foi modificado.");
		}
		return $result;
	}
	
	protected final function insert(){
		$sqlColunas = "";
		$sqlVals = "";
		$values = array();
		$first = true; 
		foreach($this->columns as $campobd => $campoClass){
			if(!$first){
				$sqlColunas.=",";
				$sqlVals.=",";
			}
			$sqlColunas.=$campobd;
			$sqlVals.="?";
			array_push($values, $this->$campoClass);
			$first = false;
		}
		$sql = "INSERT INTO ".$this->tableName."( ".$sqlColunas." ) VALUES ( ".$sqlVals." )";
		
		return $this->base->criar($sql, $values);
		
	}
	
	protected final function select($conditions = NULL, $orderColumns = NULL, $orderType = NULL, $selectColumns = NULL){
		$sqlColunas = "";
		$values = NULL;
		if($selectColumns == NULL){
			$first = true; 
			foreach($this->columns as $campobd => $campoclass){
				if(!$first){
					$sqlColunas.=",";
				}
				$sqlColunas.=$campobd;
				$first = false;
			}
		}else{
			$sqlColunas = $selectColumns;
		}
		$sql = "SELECT ".$sqlColunas." FROM ".$this->tableName;
		if($conditions != NULL){
			$first = TRUE;
			if(!is_array($conditions)){
				throw new DAOException("O parametro com condicoes deve ser um array.");
			}
			$sqlConditions = " ";
			$values = array();
			$validOper = array("LIKE",">=","<=","<>",">","<","=");
			foreach($conditions as $property => $val){
				$column = array_search($property, $this->columns);
				foreach($validOper as $oper){
					$operPos = stripos($val,$oper);					
					if($operPos !== FALSE){
						$param = trim(substr($val, $operPos+strlen($oper), strlen($val)));
						if($first === FALSE){
							$sqlConditions.=" AND ";
						}
						$sqlConditions.=$column." ".$oper." ?";
						$param = str_replace("'", "", $param);
						$param = str_replace("\"", "", $param);
						array_push($values, $param);
						break;
					}
				}
				$first = FALSE;
			}
			if(strlen($sqlConditions) > 1){
				$sql .= " WHERE ".$sqlConditions;
			}else{
				throw new DAOException("Filtro nao reconhecido. (LIKE, >=, <=, <>, >, <, =)" );
			}
			
		}
		if($orderColumns != NULL){
			$sql.= " ORDER BY ".$orderColumns;
			if($orderType != NULL){
				$sql.= " ".$orderType;
			}
		}
		$rows = array();
		$result = $this->base->consultar($sql, $values);
		foreach($result as $row){
			foreach($row as $obj){
				array_push($rows, $this->bind($obj));
			}
		}
		return $rows;
	}
	
	protected function getById($id){
		reset($this->columns);
		$campoId = current($this->columns);
		if($campoId === FALSE){
			throw new DAOException("Coluna primaria da tabela nao obtida");
		}
		$condition = array($campoId."=".$id);
		$result = $this->select($condition);
		if(!is_array($result) || count($result) == 0){
			throw new DAOException("Consulta ID $id nao encontrado");
		}
		foreach($result as $obj){
			return $obj;
		}
		return NULL;
	}
	
	protected final function parseBoolean($val){
		if($val == 0){
			return "FALSE";
		}
		return "TRUE";
	}
	
	
}

