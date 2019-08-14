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
	
	/**
	 * Variable que contiene un objeto con los datos del usuario logueado
	 * @var object
	 */
	private $userData;	
	
	public function Dialogs() {
	
		$storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
		$namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
		$this->userData = $storage->read($namespace);
		return $this;
	}
	
	public function PanelAsignar()
	{
		echo $this->view->partial('partials/dialogs/panelasignar.phtml');
	}
	
	public function NewTask()
	{
		$sbdb = new Application_Model_DbTable_Calendar_Subgrupos();
		$dgudb = new Application_Model_DbTable_Calendar_SubgruposUsername();
		echo $this->view->partial('partials/dialogs/newtask.phtml',array('userData'=>$this->userData,'subgrupos'=>$sbdb->GetSubGrupos($this->userData->OU_ID),'colegas'=>array()));
	}

    public function CreateEvent()
    {
        echo $this->view->partial('partials/dialogs/newevent.phtml');
    }
    
    public function CreateJEvent()
    {
        echo $this->view->partial('partials/dialogs/newjevent.phtml');
    }
    
    
    public function EndTask()
    {
        echo $this->view->partial('partials/dialogs/endtask.phtml');
    }
    
        public function EndJournalTask()
    {
        echo $this->view->partial('partials/dialogs/endjournaltask.phtml');
    }
    
    
    
    public function ViewEvent()
    {
        echo $this->view->partial('partials/dialogs/viewevent.phtml');
    }
    
    public function ViewJEvent()
    {
        echo $this->view->partial('partials/dialogs/viewjevent.phtml');
    }
    
    public function PreJournal()
    {
        echo $this->view->partial('partials/dialogs/prejournal.phtml');
    }
    
    
    public function ViewTemplate()
    {
        echo $this->view->partial('partials/dialogs/viewtemplate.phtml');
    }
    
    public function NewTemplate()
    {
        $sbdb = new Application_Model_DbTable_Calendar_Subgrupos();
        $dgudb = new Application_Model_DbTable_Calendar_SubgruposUsername();
        echo $this->view->partial('partials/dialogs/newtemplate.phtml',array('userData'=>$this->userData,'subgrupos'=>$sbdb->GetSubGrupos($this->userData->OU_ID),'colegas'=>array()));
        //echo $this->view->partial('partials/dialogs/newtemplate.phtml');
    }
    	
	public function AsignarTask()
	{
		echo $this->view->partial('partials/dialogs/asignartask.phtml');
	}

	public function AsignarTags()
	{
		// 		$u = new Application_Model_User();
		// 		$result = $u->getGroupMembers($this->userData->OU_ID);
		// 		print_r($result);
		echo $this->view->partial('partials/dialogs/asignartags.phtml');
	}
	public function AsignaryProgramarTask()
	{
		echo $this->view->partial('partials/dialogs/asignaryprogramartask.phtml');
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