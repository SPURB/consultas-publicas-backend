# consultas-publicas-backend
Backend de consulta públicas de Projetos de Intervenções Urbanas municipais de São Paulo.

## GET
* `apiconsultas.php/members`
Retorna todos os members
* `apiconsultas.php/members/<id>`
Retorna o member pelo seu memid
* `apiconsultas.php/consultas`
Retorna todas as consultas ativas
* `apiconsultas.php/<nome_da_consulta>`
Retorna todos os members relacionados a consulta informada


## POST
* `apiconsultas.php/members {parametros POST}`
Cria um novo member. Seu conteúdo será definido nos parâmetros POST
* `apiconsultas.php/members/<nome_da_consulta> {parametros POST}`
Cria um novo member relacionado a consulta informada. Seu conteúdo será definido nos parâmetros POST
  > Definição de parametros POST para inserção
Exemplo: `{'name':'Thomas', 'public':'1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta

* `apiconsultas.php/members/search {parametros POST}`
 Retorna uma consulta utilzando os parâmetros como filtros
  > Definição de parametros POST para consulta
Exemplo: `{'name':'=Thomas', 'public':'=1' }`
Os nomes das colunas devem ser iguais aos retornados numa consulta

* `apiconsultas.php/members/pagedsearch/<numero_da_pagina> {parametros POST}`
 Semelhante ao anterior, mas retorna somente 10 resultados por vez, de acordo com o número da página informado.
 OBS: Caso não seja informado o número da página, serão retornados os 10 primeiros resultados.

* `apiconsultas.php/consultas {parametros POST}`
Cria uma nova consulta. Seu conteúdo será definido nos parâmetros POST
  > Definição de parametros POST para inserção
  Exemplo: `{'nome':'consulta_teste' }`


## PUT
* `apiconsultas.php/members/<memid> {parametros PUT}`
Atualiza um member pelo memid. Seu conteúdo será definido nos parâmetros PUT

* `apiconsultas.php/<nome_da_consulta> {parametros PUT}`
Atualiza o nome da consulta pelo parâmetro PUT
  Exemplo: `{'nome':'nova_consulta' }`


## DELETE
* `apiconsultas.php/members/<memid>`
Desativa o member pelo memid.

* `apiconsultas.php/<nome_da_consulta>`
Desativa a consulta informada.

## Nota
**Apontar caminho do arquivo de conexao com o banco na classe GenericDAO (linha 11)**

## Estrutura do banco

````mysql
mysql> show tables;
+------------------------------------+
| Tables_in_gestaourbanasp_consultas |
+------------------------------------+
| arquivos                           |
| consultas                          |
| etapas                             |
| members                            |
| projetos                           |
| projetos_arquivos                  |
| projetos_consultas                 |
| projetos_usuarios                  |
| usuarios                           |
+------------------------------------+

mysql> desc arquivos;
+-------------+--------------+------+-----+-------------------+-----------------------------+
| Field       | Type         | Null | Key | Default           | Extra                       |
+-------------+--------------+------+-----+-------------------+-----------------------------+
| nome        | varchar(255) | YES  |     | NULL              |                             |
| id          | int(11)      | NO   | PRI | NULL              | auto_increment              |
| id_etapa    | int(11)      | YES  | MUL | NULL              |                             |
| url         | mediumtext   | YES  |     | NULL              |                             |
| atualizacao | timestamp    | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| fonte       | mediumtext   | NO   |     | NULL              |                             |
| autor       | mediumtext   | NO   |     | NULL              |                             |
| extensao    | varchar(8)   | YES  |     | NULL              |                             |
+-------------+--------------+------+-----+-------------------+-----------------------------+

mysql> desc consultas;
+----------------+--------------+------+-----+---------+----------------+
| Field          | Type         | Null | Key | Default | Extra          |
+----------------+--------------+------+-----+---------+----------------+
| id_consulta    | int(11)      | NO   | PRI | NULL    | auto_increment |
| nome           | varchar(200) | NO   |     | NULL    |                |
| data_cadastro  | date         | YES  |     | NULL    |                |
| ativo          | tinyint(1)   | NO   |     | NULL    |                |
| nome_publico   | varchar(200) | NO   |     | NULL    |                |
| data_final     | date         | YES  |     | NULL    |                |
| texto_intro    | text         | NO   |     | NULL    |                |
| url_consulta   | text         | NO   |     | NULL    |                |
| url_capa       | text         | NO   |     | NULL    |                |
| url_devolutiva | text         | YES  |     | NULL    |                |
+----------------+--------------+------+-----+---------+----------------+

mysql> desc etapas;
+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int(11)      | NO   | PRI | NULL    | auto_increment |
| nome       | varchar(255) | YES  |     | NULL    |                |
| fk_projeto | int(11)      | YES  | MUL | NULL    |                |
+------------+--------------+------+-----+---------+----------------+

mysql> desc members;
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
| id_consulta    | int(11)     | NO   | MUL | NULL    |                |
+----------------+-------------+------+-----+---------+----------------+

mysql> desc projetos;
+-------------+--------------+------+-----+-------------------+----------------+
| Field       | Type         | Null | Key | Default           | Extra          |
+-------------+--------------+------+-----+-------------------+----------------+
| nome        | varchar(500) | YES  |     | NULL              |                |
| id          | int(11)      | NO   | PRI | NULL              | auto_increment |
| ativo       | tinyint(4)   | YES  |     | 1                 |                |
| autor       | text         | YES  |     | NULL              |                |
| atualizacao | timestamp    | YES  |     | CURRENT_TIMESTAMP |                |
+-------------+--------------+------+-----+-------------------+----------------+

mysql> desc projetos_usuarios;
+-------------+---------+------+-----+---------+----------------+
| Field       | Type    | Null | Key | Default | Extra          |
+-------------+---------+------+-----+---------+----------------+
| id          | int(11) | NO   | PRI | NULL    | auto_increment |
| fk_projeto  | int(11) | YES  | MUL | NULL    |                |
| fk_usuarios | int(11) | YES  | MUL | NULL    |                |
+-------------+---------+------+-----+---------+----------------+

mysql> desc projetos_consultas;
+-------------+---------+------+-----+---------+----------------+
| Field       | Type    | Null | Key | Default | Extra          |
+-------------+---------+------+-----+---------+----------------+
| id          | int(11) | NO   | PRI | NULL    | auto_increment |
| fk_projeto  | int(11) | YES  | MUL | NULL    |                |
| fk_consulta | int(11) | YES  | MUL | NULL    |                |
+-------------+---------+------+-----+---------+----------------+

mysql> desc projetos_arquivos;
+------------+---------+------+-----+---------+----------------+
| Field      | Type    | Null | Key | Default | Extra          |
+------------+---------+------+-----+---------+----------------+
| id         | int(11) | NO   | PRI | NULL    | auto_increment |
| fk_projeto | int(11) | YES  | MUL | NULL    |                |
| fk_arquivo | int(11) | YES  | MUL | NULL    |                |
+------------+---------+------+-----+---------+----------------+

mysql> desc usuarios;
+---------------------+--------------+------+-----+-------------------+-----------------------------+
| Field               | Type         | Null | Key | Default           | Extra                       |
+---------------------+--------------+------+-----+-------------------+-----------------------------+
| ID                  | int(11)      | NO   | PRI | NULL              | auto_increment              |
| Email               | varchar(50)  | NO   | UNI | NULL              |                             |
| Nome                | varchar(255) | NO   |     | NULL              |                             |
| Organizacao         | varchar(255) | YES  |     | NULL              |                             |
| CEP                 | varchar(50)  | YES  |     | NULL              |                             |
| RegioesDeInteresse  | mediumtext   | YES  |     | NULL              |                             |
| ProjetosDeInteresse | mediumtext   | YES  |     | NULL              |                             |
| Timestamp           | timestamp    | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
+---------------------+--------------+------+-----+-------------------+-----------------------------+
````
