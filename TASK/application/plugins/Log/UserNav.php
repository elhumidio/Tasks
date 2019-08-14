<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_UserNav
 * @filesource  application/plugins/Log/UserNav.php
 * @tutorial    Instantiate in application.ini with;
 *              resources.frontController.plugins.LogUserNav = "Application_Plugin_Log_UserNav"

 *              Uso:
 *              $l_app = new Application_Plugin_UserLogNav();
 *              $l_app->setLog('Seccion'); // Nivel de error: INFO
 *              
 * @desc        Registra en la Base de datos las secciones que visita cada usuario.
 */
class Application_Plugin_Log_UserNav extends Application_Plugin_Log_Abstract
{
	
    /**
     * Los valores del array no se guardaran en la base de datos
     * @var array
     */
    private $excludeControllers = array();
    private $excludeModules = array();
    private $excludeActions = array();
    
    
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$front = Zend_Controller_Front::getInstance();
		$controllerName = $front->getRequest()->getControllerName();
		$moduleName = $front->getRequest()->getModuleName();
		$actionName = $front->getRequest()->getActionName();
		
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		$data = $auth->getIdentity();
		
		if(isset($data->username) && !in_array($controllerName,$this->excludeControllers) && !in_array($moduleName,$this->excludeModules) && !in_array($actionName,$this->excludeActions)){
			self::setLog('',6,array( 'username' => $data->username, 'module' => $moduleName, 'controller' => $controllerName, 'action' => $actionName ));
		}
    }
    
    
    public function setLog ($msj, $lvl=6, $extra=NULL)
    {
    	$this->dbName = 'core_log_users_nav';
    	$this->columnMapping = array(
    			'username'=>'username', 
    			'controller' => 'controller', 
    			'module'=>'module', 
    			'action'=>'action'
    			);
    	parent::setLog($msj,$lvl,$extra);
    }
}