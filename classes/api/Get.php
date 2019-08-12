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

		if (!$result || $result == NULL){ throw new APIException("Nada encontrado.", 204); }

		Get::cleanEmail($result);
		$result = GenericDAO::encodeObject($result);
		http_response_code(200);

		return $result;
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
