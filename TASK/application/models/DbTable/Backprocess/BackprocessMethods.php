<?php
class Application_Model_BackProcessMethods
{

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
            //$export->UpdateEasyEvents();
            //$ids = $export->UpdateEasyEvents();
            //file_put_contents("cron.txt","asdasd");
            $this->logapp->setLog(
                    "CronProcess - CloseEvents - Close EASY events for projects,  Ids: " .
                    $ids, 1,
                    array('params' => $res,
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
            //$ids = $export->CloseEventsByFinishedTasks();
            $export->UpdateEasyEvents();
            //$ids = $export->UpdateEasyEvents();
            //file_put_contents("cron.txt","asdasd");
            $this->logapp->setLog(
                    "CronProcess - UpdateEvents - Update EASY events,  Ids: " .
                    "", 1,
                    array('params' => $res,
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
        $ids = $export->GetDatosProject();
        $this->logapp->setLog(
                "CronProcess - Createvents - Create events for projects,  Ids: " .
                json_encode($ids), 1,
                array('params' => $res,
                        'user_id' => ((isset(
                                $this->user->ID)) ? $this->user->ID : NULL),
                        'username' => ((isset(
                                $this->user->username)) ? $this->user->username : NULL)));
    
    }
    
    
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
        				        $task->update(array('schedule' => $d),  'id = '.$res['id']);
    
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

    
}