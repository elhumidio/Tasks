<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_Error
 * @filesource  application/plugins/Log/Error.php
 * @tutorial    Instanciar en application.ini con:
 *              resources.frontController.plugins.LogError = "Application_Plugin_Log_Error"
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
class Application_Plugin_Log_Error extends Application_Plugin_Log_Abstract
{
	
	public function setLog ($msj, $lvl=6, $extra=NULL)
    {
    	$this->dbName = 'core_log_error';
    	$this->columnMapping = array(
    			'lvl' => 'priority', 
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
		parent::setLog($msj,$lvl,$extra);
	}
}