<?php
require_once APP_PATH.'/classes/base/main/Base.php';
require_once APP_PATH.'/classes/base/main/Logger.php';
require_once APP_PATH.'/classes/base/exceptions/DAOException.php';
require_once 'APICallableModel.php';

class GenericDAO implements APICallableModel{
	/**
	 * Efetua transação com bd
	 */
	private $base;

	private static $properties = APP_PATH.'/properties/bd.properties';

	private $tableName;
	private $columns;
    private $oneMany;
    private $manyOne;
    private $manyOneId;

	/* Necessário informar o nome da Sequence da PK para o POSTGRE */
	private $pkSequenceName;

	public function __construct($tableName, $columns, $oneMany = array(), $manyOne = array(), $manyOneId = NULL) {
        if(!file_exists(self::$properties)){
            throw new DAOException(": Erro na conexão. Arquivo inexistente ".self::$properties. " ou ".self::$properties);
        }
        if(!is_array($columns)){
            throw new Exception("Mapeamento das propriedades deve ser um array onde key=nome da coluna no banco, value=nome da propriedade da classe");
        }
        $this->base = new Base(self::$properties);
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->oneMany = $oneMany;
        $this->manyOne = $manyOne;
        $this->manyOneId = $manyOneId;
	}

	public function __get($campo) {
		return $this->$campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}

    /*
    * Obter um unico registro pelo ID
    */
	public function get($id){
        if(intval($id) <= 0){
            throw new DAOException("ID $id inválido.");
        }
		$filtroId = array($this->getPKColName() => "= $id");
		$result = $this->getList($filtroId);
		reset($result);
		return current($result);
	}

	/*
	* A primeira coluna da classe filha deve ser referente ao ID / Primary Key da tabela
    * BD == FALSE nome da propriedade da classe
    * BD == TRUE nome da coluna do BD
	*/
	private function getPKColName($bd = FALSE){
		if(!is_array($this->columns) || count($this->columns) < 1){
			throw new DAOException("Verifique o mapeamento das propriedades da classe.");
		}
		reset($this->columns);
		return ($bd === FALSE) ? current($this->columns) : key($this->columns);
	}

	/**
	 * Método utilizado para trocar os campos do BD pelas propriedades da classe
	 * @param $columns:array de campos key=campo_do_bd value=propriedade_da_classe,
	 * objetobd:um objeto já populado vindo por consulta do bd,
	 * objetoclass[opcional]: se for omitido retorna um novo objeto, se passado retorna um objeto da sua classe
	 *
	 */
	protected function bind($objetobd, $objetoclass = NULL){
        if(! ($classe = @get_class($objetoclass) ))
          if(! ($classe = get_class() ))
			throw new DAOException("Falha no mapeamento do banco de dados. O parâmetro fornecido não é um objeto.");

		if(! (is_array($this->columns) ) )
			throw new DAOException("Falha no mapeamento do banco de dados. As colunas devem ser definidas em um array.");

		if($objetoclass === NULL){
			$objetoclass = new $classe($this->tableName, $this->columns);
		}

		foreach($this->columns as $campobd => $campoclass){
			if(property_exists($objetobd, $campobd)){
				$objetoclass->$campoclass = $objetobd->$campobd;
			}
		}
		return $objetoclass;
	}
    

	public function remove($id){
		if($id === NULL || $id <= 0){
			throw new DAOException("O objeto não tem o id da base e não poderá ser removido.");
		}
		$values = array();
		$campoId = $this->getPKColName(TRUE);
		$sql = "DELETE FROM ".$this->tableName." WHERE ".$campoId." = ?";
		array_push($values, $id);

		$result = $this->base->deletar($sql, $values);
		if($result == 0){
			throw new DAOException("O comando foi executado mas nenhum registro da base foi modificado para o ID $id.", $sql, $values);
		}
		return $result;
	}
    
    protected function beforeSelfUpdate($input, $id){
        $objOrig = $this->get($id);
		if($objOrig === FALSE){
			throw new DAOException("$id nao encontrado", 404);
		}
		if($input != NULL){
			foreach($this->columns as $nomeCampo){
				$this->$nomeCampo = (isset($input[$nomeCampo]) && !is_array($input[$nomeCampo])) ? $input[$nomeCampo] : $objOrig->$nomeCampo;
			}
		}
    }

	public final function selfUpdate($input, $id = 0){
        if(intval($id) <= 0){
            $id = $input[$this->getPKColName(FALSE)];
        }
        $this->beforeSelfUpdate($input, $id);
		$sqlColunas = "";
		$values = array();
		$first = true;
		$campoId = $this->getPKColName(TRUE);
		foreach($this->columns as $campobd => $campoClass){
			if($campobd != $campoId){
				if(is_object($this->$campoClass)){
					if(isset($this->$campoClass->id)){
						$this->$campoClass = $this->$campoClass->id;
					}else{
						throw new DAOException("$campobd não tem um valor permitido. Classe:".get_class($this->$campoClass));
					}
				}
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
			throw new DAOException("O comando foi executado mas nenhum registro da base foi modificado para o ID $id.", $sql, $values);
		}
		return $result;
	}

	public final function update($columns, $conditions = NULL){

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
			$bdCol = array_search($column, $this->columns, TRUE);
			if($bdCol !== FALSE){
				$sqlColunas.=$bdCol."=?";
				array_push($values, $val);
				$first = false;
			}
		}

		if(strlen($sqlColunas) < 1){
			throw new DAOException("Pelo menos uma coluna para atualização deve ser especificada.");
		}

		$sql = "UPDATE ".$this->tableName." SET ".$sqlColunas;
		$first = TRUE;
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

		if(strlen($sqlConditions) < 1){
			throw new DAOException("Filtro nao reconhecido. Operadores aceitos =,<>,NOT LIKE,LIKE,IS" );
		}
		$sql .= " WHERE ".$sqlConditions;
		$result = $this->base->atualizar($sql, $values);
		if($result == 0){
			throw new DAOException("O comando foi executado mas nenhum registro da base foi modificado.", $sql, $values);
		}
		return $result;
	}
    
    protected function beforeInsert($input){
        if($input != NULL){
			foreach($this->columns as $nomeCampo){
				if(isset($input[$nomeCampo])){
					$this->$nomeCampo = $input[$nomeCampo];

				}
			}
		}
    }

	public final function insert($input){
        $this->beforeInsert($input);
		$sqlColunas = "";
		$sqlVals = "";
		$values = array();
		$first = true;
		$pk = $this->getPKColName();
		foreach($this->columns as $campobd => $campoClass){
			if($campoClass == $pk && $this->$campoClass == NULL){
				continue;
			}
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

		return $this->base->criar($sql, $values, $this->pkSequenceName);

	}
    
    public function getList($filtro = NULL, $limite = 0, $pagina = 1, $orderColumns = NULL, $orderType = "ASC", $selectColumns = NULL){
        $list = $this->select($filtro, $orderColumns, $orderType, $selectColumns);
        $rows = array();
		foreach($list as $row){
			foreach($row as $obj){
				$obj = $this->bind($obj);
				$obj = $this->mapOneToMany($obj);
				$obj = $this->mapManyToOne($obj);
                array_push($rows, $obj);
			}
		}
		if(intval($limite) > 0){
			$primeiro = ($pagina * $limite) - $limite;
			$result = array_slice($rows, $primeiro, $limite);
		}
        return $rows;
    }

	private function select($conditions = NULL, $orderColumns = NULL, $orderType = "ASC", $selectColumns = NULL){
		$sqlColunas = "";
		$values = NULL;
		if($selectColumns == NULL){
            $sqlColunas = $this->arrayToSelectorString($this->columns);
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
			if(strlen($sqlConditions) < 1){
                throw new DAOException("Filtro nao reconhecido. (LIKE, >=, <=, <>, >, <, =)" );
			}
            $sql .= " WHERE ".$sqlConditions;

		}
		if($orderColumns != NULL){
			$first = TRUE;
			$sqlOrder = "";
			if(!is_array($orderColumns)){
				throw new DAOException("O parametro com colunas para ordem deve ser um array.");
			}
			foreach ($orderColumns as $col) {
				if(!$first){
					$sqlOrder.=",";
				}
				$sqlOrder .= array_search($col, $this->columns);
				$first = FALSE;
			}
			$sql.= " ORDER BY ".$sqlOrder;
            $sql.= " ".$orderType;
		}
		$rows = array();
		$result = $this->base->consultar($sql, $values);
        return $result;        
	}
    
    private function mapOneToMany($obj){
        if(is_array($this->oneMany)){
            foreach ($obj as $prop => $valor) {
                if(array_key_exists($prop, $this->oneMany)){
                    try{
                        $clazzName = $this->oneMany[$prop];
                        require_once $clazzName.".php";
                        $objClazz = new $clazzName();
                        if(!$objClazz instanceof APICallableModel){
                            throw new Exception($clazzName." classe incompativel.");
                        }
                        $objRelac = $objClazz->obter($valor);
                        $obj->$prop = $objRelac;
                    }catch(Exception $ex){
                        Logger::write($ex);
                    }
                }
            }
        }
        return $obj;
    }
    
    private function mapManyToOne($obj){
        if(is_array($this->manyOne)){
            foreach($this->manyOne as $prop => $clazzName){
                try{
                    $filtro = array($this->manyOneId => "=".$obj->id);
                    require_once $clazzName.".php";
                    $objClazz = new $clazzName();
                    if(!$objClazz instanceof APICallableModel){
                        throw new Exception($clazzName." classe incompativel.");
                    }
                    $obj->$prop = $objClazz->select($filtro);
                }catch(Exception $ex){
                    Logger::write($ex);
                }
            }
        }
        return $obj;
    }

	public function encodeObject($obj){
		if(is_array($obj)){
			foreach($obj as $item){
				$item = GenericDAO::encodeObject($item);
			}
		}else if(is_object($obj)){
			foreach($obj as $key => $val){
				if($val != NULL && !is_array($val) && !is_object($val)){
					$obj->$key = utf8_encode($val);
				}
			}
		}
		return $obj;
	}

	/**
	 * Encode de arrays para utf8 recursivamente
	 * @param $dat
	 * @return array|string
	 */
	public static function encodeArray($dat)
	{
		if (is_string($dat)) {
			return utf8_encode($dat);
		} elseif (is_array($dat)) {
			$ret = array();
			foreach ($dat as $i => $d) $ret[ $i ] = self::encodeArray($d);

			return $ret;
		} elseif (is_object($dat)) {
			foreach ($dat as $i => $d) $dat->$i = self::encodeArray($d);

			return $dat;
		} else {
			return $dat;
		}
	}
    
	/**
	 * Converte array unidimensional em um string para concatenar em queries.
	 * ['id', 'nome'] => 'id, nome' ou 'id AND nome'
	 * @param Array arr
	 * @param Boolean keys Se falso utiliza os valores da array
	 * @param String caluse Separador de argumentos (',', AND' etc)
	 * @return String
	 */
	static function arrayToSelectorString($arr, $keys = TRUE, $clause = ',') {
		if(!is_array($arr)){
			throw new DAOException("O parametro com arr deve ser um array.");
		}

		$selectors = '';

		$last_key = @end(@array_keys($arr));

		foreach ($arr as $key => $value) {

			if ($key == $last_key && $keys) { $selectors.=$key.' '; } 
			else if ($keys){ $selectors.=$key.$clause.' '; }

			else if ($key == $last_key && !$keys) { $selectors.=$value.' '; }
			else if (!$keys){ $selectors.=$value.' '.$clause.' '; }

		}
		return $selectors;
	}

	/**
	 * Método invocado por Filtro. Filtra os resultados de acordo com parâmetros colocados na url
	 * Exemplo da view: /members?id_consulta=45&public=1
	 * @param Array $conditions é um array de Strings com os parâmetros dos filtros. 
	 * Exemplo: array("id_consulta=45", "public=1")
	 */
	protected final function filtrar($conditions) {
		if(!is_array($conditions)){
			throw new DAOException("O parametro com condicoes de filtragem deve ser um array.");
		}

		$selectors = $this->arrayToSelectorString($this->columns);
		$limits = $this->arrayToSelectorString($conditions, FALSE, 'AND');

		$sql = "SELECT " .$selectors. " FROM ".$this->tableName." WHERE ".$limits;

		$result = $this->base->consultar($sql);
		$resultEncoded = $this->encodeArray($result);
		
		return $resultEncoded;
	}

}
