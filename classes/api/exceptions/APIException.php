<?php

require_once APP_PATH.'/classes/base/main/Logger.php';

class APIException extends Exception{
	private $log;

	public function __construct($message, $code = NULL){
		parent::__construct($message, $code);
		//$log = new Logger();
		//$log->write($message);
	}


}

?>