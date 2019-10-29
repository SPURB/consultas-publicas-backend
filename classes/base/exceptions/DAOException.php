<?php

class DAOException extends Exception{
	public function __construct($msg, $query = NULL, $valuesArray = array(), $code = 0){
        if(!empty($query)){
            $msg.= " $query ";
            foreach($valuesArray as $v){
                $msg.=" | $v";
            }
        }
		parent::__construct($msg, $code);
	}
}

?>