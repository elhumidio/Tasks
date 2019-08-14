<?php
class Application_Form_Task_New extends Zend_Form 
{
	
	public function init() 
	{
		$this->setAction('/auth/login');
		
		$this->clearDecorators();
		$this->setDecorators(array('FormElements','fieldset','form'));
		
		
		$referer = $this->createElement("hidden","easyReferer")->setDecorators(array('ViewHelper'));
		$referer->setValue(Zend_Controller_Front::getInstance()->getBaseUrl().'/auth/login');
		
		$req = Zend_Controller_Front::getInstance()->getRequest();
		$appInternalReferer = $this->createElement("hidden","appInternalReferer")->setDecorators(array('ViewHelper'));
		$appInternalReferer->setValue('/r/'.base64_encode($req->getRequestUri()));
		
		
		$username = $this->createElement("text","username");
		$username->setLabel("Username")
				 ->setAttribs(array('required'=>'required','autocomplete'=>'off'))
				 ->setRequired(true)
				 ->addErrorMessage('Campo requerido')
				 ->setDecorators(
						 		array(
									'ViewHelper',
									array('label', array('class'=>'infield')),
									array('HtmlTag',array('tag'=>'p', 'class'=>'infield'))
									)
						 		);
		
		
		$password = $this->createElement("password", "password");
		$password->setLabel("Password")
				 ->setAttribs(array('required'=>'required','autocomplete'=>'off'))
				 ->setRequired(true)
				 ->addErrorMessage('Campo requerido')
				 ->setDecorators(
						 		array(
									'ViewHelper',
									array('label', array('class'=>'infield')),
									array('HtmlTag',array('tag'=>'p', 'class'=>'infield'))
									)
						 		);
		
		
		$signin = $this->createElement("submit", "submit");
		$signin->setAttrib('class', 'login')
			   ->setLabel("Log in")->setIgnore(true)->setDecorators(array('ViewHelper'));
		
		
		
		$this->addElements(array($referer,$appInternalReferer,$username,$password,$signin));
	
	}

}

?>