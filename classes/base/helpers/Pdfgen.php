<?php

require_once 'dompdf/lib/html5lib/Parser.php';
require_once 'dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'dompdf/lib/php-svg-lib/src/autoload.php';
require_once 'dompdf/src/Autoloader.php';

Dompdf\Autoloader::register();
// reference the Dompdf namespace
use Dompdf\Dompdf;

class PdfGen{

	public function __construct(){}

	public static function generate($html, $download=TRUE, $nomeArquivo="document", $pathArquivo=NULL){
		$result = TRUE;
		// instancia dompdf
		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);

		// (Opcional) 
		$dompdf->setPaper('A4', 'landscape');

		// Renderiz HTML como PDF
		$dompdf->render();

		// Saída PDF p Browser
		/*
		if($download === TRUE){
			$dompdf->stream();
		}else{
			exec("net use z: /delete");

			$output = $dompdf->output();
			if($pathArquivo == NULL){
				$pathArquivo = "\\\\spurbsp01\\Informatica\\pdf";
			}
			exec("net use z: ".$pathArquivo);

	    	file_put_contents("z:\\".$nomeArquivo.".pdf", $output);
	    	if(!file_exists("z:\\".$nomeArquivo.".pdf")){
	    		$result = FALSE;
	    	}
	    	exec("net use z: /delete");
		}
		return $result;
		*/
		return $dompdf->output();
	}

}



?>