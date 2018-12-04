<?php
require_once 'APIMethod.php';

class Get extends APIMethod{

	public static function load($request){

		$memberDAO = new Member();
		$consultaDAO = new Consulta();
		$arquivoDAO = new Arquivo();
		$etapaDAO = new Etapa();
		$projetoDAO = new Projeto();
		$urlDAO = new Url();
		$result = NULL;
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$id = intval(array_shift($request));

		$consulta = APIMethod::getConsulta($table);
		if($consulta === FALSE){
			if($table == "members"){
				$result = ($id > 0) ? $memberDAO->obter($id) : $memberDAO->listarAtivos();
			}else if($table == "consultas"){
				$result = ($id > 0) ? $consultaDAO->obter($id) : $consultaDAO->listar();
			}else if($table == "pagedmembers"){
				$result = ($id > 0) ? $memberDAO->listarAtivos($id) : $memberDAO->listarAtivos(1);
			}else if($table == "arquivos"){
				$result = ($id > 0) ? $arquivoDAO->obter($id) : $arquivoDAO->listar();
			}else if($table == "etapas"){
				$result = ($id > 0) ? $etapaDAO->obter($id) : $etapaDAO->listar();
			}else if($table == "projetos"){
				$result = ($id > 0) ? $projetoDAO->obter($id) : $projetoDAO->listar();
			}else if($table == "urls"){
				$result = ($id > 0) ? $urlDAO->obter($id) : $urlDAO->listar();
			}
		}else{
			$result = ($id > 0) ? $memberDAO->obterPorConsulta($consulta->id, $id) : $memberDAO->listarPorConsulta($consulta->id);
		}
		if(!$result || $result == NULL){
			throw new APIException("Nada encontrado.", 204);
		}
		Get::cleanEmail($result);
		$result = GenericDAO::encodeObject($result);
		http_response_code(200);

		return $result;



		$result = NULL;
		$id = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$param = (intval($id) > 0) ? preg_replace('/[^a-z0-9_]+/i','',array_shift($request)) : $id;
		$function = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		if(strpos($function, "php") !== FALSE){
			$function = $param;
		}

		$obj = APIMethod::getTable($function);
		$filtro = NULL;
		if($function == "candidato" && $param == "eleicao"){
			$filtro = array(
				"idEleicao" => "=$id"
			);
			$result = $obj->listar($filtro);
		}
		else{
			$result = ($id > 0) ? $obj->obter($id) : $obj->listar();			
		}

		if(!$result || $result == NULL){
			throw new APIException("Nada encontrado.", 204);
		}
			
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