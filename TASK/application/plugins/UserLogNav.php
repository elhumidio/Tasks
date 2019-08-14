<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_UserLogNav
 * @filesource  application/plugins/UserLogNav.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.UserLogNav = "Application_Plugin_UserLogNav"

 *              Uso:
 *              $l_app = new Application_Plugin_UserLogNav();
 *              $l_app->setLog('Seccion'); // Nivel de error: INFO
 *              
 * @desc        Registra en la Base de datos las secciones que visita cada usuario.
 */
class Application_Plugin_UserLogNav extends Zend_Controller_Plugin_Abstract
{
	
    /**
     * Los valores del array no se guardaran en la base de datos
     * @var array
     */
    private $excludeControllers = array();
    private $excludeModules = array();
    private $excludeActions = array('refreshbar','refresheventos','getprocesoseventos','gantt','ruta','tiempos','log');
    
    
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$front = Zend_Controller_Front::getInstance();
		$controllerName = $front->getRequest()->getControllerName();
		$moduleName = $front->getRequest()->getModuleName();
		$actionName = $front->getRequest()->getActionName();
    	
    	$storage = new Zend_Auth_Storage_Session ();
		$data = $storage->read ();
		
		if(isset($data->username) && !in_array($controllerName,$this->excludeControllers) && !in_array($moduleName,$this->excludeModules) && !in_array($actionName,$this->excludeActions)){
			$colAction = array('username'=>'username', 'controller' => 'controller', 'module'=>'module', 'action'=>'action');
			$wriAction = new Zend_Log_Writer_Db($db, 'log_users_nav', $colAction);
			$l = new Zend_Log($wriAction);
			$l->info('', array(
							'username' => $data->username, 
							'controller' => $controllerName,
							'module' => $moduleName,
							'action' => $actionName
							));
		}
    }
}