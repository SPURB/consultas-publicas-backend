<?php

require_once 'APIMethod.php';

class Put extends APIMethod{

	public static function load($request){

		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));
		$input = json_decode(file_get_contents('php://input'),true);

		$obj = APIMethod::getTable($table);

		$result = NULL;
		try{
			if($id <= 0){
				throw new APIException("$id nao encontrado", 404);
			}
			$objOrig = $obj->obter($id);
			if($objOrig === FALSE){
				throw new APIException("$id nao encontrado", 404);
			}
			
			foreach($obj->columns as $key){
				if(isset($input[$key]) && $objOrig->$key != $input[$key]){
					$obj->$key = $input[$key];
				}else if(isset($objOrig->$key)){
					$obj->$key = $objOrig->$key;
				}else{
					$obj->$key = NULL;
				}
			}
			$result = $obj->atualizar();
			if(!$result || $result == 0){
				$msg = "";
				foreach($obj as $key => $val){
					$msg.=" ".$key." | ".$val;
				}
				throw new APIException("$msg Erro na atualização.", 500);
			}
		}catch(Exception $ex){
			http_response_code($ex->getCode());
			$result=$ex->getMessage();
		}
		return $result;

	}

}

?>