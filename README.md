# consultas-publicas-backend
Backend de consulta públicas de Projetos de Intervenções Urbanas municipais de São Paulo.


## GET
* `testeapi.php/members`
Retorna todos os members
* `testeapi.php/members/<id>`
Retorna o member pelo seu memid
* `testeapi.php/<nome_da_consulta>`
Retorna todos os members relacionados a consulta informada


## POST
* `testeapi.php/members {parametros POST}`
Cria um novo member. Seu conteúdo será definido nos parâmetros POST
  > Definição de parametros POST para inserção
Exemplo: `{'name':'Thomas', 'public':'1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta

* `testeapi.php/members/search {parametros POST}`
 Retorna uma consulta utilzando os parâmetros como filtros
  > Definição de parametros POST para consulta
Exemplo: `{'name':'=Thomas', 'public':'=1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta


## PUT
* `testeapi.php/members/<memid> {parametros PUT}`
Atualiza um member pelo memid. Seu conteúdo será definido nos parâmetros PUT


## DELETE
* `testeapi.php/members/<memid>`
Desativa o member pelo memid.

## Nota
**Temporariamente para testes, aponte o caminho do arquivo de conexao com o banco na classe GenericDAO**