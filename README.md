# Backend das consultas públicas
API REST das consulta públicas da Secretaria de Desenvolvimento Urbano e São Paulo Urbanismo.

## Para montar localmente
* Criar o diretório `properties` e o arquivo `properties/bd.properties` na raiz do projeto e incluir os parâmetros:
```
dbtype:mysql
host:127.0.0.1
port:3306
user:root
password:yourpassword
dbname:yourdbname
```
* Criar no mesmo diretório o arquivo `properties/keys.properties`
```
Number: String
Number: String
Number: String
...
```
> Verifique com no ambiente de homologação as chaves e valores de `keys.properties`.

* Criar na raiz do projeto o diretório `logs` e o arquivo `logs/api.log`

## Documentação para frontend
[![Run in Postman](https://run.pstmn.io/button.svg)](https://documenter.getpostman.com/view/4136141/S1ZxbpLD)

Para habilitar `post, put` e `delete` envie um email para desenvolvimento@spurbanismo.sp.gov.br.