<?php
class Application_Plugin_Log_Firebug extends Zend_Controller_Plugin_Abstract
{
	//protected $logger;
	
	public function __construct(){
		
	}
	
	static public function Log($msj,$priority = Zend_Log::INFO)
	{
		$writer = new Zend_Log_Writer_Firebug();
		$logger = new Zend_Log($writer);
		$logger->log($msj,$priority);
	}
}