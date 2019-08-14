<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_UserLogAccess
 * @filesource  application/plugins/UserLogAccess.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.UserLogAccess = "Application_Plugin_UserLogAccess"

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
class Application_Plugin_UserLogAccess extends Zend_Controller_Plugin_Abstract
{
	protected $l;
    
    public function setLog($extras)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$colAction = array('username'=>'username','ip'=>'ip','browser'=>'browser','stage'=>'stage');
		$wriAction = new Zend_Log_Writer_Db($db, 'log_users_access', $colAction);
		$l = new Zend_Log($wriAction);
		$l->info('In',$extras);
		//array('username'=>'', 'ip'=>'', 'browser'=>'')
    }
}