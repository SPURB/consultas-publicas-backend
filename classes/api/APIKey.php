<?php

class APIKey{
    
    private static $KEYS = array();
    
    private static function openPropertiesFile(){
        $filePath = APP_PATH.'/properties/keys.properties';
		if($propFile = fopen($filePath, "r")){
			while(!feof($propFile)){
				$line = fgets($propFile);
				$split = explode(":", $line);
                $key = trim($split[0]);
                $val = trim($split[1]);
                self::$KEYS[$key] = $val;
			}
            fclose($propFile);
        }else{
            throw new Exception("Erro ao abrir o arquivo properties: $filePath");
        }
	}
    
    public static function check($token){
        return self::generate() == $token;
    }

    public static function generate(){
        $hora = gmdate("G");//Hora sem zero a esq
        $mes = gmdate("n");//Mes sem zero a esq
        $dia = gmdate("j");//Dia do mes sem zero a esq
        $key = self::getKey();
        $result = "hora:".$hora."_mes:".$mes."_dia:".$dia."_bicho:".$key;
        return md5($result);
    }
    
    private static function getKey(){
        self::openPropertiesFile();
        return array_shift(
            array_filter(
                self::$KEYS, 
                function($n){
                    $min = gmdate("i");
                    if($min == 0){
                        $min = 60;
                    }
                    return $n >= $min;
                },
                ARRAY_FILTER_USE_KEY)
        );
    }

}

?>