<?php
class Application_Model_Backprocess_BackprocessMethods
{
   
        protected $user;
        protected $logapp;
        public function   __construct(){
        $this->logapp = new Application_Plugin_SystemLog();
    }
     

 
public function test()
{
    var_dump("mensaje");
}
    
    /**
     * Cierra eventos
     */
    public function cierraticketseventos(){
    
        $sm9 = new Application_Model_SM9();
        $dbEventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
        $dbEventos = new Application_Model_DbTable_Calendar_Eventos();
        $eventos = $dbEventos->getEventosTicketAbierto();
        $calendar = new Application_Model_Calendar_Calendar();
           
        foreach($eventos as $evento){

            $incident = $sm9->RetrieveIncidentXML(array('ticketid'=>$evento['ticket-id'],'origen'=>'checkBitac'));
            $downtimeStart = $incident['DowntimeStart'];
    
            $data = array();
            $data['origen'] = 'checkBitac';
            $data['ticketid'] = $evento['ticket-id'];
            $data['resolution']= 'Se valida-realiza la tarea CheckList Planificada.';
            //$data['downtimeEnd'] = $date = date("Y-m-d")."T".date('H:i:s', strtotime(date('H:i:s').' -1 hour'))."+00:00";
            //$data['downtimeEnd'] = $endFormat;
            $data['downtimeEnd'] = $downtimeStart;
    
            $sm9->ResolveIncidentXML($data);
            $time_app = $calendar->calculateTaskDuration($evento['id']);
            //$dbEventos->UpdateEvent(array('ticket-end'=>date("Y-m-d H:i:s"),'time_app'=>$time_app),'id ='.$evento['id']);

            $dbEventos->UpdateEvent(array('ticket-end'=>date("Y-m-d H:i:s"),'status'=>'Finish','time_app'=>$time_app),'id ='.$evento['id']);
             
            $whereOpciones = "idEvento = ".$evento['id'];
            $dbEventosOpciones->update(array('value'=>"Checklist_Finish"), $whereOpciones);
           
        }
      
    }
    
    /**
     * Close easy events in TASK
     */
    public function closeeventseasyfinished()
    {
        try{
            $export = new Application_Model_Calendar_ExportProjectsEvents();
            $ids = $export->CloseEventsByFinishedTasks();
            
            $this->logapp->setLog(
                    "CronProcess - CloseEvents - Close EASY events for projects,  Ids: " .
                    $ids, 1,
                    array('params' => $ids,
                            'user_id' => ((isset(
                                    $this->user->ID)) ? $this->user->ID : NULL),
                            'username' => ((isset(
                                    $this->user->username)) ? $this->user->username : NULL)));
           
      
            	
        }
        catch(Exception $ex)
        {
            //file_put_contents("exception_closeeventseasyfinished.txt",$ex);
        }
         
    }
    
    
    /**
     * Update easy events in TASK
     */
    public function updateeventseasy()
    {
        try{
            $export = new Application_Model_Calendar_ExportProjectsEvents();
            
            $export->UpdateEasyEvents();
            
            $this->logapp->setLog(
                    "CronProcess - UpdateEvents - Update EASY events,  Ids: " .
                    "", 1,
                    array('params' => "",
                            'user_id' => ((isset(
                                    $this->user->ID)) ? $this->user->ID : NULL),
                            'username' => ((isset(
                                    $this->user->username)) ? $this->user->username : NULL)));
      
    
        }
        catch(Exception $ex)
        {
            //file_put_contents("exception_updateeventseasy.txt",$ex);
        }
    }
    
    /**
     * Exporta checklist, genera informe
     */
    public function exportchecklist()
    {
        $CRON = New Application_Model_CronProcess();
        $res = $CRON->ExportChecklistGCC();
        if ($res) $this->logapp->setLog("CronProcess - ExportChecklistGCC", 1, array( 'params'=>$res, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
            

    }
    
    /**
     * Generates tasks and events from Projects
     */
    public function generateseventstask()
    {
       
        $export = new Application_Model_Calendar_ExportProjectsEvents();
      try{
        $ids = $export->GetDatosProject();
        $this->logapp->setLog(
                "CronProcess - Createvents - Create events for projects,  Ids: " .
                json_encode($ids), 1,
                array('params' => "",
                        'user_id' => ((isset(
                                $this->user->ID)) ? $this->user->ID : NULL),
                        'username' => ((isset(
                                $this->user->username)) ? $this->user->username : NULL)));
      

      }
      catch(Exception $ex){
             // file_put_contents("exception_generateseventstask.txt",$ex);

      }
        
        
    
    }
    
    /**
     * programa eventos checklist
     */
   public function programevents()
    {
        $CRON = New Application_Model_CronProcess();
        $calendar = New Application_Model_Calendar_Calendar();
        $task = New Application_Model_DbTable_Calendar_Tareas();
        $result = $CRON->scheduleEvents();
       
        if($result){
        				foreach ($result as $res){
        				    if(isset($res['date']))
        				    {
        				        echo 'Create events for template Id: '. $res['id'];
        				        $d= $res['date'][count($res['date'])-1] .' 00:05:00';
        				        $this->logapp->setLog("UPD schedule task CronProcess - Programevents - Update schedule field for template Id: ". $res['id']." Date: ".$d, 1, array( 'params'=>$res, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
                                $task->update(array('schedule' => $d),  'id = '.$res['id']);
                                
                                $this->logapp->setLog("UPDATED schedule task CronProcess - Programevents - UPDATED schedule field for template Id: ". $res['id']." Date: ".$d, 1, array( 'params'=>$res, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
        				        
                                foreach ($res['date'] as $rec_dat){
        				            $calendar->NewEventR($res['id'], $rec_dat,$res['data']);
        				        }
        				        $this->logapp->setLog("CronProcess - Programevents - Create events for template Id: ". $res['id'], 1, array( 'params'=>$res, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
        				    }
        				}
    
        } else{
            echo 'Not pending events...';
            $this->logapp->setLog("CronProcess - Programevents - Not pending events... ", 1, array( 'params'=>$result, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
        }
        
    }
    
    /**
     * Controls if there is pending events
     */
    public function controlPendingEvents()
    {
            try{
                   $eventos = new  Application_Model_DbTable_Calendar_Eventos();
                        $resultado = $eventos->getEventsPendingTreated();
                     // file_put_contents("eventospendientes.txt", json_encode($resultado));
                       $this->logapp->setLog("CronProcess - ControlEvents There is not treated pending events", 1, array( 'params'=>$resultado, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
                        $destinatarios = array("DL.DL-TS-IB-CSS-GCC-Resp-turno-OSY@t-systems.com","FMB.FMB-TS-IB-GSOPER-24X7@t-systems.com","DL.DL-TS-IB-CSS-GCC-Backoffice@t-systems.com");
                        //$destinatarios = array("damian.blanc@t-systems.com","damian.blanc@t-systems.com");
                        $asunto = "Tareas pending o progress con ventana vencida";
                        $mje="";
                        $cc ="";
                    
                        $logs = new Application_Model_DbTable_Calendar_EventosLog();
                        for($i = 0; $i < count($resultado); $i++)
                        {   
                            //if $resultado[$i]['status'] == error 
                            //buscar el log-- elegir el ultimo get type = closure get result
                            $closureMsg = "";
                             if($resultado[$i]['status'] == "Error")
                             {
                                $closureMsg = $logs->getClosureMessagesJournal($resultado[$i]['id']);
                             }   

                            $mje.="<div style='font-family:arial;font-size:10px;width:90%;background-color:#d9d9d9;padding:10px;border-radius:8px;font-family:arial;box-shadow: 7px 7px 3px #888888;'>";
                            $mje.="<p style='padding:10px;'>";
                            $mje.="<b>Cliente: </b>".$resultado[$i]['customer_affected']."<br />";
                            $mje.="<b>Titulo: </b>".$resultado[$i]['title']."<br />";
                            $mje.="<b>Fecha fin: </b>".$resultado[$i]['end']."<br />";
                            $mje.="<b>Nº Cambio: </b>".$resultado[$i]['refer'];
                            $mje.="<b>Estado: </b>".$resultado[$i]['status']."<br />";
                            $mje.="<b>ID:</b> ".$resultado[$i]['id']."<br />";
                            $mje.="<b>Comentarios: </b>".$resultado[$i]["comentarios"]."<br />";
                            if($closureMsg != "") 
                                $mje.="<b>Cierre: </b>".$closureMsg[0]."<br />";
                            $mje.="</p>";
                            $mje .= "</div>";
                        }
                        if(isset($resultado[0]['customer_affected'])){
                            
                            $mail = new Application_Model_SendMails_Mails();
                            $mail->sendMail($destinatarios,$cc,$asunto,$mje,false);
                            
                            return $resultado;
                        }

            }
            catch(Exception $ex)
            {
               return $ex;
            }
            
                
        
    } 

        /**
     * Create eventos easya intervention
     */
    public function CreateEventosEasyaIntervention()
    {
        try{
        
            $model = new Application_Model_Calendar_EasyInterventionEvents();
            $model->CreateEventsFromIntervenciones();
           // $model->VerifyInterventionsPassToHistoric();
        }
        catch(Exception $ex){
            file_put_contents("Excepcion_CreateEventosEasyaIntervention.txt",$ex);
        }
    }
    
    /**
     * Update eventos easya intervention
     */
    public function UpdateEventosEasyaIntervention()
    {  
        try{
            $model = new Application_Model_Calendar_EasyInterventionEvents();
            $model->UpdateInterventionAllFields();
            $db = new Application_Model_DbTable_Easya_Intervenciones();
            $db->VerifyInterventionPassToHistoric();
        }
        catch(Exception $ex){
            file_put_contents("Excepcion_UpdateEventosEasyaIntervention.txt",$ex);
        }
          
    }
        
        
        
    

    
}