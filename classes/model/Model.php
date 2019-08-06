<?php

require_once 'GenericDAO.php';
//require_once APP_PATH.'/classes/base/main/Logger.php';

abstract class Model{
    
    private static $DAO;
    
    public function __construct(){
        $this->loadDAO();
    }
    
    private function __clone(){}
    
    public static function getCalledClass(){
        return get_called_class();
    }
    
    private function loadDAO(){
        //$this->DAO = new GenericDAO($this->getTableName(), $this->getColumns());
    }
    
    public function getById($id){
        //return $this->DAO->obter($id);
    }
    
    public function getList($filtro = NULL, $limite = 0, $pagina = 1){
        $list = $this->DAO->select($filtro);
        $rows = array();
		foreach($list as $row){
			foreach($row as $obj){
				$obj = $this->bind($obj);
				$obj = $this->mapOneToMany($obj);
				$obj = $this->mapManyToOne($obj);
                array_push($rows, $obj);
			}
		}
		if($limite > 0){
			$primeiro = ($pagina * $limite) - $limite;
			$result = array_slice($rows, $primeiro, $limite);
		}
        return $rows;
    }
    
    protected function beforePost($input){
        
    }
    
    public function post($input){
        
    }
    
    protected function beforePut($input){
        
    }
    
    public function put($input, $id){
        
    }
    
    public function delete($id){
        
    }
    
    public function execAction($actionName, $filtro, $id){
        
    }
    
	/**
	 * Método utilizado para trocar os campos do BD pelas propriedades da classe
	 * @param $columns:array de campos key=campo_do_bd value=propriedade_da_classe,
	 * objetobd:um objeto já populado vindo por consulta do bd,
	 * objetoclass[opcional]: se for omitido retorna um novo objeto, se passado retorna um objeto da sua classe
	 *
	 */
	protected function bind($objetobd, $objetoclass = NULL){
        if($objetoclass == NULL){
            $classe = "Consulta";
            $objetoclass = new $classe();
            var_dump($objetoclass);
        }
        if(!is_object($objetoclass)){
            throw new DAOException("Falha no mapeamento do banco de dados. O parâmetro fornecido não é um objeto.");
        }
        $classe = get_class($objetoclass);
        
        /*
        
		if(! ($classe = get_class($objetoclass) ) )
			throw new DAOException("Falha no mapeamento do banco de dados. O parâmetro fornecido não é um objeto.");

		if(! (is_array($this->getColumns()) ) )
			throw new DAOException("Falha no mapeamento do banco de dados. As colunas devem ser definidas em um array.");

		if($objetoclass === NULL){
			$objetoclass = new $classe();
		}

*/  
		foreach($this->getColumns() as $campobd => $campoclass){
			if(property_exists($objetobd, $campobd)){
				$objetoclass->$campoclass = $objetobd->$campobd;
			}
		}
		return $objetoclass;
	}
    
    private function mapOneToMany($obj){
        $oneMany = $this->getOneMany();
        if(is_array($oneMany)){
            foreach ($obj as $prop => $valor) {
                if(array_key_exists($prop, $oneMany)){
                    try{
                        $clazzName = $oneMany[$prop];
                        require_once $clazzName.".php";
                        $objClazz = new $clazzName();
                        if(!$objClazz instanceof Model){
                            throw new Exception($clazzName." classe incompativel.");
                        }
                        $objRelac = $objClazz->obter($valor);
                        $obj->$prop = $objRelac;
                    }catch(Exception $ex){
                        $this->base->logErro("Erro ao consultar objeto relacionado: ".$ex);
                    }
                }
            }
        }
        return $obj;
    }
    
    private function mapManyToOne($obj){
        $manyOne = $this->getManyOne();
        $manyOneId = $this->getManyOneId();
        if(is_array($manyOne)){
            foreach($manyOne as $prop => $clazzName){
                try{
                    $filtro = array($manyOneId => "=".$obj->id);
                    require_once $clazzName.".php";
                    $objClazz = new $clazzName();
                    if(!$objClazz instanceof Model){
                        throw new Exception($clazzName." classe incompativel.");
                    }
                    $obj->$prop = $objClazz->select($filtro);
                }catch(Exception $ex){
                    $this->base->logErro("Erro ao consultar lista relacionada: ".$ex);
                }
            }
        }
        return $obj;
    }
    
    abstract function getTableName();
    
    abstract function getColumns();
    
    abstract function getOneMany();
    
    abstract function getManyOne();
    
    abstract function getManyOneId();
    
}

?>