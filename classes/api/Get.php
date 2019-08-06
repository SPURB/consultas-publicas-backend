<?php
require_once 'APIMethod.php';

class Get extends APIMethod{

	public static function load($request){

		$result = NULL;
		$table = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
		$id = intval(array_shift($request));
        $filtros = isset($_GET) ? $_GET : array();
        $conditions = array();
        foreach ($filtros as $value) {
			$key = array_search ($value, $filtros);
            $conditions[$key] = "=".$value;
		}
        
		$DAO = parent::getTable($table);
        
        $result = ($id > 0) ? $DAO->get($id) : $DAO->getList($conditions); 
/*
		$consulta = APIMethod::getConsulta($table);
        if($consulta !== FALSE){
            $result = $DAO->execAction("listarPorConsulta", $filtros, $id);
        }else{
            $result = ($id > 0) ? $DAO->getById($id) : $DAO->getList($conditions); 
        }

        -----

        /*
		if($consulta === FALSE && !$filtros && $table === "members"){
            $result = ($id > 0) ? $DAO->obter($id) : $DAO->listarAtivos(); 
        }
		else if($consulta === FALSE && !$filtros){ 
            $result = ($id > 0) ? $DAO->obter($id) : $DAO->select(); 
        }
		else if($filtros) { 
            require_once APP_PATH.'/classes/model/Filtro.php';
            $filtrarDAO = new Filtro($table);
            $result = $filtrarDAO->listar($filtros); 
        }
		else { 
            $result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id); 
        }
        */
		if (!$result || $result == NULL){ throw new APIException("Nada encontrado.", 204); }

		Get::cleanEmail($result);
		$result = GenericDAO::encodeObject($result);
		http_response_code(200);

		return $result;
/*
		$result = NULL;
		$id = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$param = (intval($id) > 0) ? preg_replace('/[^a-z0-9_]+/i','',array_shift($request)) : $id;
		$function = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		if(strpos($function, "php") !== FALSE){
			$function = $param;
		}

		$obj = APIMethod::getTable($function);

		$result = ($id > 0) ? $obj->obter($id) : $obj->listar();			

		if(!$result || $result == NULL){
			throw new APIException("Nada encontrado.", 204);
		}
			
		return $result;
        */
	}

	private function cleanEmail($obj){
		if(is_array($obj)){
			foreach($obj as $item){
				$item = Get::cleanEmail($item);
			}
		}else if(is_object($obj) && isset($obj->email)){
			$obj->email = "";
		}
		return $obj;
	}

}

?>