<?php

class BackprocessController extends Zend_Controller_Action
{
  


	   protected $user;
        protected $logapp;
	
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        //LOG
        $this->logapp = new Application_Plugin_SystemLog();
    }

    public function indexAction()
    {
    	    	
    }
    

    public function testAction()
    {
          error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
     $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
          /*    $methods = new Application_Model_Backprocess_BackprocessMethods();
           $res =  $methods->controlPendingEvents();
              echo $res; */
          /*$mail = new Application_Model_SendMails_Mails();
          $mail->sendMail("damian.blanc@t-systems.com","damian.blanc@t-systems.com","Hola que tal","Hola",false);     */
          /*$b = new Application_Model_BackProcessMethods();
          $b->cierraticketseventos();*/
            //$inter = new Application_Model_Calendar_EasyInterventionEvents();
            //$inter->GetInterventionData();
              $methods = new Application_Model_Backprocess_BackprocessMethods();
            
            //$methods->cierraticketseventos();
            
            //$methods->closeeventseasyfinished();
            
            //$methods->updateeventseasy();
            
            
           // $methods->UpdateEventosEasyaIntervention();
            
            //$methods->CreateEventosEasyaIntervention();
            $methods->controlPendingEvents();
    }
    
    
    
    public function testchangeAction()
    {
        $change='C000700038';
        $ch = New Application_Model_SM9Change();
        $res = $ch->RetrieveChangeTaskKeysXML($change, 'prueba desde backprocess');
        $newarray = array_slice($res, 1, -1);
        unset ($newarray['TaskID']);
        $control_task = false;
        foreach($newarray as $value)
        {
            echo '->El id de la tarea es: '.$value;
            echo'<br/>' ;
            echo'-------------------------------------------------------------------------------------------------' ;
            echo'<br/>' ;
            try
            {
                $result1 = $ch->RetrieveChangeTaskXML($value, 'prueba desde backprocess');
                if($result1)
                {
                    if($result1['ImplementerGroup'] == 'C.EMEA.IB.OSY.COP.GCC')
                    {
                        if(!$control_task)print_r($result1);
                        $control_task = true;
                    }
                }
            } 
            catch (Exception $e){
                    //todo Toni Ibáñez, busco numero de cambio para crear tarea por defecto para operaciones.
            }
            
            echo'<br/>' ;
            echo'-------------------------------------------------------------------------------------------------' ;
            echo'<br/>' ;
            echo'<br/>' ;
        }
        if(!$control_task){
         echo 'No existe...'; 
         $res = $ch->RetrieveChangeXML($change, 'prueba desde backprocess');
         var_dump($res);
        }
        
        
        if ($res) $this->logapp->setLog("CronProcess - ExportChecklistGCC", 1, array( 'params'=>$res, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
    }
    

        /**
     * Eventos con intervalo de ejecucion cada cinco minutos
     */
    public function eventosminperiodAction()
    {
        /*  error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);*/
        
        try{
            
            $methods = new Application_Model_Backprocess_BackprocessMethods();
            
            $methods->cierraticketseventos();
            
            $methods->closeeventseasyfinished();
            
            $methods->updateeventseasy();
            // file_put_contents("eventosminperiod.txt","ex");
            
        }
        catch(Exception $ex){
            file_put_contents("Excepcion_eventosminperiod.txt",$ex);
        }
        
        
    }

    /**
     * Eventos con intervalo de ejecucion cada hora
     */
    public function eventosmediumperiodAction()
    {
             error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
        //file_put_contents("eventosmediumperiod.txt","ex");
        try{
            $methods = new Application_Model_Backprocess_BackprocessMethods();
            $methods->generateseventstask();
            $methods->exportchecklist();
             
        }
        catch(Exception $ex){
            file_put_contents("Excepcion_eventosmediumperiod.txt",$ex);
        }
           
    }
    

    /**
     * Eventos con intervalo de ejecucion cada 24 horas
     */
    public function eventosdailyperiodAction()
    {
        try{
            $methods = new Application_Model_Backprocess_BackprocessMethods();
            $methods->programevents();
        }
        catch (Exception $ex){
            file_put_contents("Excepcion_eventosdailyperiod.txt",$ex);
        }
        
    }
    
     /**
     * Eventos que no han sido resueltos y su fecha fin está entre now y 24 horas antes.
     */
    public function controlpendingeventsAction()
    {
        try{
            $methods = new Application_Model_Backprocess_BackprocessMethods();
            $methods->controlPendingEvents();      
            
        }
        catch (Exception $ex){
        echo $ex;
            file_put_contents("Excepcion_controlPendingEvents.txt",$ex);
        }
    }
    
    /**
     * Eventos creados y actualizados a partir de Intervenciones 
     */
    public function eventosintervencionesAction()
    {
    	try{
    		$methods = new Application_Model_Backprocess_BackprocessMethods();
    		$methods->UpdateEventosEasyaIntervention();
    		$methods->CreateEventosEasyaIntervention();
    	}
    	catch (Exception $e){
    		file_put_contents("Excepcion_controlPendingEvents.txt",$ex);
    	}
    	
    }



    
}

