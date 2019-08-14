<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_UserAccess
 * @filesource  application/plugins/Log/UserAccess.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.LogUserAccess = "Application_Plugin_Log_UserAccess"

 *              Uso:
 *              $l_app = new Application_Plugin_UserLogAccess();
 *              $l_app->setLog(); // Nivel de error: INFO
 *             
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
class Application_Plugin_Log_UserAccess extends Application_Plugin_Log_Abstract
{
	public function setLog ($extra=NULL,$lvl=6)
    {
    	$this->dbName = 'core_log_users_access';
    	$this->columnMapping = array(
    			'username'=>'username',
    			'ip'=>'ip',
    			'browser'=>'browser',
    			'stage'=>'stage'
    			);
		parent::setLog('',$lvl,$extra);
	}
    
}