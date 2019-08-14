<?php

class AjaxController extends Zend_Controller_Action
{
	
    public function init()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function indexAction()
    {
    	
    }
    
    /**
     * 
     */
    public function clientesAction()
    {
    	$clientes = new Application_Model_DbTable_Clientes();
    	$clilist = $clientes->getClientesEventosView();
    	$this->_helper->json->sendJson($clilist);
    }
    
    
    /**
     * Gets users from Core
     */
    public function getusersAction()
    {
    	//$users = new Application_Model_User();
    	$log = new Application_Model_DbTable_Calendar_EventosLog();
    	$userslist = $log->getUsersFromLog();
    	$this->_helper->json->sendJson($userslist);
    }
    
    /**
     * Gets customer affected from cal_eventos table
     */
    public function getcustomeraffectedAction()
    {
    	$ev = new Application_Model_DbTable_Calendar_Eventos();
    	$customerlist = $ev->getCustomer_AffectedFromEvents();
    	$this->_helper->json->sendJson($customerlist);
    }
    
    /**
     * Get Coordinators list
     */
    public function getcoodinatorslistAction()
    {
    	$ev = new Application_Model_DbTable_Calendar_Eventos();
    	$coordinators = $ev->getCoordinatorFromEventosSM9();
    	$this->_helper->json->sendJson($coordinators);
    	
    }
    
    /**
     * Gets CI from events 
     */
    public function getcisAction()
    {
    	$ev 	= new Application_Model_DbTable_Calendar_Eventos();
    	$cis 	= $ev->getCIFromEventos();
    	$this->_helper->json->sendJson($cis);
    	
    }
    
    /**
     * Updates event calculated and manuals times 
     */
    public function updatescomputedtimesAction()
    {
    	$id			=$this->_getParam('id');
    	$arraytimes = $this->_getParam('data');
    	//file_put_contents("valoresmodiftimes.txt",json_encode($arraytimes)." - - - - ".$id);
    	$ev 		= new Application_Model_DbTable_Calendar_Eventos();
    	$res = $ev->updateTimes($arraytimes['time_app'],$arraytimes['time_user'],$id);
    	$this->_helper->json->sendJson($res);
    }
    
}

