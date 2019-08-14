<?php

class IndexController extends Zend_Controller_Action
{
	
	
	
    public function init()
    {
    	
    	
    }

	public function testAction(){
    
       /* if (APPLICATION_ENV == 'local') {
            $this->_helper->Util->escribeLocal("TEST3", true);
        }

		$sm9 = New Application_Model_SM9Change();

		$sm9 = New Application_Model_SM9Change();
        $test = $sm9->StartWorkTaskXML('T0002083394', 'Task Journal');
		die ;

		$task = $sm9->RetrieveChangeTaskXML('T0002083394', 'Journal');

		$term = array();
		$term['TaskID'] = 'T0002083394';
		$term['ResolutionCode'] = 'COMPLETE';
		$term['ActualStart'] = $task['PlannedStart'];
		$term['ActualEnd'] = $task['PlannedEnd'];
        $term['ClosureComments'] = array('Done');

		$test = $sm9->CloseTsiChangeTaskXML2($term,'Journal');

		

        die('<br>FIN');*/
        /* $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $e = new Application_Model_DbTable_Calendar_Eventos();
        $result = $e->getEventosTicketAbierto();
        $this->_helper->json->sendJson($result);*/
                $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
                  error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors',1);
    $model  = new Application_Model_Calendar_EasyInterventionEvents();
  //  error_reporting(1);
		/*$b = new Application_Model_Backprocess_BackprocessMethods();
          $b->cierraticketseventos();*/
    //$create = new Application_Model_Calendar_EasyInterventionEvents();
   // $model->CreateEventsFromIntervenciones();
   //  $inter = new Application_Model_DbTable_Easya_Intervenciones();
   //  $r = $inter->VerifyInterventionPassToHistoric();die;
    
    $model->UpdateInterventionAllFields();
	}
	
	public function verpopupAction()
	{
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setRender ( "/index/popup" );
	}
	   
    public function indexAction()
    {
    	$this->view->headScript()->appendFile ( '/estilos/js/EasyBitacora.js' );
    	
    	$this->view->headLink()->appendStylesheet ( '/estilos/css/bitacora.css' );
    	
    	$this->view->headScript()->appendFile ( '/estilos/js/select2.min.js' );
    	$this->view->headLink()->appendStylesheet ( '/estilos/css/select2.min.css');
    	
    
    	
        if(in_array("Task_Restricted",$this->view->user->role))
        {
            $this->_redirect ( '/calendar/index/ijournal' );        
        }
        else $this->_redirect ( 'cambioturno' );        
    }
    
    
    
    public function changelangAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	Application_Model_Lang::change($this->_getParam('lang',false));
    
    	$this->_redirect ( '/' );
    
    }
    
    public function serversideAction()
    {
    	$this->_helper->layout->disableLayout();
    	$tabla = $this->_getParam('tabla');
    
    	switch($tabla)
    	{
    		case 'user':
    			$this->view->output = Application_Model_Datatables_Datatables::generateUserTable();
    			Break;
    			 
    		case 'app_recursos':
    			$this->view->output = Application_Model_Datatables_Datatables::generateResursosTable();
    			Break;
    
    		case 'app_roles':
    			$this->view->output = Application_Model_Datatables_Datatables::generateRolesTable();
    			Break;
    			 
    		case 'app_modulos':
    			$this->view->output = Application_Model_Datatables_Datatables::generateModulesTable();
    			Break;
    
    		default:
    			 
    			Break;
    	}
    }
    
    /**
     * Gets empty view
     */
   	public function exportviewAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->view->userrole = json_encode($this->view->user->role);
    	$this->_helper->viewRenderer->setRender ( 'export' );
    }
    
    /**
     * Gets results from event query
     */
    public function exporttableviewAction()
    {
    	$data = $this->_getParam('data');
    	//	file_put_contents("filters.txt",json_encode($data));
    	$this->_helper->layout->disableLayout();
    	$ev = new Application_Model_DbTable_Calendar_Eventos();
    	$this->view->userrole = json_encode($this->view->user->role);
    	if(isset($data)){
    		
    		$ret = $ev->getEventsFiltered($data);
    		if($ret == "KO")
    		{
    			$this->_helper->layout->disableLayout();
    			$this->_helper->viewRenderer->setNoRender();
    			$this->_helper->json->sendJson($ret);
    		}
    		else{
    			$this->view->data = $ret;
    		}
    		
    	}
    		
    	$this->_helper->viewRenderer->setRender ( 'exporttable' );
    }
}

