<?php

// This a copy taken 2008-08-21 from http://siphon9.net/loune/f/ntlm.php.txt to make sure the code is not lost.

// For more information see:

// http://blogs.msdn.com/cellfish/archive/2008/08/26/getting-the-logged-on-windows-user-in-your-apache-server.aspx


// NTLM specs http://davenport.sourceforge.net/ntlm.html

class WindowsAuth{

	private $user;
	private $workstation;
	private $domain;

	public function __get($campo) {
		return $this -> $campo;
	}

	public function __set($campo, $valor) {
		$this -> $campo = $valor;
	}

	private function get_msg_str($msg, $start, $unicode = true) {

		$len = (ord($msg[$start+1]) * 256) + ord($msg[$start]);

		$off = (ord($msg[$start+5]) * 256) + ord($msg[$start+4]);

		if ($unicode)

			return str_replace("\0", '', substr($msg, $off, $len));

		else

			return substr($msg, $off, $len);

	}

	public static function getUser(){

		$headers = getallheaders();


		if (!isset($headers['Authorization'])){

			header('HTTP/1.1 401 Unauthorized');

			header('WWW-Authenticate: NTLM');

			exit;

		}


		$auth = $headers['Authorization'];


		if (substr($auth,0,5) == 'NTLM ') {

			$msg = base64_decode(substr($auth, 5));

			if (substr($msg, 0, 8) != "NTLMSSP\x00")

				die('error header not recognised');


			if ($msg[8] == "\x01") {

				$msg2 = "NTLMSSP\x00\x02"."\x00\x00\x00\x00". // target name len/alloc

					"\x00\x00\x00\x00". // target name offset

					"\x01\x02\x81\x01". // flags

					"\x00\x00\x00\x00\x00\x00\x00\x00". // challenge

					"\x00\x00\x00\x00\x00\x00\x00\x00". // context

					"\x00\x00\x00\x00\x30\x00\x00\x00"; // target info len/alloc/offset


				header('HTTP/1.1 401 Unauthorized');

				header('WWW-Authenticate: NTLM '.trim(base64_encode($msg2)));

				exit;

			}

			else if ($msg[8] == "\x03") {

				$user = $this->get_msg_str($msg, 36);

				$domain = $this->get_msg_str($msg, 28);

				$workstation = $this->get_msg_str($msg, 44);

				return $user;

			}

		}
	}


}

?>