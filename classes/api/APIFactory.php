<?php
require_once 'Get.php';
require_once 'Post.php';
require_once 'Put.php';
require_once 'Delete.php';
require_once 'exceptions/APIException.php';

class APIFactory{

	public function __construct(){}

	public static function executeRequest($method, $requestData, $jsonEncode = TRUE){
		$class = NULL;
		$result = NULL;
		switch ($method) {
			case 'GET':
				$class = "Get";
			break;
			case 'POST':
				$class = "Post";
			break;
			case 'PUT':
				$class = "Put";
			break;
			case 'DELETE':
				$class = "Delete";
			break;
			case 'OPTIONS':
				return NULL;
		}

		try{
			if($class == NULL){
				throw new APIException("$method Invalid HTTP method", 405);
			}
			$result = $class::load($requestData);
			if($jsonEncode === TRUE){
				$resultEnc = json_encode($result);
				if(json_last_error() != JSON_ERROR_NONE){
					throw new APIException(json_last_error_msg());
				}else{
					$result = $resultEnc;
				}
			}
		}catch(Exception $ex){
			http_response_code($ex->getCode());
			$result=$ex->getMessage();
		}

		return $result;
	}

}


?>
