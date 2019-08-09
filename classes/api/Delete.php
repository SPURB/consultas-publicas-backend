<?php

class Delete extends APIMethod{

    public static function load($request){

		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));
		$result=NULL;

		if($id <= 0){
			throw new Exception("$id nao encontrado", 404);
		}

		$obj = parent::getTable($table);
		$result = $obj->remove($id);

		if($result == NULL || $result === FALSE){
			http_response_code(500);
		}
		return $result;


	}

    
    
    
    /*
	public static function load($request){
		$memberDAO = new Member();
		$result = NULL;
		
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));
		
		try{
			if($id <= 0){
				throw new APIException("$id parametro invalido", 400);
			}
			$result = $memberDAO->desativar($id);
			
			if($result == NULL || $result === FALSE || $result == 0){
				throw new APIException("$id gerou um erro. Nenhuma linha atualizada.", 500);
			}
			http_response_code(200);
		}catch(Exception $ex){
			http_response_code($ex->getCode());
			$result=$ex->getMessage();
		}
		return $result;
	}
*/
}

?>
