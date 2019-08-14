<?php
class Application_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	private $_acl;
	private $_auth;
	private $_access= false;

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{		
		self::checkEasyaOnline();
		
		// Module, Controller and action called
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();    
        
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		
		
		
		if($controller=='error') return;
		
		// Is the user authentified ?
		if ($auth->hasIdentity()):
            //echo $this->_auth->getIdentity();
// 			Zend_Session::rememberMe(3600);
			
            // Cargo datos del usuario
			$user = $auth->getStorage()->read();
			if(!is_array($user->role)):
				$user->role = array_reverse(unserialize($user->role));
			endif;
			
			$this->_acl = new Application_Model_Acl($user);

			// Ahora recorremos todos los roles del usuario y si alguno tiene acceso al recurso modificamos la variable
			// $this->_access para que luego al denegar acceso no se haga.
			foreach($user->role as $role):
				if ($this->_acl->has($controller) && $this->_acl->isAllowed($role, $controller, $action)):
					$this->_access = true; 
				endif;
			endforeach;

			
			//self::denyAccess();
			
			// Verifico si está restringido a un cliente
			if (!is_null($user->cliente) && $controller!='auth'):
				self::forceModule($user->cliente,$module);
			endif;
		
		else:
			// No hay identidad, por lo tanto no estamos autentificados
			switch($controller):
				case 'backprocess':
				case 'auth':
				case 'rss':
				case 'error':
				case 'test':Break;// No hacemos nada si es alguno de estos controladores
				case 'index':
				default:self::logoutModule();Break;// Cargamos la pantalla de login
			endswitch;
		endif;  
		
		$this->_request->setParam('_access', $this->_access);
		
		$front = Zend_Controller_Front::getInstance();
		$bootstrap = $front->getParam('bootstrap');
		$view = $bootstrap->getResource('view');
		
		$view->assign(array(
				'acl'=>$this->_acl
		));
    }

        
	private function denyAccess()
	{
		if(!$this->_access):
		$this->_request->setModuleName('default');
		$this->_request->setControllerName('error');
		$this->_request->setActionName('denied');
		endif;
	}
	
	private function logoutModule()
	{
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		$auth->clearIdentity();
		$this->_request->setModuleName('default');
		$this->_request->setControllerName('auth');
		$this->_request->setActionName('login');
		
	}
	
	private function forceModule($module,$moduleActive)
	{
		if($module != $moduleActive):
			$this->_response->setRedirect("$module");
		endif;
	}
	
	private function checkEasyaOnline()
	{
		$db = new Application_Model_DbTable_Easya_AppConfig();
		 
		if(!$db->appStatus()):
			$this->_request->setModuleName('default');
			$this->_request->setControllerName('auth');
			$this->_request->setActionName('mantenimiento');
			$auth = Zend_Auth::getInstance();
			$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
			$auth->clearIdentity();
		endif;
	}
}
