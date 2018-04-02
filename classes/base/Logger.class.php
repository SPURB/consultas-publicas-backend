<?php
class Logger{
	
    /*Método construtor do banco de dados*/
	public function __construct($logPath = NULL){
		if($logPath != NULL){
			self::$LOGPATH = $logPath;
		}
	}
	
	private static $LOGPATHPROD = '/var/www/minuta.gestaourbana.prefeitura.sp.gov.br/public/apiconsultas/log/apiconsultas.log';
	private static $LOGPATHLOCAL = 'C:\\xampp\\htdocs\\apiconsultas.log';
     
    /*Evita que a classe seja clonada*/
    private function __clone(){}
	
	public function write($message){
		$logPath = file_exists(self::$LOGPATHPROD) ? self::$LOGPATHPROD : self::$LOGPATHLOCAL;
		
		if(is_writable($logPath)){
			try{
				if(is_object($message) && method_exists($message, "getMessage")){
					$message = $message->getMessage();
				}
				$ip = $_SERVER['REMOTE_ADDR'];
				$time = date("d/m/Y H:i:s");
				$fullMsg = "[".$time."][".$ip."] ".$message.PHP_EOL;
				
				$logFile = fopen($logPath, "a");//append
				fwrite($logFile, $fullMsg);
				fclose($logFile);
			}catch(Exception $ex){
				error_log($ex->getMessage());
			}
		}else{
			try{
				$logFile = fopen($logPath, "w");//write do zero
				fclose($logFile);
				$this->write($message);
			}catch(Exception $ex){
				error_log($ex->getMessage());
			}
		}
		
	}
}

?>
