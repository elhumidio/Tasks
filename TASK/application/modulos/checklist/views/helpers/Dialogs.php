<?php
/**
 *
 * @author 9jibadia
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * MenuActivo helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Dialogs {
	
	public $view;
	
	
	public function Dialogs() {
	
		$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
		$namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
		$this->userData = $storage->read($namespace);
		return $this;
	}
	
	
	public function NewTask()
	{
		echo $this->view->partial('partials/dialogs/newtask.phtml');
	}
	
	public function NewEvent()
	{
		echo $this->view->partial('partials/dialogs/newevent.phtml');
	}
	
	public function NewTemplate()
	{
		$sbdb = new Application_Model_DbTable_Calendar_Subgrupos();
		$dgudb = new Application_Model_DbTable_Calendar_SubgruposUsername();
		echo $this->view->partial('partials/dialogs/newtemplate.phtml',array('userData'=>$this->userData,'subgrupos'=>$sbdb->GetSubGrupos($this->userData->OU_ID),'colegas'=>array()));
		//echo $this->view->partial('partials/dialogs/newtemplate.phtml');
	}
	
	public function AsignarTags()
	{
		echo $this->view->partial('partials/dialogs/asignartags.phtml');
	}
	
	public function AsignarTak()
	{
		echo $this->view->partial('partials/dialogs/asignartask.phtml');
	}

	public function createRecurringEvent()
	{
		echo $this->view->partial('partials/dialogs/recurringevent.phtml');
	}
	
	/**
	 * Sets the view field
	 *
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}