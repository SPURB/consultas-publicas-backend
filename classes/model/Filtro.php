<?php
require_once "GenericDAO.php";

class Filtro extends GenericDAO {

	public function __construct($table){
		parent::__construct($table);
		$this->tableName = $table;

		switch ($table) {
			case 'etapas':
				$this->columns = array(
					"id" => "id",
					"nome" => "nome"
				); break;

			case 'subetapas':
				$this->columns = array(
					"id" => "id",
					"nome" => "nome"
				); break;

			case 'extensao':
				$this->columns = array(
					"id" => "id",
					"nome" => "nome"
				); break;

			case 'arquivos':
				$this->columns = array(
					"id" => "id",
					"nome" => "nome",
					"id_etapa" => "idEtapa",
					"id_subetapa" => "idSubEtapa",
					"id_projeto" => "idProjeto",
					"url" => "url",
					"id_extensao" => "idExtensao",
					"atualizacao" => "atualizacao",
					"posicao" => "posicao",
					"fonte" => "fonte"
				); break;

			case 'consultas':
				$this->columns=array(
					"id_consulta" => "idConsulta",
					"nome" => "nome",
					"data_cadastro" => "dataCadastro",
					"ativo" => "ativo",
					"nome_publico" => "nomePublico",
					"data_final" => "dataFinal",
					"texto_intro" => "textoIntro",
					"url_consulta" => "urlConsulta",
					"url_capa" => "urlCapa",
					"url_devolutiva" => "urlDevolutiva"
				); break;
			case 'members': 
				$this->columns = array(
					"memid" => "memid",
					"name" => "name",
					"email" => "email",
					"content" => "content",
					"commentdate" => "commentdate",
					"public" => "public",
					"postid" => "postid",
					"trash" => "trash",
					"commentid" => "commentid",
					"commentcontext" => "commentcontext",
					"id_consulta" => "idConsulta",
					"proposta" => "idProposta",
					"justificativa" => "justificativa",
					"opiniao" => "idOpiniao"
				);
		}
	}

	public function listar($filters) {
		$conditions = array();

		foreach ($filters as $value) {
			$key = array_search ($value, $filters);
			$condition = $key. "=" .$value;
			array_push($conditions, $condition);
		}

		$result = $this->filtrar($conditions);

		return $result;
	}
}