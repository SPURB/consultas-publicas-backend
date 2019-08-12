<?php

class Delete extends APIMethod{

    public static function load($request){

		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));
		$result=NULL;

		if($id <= 0){
			throw new Exception("$id nao encontrado", 404);
		}
        try{
            $obj = parent::getTable($table);
            $result = $obj->remove($id);
            if($result == NULL || $result === FALSE){
                http_response_code(500);
            }
        }catch(Exception $ex){
            Logger::write($ex);
            $result="Erro ao remover o registro.";
            http_response_code(500);
        }
		return $result;
	}
}

?>
