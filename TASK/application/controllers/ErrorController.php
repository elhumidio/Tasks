<?php

class ErrorController extends Zend_Controller_Action
{
	
	public function init()
	{
		$this->_helper->layout->setLayout('error');
		
	}
	public function indexAction()
	{
		
	}
	
    public function errorAction()
    {
    	$lvl = 3;
    	$errors = $this->_getParam('error_handler');
    	$params = $errors->request->getParams();
    
    	switch ($errors->type) {
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
    
    			// 404 error -- controller or action not found
    			$this->getResponse()->setHttpResponseCode(404);
    			$this->view->codeError = 404;
    			$this->view->message = $this->view->translate('Page not found');
    			$lvl = 4;
    			break;
    
    		default:
    			// application error
    			$this->getResponse()->setHttpResponseCode(500);
    			$this->view->codeError = 500;
    			$this->view->message = $this->view->translate('Application error');
    			$lvl = 2;
    			break;
    	}
    
    	if($errors->request->controller!='ajax'):
    
    
    	$this->view->HeadTitle($this->view->message);
    	 
//      print_r($params);
//      die();
    
    	// conditionally display exceptions
    	if ($this->getInvokeArg('displayExceptions') == true) {
    		$this->view->exception = $errors->exception;
    	}
    	 
    	$this->view->request   = $errors->request;
    	
    	if(!$params['_access']):    
    		$this->_request->setModuleName('default');
	    	$this->_request->setControllerName('error');
	    	$this->_request->setActionName('denied');
    	endif;
    	
    	else:
    
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	// conditionally display exceptions
    	if ($this->getInvokeArg('displayExceptions') == true) {
    		Application_Plugin_Log_Firebug::Log($errors->exception->getMessage(),Zend_Log::ALERT);
    		$trace = explode('#',$errors->exception->getTraceAsString());
    		foreach($trace as $v):
    		Application_Plugin_Log_Firebug::Log($v,Zend_Log::ALERT);
    		endforeach;
    	}
    
    	echo json_encode($errors->request->getParams());
    	 
    	endif;

    	$log = new Application_Plugin_Log_Error();
    	$log->setLog($errors->exception->getMessage(), $lvl, array('error_code' => $this->view->codeError, 'error_name' => $this->view->message, 'trace' => $errors->exception, 'exeption' => $errors->type, 'controller'=>$params['controller'], 'action'=>$params['action'], 'referer'=>((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:NULL), 'parametros'=>serialize($params)));
    	 
    	 
    }
    
    
    
    public function csrfForbiddenAction()
    {
    	
    }
    
    public function deniedAction()
    {
    	// action body
    }
}