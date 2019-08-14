<?php

class Soap_IndexController extends Zend_Controller_Action
{
	
	private $_WSDL_URI;
	private $error = false;
	
		
	public function preDispatch() {
	$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session('auth_user'));
		if (!$auth->hasIdentity()) {
			$config = array(
					'accept_schemes' => 'basic',
					'realm' => 'EasyATASK Webservices',
					'nonce_timeout' => 3600,
			);
			// make sure the class file be found
			$adapter = new Application_Model_AuthHttp($config);
	
			// assuming it is in the Zend_Controller_Action
			$adapter->setRequest($this->getRequest());
			$adapter->setResponse($this->getResponse());

			// $result will be a Zend_Auth_Result object
			$result = $auth->authenticate($adapter);
	
			if (!$result->isValid()) {
				// login failed
				$auth->clearIdentity();
				echo '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   						<soapenv:Body>
      						<soapenv:Fault>
         						<faultcode>soapenv:Login Exception</faultcode>
         						<faultstring>Authentication failed or permision denied</faultstring>
         						<detail/>
      						</soapenv:Fault>
   						</soapenv:Body>
						</soapenv:Envelope>
				';
				
				$this->error = true;
			}
		}
	}
	
	public function indexAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		if (!$this->error){
			$configini = Zend_Registry::get ( 'configuracion.ini' );
			$this->_WSDL_URI = $configini["urlportal"]."/soap?wsdl";
		
			$WebService = new Application_Service_WebService();
			$WebService->setWSDL_URI($this->_WSDL_URI);
				
			if(isset($_GET['wsdl'])) {
				//return the WSDL
				$WebService->hadleWSDL();
			} else {
				//handle SOAP request
				$WebService->handleSOAP();
			}
		}
	}
    
    
}

