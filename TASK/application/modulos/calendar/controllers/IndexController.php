<?php

class Calendar_IndexController extends Zend_Controller_Action
{
	
    public function init()
    {
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/fullcalendar.css','screen' );
    	$this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/fullcalendar.print.css','print' );
    	$this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery/plugin/fullcalendar.js' );
    
    	$this->view->headLink()->appendStylesheet ( '/estilos/css/animate.css','screen,print' );
        $this->view->headLink()->appendStylesheet ( '/estilos/css/jquery.tagit.css','screen,print' );
        $storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
        $namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
        $this->userData = $storage->read($namespace);
    	Application_Model_Token::Token();
    	$this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/notify.min.js' );
      //var_dump($this->userData->role);
    	  	if(in_array("Task_Restricted",$this->userData->role))
    	    $this->_helper->viewRenderer->setRender ( "ijournal" );
    }
	
	
  
    public function indexAction()
    {
    
        
        $this->_helper->layout->setLayout ( 'general' );
        $Tags = new Application_Model_Calendar_Tags();
        
        $TagsClients = new Application_Model_DbTable_Calendar_Eventos ();
        $this->view->TagsUser =$Tags->GetUserTags();
        // var_dump($this->view->TagsUser);
        // die();
        $tes = array(
                                array('name' =>'Evening', 'filter' =>'a.turno'),
                                array('name' =>'Ávila', 'filter' =>'a.centro'),
                                array('name' =>'BCN - 22@', 'filter' =>'a.centro'),
                                array('name' =>'Bitacora', 'filter' =>'a.origen'),
                                array('name' =>'Checklist', 'filter' =>'a.origen'),
                                array('name' =>'Morning', 'filter' =>'a.turno'),
                                array('name' =>'Night', 'filter' =>'a.turno'));
                                
                                //array('name' =>'jbadia', 'filter' =>'c.username'));
       // $tes = array_merge($this->view->TagsUser,$tes);
       // $this->view->TagsBase = array_diff($this->view->TagsBase, $this->view->TagsUser);
       //$tes = array_map("unserialize", array_unique(array_map("serialize", $tes)));
       $te0 = $this->view->TagsUser;
        $this->view->ClientsBase = $TagsClients->GetClients(); 
       $this->view->GroupBase = $TagsClients->GetGroups();
       

       $te1 = $tes;
       $this->view->TagsBase = self::arrayRecursiveDiff($te0,$te1);
    }

	 public function testAction()
    {	

	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->urlportalproyectos = $this->urlportal.'/projects';
        $wsdl = $this->urlportalproyectos.'/soap?wsdl';
        $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1, 'user_agent' => 'PHPSoapClient'));
        $estado = $client->consultaEstadoTarea("319e5f0524fed0",new SoapParam($a, 138633));
        echo $estado;

        
    }
    
    
    public function ijournalAction()
    {
        
     //   $this->_helper->layout->setLayout ( 'general' );
        $Tags = new Application_Model_Calendar_Tags();
        
        $TagsClients = new Application_Model_DbTable_Calendar_Eventos ();
        $this->view->TagsUser =$Tags->GetUserTags();
        // var_dump($this->view->TagsUser);
        // die();
        $tes = array(
                                array('name' =>'Evening', 'filter' =>'a.turno'),
                                array('name' =>'Ávila', 'filter' =>'a.centro'),
                                array('name' =>'BCN - 22@', 'filter' =>'a.centro'),
                                array('name' =>'Bitacora', 'filter' =>'a.origen'),
                                array('name' =>'Checklist', 'filter' =>'a.origen'),
                                array('name' =>'Morning', 'filter' =>'a.turno'),
                                array('name' =>'Night', 'filter' =>'a.turno'));
       $te0 = $this->view->TagsUser;
        $this->view->ClientsBase = $TagsClients->GetClients(); 
       $this->view->GroupBase = $TagsClients->GetGroups();
       

       $te1 = $tes;
       $this->view->TagsBase = self::arrayRecursiveDiff($te0,$te1);
    }


    
    function arrayRecursiveDiff($aArray1, $aArray2) {
      $aArray2 = json_encode($aArray2);  
      foreach ($aArray1 as $value1) {
        $aArray2 = str_replace(json_encode($value1),'', $aArray2);  
          
      }
      $pattern = '/\},{2,}\{/i';
      $aArray2 = preg_replace($pattern, '},{', $aArray2);
      $aArray2 = str_replace('[,', '[', $aArray2);
      $aArray2 = str_replace(',]', ']', $aArray2);
      $aArray2 = json_decode($aArray2, true);
      
      return $aArray2;
    } 
	
    
   /**
     * 
     */
    public function addclientAction()
    {
        $data = $this->_getParam('data');
        $tipo = $this->_getParam('tipo');
        $clientes = new Application_Model_DbTable_Clientes();
        $id= $clientes->insertCliente($data,$tipo);
        $this->view->datos = $clientes->getClientes();
        $this->_helper->viewRenderer->setRender ( "admincligrup" );
    }
	
	/**
	* Abre vista de admin
	*/
	public function admincligrupAction()
    {
        $this->_helper->layout->setLayout('administracion');
        $clientes = new Application_Model_DbTable_Clientes();
        $grupos = new Application_Model_DbTable_Grupos();
        $groups = new Application_Model_DbTable_Groups();
        $titulos = new Application_Model_CambioTurno_CambioTurno($this->userData);
        
        $this->view->datos = $clientes->getClientes();
        $this->view->datosGrupos = $grupos->getGrupos();
        $this->view->groups = $groups->getGroups();
        $this->view->titulos = $titulos->getTitulos();
        $this->_helper->viewRenderer->setRender ( "admincligrup" );
    }

    
	
	/**
     * Abre pagina de busqueda de eventos
     */
	public function jeventsnewAction()
    {
        $this->_helper->viewRenderer->setRender ( "jeventsnew" );
    }
	
	
    
}

