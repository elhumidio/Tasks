<?php
class Application_Plugin_Lang extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{		
		Application_Model_Lang::change($request->getParam('changeLang',false));
    }
}
