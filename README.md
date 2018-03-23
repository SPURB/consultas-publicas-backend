# consultas-publicas-backend
Backend de consulta públicas de Projetos de Intervenções Urbanas municipais de São Paulo.

## GET
* `testeapi.php/members`
Retorna todos os members
* `testeapi.php/members/<id>`
Retorna o member pelo seu memid
* `testeapi.php/consultas`
Retorna todas as consultas ativas
* `testeapi.php/<nome_da_consulta>`
Retorna todos os members relacionados a consulta informada


## POST
* `testeapi.php/members {parametros POST}`
Cria um novo member. Seu conteúdo será definido nos parâmetros POST
  * `testeapi.php/members/<nome_da_consulta> {parametros POST}`
Cria um novo member relacionado a consulta informada. Seu conteúdo será definido nos parâmetros POST
  > Definição de parametros POST para inserção
Exemplo: `{'name':'Thomas', 'public':'1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta

* `testeapi.php/members/search {parametros POST}`
 Retorna uma consulta utilzando os parâmetros como filtros
  > Definição de parametros POST para consulta
Exemplo: `{'name':'=Thomas', 'public':'=1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta

* `testeapi.php/consultas {parametros POST}`
Cria uma nova consulta. Seu conteúdo será definido nos parâmetros POST
  > Definição de parametros POST para inserção
  Exemplo: `{'nome':'consulta_teste' }`


## PUT
* `testeapi.php/members/<memid> {parametros PUT}`
Atualiza um member pelo memid. Seu conteúdo será definido nos parâmetros PUT

* `testeapi.php/<nome_da_consulta> {parametros PUT}`
Atualiza o nome da consulta pelo parâmetro PUT
  Exemplo: `{'nome':'nova_consulta' }`


## DELETE
* `testeapi.php/members/<memid>`
Desativa o member pelo memid.

* `testeapi.php/<nome_da_consulta>`
Desativa a consulta informada.

## Nota
**Temporariamente para testes, aponte o caminho do arquivo de conexao com o banco na classe GenericDAO**

## Estrutura do banco

````mysql
mysql> DESC members;
+----------------+-------------+------+-----+---------+----------------+
| Field          | Type        | Null | Key | Default | Extra          |
+----------------+-------------+------+-----+---------+----------------+
| memid          | int(11)     | NO   | PRI | NULL    | auto_increment |
| name           | varchar(30) | NO   |     | NULL    |                |
| email          | varchar(30) | NO   |     | NULL    |                |
| content        | text        | NO   |     | NULL    |                |
| commentdate    | datetime    | NO   |     | NULL    |                |
| public         | tinyint(1)  | NO   |     | NULL    |                |
| postid         | int(11)     | NO   |     | NULL    |                |
| trash          | tinyint(1)  | NO   |     | NULL    |                |
| commentid      | int(11)     | NO   |     | NULL    |                |
| commentcontext | text        | NO   |     | NULL    |                |
| idConsulta     | int(11)     | NO   | MUL | NULL    |                |
+----------------+-------------+------+-----+---------+----------------+
11 rows in set (0.00 sec)

mysql> DESC consultas;
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| id_consulta   | int(11)      | NO   | PRI | NULL    | auto_increment |
| nome          | varchar(200) | NO   |     | NULL    |                |
| data_cadastro | date         | YES  |     | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+
3 rows in set (0.00 sec)

mysql> SELECT * FROM consultas;
+-------------+---------------------------------------+---------------+
| id_consulta | nome                                  | data_cadastro |
+-------------+---------------------------------------+---------------+
|           1 | nome_banco_consulta                   | 0000-00-00    |
+-------------+---------------------------------------+---------------+

````
