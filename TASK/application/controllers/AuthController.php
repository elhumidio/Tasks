<?php
class AuthController extends Zend_Controller_Action 
{
	
	public function init()
	{
		$this->_helper->layout->setLayout ( 'login' );
		$this->view->headLink()->appendStylesheet ( '/estilos/css/login.css' );
		$this->view->headScript()->appendFile ( '/estilos/js/jquery/plugins/jquery.infieldlabel.mod.js' );
		$this->view->urlOut = $this->view->baseUrl('/auth/login');
	}
	
	
	
	public function indexAction() 
	{
		
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		
		if($auth->hasIdentity()) $this->_redirect ('/');
		
		return $this->_forward('login');
	}
	
	
	
	
	public function loginAction()
	{
	
		$form = new Application_Form_Login();
		$login = new Application_Model_Login_Authenticate();
		
		if ($this->getRequest()->isPost())
		{
			if ($form->isValid ( $_POST ))
			{
				
				if ($login->Authenticate($form->getValues(),array('Local','Ad')))
				{
					// Aquí estamos logueados.
					
				}
			}
		} 
		else if ($this->_getParam('sso',isset($_COOKIE['_a'])))
		{
			$sso = new Application_Model_Login_LoginSso();
			$sso->login($this->_getParam('sso',$_COOKIE['_a']));
			
			$r = $this->_getParam('r','/');
			$r = $r!='/'?base64_decode($r):$r;
			$r = str_replace(Zend_Registry::get('urlBase'), '', $r);
			$login->__set('baseuri', $r);
		}
		
		
		$this->view->form = $form;
		$this->view->errorMessage = $login->__get('msj');

		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		$identity = Zend_Auth::getInstance()->getIdentity(); //datos del usuario logueado
		
		if($auth->hasIdentity())
		{
			
				if(in_array("Task_Restricted",$identity->role))
				{
						
					$this->_redirect ('/calendar/index/ijournal');			
				}
				else{
					$this->_redirect ('/cambioturno');
				}

		 
		}
	}
	
	
	
	
	public function logoutAction()
	{
		$this->_helper->layout->setLayout ( 'base' );
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session(NAMESPACE_APP,NAMESPACE_MEMBER));
		if($auth->hasIdentity()) $this->view->urlOut = '/auth/logout';
		$auth->clearIdentity();
	}
	
	public function mantenimientoAction()
	{
	
	}
	
}

?>