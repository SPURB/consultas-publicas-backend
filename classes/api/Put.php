<?php

require_once 'APIMethod.php';

class Put extends APIMethod{

	public static function load($request){
		
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
        $id = intval(array_shift($request));
		$input = isset($_PUT["data"]) ? $_PUT["data"] : file_get_contents('php://input');
		$input = json_decode($input, true);
		$result=NULL;
        
        try{
            $headers = getallheaders();
            $token = $headers['Current'];
            if(!isset($token) || APIKey::check($token) !== TRUE){
                throw new APIException($_SERVER['REMOTE_ADDR']." Token incorreto ", 403);
            }

            $obj = parent::getTable($table);

            if($id > 0){
                $result = $obj->selfUpdate($input, $id);
            }else{
                if(!is_array($input) || count($input) <= 0){
                    throw new APIException("Lista de registros a atualizar inválida", 400);
                }
                $updated = 0;
                foreach ($input as $item) {
                    $res = $obj->selfUpdate($item, $id);
                    if(intval($res)>0){
                       $updated++; 
                    }
                }
                $result = $updated;
            }
            if($result == NULL || $result === FALSE){
                http_response_code(500);
            }
        }catch(Exception $ex){
            Logger::write($ex);
            $result="Erro ao processar a requisição.";
            http_response_code(500);
        }
		return $result;
    }
}

?>
