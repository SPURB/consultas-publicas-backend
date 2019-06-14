<?php
require_once 'APIMethod.php';

class Post extends APIMethod{

	public static function load($request){

		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$action = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$input = json_decode(file_get_contents('php://input'),true);
		
		try{
			$headers = getallheaders();
			$token = $headers['Content-Post'];
			if(!isset($token) || APIMethod::allow($token) !== TRUE){
				throw new APIException($_SERVER['REMOTE_ADDR']." Token incorreto", 403);
			}
			if($table == "members"){
				$member = new Member();
				$result = NULL;
				$actions = array("search", "pagedsearch");		
				if(array_search($action, $actions) !== FALSE){
					//SEARCH MEMBER
					if($action == "pagedsearch"){
						$page = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
						$page = (intval($page) > 0) ? intval($page) : 1;
					}else{
						$page = NULL;
					}
					$result = $member->buscar($input, $page);

					if(!$result || $result == NULL){
						throw new APIException("Nenhum resultado encontrado", 204);
					}
					$result = GenericDAO::encodeObject($result);
				}else if($action == ""){
					//INSERT MEMBER
					$consulta = APIMethod::getConsulta($table);
					if($consulta !== FALSE){
						if($consulta->ativo == '0'){
							throw new APIException("Consulta encerrada. Periodo de participacao terminado.", 403);
						}
						$member->idConsulta = $consulta->id;
					}

					$result = $member->cadastrar($input);
					if($result == NULL || $result === FALSE){
						$msg = "";
						foreach($member as $key => $val){
							$msg.=" ".$key." | ".$val;
						}
						throw new APIException("$msg Erro ao gravar no banco de dados.", 500);
					}
				}else{
					throw new APIException("Recurso nao encontrado", 404);
				}
			}else{
				//INSERT
				$obj = APIMethod::getTable($table);
				$result = $obj->cadastrar($input);
				if($result == NULL || $result === FALSE){
					throw new APIException("Erro ao gravar no banco de dados.", 500);
				}
			}
			http_response_code(200);
		}catch(Exception $ex){
			http_response_code($ex->getCode());
			$result=$ex->getMessage();	
		}
		return $result;



	}

}

?>