<?php

class Logger{
    
	private static $LOGDIR = APP_PATH.'/logs/';
    private static $LOGFILE = 'api.log';
    private static $LOGPATH;
  
    private static $ACTIVATED = FALSE;
  
    protected function __construct(){}
     
    /*Evita que a classe seja clonada*/
    private function __clone(){}
  
	public static function startLogger($logDir = NULL){
        // self::$LOGDIR = $logDir ?? self::$LOGDIR;
        self::$LOGDIR = isset($logDir) ? $logDir : self::$LOGDIR;
        self::$LOGPATH = self::$LOGDIR.self::$LOGFILE;
        if(!is_writable(self::$LOGDIR)){
          error_log("Sem permissao de escrita no diretorio :".self::$LOGPATH);
        }else{
          if(!file_exists(self::$LOGPATH) && $logFile = fopen(self::$LOGPATH, "w")){
              fclose($logFile);
          }else{
            self::checkLogSize();
          }
          self::$ACTIVATED = TRUE;
          self::write("Iniciado o log..............................");
        }
	}
    
    private static function genMsgHeader(){
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = date("d/m/Y H:i:s");
        return "[".$time."][".$ip."] ";
    }
    
    private static function checkLogSize(){
        if(filesize(self::$LOGPATH) > 204800){// 200KB
            $newName = self::$LOGPATH."_".date('YmdHi');
            if(!rename(self::$LOGPATH, $newName)){
              error_log("Erro ao renomear o log ".self::$LOGPATH);
            }
        }
    }
	
	public static function write($message){
		
		if(self::$ACTIVATED){
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
		}else{
          error_log("Log da aplicacao nao iniciado::: ".$message);
        }
	}
    
}

?>
