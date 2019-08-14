<?php 

class Application_Service_WebService
{
	private $_WSDL_URI;
	
	

	public function __construct()
	{
		
	}
	
	
	public function hadleWSDL() {
		$autodiscover = new Zend_Soap_AutoDiscover();
		$autodiscover->setClass('Application_Service_WebServiceMetodos');
		$autodiscover->handle();
	}
	
	public function handleSOAP() {
		$soap = new Zend_Soap_Server($this->_WSDL_URI);
		$soap->setEncoding('UTF-8');
		$soap->setClass('Application_Service_WebServiceMetodos');
		$soap->handle();
	}
	
	
	/**
	 * @return the $_WSDL_URI
	 */
	public function getWSDL_URI() {
		return $this->_WSDL_URI;
	}
	
	/**
	 * @param field_type $_WSDL_URI
	 */
	public function setWSDL_URI($_WSDL_URI) {
		$this->_WSDL_URI = $_WSDL_URI;
	}
	
}

?>