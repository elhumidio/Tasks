<?php

class Zend_Controller_Action_Helper_Util extends Zend_Controller_Action_Helper_Abstract {

	function getPrueba($a){
		return "devuelve: ".$a;	
	}
	
	public function escribeLocal($data, $append = false){
	
	    $nombre_archivo = "test-local.txt";
	     
	    if (!$append){
	        if(file_exists($nombre_archivo)) unlink($nombre_archivo);
	    } else {
	        $data = "\n\n".$data;
	    }
	        
	    if($archivo = fopen($nombre_archivo, "a")) {
	        fwrite($archivo, $data);
	        fclose($archivo);
	    }
	
	}
	
}
