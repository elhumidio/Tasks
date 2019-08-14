<?php

class Calendar_AjaxController extends Zend_Controller_Action
{
	protected $calendar;
	protected $planificador;
	
    public function init()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
		// Importante pasar los parámetros
    	$this->calendar = new Application_Model_Calendar_Calendar($this->_getAllParams());
		
    	$this->planificador = new Application_Model_Calendar_Planificador($this->_getAllParams());
    }

	public function indexAction()
    {

    }

     /**
     * Pone el evento journal en estado replanificado, parent y children
     */
    public function asignarestadoreplanificadoAction()
    {
        $id = $this->_getParam('eventoid');
        $refer = $this->_getParam('refer');
        $calendar = new Application_Model_Calendar_Calendar();
        $calendar->replanificaJournalEvent($id,$refer);
        $this->_helper->json->sendJson("OK");
    }
    
    
    /**
     * Pone el evento en pending y modifica fechas (parent y children)
     */
    public function asignarestadopendingjournalAction()
    {
        $id = $this->_getParam('eventoid');
        $refer = $this->_getParam('refer');
        $start = $this->_getParam('start');
        $end = $this->_getParam('end');
        $calendar = new Application_Model_Calendar_Calendar();
        $calendar->replanificaJournalToPending($id,$refer,$start,$end);
        $this->_helper->json->sendJson("OK");
    }
	
 /**
     * Pone el evento journal en estado cancel
     */
    public function asignarestadocanceljournalAction()
    {
        $id = $this->_getParam('eventoid');
        $closurecomments = $this->_getParam('closurecomments');
        $calendar = new Application_Model_Calendar_Calendar();
        $res = $calendar->cancelJournalEvent($id,$closurecomments);
        $this->_helper->json->sendJson("OK");
        
    }
    
    
    /**
     * Consulta estado de una tarea project
     */
    public function consultaestadotareaeasyAction()
    {
        $idtarea = $this->_getParam('idtarea');
        $model = new Application_Model_Calendar_ExportProjectsEvents();
        $result = $model->consultaEstadoTarea($idtarea);
        $this->_helper->json->sendJson(json_encode($result));
        
    }
    
    /**
     * Inicia tarea Easy
     */
	public function starttareaeasyAction()
	{
		$idtarea = $this->_getParam('idtarea');
		$passCode = "0Tdy3AsS2N2BxRDI";
        $projects = new Application_Model_Calendar_ExportProjectsEvents();
    	$res = $projects->iniciaTareaProjects($passCode, $idtarea,$this->planificador->userData->display_name);
		$this->_helper->json->sendJson(json_encode($res));
	}

public function test()
{
	    $passCode = "0Tdy3AsS2N2BxRDI";
        $projects = new Application_Model_Calendar_ExportProjectsEvents();
        echo $this->planificador->userData->display_name;
		$res = $projects->iniciaTareaProjects($passCode, "138021",$this->planificador->userData->display_name);
		$this->_helper->json->sendJson(json_encode($res));
}

	/**
     * Set "protocolo critico" in an event
     */
    public function setinfoprotocoloAction()
    {
        $data = $this->_getParam('eventdata');
        
        $eventos = new Application_Model_DbTable_Calendar_Eventos();
        
        $info = $eventos->setInfoProtocoloCritico($data,$this->planificador->userData->username,$this->planificador->userData->OU_ID);
		
        $rta = $info == "NO" ? "Se ha quitado el protocolo crítico" : "Se ha agregado el protocolo crítico"; 
        
        $this->_helper->json->sendJson($rta);
    }
	
	
	/**
     * Set significant in an event
     */
	public function setsignificantAction()
	{
		$rta = "";
		$data = $this->_getParam('eventdata');
		$eventos = new Application_Model_DbTable_Calendar_Eventos();
		$info = $eventos->setSignificant($data,$this->planificador->userData->username,$this->planificador->userData->OU_ID);
		$rta = $info == "ADDED" ? "Se ha agregado Significant +" : "Se ha quitado Significant";
		$rta = $info == "NO" ? "Se ha quitado Significant es Minor" : "Se ha agregado Significant +"; 
		$this->_helper->json->sendJson($rta);
	}
	
	/**
     * Set direccion in an event
     */
	public function setdireccionAction()
	{
		$rta = "";
		$data = $this->_getParam('eventdata');
        //file_put_contents("dataincontroller.txt", json_encode($data));
		$eventos = new Application_Model_DbTable_Calendar_Eventos();
		$info = $eventos->setDireccion($data,$this->planificador->userData->username,$this->planificador->userData->OU_ID);
		 $rta = $info == "NO" ? "Se ha quitado el protocolo direccion" : "Se ha agregado el protocolo direccion"; 
		$this->_helper->json->sendJson($rta);
	}
    
    /**
     * Actualiza evento info de Journal
     */
    public function updateinfotaskAction()
    {
    	$username = $this->planificador->userData->username;
        $eventoid = $this->_getParam('eventoid');
        $metodo = $this->_getParam('metodo');
        $mjeprogress = true;
        $log = new Application_Model_DbTable_Calendar_EventosLog();
        $db = new  Application_Model_DbTable_Calendar_Eventos();
        $dbEventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
        $calendar = new Application_Model_Calendar_Calendar();
        
        if(strtolower($metodo) == "finish")
        {
        	$mjeprogress = false;
        }
        if(strtolower($metodo) == "progress")
        {
        	$mjeprogress = true;
        }
        	//file_put_contents("datosprogerssinfo.txt",$eventoid." -  -- - ".$metodo);
        
        $data["status"] = $metodo;
        $data["resolution_code"]= "COMPLETE";
        
        if($mjeprogress)
        	$log->log($eventoid,json_encode(array("type"=>"start-assignment","result"=>"Evento Info sera iniciado")),$username);
        
        /*Computing task duration*/
        if(!$mjeprogress)
        {
        	$time_app = $calendar->calculateTaskDuration($eventoid);
        	$data['time_app'] = $time_app;
        }
        	
        /**/
        file_put_contents("1.txt", json_encode($data). " - - id: ".$eventoid);
        $db->UpdateEvent($data,'id = '.$eventoid);
                
        
        if($mjeprogress)
        	$log->log($eventoid,json_encode(array("type"=>"end-assignment","result"=>"Evento Info sera iniciado")),$username);
        else if(!$mjeprogress)
        	$log->log($eventoid,json_encode(array("type"=>"closure","result"=>"Evento Info cerrado")),$username);
        
        $where['`idEvento` = ?'] = $eventoid;
        $where['`option` = ?']  = 'className';
        
        $dbEventosOpciones->update(array('value'=>'Journal_Info_'.$metodo), $where);
        
        
    }

    public function getloginfoAction()
    {
        $eventoid = $this->_getParam('eventoid');
        $log = new Application_Model_DbTable_Calendar_EventosLog();
        $messages = $log->getlog($eventoid);
        //file_put_contents ( "logmessages.txt" ,json_encode($messages) );
        $this->_helper->json->sendJson(json_encode($messages));

    }
	
	    /**
     * Cierra tarea EASY
     */
    public function closeeasytaskAction()
    {
        $idTarea = $this->_getParam('idtarea');
        $ws = new Application_Model_Calendar_ExportProjectsEvents();
		//file_put_contents ( "cierratarea.txt" ,$ws ,FILE_APPEND );
        $ok= $ws->cierraTareaProject("54mg4mfk34mn49",$idTarea);
        $this->_helper->json->sendJson($ok);
    }
	
	 /**
     * Add closure comment
     */
    public function addclosurecommentAction()
    {
        $id = $this->_getParam('id');
        $user = $this->planificador->userData->username;
        $comentario = $this->_getParam('comentario');
        //file_put_contents("comments_id.txt", $id." - ".$comentario);
        $event = new Application_Model_DbTable_Calendar_Eventos();
        $data  = Array('comentarios'=>$comentario);
        $where = Array();
        $where['id = ?'] = $id;
        $event->UpdateEvent($data,$where);
        $log = new Application_Model_DbTable_Calendar_EventosLog();
        $msj = array();
        $msj['type'] = "closure";
        $msj['result'] = $comentario;
      
        $log->log($id,json_encode($msj),$user);
        
    }
	
	/**
     * Añade un comentario a un evento
     */
    public function addcommentAction()
    {
         $prevcoms = $this->_getParam('prevcoms');
        $user = $this->planificador->userData->username;
        
        if($prevcoms != "")
            $comment =$prevcoms.$user.": ".$this->_getParam('com');
        else 
            $comment =$user.": ".$this->_getParam('com');
        
        $eventId= $this->_getParam('eventid');
        $db = new  Application_Model_DbTable_Calendar_Eventos();
       
        $data = ["comentarios" => $comment];
        $result = $db->UpdateEvent($data, 'id =' . $eventId);
         
    }
    
    
    public function tagsAction()
    {
    	$term = ($this->_getParam('term'))?$this->_getParam('term'):'';
    	$salida = $this->calendar->GetTags($term);
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Imprescindible para el funcionamiento del Plugin Token
     */
    public function csrfForbiddenAction()
    {
    	$salida = array('security'=>array('Token'=>$this->view->translate('Fallo de seguridad, actualice la página o vuelva a acceder a ella. Si el problema persiste contacte con automation@t-systems.es')));
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Devuelve las tareas asignadas
     */
    public function gettaskAction()
    {
    	$this->view->TareasExternas()->Get($this->_getParam('idtarea'));
    }
    
    public function addtagsAction()
    {
    	$term = ($this->_getParam('term'))?$this->_getParam('term'):'';
    	$salida = $this->calendar->InsertTags($term);
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Desasigna una tarea de un usuario
     */
    public function desasignartareaAction()
    {
    	$salida = $this->calendar->DesasignarTarea();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Asigna una tarea al usuario
     */
    public function asignartareaAction()
    {
    	$salida = $this->calendar->AsignarTarea();
    	$this->_helper->json->sendJson($salida);
    }
    
     /**
     * Asigna una tarea al usuario
     */
    public function asignareventAction()
    {
        
        $log = new Application_Model_DbTable_Calendar_EventosLog();
        $msj = array();
        $msj['type'] = "assignment";
      
       
      
        $term = ($this->_getParam('prm'))?json_decode($this->_getParam('prm')):false;
        
        
        
        $salida = $this->calendar->AsignarEvent();
      
        if($term){
      
           file_put_contents("termProblemaEnLogID---.txt",json_encode($term));
            if( $term->idTarea && $term->idTarea != 'Parent')
            {
               $sm9 = New Application_Model_SM9Change();
               
               $msj['result'] = "Se asignará la tarea ".$term->idTarea." al usuario ".$this->planificador->userData->username;
               $log->log($term->idTarea,json_encode($msj),$this->planificador->userData->username);
               $test = $sm9->StartWorkTaskXML($term->idTarea, 'Task Journal');
               $msj['result'] = "Se ha asignado la tarea ".$term->idTarea." al usuario ".$this->planificador->userData->username;
               $log->log($term->idTarea,json_encode($msj),$this->planificador->userData->username);
            } else {
			}
        }
       
        $this->_helper->json->sendJson($salida);
    }
	
    
    /**
     * Consulta los datos de una tarea en particular
     */
    public function consulttaskAction()
    {
        $idTarea = $this->_getParam('idTarea');
        $sm9 = New Application_Model_SM9Change();
        $res = $sm9->RetrieveChangeTaskXML($idTarea, "consultaDatosTarea");
        $this->_helper->json->sendJson($res);
    }
    
    public function resolvesm9taskAction()
    {

		$eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $term = ($this->_getParam('params'))?$this->_getParam('params'):false;
		$json = json_encode(array(  'type'   =>'assignment',
                                    'result' =>'evento cerrado por el usuario '.$this->planificador->userData->username.'.'
                                            ));


        //file_put_contents('term.txt', json_encode($term));
        if( $term['TaskID'])
        {
		    $format = 'Y-m-d H:i:s';
	        $actualEnd = new DateTime($term['ActualEnd']);
		    $sm9 = New Application_Model_SM9Change();
		    
   		    $timezone = date_default_timezone_get();
	        
   		    $dateTmpStart = new DateTime($term['ActualStart'], new DateTimeZone($timezone));
			$dateTmpStart->setTimeZone(new DateTimeZone('UTC'));
			$dateTmpStartFRM = $dateTmpStart->format(DATE_ATOM);
			$term['ActualStart'] = $dateTmpStartFRM;
			
			$dateTmpEnd = new DateTime($term['ActualEnd'], new DateTimeZone($timezone));
			$dateTmpEnd->setTimeZone(new DateTimeZone('UTC'));
			$dateTmpEndFRM = $dateTmpEnd->format(DATE_ATOM);
			$term['ActualEnd'] = $dateTmpEndFRM;		
       
       	   
		    $test = $sm9->CloseTsiChangeTaskXML2($term,'Journal');
            if(isset($test["errno"]) && $test["errno"] != 0 )
                //if(true)
            {
                //error cerrando tarea
                $this->_helper->json->sendJson("ERROR_CLOSING_TASK");
                die;
            }
            else{
                $eve = new Application_Model_DbTable_Calendar_Eventos();

               //TODO Cambiar Status Finish por valor real
                $ret = $eve->UpdateEvent(array('status'=>'Finish'),'id ='.$this->_getParam('eventid') );
                $db = new Application_Model_DbTable_Calendar_EventosOpciones();
                $where['`idEvento` = ?'] = $this->_getParam('eventid');
                $where['`option` = ?']  = 'className';
                $db->update(array('value'=>'Journal_Finish'), $where);
                $this->_helper->json->sendJson($ret);
            }
            
        }
				 	 		
    }


    public function testAction()
    {
        /*$sm9 = New Application_Model_SM9Change();
        $res = $sm9->RetrieveChangeTaskXML("T0002494234", "damianTEST");
        $this->_helper->json->sendJson(json_encode($res['ResolutionCode']."|"."104123"));    */
        
        /*$eventos = new Application_Model_DbTable_Calendar_Eventos();
        
        $res = $eventos->updateeventosparent();
        $this->_helper->json->sendJson($res);*/
        
        $sm9 = New Application_Model_SM9Change();
        $res= $sm9->RetrieveChangeTaskXML('T0002937908', "vs");
        //$res = $sm9->RetrieveChangeXML('C001420283','vs');
        $this->_helper->json->sendJson($res);
        
    }

    public function asignarestadoerrorAction()
    {
        
          $id = $this->_getParam('id');
          $this->calendar->UpdateEventError($id);
    }
    
        /**
     * Asigna una tarea Journal al usuario
     */
    public function asignareventjournalAction()
    {
        $log = new Application_Model_DbTable_Calendar_EventosLog();
        $continue = true;
        $sm9 = New Application_Model_SM9Change();
        
        $term = ($this->_getParam('prm'))?json_decode($this->_getParam('prm')):false;
        
        $easy = $this->_getParam('easy');
        $type = $this->_getParam('type');
        $eventoid = $this->_getParam('eventoid');
        $idchange = $this->_getParam('idchange');
        $start = $this->_getParam('start');
        $end = $this->_getParam('end');
        
        $timezone = date_default_timezone_get();
      //  file_put_contents("timezone.txt", $timezone);
      
        $startMod = new DateTime($start, new DateTimeZone($timezone));
        $endMod = new DateTime($end, new DateTimeZone($timezone ));
       
        
        if($term && $type == "SM9"){ //Verificamos estado de la tarea SM9
          
            $res= $sm9->RetrieveChangeTaskXML($term->idTarea, "vs");
            //file_put_contents("res.txt", json_encode($res));
            
            /*$plannedStart = new DateTime($res['PlannedStart'],new DateTimeZone('UTC'));
            $plannedStart->setTimezone(new DateTimeZone('UTC'));
            
            
            $plannedEnd = new DateTime($res['PlannedEnd'],new DateTimeZone('UTC')); 
            $plannedEnd->setTimezone(new DateTimeZone('UTC'));*/

            $plannedStart = new DateTime($res['PlannedStart'],new DateTimeZone($timezone));
            $plannedStart->setTimezone(new DateTimeZone($timezone));
            
            
            $plannedEnd = new DateTime($res['PlannedEnd'],new DateTimeZone($timezone)); 
            $plannedEnd->setTimezone(new DateTimeZone($timezone));

            //Verificacion fechas
            //file_put_contents("fechasdif.txt","fecha evento END: ".$endMod->format('Y-m-d H:i:s')." - fecha sm9 END: ".$plannedEnd->format('Y-m-d H:i:s')." - fecha Evento START:".$startMod->format('Y-m-d H:i:s')." - fecha sm9 START:".$plannedStart->format('Y-m-d H:i:s'));die;
            //Verificacion de cambio de ventana
            if($endMod->format('Y-m-d H:i:s') != $plannedEnd->format('Y-m-d H:i:s') || $startMod->format('Y-m-d H:i:s') != $plannedStart->format('Y-m-d H:i:s'))
            {
                //file_put_contents("fechasdif.txt","fecha evento END: ".$endMod->format('Y-m-d H:i:s')." - fecha sm9 END: ".$plannedEnd->format('Y-m-d H:i:s')." - fecha Evento START:".$startMod->format('Y-m-d H:i:s')." - fecha sm9 START:".$plannedStart->format('Y-m-d H:i:s'));
                $continue = false;
               // $this->calendar->UpdateEventError($eventoid);
                $this->_helper->json->sendJson("ERROR_VENTANA_".$eventoid . "_" . $plannedStart->format('Y-m-d H:i:s') . "_" . $plannedEnd->format('Y-m-d H:i:s'));
                
                die;
            }
           
            
                if(isset($res['TaskStatus'])){
                   
            
                    if($res['TaskStatus'] == "Closed" )
                    {
                        
                        /*SI LA TAREA ESTA CERRADA , PONEMOS EL EVENTO EN ESTADO FINISH*/
                       
                        $eve = new Application_Model_DbTable_Calendar_Eventos();
                        $ret = $eve->UpdateEvent(array('status'=>'Finish'),'id ='.$eventoid);
                        $db = new Application_Model_DbTable_Calendar_EventosOpciones();
                        $where['`idEvento` = ?'] = $eventoid;
                        $where['`option` = ?']  = 'className';
                        $db->update(array('value'=>'Journal_Finish'), $where);
                        $this->_helper->json->sendJson('CLOSED_'.$eventoid);
                        die;
                    }
                    
                    if($res['TaskStatus'] != "Closed"  && $res['TaskStatus'] != "Released" && $res['TaskStatus'] != "Work in Progress") // TODO agregar condicion si es work in progress)
                    {
                        $this->_helper->json->sendJson('NCLSENRLSE_'.$res['TaskStatus'].'_'.$eventoid);
                        die;
                    }
                    
                    if($res['TaskStatus'] == "Work in Progress")
                    {
                        $this->_helper->json->sendJson('WORKINPROGRESS_'.$res['TaskStatus'].'_'.$eventoid);
                    }
                }
              
        }
        
    try{
        
        $salida = $this->calendar->AsignarEvent();
      
        if($term){
            if( $term->idTarea && $term->idTarea != 'Parent')
            {
                $msj['result'] = "Se asignará la tarea ".$term->idTarea." al usuario ".$this->planificador->userData->username;
                $log->log($eventoid,json_encode($msj),$this->planificador->userData->username);
                $test = $sm9->StartWorkTaskXML($term->idTarea, 'Task Journal');
                $msj['result'] = "Se ha asignado la tarea ".$term->idTarea." al usuario ".$this->planificador->userData->username;
                $log->log($eventoid,json_encode($msj),$this->planificador->userData->username);
              
                
                //TODO esto es para SM9 only!!!!!!!
                if($type== "SM9")
                {
                    $arraytest = json_decode($test,true);
                    $timezone = date_default_timezone_get();
                    $fechaStart = new DateTime($arraytest['ActualStart'],new DateTimeZone($timezone));
                    $fechaStart->setTimeZone(new DateTimeZone($timezone));
                    $even = new Application_Model_DbTable_Calendar_Eventos();
                    $where = 'id = '.$eventoid;
                    $data = array('ticket-start'=>$fechaStart->format('Y-m-d H:i:s'));
                    $even->UpdateEvent($data,$where);
                }
                
            }
        } 
        
        //Agregar update event here
        
        $salida = $this->calendar->StartEventJournal();
        
        if($easy=="true")
        {
        	$passCode = "0Tdy3AsS2N2BxRDI";
        	$idtarea = $term->idTarea;
        	
            $projects = new Application_Model_Calendar_ExportProjectsEvents();
            
            $res = $projects->iniciaTareaProjects($passCode, $idtarea,$this->planificador->userData->display_name);
            $this->_helper->json->sendJson(json_encode($res));
        }
        
        $this->_helper->json->sendJson(json_encode($salida));
    }
    catch(Exception $ex){
       // file_put_contents("exception.txt",json_encode($ex));
    }
    
    }
    
    
    /**
     * Update an event
     */
    public function updateventAction()
    {
		try{
            
			$salida = $this->calendar->UpdateEvent();
			$this->_helper->json->sendJson($salida);
		}catch(Exception $ex){
			file_put_contents("updateevent_exception.txt",$ex);
		}
       
    }
        
    /**
     * Finaliza una tarea al usuario
     */
    public function finalizartareaAction()
    {
    	$salida = $this->calendar->FinalizarTarea();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Devuelve los eventos para el calendario
     */
    public function eventsAction()
    {
        $user = ($this->_getParam('users'))?json_decode($this->_getParam('users')):false;
        $wh = ($this->_getParam('dat_user'))?json_decode($this->_getParam('dat_user'),true):false;
        $wh1 = ($this->_getParam('pos'))?$this->_getParam('pos'):false;
    	$salida = $this->calendar->GetEvents($user,$wh,$wh1);
//     	$salida = $this->calendar->GetGroupEvents();
		//echo $salida;
        
    	$this->_helper->json->sendJson($salida);
    }
        
    /**
     * Borra un evento del calendario
     */
    public function deleteeventsAction()
    {
    	$salida = $this->calendar->DeleteEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    
    /**
     * Acción que guarda el movimiento de un evento en el calendario
     */
    public function moveAction()
    {
    	$salida = $this->calendar->MoveEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción para crear un nuevo evento
     */
    public function newAction()
    {
    	$salida = $this->calendar->NewEvent();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción dedicada al la gestión de los comentarios
     */
    public function comentariosAction()
    {
    	switch($this->_getParam('type'))
    	{
    		case 'add': $salida = $this->calendar->addComentario();Break;
    		case 'del': $salida = $this->calendar->delComentario();Break;
    		default: $salida = $this->calendar->getComentario();Break;
    	}
    	
    	$this->_helper->json->sendJson($salida);
    	//echo $salida;
    }



   
    
        
    /**
     * Accion que devuelve los miembros del grupo (Tools, wintel, etc..) del usuario
     */
    public function getgroupmembersAction()
    {
    	$salida = $this->planificador->GetGroupMembers();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción de Planificador, devuelve:
     * 		Lista de miembros para cabecera de tabla.
     * 		Lista de origienes de datos para cabecera de tabla.
     * 		Lista de tareas.
     * 		Stats
     */
    public function gestplanificadorAction()
    {
    	$this->getResponse()->clearHeaders()->setHttpResponseCode(200)->sendResponse(); // Acceso no permitido
    	
    	$salida = array();
    	
    	$cache = Zend_Registry::get ( 'Cache' );
    	
		//Comentado 30/12/2014 para realziar pruebas asignación de grupos en cache
    	/*if( ($salida['Memberlist'] = $cache->load(md5($this->planificador->userData->OU_ID.'_GetGroupMembers'))) === false )
    	{
    		$salida['Memberlist'] = $this->planificador->GetGroupMembers();
    		$cache->save($salida['Memberlist'], md5($this->planificador->userData->OU_ID.'_GetGroupMembers'));
    	}*/
    	
    	$salida['Memberlist'] = $this->planificador->GetGroupMembers();
    	$salida['OrigenesList'] = $this->planificador->GetOrigenList();
    	$salida['TareasList'] = $this->planificador->GetTaskMembers();
    	$salida['Stats'] = $this->planificador->GetTaskStats();
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción de Subgrupo, devuelve:
     * 		Subgrupos (Ovo, Remedy, etc...) del grupo del usuario
     * 		Usuarios
     * 		Usuarios Miembros
     * 		Stats
     * 		
     */
    public function gestorgroupAction()
    {
    	$salida = array();
    	$salida['subgrupos'] = $this->planificador->GetSubGroup();
		$salida['miembros'] = $this->planificador->GetSubGroupMembers();
		
		$cache = Zend_Registry::get ( 'Cache' );
		if( ($salida['usuarios'] = $cache->load(md5($this->planificador->userData->OU_ID.'_GetGroupMembers'))) === false )
	    {
	    	$salida['usuarios'] = $this->planificador->GetGroupMembers();
    		$cache->save($salida['usuarios'], md5($this->planificador->userData->OU_ID.'_GetGroupMembers'));
    	}
    	
    	$this->_helper->json->sendJson($salida);
    }
    
    /**
     * Acción dedicada al la gestión de los subgrupos
     */
    public function subgruposAction()
    {
    	switch($this->_getParam('type'))
    	{
    		case 'set': $salida = $this->planificador->SetSubGroup(); Break;
    		case 'edit': $salida = $this->planificador->EditSubGroup(); Break;
    		case 'delete': $salida = $this->planificador->DeleteSubGroup(); Break;
    		case 'empty': $salida = $this->planificador->EmptySubGroup(); Break;
    		
    		case 'relusers': $salida = $this->planificador->RelUserSubGroup(); Break;
    		case 'deleteusers': $salida = $this->planificador->DeleteUserFromSubGroup(); Break;
    		case 'users': $salida = $this->calendar->getSubgroupUsers();Break;
    		    
    		default: $salida = array(); Break;
    	}
    	$this->_helper->json->sendJson($salida);
    }
    
//     /**
//      * Añade un Subgrupo
//      */
//     public function setsubgroupAction()
//     {
//     	$salida = $this->planificador->SetSubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }
    
//     /**
//      * edita un Subgrupo
//      */
//     public function editsubgroupAction()
//     {
//     	$salida = $this->planificador->EditSubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }
    
//     /**
//      * Elimina un Subgrupo
//      */
//     public function deletesubgroupAction()
//     {
//     	$salida = $this->planificador->DeleteSubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }
    
//     /**
//      * Relaciona un usuario con un grupo
//      */
//     public function relusergroupAction()
//     {
//     	$salida = $this->planificador->RelUserSubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }
   
//     public function deleteuserfromsubgroupAction()
//     {
//     	$salida = $this->planificador->DeleteUserFromSubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }
    
//     public function emptysubgroupAction()
//     {
//     	$salida = $this->planificador->EmptySubGroup();
//     	$this->_helper->json->sendJson($salida);
//     }

    public function taskAction()
    {    	
    	switch($this->_getParam('type'))
    	{
    		case 'new': $salida = $this->calendar->NewTarea(); Break;
    		default: $salida = array(); Break;
    	}
    	
    	$this->_helper->json->sendJson($salida);
    }
    
    public function openticketAction(){
        
        $usuario = $this->_getParam('usuario');
        $grupoUsuario = $this->_getParam('grupoUsuario');
        $eventoid = $this->_getParam('eventoid');
        $cliente_nombre = $this->_getParam('cliente_nombre');
           
        $salida = $this->calendar->OpenTicket($usuario, $grupoUsuario, $eventoid,$cliente_nombre);
        $this->_helper->json->sendJson($salida);
    }
	
	    /**
     * Actualiza campo "activo" de un grupo
     */
    public function updategrupoAction()
    {
        $id = $this->_getParam('idgrupo');
        $activo = $this->_getParam('activo');
        //file_put_contents ( "updategrupo.txt" ,$id." - ".$activo ,FILE_APPEND );
        $grupo = new Application_Model_DbTable_Grupos();
        $grupo->updateGrupo($id,$activo);
        //$this->_helper->viewRenderer->setRender ( "admincligrup" );
    }
    
    /**
     * Edita un cliente a petición de pagina de administración
     */
    public function editclienteAction()
    {
        $id = $this->_getParam('idcliente');
        $data = $this->_getParam('data');
        $tipo = $this->_getParam('tipo');
        $clientes = new Application_Model_DbTable_Clientes();
        $clientes->updateCliente($id, $data,$tipo);
        $this->view->datos = $clientes->getClientes();
       // $this->_helper->viewRenderer->setRender ( "admincligrup" );
    
    }
    
    /**
     * Elimina un cliente
     */
    public function deleteclienteAction()
    {
        $id = $this->_getParam('idcliente');
        $clientes = new Application_Model_DbTable_Clientes();
        $clientes->deleteCliente($id);
        $this->view->datos = $clientes->getClientes();
       // $this->_helper->viewRenderer->setRender ( "admincligrup" );
    
    }
	
	    
    /**
     * Carga todos los clientes
     */
    public function loadclientesAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $clientes = new Application_Model_DbTable_Clientes();
        $result =  $clientes->getClientes();
        return $this->_helper->json->sendJson($result);
      
    }

    /**
     * Añade un grupo a la tabla cal_groups
     */
    public function addgroupAction()
    {
         $this->_helper->viewRenderer->setNoRender();
          $data = $this->_getParam('data');
         $group = new Application_Model_DbTable_Groups();
         $group->insertGroup($data);
    }


     /**
     * Modifica un grupo de la tabla cal_groups
     */
    public function modificagroupAction()
    {
        $data = explode("|",$this->_getParam('data'));
        $group = new Application_Model_DbTable_Groups();
        $group->updateGroup($data[0],$data[1]);

    }

    /**
     * Elimina un grupo de la tabla cal_groups
     */
    public function eliminargroupAction()
    {
        $id = $this->_getParam('id');
        $group = new Application_Model_DbTable_Groups();
        $group->deleteGroup($id);        
    }
    
    /**
     * Refresca la tabla 
     */
    public function refreshgroupsAction()
    {

        $groups = new Application_Model_DbTable_Groups();
        $groupsjson = $groups->getGroups();
        $this->_helper->json->sendJson($groupsjson);
    }
    
    /**
     * Añade título cambio de turno
     */
    public function addtitlecambioturnoAction()
    {
        $titulo = $this->_getParam('titulo');
        $model = new Application_Model_CambioTurno_CambioTurno($this->planificador->userData);
        $model->addTitulo($titulo);
        $this->_helper->json->sendJson("OK");
    }
    
    /*
     * Elimina un título
     */
    public function deletetitlecambioturnoAction()
    {
        $id = $this->_getParam('id');
        $model = new Application_Model_CambioTurno_CambioTurno($this->planificador->userData);
        $model->deleteTitulo($id);
    }
    
    /**
     * Recarga la tabla de titulos
     */
    public function refreshtitulosAction()
    {
        $titulos = new Application_Model_CambioTurno_CambioTurno($this->planificador->userData);
        $this->_helper->json->sendJson($titulos->getTitulos()); 
        
    }
    
    /**
     * Edita un titulo de cambio de turno
     */
    public function edittitlecambioturnoAction()
    {
        
        $id = $this->_getParam('id');
        $titulo = $this->_getParam('titulo');
        $model = new Application_Model_CambioTurno_CambioTurno($this->planificador->userData);
      //  file_put_contents("idtitulo.txt",$id." - ".$titulo);
        $model->updateTitulo($id,$titulo);
        
    }

        /**
     * Returns a list of events not treated with passed end dates
     */
    public function controlpendingeventsAction()
    {
        
        $eventos = new  Application_Model_DbTable_Calendar_Eventos();
        $resultado = $eventos->getEventsPendingTreated();
        $this->_helper->json->sendJson($resultado);
    }
    
    
    /**
     * Gets info about a server
     */
    public function getinfociAction()
    {
        try
        {
            $ci = $this->_getParam('ci');
            $model = new Application_Model_Calendar_ModelsClient();
            
            $res = $model->GetServersByCI($ci);
            //file_put_contents("servidores.txt",json_encode($res));
            $this->_helper->json->sendJson($res);
        }
        catch(Exception $ex){
            $this->_helper->json->sendJson($ex);
            file_put_contents("getinfoci_exception.txt",$ex);
        }
            
            
     }
     
     
     /**
      * Gets info about a Cluster and its servers
      */
     public function getinfoclusterciAction()
     {
         try
         {
             $ci = $this->_getParam('ci');
             $model = new Application_Model_Calendar_ModelsClient();
     
             $res = $model->getServersByClusterName($ci);
             
             $this->_helper->json->sendJson($res);
         }
         catch(Exception $ex){
             $this->_helper->json->sendJson($ex);
             file_put_contents("servidores.txt",$ex);
         }
     
     
     }
     
     /**
      * Updates dates of a single event
      */
     public function updatedateeventAction()
     {
         $id = $this->_getParam('id');
       
         $ev = new Application_Model_Calendar_Calendar();
         $rta =  $ev->updateDatesEvent($id);
         $this->_helper->json->sendJson($rta);
         
     }
     
     /**
      * Starts event without any further verification
      */
     public function starteventsonlyAction()
     {
         $id = $this->_getParam('id');
         $salida = $this->calendar->StartsEventsChangeSynchro($id);
         
         
         
     }
    
    

    
    
    
    
}

