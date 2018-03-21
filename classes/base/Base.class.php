<?php
include_once "PDOQueryException.class.php";

class Base{
	
    /*Método construtor do banco de dados*/
	public function __construct($filePath = NULL){
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
	
	private static $credentials = array();

    private function getDBType()  {return self::$credentials["dbtype"];}
    private function getHost()    {return self::$credentials["host"];}
    private function getPort()    {return self::$credentials["port"];}
    private function getUser()    {return self::$credentials["user"];}
    private function getPassword(){return self::$credentials["password"];}
    private function getDB()      {return self::$credentials["dbname"];}
	
	private function openPropertiesFile($filePath){
		try{
			$propFile = fopen($filePath, "r");
			while(!feof($propFile)){
				$line = fgets($propFile);
				$split = explode(":", $line);
				$propParams = array("dbtype", "host", "port", "user", "password", "dbname");
				foreach($propParams as $p){
					$paramObtido = trim($split[0]);
					if($paramObtido == $p){
						self::$credentials[$paramObtido] = trim($split[1]);
						break;
					}
				}
			}
		}catch(Exception $ex){
			throw new Exception($ex->getMessage());
		}finally{
			fclose($propFile);
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
		error_log($msg);
	}
	
    private function connect(){
        try
        {
            $this->conexao = new PDO($this->getDBType().":host=".$this->getHost().";port=".$this->getPort().";dbname=".$this->getDB(), $this->getUser(), $this->getPassword());

        }
        catch (Exception $ex)
        {
            trigger_error("Banco de dados indisponível: " . $ex->getMessage(), E_USER_ERROR);
        }
         
        return ($this->conexao);
    }
     
    private function disconnect(){
        $this->conexao = null;
    }
     
    /*Método select que retorna um VO ou um array de objetos*/
    public function consultar($sql,$params=null){
    	
		$rs = new ArrayObject();
		
        try{
	        $query=$this->connect()->prepare($sql);
			if($query->execute($params) === FALSE){
				throw new PDOQueryException($query->errorInfo());
			}	         
	    	while($row = $query->fetchAll(PDO::FETCH_OBJ)){
	    		$rs->append($row);
	    	}
	        self::__destruct();
		}
		catch(Exception $ex){
			$this->logErro($ex->getMessage(), $params, $sql);
		}
		
        return $rs;
    }
	
    /*Método insert que insere valores no banco de dados e retorna o último id inserido*/
    public function criar($sql,$params=null){
		$rs = NULL;
    	try{
	        $conexao=$this->connect();
	        $query=$conexao->prepare($sql);
			if($query->execute($params) === FALSE){
				throw new PDOQueryException($query->errorInfo());
			}
	        $rs = $conexao->lastInsertId() or die(print_r($query->errorInfo(), true));
	        self::__destruct();
		}
		catch(Exception $ex){
			$this->logErro($ex->getMessage(), $params, $sql);
		}
		
        return $rs;
    }
     
    /*Método update que altera valores do banco de dados e retorna o número de linhas afetadas*/
    public function atualizar($sql,$params=null){
    	$rs = NULL;
		try{
	        $query=$this->connect()->prepare($sql);
	        if($query->execute($params) === FALSE){
				throw new PDOQueryException($query->errorInfo());
			}	
	        $rs = $query->rowCount();
	        self::__destruct();
		}
		catch(Exception $ex){
			$this->logErro($ex->getMessage(), $params, $sql);
		}
        return $rs;
    }
     
    /*Método delete que excluí valores do banco de dados retorna o número de linhas afetadas*/
    public function deletar($sql,$params=null){
    	try{
	        $query=$this->connect()->prepare($sql);
	        if($query->execute($params) === FALSE){
				throw new PDOQueryException($query->errorInfo());
			}	
	        $rs = $query->rowCount();
	        self::__destruct();
		}
		catch(Exception $ex){
			$this->logErro($ex->getMessage(), $params, $sql);
		}
        return $rs;
    }
}

?>
