<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_UserAction
 * @filesource  application/plugins/Log/UserAction.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.LogUserAction = "Application_Plugin_Log_UserAction"

 *              Uso:
 *              $l_app = new Application_Plugin_UserLogAction();
 *              $l_app->setLog('Accion'); // Nivel de error: INFO
 *              $l_app->setLog('Accion',5); // Nivel de Error Manual
 *              
 *              Niveles de aviso:
 *              EMERG   = 0;  // Emergency: system is unusable
 *			    ALERT   = 1;  // Alert: action must be taken immediately
 *			    CRIT    = 2;  // Critical: critical conditions
 *				ERR     = 3;  // Error: error conditions
 *				WARN    = 4;  // Warning: warning conditions
 *				NOTICE  = 5;  // Notice: normal but significant condition
 *				INFO    = 6;  // Informational: informational messages
 *				DEBUG   = 7;  // Debug: debug messages
 *
 * @desc        Registra en la Base de datos las secciones que visita cada usuario.
 */
class Application_Plugin_Log_UserAction extends Application_Plugin_Log_Abstract
{
	public function setLog ($msj, $lvl=6, $extra=NULL)
    {
    	$this->dbName = 'core_log_users_action';
    	$this->columnMapping = array(
    			'lvl' => 'priority', 
    			'action' => 'message', 
    			'username'=>'username'
    			);
		parent::setLog($msj,$lvl,$extra);
	}
}