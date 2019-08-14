<?php
class Application_Plugin_Token extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if($request->getControllerName()=='ajaxXXX' && $request->getActionName()!='csrf-forbiddenXXX'): //modified temporarily in order to avoid the problem when a new token is required

			$token = $request->getParam('token',false);
		
			if(in_array(APPLICATION_ENV,array('local','development'))):Application_Plugin_Log_Firebug::Log("Token petición: $token");endif;
			 
			$mysession = new Zend_Session_Namespace('token');
			$hash = $mysession->token;
			//die($hash.' Token: '.$token );
			if(!isset($token) || $hash != $token):
				//$this->getResponse()->clearHeaders()->setHttpResponseCode(403)->sendResponse(); // Acceso no permitido
				$log = new Application_Plugin_Log_Error();
				$log->setLog('Incorrect security token'.' Hash Value: '.$hash.'  <----> Token Value: '.$token, 3, array('session_id'=>Zend_Session::getId(), 'error_code' => 403, 'error_name' => 'Acceso no permitido', 'exeption' => 'EXCEPTION_TOKEN_INCORRECT', 'controller'=>$request->getControllerName(),'trace'=>new Zend_Db_Expr('NULL') ,'action'=>$request->getActionName(), 'referer'=>((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:NULL), 'parametros'=>serialize($request->getParams())));
				$request->setActionName('csrf-forbidden')->setControllerName('ajax')->setDispatched(false); // Muestro el error ajax
				
			endif;
			
		endif;
    }
}