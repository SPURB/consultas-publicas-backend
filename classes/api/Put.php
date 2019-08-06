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
        
        
        
/*
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));
		$input = json_decode(file_get_contents('php://input'),true);

		$result = NULL;

		if($id <= 0){
			if(!is_array($input) || count($input) <= 0){
				throw new APIException("$id nao encontrado", 400);
			}
			$updated = 0;
			foreach ($input as $item) {
				$obj = APIMethod::getTable($table);
				$pkName = $obj->getPKColName();
				if(!isset($item[$pkName])){
					throw new APIException("Objeto a atualizar fornecido sem ID: $pkName", 400);
				}

				$id = $item[$pkName];
				$objOrig = $obj->obter($id);
				$up = intval(Put::updateObject($obj, $objOrig, $item));
				if($up == 0){
					throw new APIException("Nao atualizado com ID $id", 500);
				}
				$updated++;
			}


			$result = $updated;
		}else{
			Put::updateArray($input);
			$obj = APIMethod::getTable($table);
			$objOrig = $obj->obter($id);
			if($objOrig === FALSE || !is_object($objOrig)){
				throw new APIException("$table $id nao encontrado", 404);
			}
			$result = Put::updateObject($obj, $objOrig, $input);


		}

		if(!$result || $result == 0){
			$msg = "";
			foreach($obj as $key => $val){
				$msg.=" ".$key." | ".$val;
			}
			throw new APIException("$msg Erro na atualização.", 500);
		}
		
		return $result;

	}

	private static function updateObject($obj, $objOrig, $input){
		foreach($obj->columns as $key){
			if(isset($input[$key]) && $objOrig->$key != $input[$key]){
				$obj->$key = $input[$key];
			}else if(isset($objOrig->$key)){
				$obj->$key = $objOrig->$key;
			}else{
				$obj->$key = NULL;
			}
		}
		return $obj->atualizar();
	}

	private static function updateArray($input){
		foreach ($input as $name => $arrayInput) {
			if(!is_array($arrayInput)){
				continue;
			}
			$i = 0;
			foreach($arrayInput as $item){
				$obj = APIMethod::getTable($name);
				$id = $item[$obj->getPKColName()];
				$objOrig = $obj->obter($id);
				if($objOrig === FALSE || !is_object($objOrig)){
					throw new APIException("$name $id nao encontrado", 404);
				}
				if($name == "arquivos"){
					$item["posicao"] = ++$i;
				}
				Put::updateObject($obj, $objOrig, $item);
			}
		}
	}
*/
}

?>