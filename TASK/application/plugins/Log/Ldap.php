<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_Ldap
 * @filesource  application/plugins/Log/Ldap.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.LogLdap = "Application_Plugin_Log_Ldap"

 *              Uso:
 *              $l_app = new Application_Plugin_LdapLog();
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
class Application_Plugin_Log_Ldap extends Zend_Controller_Plugin_Abstract
{
	protected $l;
    protected $emailFrom	= 'prd_app@t-systems.es';
    protected $emailTo		= 'juanignacio.badia@t-sistems.es';
    
	 
	
    public function setLog($accion,$extras=NULL)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$colAction = array('lvl' => 'priority', 'msj' => 'message',);
		$wriAction = new Zend_Log_Writer_Db($db, 'log_ldap', $colAction);
		$l = new Zend_Log($wriAction);
		$l->debug($accion, $extras);

    }
    
}