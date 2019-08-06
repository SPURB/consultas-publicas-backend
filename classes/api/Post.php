<?php
require_once 'APIMethod.php';
require_once 'APIKey.php';

class Post extends APIMethod{

	public static function load($request){

		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$action = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$input = json_decode(file_get_contents('php://input'),true);
		
		try{
			$headers = getallheaders();
			$token = $headers['Current'];
			if(!isset($token) || APIKey::check($token) !== TRUE){
				throw new APIException($_SERVER['REMOTE_ADDR']." Token incorreto ", 403);
			}

            //INSERT
            $obj = APIMethod::getTable($table);
            $result = $obj->insert($input);
            if($result == NULL || $result === FALSE){
                throw new APIException("Erro ao gravar no banco de dados.", 500);
            }
            
			http_response_code(200);
		}catch(Exception $ex){
            Logger::write($ex);
			http_response_code($ex->getCode());
			$result=$ex->getMessage();	
		}
		return $result;



	}

}

?>