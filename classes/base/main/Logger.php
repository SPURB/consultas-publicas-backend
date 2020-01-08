<?php

class Logger{
    
	private static $LOGPATH = APP_PATH.'/logs/api.log';
	
   /*Método construtor*/
	public function __construct($logPath = NULL){
		if($logPath != NULL){
			self::$LOGPATH = $logPath;
		}
        self::checkLogSize();
	}
     
    /*Evita que a classe seja clonada*/
    private function __clone(){}
    
    private static function genMsgHeader(){
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = date("d/m/Y H:i:s");
        return "[".$time."][".$ip."] ";
    }
    
    private static function checkLogSize(){
        if(file_exists(self::$LOGPATH) && is_writable(self::$LOGPATH)){
            if(filesize(self::$LOGPATH) > 102400){//> 100KB
                $newName = self::$LOGPATH."_".date('YmdHi');
                rename(self::$LOGPATH, $newName);
            }
        }
    }
	
	public static function write($message){
		
		if(is_writable(self::$LOGPATH)){
			try{
				if(is_array($message)){
					$str = "";
					$first = TRUE;
					foreach($message as $item){
						if(!$first){
							$str.=",";
						}
						$str.=$item;
						$first = FALSE;
					}
					$message = $str;
				}
				else if($message instanceof Exception){
					$message = $message->getMessage()." = ".$message->getTraceAsString();
                    
				}
				$fullMsg = self::genMsgHeader().$message.PHP_EOL;
				
				$logFile = fopen(self::$LOGPATH, "a");//append
				fwrite($logFile, $fullMsg);
				fclose($logFile);
			}catch(Exception $ex){
				error_log($ex->getMessage());
			}
		}else{
            if($logFile = fopen(self::$LOGPATH, "w")){//write do zero
              fclose($logFile);
              self::write($message);
            }else{
              error_log("Não foi possivel criar o log ".self::$LOGPATH);
            }
		}
		
	}
    
}

?>
