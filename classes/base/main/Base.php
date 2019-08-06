<?php
//require_once 'Logger.php';
require_once APP_PATH.'/classes/base/exceptions/PDOQueryException.php';

class Base{
	
	/*Método construtor do banco de dados*/
	public function __construct($filePath){
		if($filePath != NULL){
			$this->openPropertiesFile($filePath);
		}
	}
	
	/*Evita que a classe seja clonada*/
	private function __clone(){}
	
	/*Método que destroi a conexão com banco de dados e remove da memória todas as variáveis setadas*/
	public function __destruct() {
		$this->disconnect();
		foreach ($this as $key => $value) {
			unset($this->$key);
		}
	}

	private $conexao = NULL;
	
	private static $credentials = array();

	private function getDBType()  {return self::$credentials["dbtype"];}
	private function getHost()    {return self::$credentials["host"];}
	private function getPort()    {return self::$credentials["port"];}
	private function getUser()    {return self::$credentials["user"];}
	private function getPassword(){return self::$credentials["password"];}
	private function getDB()      {return self::$credentials["dbname"];}
	
	private function openPropertiesFile($filePath){
		if($propFile = fopen($filePath, "r")){
			while(!feof($propFile)){
				$line = fgets($propFile);
				$split = explode(":", $line);
				$propParams = array("dbtype", "host", "Server", "port", "user", "password", "dbname", "Database");
				foreach($propParams as $p){
					$paramObtido = trim($split[0]);
					if($paramObtido == $p){
						self::$credentials[$paramObtido] = trim($split[1]);
						break;
					}
				}
			}
            fclose($propFile);
        }else{
            throw new Exception("Erro ao abrir o arquivo properties: $filePath");
        }
	}
	
	private function logErro($msg, $params=null, $sql = null){
		if($sql != null){
			$msg.=" | Query : $sql | ";
		}
		if($params!=null){
			$msgParam = "";
			for($i=0;$i<count($params);$i++){
				$msgParam .= "[".$params[$i]."]";
			}
			$msg.=$msgParam;
		}
        /*
		$log = new Logger();
		$log->write($msg);
        */
	}
	
	private function connect(){
        $connString = "";
        $db = $this->getDBType();
        if($db=="sqlsrv"){
            $connString = $db.":Server=".$this->getHost().",".$this->getPort().";Database=".$this->getDB();
        }else{
            $connString = $db.":host=".$this->getHost().";port=".$this->getPort().";dbname=".$this->getDB();
        }
        //$this->conexao = new PDO($connString, $this->getUser(), $this->getPassword(), array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->conexao = new PDO($connString, $this->getUser(), $this->getPassword());
		return ($this->conexao);
	}
		
	private function disconnect(){
		$this->conexao = null;
	}
		
	/*Método select que retorna um VO ou um array de objetos*/
	public final function consultar($sql,$params=null){
		$rs = new ArrayObject();
		//$log = new Logger();
		//$log->write("SQL Query $sql");

        $query=$this->connect()->prepare($sql);
        if($query->execute($params) === FALSE){
            throw new PDOQueryException($query->errorInfo());
        }
        while($row = $query->fetchAll(PDO::FETCH_OBJ)){
            $rs->append($row);
        }
        self::__destruct();
		return $rs;
	}

	/*Método insert que insere valores no banco de dados e retorna o último id inserido*/
	public final function criar($sql,$params=null, $sequenceName=null){
		$rs = NULL;
        $conexao=$this->connect();
        $query=$conexao->prepare($sql);
        if($query->execute($params) === FALSE){
            throw new PDOQueryException($query->errorInfo());
        }
        $rs = $sequenceName != null ? $conexao->lastInsertId($sequenceName) : $conexao->lastInsertId();
        if($rs == NULL){
            throw new Exception("Erro ao obter o ID inserido. Verificar se a sequence da PK foi informada ou se o driver nao suporta a funcao.");
        }
        self::__destruct();
		
		return $rs;
	}
		
	/*Método update que altera valores do banco de dados e retorna o número de linhas afetadas*/
	public final function atualizar($sql,$params=null){
		$rs = NULL;
        $query=$this->connect()->prepare($sql);
        if($query->execute($params) === FALSE){
            throw new PDOQueryException($query->errorInfo());
        }	
        $rs = $query->rowCount();
        self::__destruct();
		
		return $rs;
	}

	/*Método delete que excluí valores do banco de dados retorna o número de linhas afetadas*/
	public final function deletar($sql,$params=null){
        $query=$this->connect()->prepare($sql);
        if($query->execute($params) === FALSE){
            throw new PDOQueryException($query->errorInfo());
        }
        $rs = $query->rowCount();
        self::__destruct();
		
		return $rs;
	}
}

?>
