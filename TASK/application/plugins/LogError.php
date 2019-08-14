<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_LogApp
 * @filesource  application/plugins/LogError.php
 * @tutorial    Instanciar en application.ini con:
 *              resources.frontController.plugins.LogError = "Application_Plugin_LogError"
 *              
 * @example		$log = new Application_Plugin_LogApp();
 * 				$log->setLog('test');
 * 				o
 * 				$log->setLog('test', 6); // Donde 6 es el nivel de error.
 * 
		EMERG   = 0;  // Emergency: system is unusable
		ALERT   = 1;  // Alert: action must be taken immediately
		CRIT    = 2;  // Critical: critical conditions
		ERR     = 3;  // Error: error conditions
		WARN    = 4;  // Warning: warning conditions
		NOTICE  = 5;  // Notice: normal but significant condition
		INFO    = 6;  // Informational: informational messages
		DEBUG   = 7;  // Debug: debug messages
 */
class Application_Plugin_LogError extends Zend_Controller_Plugin_Abstract
{	
	public function setLog ($msj, $lvl=7, $extra=NULL)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	    	
    	$columnMapping = array(
    			'lvl' => 'priority',
    			'session_id'=>'session_id',
    			'msj' => 'message',
    			'error_code'=>'error_code',
    			'error_name'=>'error_name',
    			'exeption'=>'exeption',
    			'trace'=>'trace',
    			'controller'=>'controller',
    			'action'=>'action',
    			'referer'=>'referer',
    			'parametros'=>'parametros'
    	);
    	
    	$writer = new Zend_Log_Writer_Db($db, 'core_log_error', $columnMapping);
		
		$logger = new Zend_Log($writer);
		
		switch ($lvl)
		{
			case 0:
				$logger->emerg($msj,$extra);
				break;
			case 1:
				$logger->alert($msj,$extra);
				break;
			case 2:
				$logger->crit($msj,$extra);
				break;
			case 3:
				$logger->err($msj,$extra);
				break;
			case 4:
				$logger->warn($msj,$extra);
				break;
			case 5:
				$logger->notice($msj,$extra);
				break;
			case 6:
				$logger->info($msj,$extra);
				break;
			default:
				$logger->debug($msj,$extra);
				break;
		}
	}
	
}