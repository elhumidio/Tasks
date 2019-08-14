<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Calendar_EditController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/jquery.kwicks.css','screen' );
        $this->view->headLink()->appendStylesheet ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/css/font-awesome/css/font-awesome.min.css','screen,print' );

        $this->view->headLink()->appendStylesheet ( '/estilos/blueimp-jQuery-File-Upload-02efca0/css/jquery.fileupload-ui.css','screen,print');
        $this->view->headLink()->appendStylesheet ( '/estilos/css/upload.css','screen,print');

        $this->view->headScript()->appendFile ( '/estilos/js/iphone-style-checkboxes.js' );
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery.kwicks.js' );
        $this->view->headScript()->appendFile ( '/estilos/js/moment.min.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.iframe-transport.js' );
        $this->view->headScript()->appendFile ( '/estilos/blueimp-jQuery-File-Upload-02efca0/js/jquery.fileupload.js' );
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/moment-recur.js' );
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/jquery.spinner.js' );

        $this->checklistnew = new Application_Model_Calendar_Calendar($this->_getAllParams());
        $this->planificador = new Application_Model_Calendar_Planificador($this->_getAllParams());
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/notify.min.js' );
        //Application_Model_Token::Token();
    }


	public function testAction()
	{
	ini_set('display_errors', 1);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

       $export = new Application_Model_Calendar_ExportProjectsEvents();
	// $e =  $export->CloseEventsByFinishedTasks();
        $e = $export->UpdateEasyEvents();
        $this->_helper->json->sendJson($e);
       /* $ids = $export->CloseEventsByFinishedTasks();
        $this->_helper->json->sendJson($ids);*/

       /* $idTarea = '140250';
        $closurecomments = "Prueba desde TEST";
        $code = "Finalizada";
        $ActualStart = "2017-07-20 00:30:00";
        $ActualEnd = "2017-07-20 04:20:00";

        $ws = new Application_Model_Calendar_ExportProjectsEvents();

        $ok= $ws->cierraTareaProject("54mg4mfk34mn49",$idTarea,$closurecomments,$code,$ActualStart,$ActualEnd);*/

	}
	
	
	/**
	 * Crea eventos manuales en Journal (Bitácora)
	 */
	public function createeventsjournalmanuallyAction()
	{
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	    try{
	    
	        $dbvar = $this->_getParam('dynamictasks');
	       // $dbvar =json_encode($this->_getParam('dbvar')) ;
	        
	        unset($dbvar['titledynamic']);
	        unset($dbvar['descriptiondynamic']);
	        
	        $arrayDynamic = $dbvar['dynamicValues'];
	        unset($dbvar['dynamicValues']);
	        unset($dbvar['idtareadynamic']);
	        
	        $db = new Application_Model_DbTable_Calendar_Tareas();
	        $dbev = new Application_Model_DbTable_Calendar_Eventos();
	        $dbEveOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
	        $clientedb = new Application_Model_DbTable_Clientes();
	        $dbLog = new Application_Model_DbTable_Calendar_EventosLog();
	    
	        
	        //TODO  Crear tarea, preparación de las variables (adaptarlas a tabla cal_tareas)
	      
	        $info =  isset($dbvar['info']) && $dbvar['info'] == "checked" ? true : false;
	        
	        $params = array("idChange"=>$dbvar['refer'],
	                "idTarea"=>"Parent",
	                "Status"=>"all tasks confirmed",
	                "Service"=>"",
	                "Environment"=>"Pro",
	                "Coordinator"=>"",
	                "Approval Status"=>"",
	                "CBI"=>"MINOR",
	                "CI"=>"","INFO"=>$info);

	        $dbvar['params'] = json_encode($params); //Add parameters
	        $dbvar['client'] = $dbvar['cliente']; //change cliente by client
	        $dbvar['group'] = $dbvar['rgroup'];
	        unset($dbvar['cliente']);
	        $start = $dbvar['start'];
	        $end = $dbvar['end'];
	        $cliente = $dbvar['client'];
	        $dbvar['t_start'] = explode(" ",$dbvar['start'])[1]; //save time start and end
	        $dbvar['t_end'] = explode(" ",$dbvar['end'])[1];
	        unset($dbvar['start']);
	        unset($dbvar['end']);
	         
	        if(isset($dbvar['info']))
	        {
	            $info = true;
	            unset($dbvar['info']);
	        }
	        
	        $dbvar['owner'] = "Morning";
	        $dbvar['type'] = "Manual";
	        $dbvar['customer_affected'] = $clientedb->getNombre($dbvar['client']);
	        $idtarea = $db->InsertTarea($dbvar);
	    
	        //TODO Crear eventos
	        //preparar variables
	    
	        $dbvar['origen'] = 'Journal';
	        $dbvar['centro'] = 'BCN - 22@';
	        $dbvar['cliente'] = $dbvar['client'];
	        $dbvar['turno'] = 'Morning';
	        unset($dbvar['client']);
	        $dbvar['start'] = $start;
	        unset($dbvar['t_start']);
	        $dbvar['end'] = $end;
	        unset($dbvar['t_end']);
	        unset($dbvar['owner']);
	        $dbvar['idTarea'] = $idtarea;
	        $dbvar['customer_affected'] = $clientedb->getNombre($dbvar['cliente']);
	        $dbvar['creada'] = date("Y-m-d H:i:s");;
	        $dbvar['usuario'] = $this->planificador->userData->username;
	        $ids = array();
	        
	        if($info) //solo parent
	        {
	            $paramsEvento = array("idChange"=>$dbvar['refer'],
	                    "idTarea"=>"Parent",
	                    "Status"=>"","Service"=>"",
	                    "Environment"=>"PRO",
	                    "Approval Status"=>"",
	                    "CI"=>"",
	                    "Downtime?"=>"Total",
	                    "Affected Groups"=>$this->planificador->userData->OU_ID,
	                    "Petitioner"=>$this->planificador->userData->display_name,
	                    "SDM"=>"",
	                    "critical_services"=>null,
	                    "CBI"=>"MINOR",
	                    "INFO"=>$info);

	            $dbvar['params'] = json_encode($paramsEvento);
	            $idEvento = $dbev->InsertEvent($dbvar);
	            array_push($ids,$idEvento);
	            $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Pending");
	            
	            $dbLog->log($idEvento,json_encode(array('type'=>'MANUAL_INFO_Event_Creation','result'=>"Evento creado, Evento tipo INFO, Cambio (refer): ".$dbvar['refer'])),$this->planificador->userData->username);
	        }
	        else 
	        {
	            //PARENT
	           
	            $title = $dbvar['title'];
	            $description = $dbvar['description'];
	            $dbvar['description']  = $dbvar['group']." - ".$dbvar['description'];
	            $dbvar['title']  = $dbvar['title'];
	            
	            $paramsEvento = array("idChange"=>$dbvar['refer'],
	                    "idTarea"=>"Parent",
	                   "Status"=>"","Service"=>"",
	                    "Environment"=>"PRO",
	                    "Approval Status"=>"",
	                    "CI"=>"","Downtime?"=>"Total",
	                    "Affected Groups"=>$this->planificador->userData->OU_ID,
	                    "Petitioner"=>$this->planificador->userData->display_name,
	                    "SDM"=>"",
	                    "critical_services"=>null,
	                    "CBI"=>"MINOR",
	                    "INFO"=>$info);
	            $dbvar['params'] = json_encode($paramsEvento);
	            $idEvento = $dbev->InsertEvent($dbvar);
	         
	            $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Parent");
	    
	    
	            //CHILDREN
	            
	            $dbvar['description']  = $description;
	            $dbvar['title']  = $title;
	    
	            
	            for($i = 0; $i < count($arrayDynamic); $i++){
	                $dbvar['description'] = $arrayDynamic[$i]['description'];
	                $dbvar['title'] = $arrayDynamic[$i]['title'];
                    $idtareadyn = "";

                    if(trim($arrayDynamic[$i]['idTarea']) == "")
                        $idtareadyn = $idtarea."_".($i + 1);
                    else $idtareadyn = $arrayDynamic[$i]['idTarea'];
	                
	                $paramsEvento = array("idChange"=>$dbvar['refer'],
	                        "idTarea"=>$idtareadyn,
	                        "Status"=>"",
	                        "Service"=>"",
	                        "Environment"=>"PRO",
	                        "Approval Status"=>"",
	                        "CI"=>"",
	                        "Downtime?"=>"Total",
	                        "Affected Groups"=>$this->planificador->userData->OU_ID,
	                        "Petitioner"=>$this->planificador->userData->display_name,
	                        "SDM"=>"","critical_services"=>null,
	                        "CBI"=>"MINOR",
	                        "INFO"=>$info);
	                $dbvar['params'] = json_encode($paramsEvento);
	                
	                $idEvento = $dbev->InsertEvent($dbvar);
	                array_push($ids,$idEvento);
	                $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Pending");
	                $dbLog->log($idEvento,json_encode(array('type'=>'Event_Creation','result'=>"Evento creado, Cambio (refer): ".$dbvar['refer'])),$this->planificador->userData->username);
	            }
	          
	        }
	       
	        $this->_helper->json->sendJson($ids);
	    }
	    catch(Exception $ex){
	        file_put_contents("creartarea_exception.txt", $ex);
	    }
	    
	    
	    
	}

	 /**
     * Obtiene la programacion de una tarea
     */
    public function getprogramacionAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $tareas = new Application_Model_DbTable_Calendar_Tareas();
        $idTarea = $this->_getParam('idtarea');
       
        $ret = $tareas->GetProgramTaskById($idTarea);
       	$this->_helper->json->sendJson($ret);

    }

    public function indexAction()
    {
        $this->_helper->layout->setLayout('administracion');
    }

    public function checklistAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function bitacoraAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function seguimientoAction()
    {
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/screenfull.js' );
        $this->_helper->layout->setLayout ( 'generalclear' );

    }

    /**
     * Cierra tarea EASY
     */
    public function closeeasytaskAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idTarea = $this->_getParam('idtarea');
		
        $closurecomments = $this->_getParam('closurecomments');
        $code = $this->_getParam('code');
        $ActualStart = $this->_getParam('ActualStart');
		
        $ActualEnd = $this->_getParam('ActualEnd');
	
        $ws = new Application_Model_Calendar_ExportProjectsEvents();
	
        $user = $this->planificador->userData->display_name;
		
        $ok= $ws->cierraTareaProject("54mg4mfk34mn49",$idTarea,$closurecomments,$code,$ActualStart,$ActualEnd,$user);
        $this->_helper->json->sendJson($ok);
    }

     /**
     * Accion que devuelve array de datos para rellenar el Datatables de Bitacora en estado de Pre_aprobación
     */
    public function gettaskfromchangeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $ids = ($this->_getParam('ids'))?preg_split( "/[\s,;:]+/", $this->_getParam('ids') ):false;
        //explode(',',$this->_getParam('ids')):false;
        $db1 = new Application_Model_DbTable_Calendar_Tareas();
        if($ids)
        {
            $ch = New Application_Model_SM9Change();
            foreach($ids as $id){
					$id = trim($id);
				
                    $alert 		= ($db1->CheckIs($id))?'<img class="alert_row" src="/img/icon/alert.png" alt="Duplicated entry" title="Duplicated Entry in DB">':'';
                    $res 		= $ch->RetrieveChangeTaskKeysXML($id, 'prueba desde backprocess');
                    $reschange 	= $ch->RetrieveChangeXML($id, 'prueba desde backprocess');
                  
                    if($reschange['returnCode'] == 0)
                    {
                        $newarray = array_slice($res, 1, -1);
                        unset ($newarray['TaskID']);
                        $salida[] = array('Delimg'=>'<img class="delete_row" src="/img/icon/table_row_delete.png" alt="DELETE" title="DELETE">'.$alert,'ParentChange' => $id,'TaskID'=>'Parent',
                                'PlannedStart'=> date("Y-m-d H:i:s",strtotime($reschange['PlannedStart'])),
                                'PlannedEnd' => date("Y-m-d H:i:s",strtotime($reschange['PlannedEnd'])),
                                'CodCli'=> $reschange['CustomerName'],
                                'Customer'=> (array_key_exists('AffectedCustomer',$reschange))?implode("\n", $reschange['AffectedCustomer']):$reschange['CustomerName'],
                                'TaskStatus'=>@$reschange['TaskStatus'],
                                'Service' => '',
                                'Environment'=>'Pro',
                                'Coordinator' => @$reschange['CoordinatorGroup'].'-> '.@$reschange['Coordinator'],
                                'ApprovalStatus' => @$reschange['ApprovalStatus'], 
                                'ChangeTypeCBI' => @$reschange['ChangeTypeCBI'],
                                'Title' => @$reschange['Title'], 
                                'TaskDescription'=>implode("\n", @$reschange['Plan']),
                                'NameOfCI'=> implode("\n", @$reschange['NameOfCI']));


                        $control_task = false;
                        foreach($newarray as $value)
                        {
                            try
                            {
                                $result1 = $ch->RetrieveChangeTaskXML($value, 'prueba desde backprocess');
                                if($result1)
                                {
                                    if($result1['ImplementerGroup'] == 'C.EMEA.IB.OSY.COP.GCC')
                                    {
                                    		$nameOfCI = isset($result1['NameOfCI']) ? implode("\n", @$result1['NameOfCI']) : "";
                                            $salida[] = array('Delimg'=>'<img class="delete_row" src="/img/icon/table_row_delete.png" alt="DELETE" title="DELETE">','ParentChange' => $id,'TaskID'=>$value,
                                                    'PlannedStart'=> date("Y-m-d H:i:s",strtotime($result1['PlannedStart'])),'PlannedEnd' => date("Y-m-d H:i:s",strtotime($result1['PlannedEnd'])),
                                                    'CodCli'=> @$reschange['CustomerName'],'Customer'=>(array_key_exists('AffectedCustomer',$reschange))?implode("\n", $reschange['AffectedCustomer']):$reschange['CustomerName'],'TaskStatus'=>@$result1['TaskStatus'],
                                                    'Service' => '','Environment'=>'Pro','Coordinator' => $reschange['CoordinatorGroup'].'-> '.$reschange['Coordinator'],
                                                    'ApprovalStatus' => @$reschange['ApprovalStatus'], 'ChangeTypeCBI' =>@$reschange['ChangeTypeCBI'],
                                                    'Title' => @$result1['TaskTitle'], 'TaskDescription'=>implode("\n", @$result1['TaskDescription']),
                                                    'NameOfCI'=> $nameOfCI);
                                            $control_task = true;

                                        //if(!$control_task)print_r($result1);

                                    }
                                }
                            }
                            catch (Exception $e){
                                    //todo Toni Ibáñez, busco numero de cambio para crear tarea por defecto para operaciones.
                            }

                        }
                        if(!$control_task){
                                /*$salida[] = array('Delimg'=>'<img class="delete_row" src="/img/icon/table_row_delete.png" alt="DELETE" title="DELETE">','ParentChange' => $id,'TaskID'=>'info',
                                                'PlannedStart'=> date("Y-m-d H:i:s",strtotime($reschange['PlannedStart'])),'PlannedEnd' => date("Y-m-d H:i:s",strtotime($reschange['PlannedEnd'])),
                                                'CodCli'=> @$reschange['CustomerName'],'Customer'=>(array_key_exists('AffectedCustomer',$reschange))?implode("\n", @$reschange['AffectedCustomer']):@$reschange['CustomerName'],'TaskStatus'=>@$reschange['TaskStatus'],
                                                'Service' => '','Environment'=>'Pro','Coordinator' => @$reschange['CoordinatorGroup'].'-> '.@$reschange['Coordinator'],
                                                'ApprovalStatus' => @$reschange['ApprovalStatus'], 'ChangeTypeCBI' => @$reschange['ChangeTypeCBI'],
                                                'Title' => @$reschange['Title'], 'TaskDescription'=>implode("\n", @$reschange['Plan']),
                                                'NameOfCI'=> implode("\n", @$reschange['NameOfCI']));*/
                        }
                    }

            }
           // file_put_contents("cambios.txt", json_encode($salida));
            $this->_helper->json->sendJson($salida);
        }
    }

    public function templatesAction()
    {
        //Application_Model_Token::Token();
        $this->_helper->layout->disableLayout();
        if ($this->view->User()->hasRole('SDM') || $this->view->User()->hasRole('Admin')) $this->view->debug = 1;
        else $this->view->debug = 0;
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/screenfull.js' );
                $salida = array();
        $this->view->subgrupos = $this->planificador->GetSubGroup();
        $this->view->miembros = $this->planificador->GetSubGroupMembers();
         $cache = Zend_Registry::get ( 'Cache' );

       /*if( ($this->usuarios = $cache->load(md5($this->planificador->userData->OU_ID.'_GetGroupMembers'))) === false )
        {
            $this->usuarios = $this->planificador->GetGroupMembers();
            // $this->usuarios = Array (
                                                // Array('uid' => 'sgarrido' ,'OU_ID' => 'ES003682' ,'Emp_givenname' => 'SERGIO' ,'Emp_surname' => 'GARRIDO ARRIBAS' , 'className' => 'drag'),
                                                // Array('uid' => 'jverges' ,'OU_ID' => 'ES003682' ,'Emp_givenname' => 'JORDI' , 'Emp_surname' => 'VERGES CASADEVALL' ,'className' => 'drag'),
                                                // Array('uid' => 'pabazan' ,'OU_ID' => 'ES003682' ,'Emp_givenname' => 'PABLO IGNACIO' ,'Emp_surname' => 'BAZAN VILLAR' ,'className' => 'drag'),
                                                // Array('uid' => 'anibanez' ,'OU_ID' => 'ES003682' ,'Emp_givenname' => 'ANTONIO' ,'Emp_surname' => 'IBAÑEZ JUSTICIA' ,'className' => 'drag')
                                    // );
            $cache->save($this->usuarios, md5($this->planificador->userData->OU_ID.'_GetGroupMembers'));
        }*/
    }

    public function jtemplatesAction()
    {
        //Application_Model_Token::Token();
        $this->_helper->layout->disableLayout();
        if ($this->view->User()->hasRole('SDM') || $this->view->User()->hasRole('Admin')) $this->view->debug = 1;
        else $this->view->debug = 0;
        $this->view->headScript()->appendFile ( Zend_Controller_Front::getInstance()->getBaseUrl().'/estilos/js/screenfull.js' );
    }

    public function eventsAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function jeventsAction()
    {
        $this->_helper->layout->disableLayout();
    }

	public function verificaclienteAction()
	{
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		
		$name = $this->_getParam('name');
		$cliente = new Application_Model_DbTable_Clientes();
		$id = $cliente->getClienteIdByName(json_decode($name));
		
		if($id == "KO")
			$this->_helper->json->sendJson("KO");
		else $this->_helper->json->sendJson("OK");
	}

/**
 * Realiza una serie de tareas relacionadas con la creacion, actualizacion y remoción de eventos
 * @param array $info
 * @param array $i
 * @return string      
 */
public function getTasksFromArray($info,$i)
{
		$ret ="";
		$cont = 0;
        foreach ($info as $value) {
            
            if($value[1] == $i[1]){

			if($cont != 0){
				if($value[2] != "Parent")
                   $ret  = $ret." - ".$value[2];
				
			}
			else{
				if($value[2] != "Parent")
                   $ret  = $ret.$value[2];
				
			}
                
            }
			$cont++;
        }
return $ret;

}


    /**
     * Crea eventos de un cambio
     */
    public function createeventsfromchangeAction()
    {

		 $eLog = new Application_Model_DbTable_Calendar_EventosLog();
        /*[0]=>    string(0) "" -> del
            [1]=>    string(10) "C000682862" -> params -> idChange
            [2]=>    string(11) "T0001304643" -> params -> idTarea
            [3]=>    string(19) "2016-05-05 16:00:00" -> start
            [4]=>    string(19) "2016-05-05 18:00:00" -> end
            [5]=>    string(19) "MC MUTUAL/MC MUTUAL" -> cliente
            [6]=>    string(19) "MC MUTUAL/MC MUTUAL" -> customer_affected
            [7]=>    string(6) "Closed" -> params -> status
            [8]=>    string(0) "" -> params -> service
            [9]=>    string(3) "Pro"-> params -> environment
            [10]=>    string(31) "CS.EMEA.IB.OSY.TOOLS-> ANIBANEZ"-> params -> coordinator
            [11]=>    string(8) "approved" -> params -> aproval status
            [12]=>    string(5) "MINOR" -> params -> cbi
            [13]=>    string(64) "verify that all monitoring events were closed for server VMWZ422" -> title
            [14]=>    string(73) "After the change, you must verify that all monitoring events were closed." -> description
        [15]=>    string(19) "VMWZ422 (S19334799)"-> params -> ci*/

        $del = $this->_getParam('del');

        $this->_helper->layout->disableLayout();
        $info 			= $this->_getParam('info');
		$dbceo 			= new Application_Model_DbTable_Calendar_EventosOpciones();
        $db 			= new Application_Model_DbTable_Calendar_Eventos();
        $dba 			= new Application_Model_DbTable_Calendar_UsuariosEventos();
        $db1 			= new Application_Model_DbTable_Calendar_Tareas();
        $dbLog 			= new Application_Model_DbTable_Calendar_EventosLog();
        $cliente 		= new Application_Model_DbTable_Clientes();
        $eventosModel 	= new Application_Model_Calendar_Calendar();
        
        //get sure to eliminate previous same change events
        
    
		foreach($info as $i)
        {
		
			$isInfo = $eventosModel->isInfo($i,$info);
			$error = array();

            if ($i[2] == 'Parent'){
                $db1->delete("refer = '$i[1]'");
                $db->delete("refer = '$i[1]'");
            }
		    $tasks = "";
            $tasks = $this->getTasksFromArray($info,$i);
            $temp2 = array   (  'id'=>null,
								'customer_affected'  =>$i[6],'title' => $i[13],'tasks' => $tasks,
                                'description' => $i[14], 'refer' => $i[1],
                                'origen' => 'Journal', 'centro' => 'BCN - 22@',
                                'turno' => 'Morning', 'group'  => 'MULTI', 'type' => 'SM9',
                                'params' =>json_encode(array(
                                                            'idChange'         =>$i[1],
                                                            'idTarea'          =>$i[2],
                                                            'Status'           =>$i[7],
                                                            'Service'          =>$i[8],
                                                            'Environment'      =>$i[9],
                                                            'Coordinator'      =>$i[10],
                                                            'Approval Status'  =>$i[11],
                                                            'CBI'              =>$i[12],
                                                            'CI'               =>$i[15],
                                                            'INFO'             =>$isInfo
                                                        )
                                                    )
                            );

            try {
           
			        if($i[2] == 'Parent')
                    {
                        //VERIFICAR SI ES INFO
                                        
                        $id 		= $cliente->getClienteIdByName($i[5]);
                        $temp 		= array_merge($temp2,array('t_start' =>$i[3], 't_end' =>$i[4],'client' => $i[5], 'estado' => 'Pending', 'owner'=>'Morning',));
                        $resTarea 	=  $db1->InsertTarea($temp);
						unset($temp2['tasks']);
                        $temp 		= array_merge($temp2,array('start' =>$i[3], 'end' =>$i[4],'cliente' => $id,'idTarea'=>$resTarea, 'status' => 'Pending','creada'=>  date("Y-m-d H:i:s"), 'usuario' =>   $this->planificador->userData->username));
                       
                        $res =  $db->InsertEvent($temp);
                        if(!empty($res))
						{
                      		$dba->InsertData(array('idEvento'=>$res,'username'=> 'Morning'));
                        	$dbceo->insert(array('idEvento'=>$res,'option'=>'className','value'=>"Journal".'_'."Parent"));
							//$dbLog->log($res,json_encode(array('type'=>'Parent_Event_Creation','result'=>"Evento creado, Parent perteneciente a cambio ".$i[1])));
							$dbLog->log($res,json_encode(array('type'=>'SM9_Parent_Event_Creation','result'=>"Evento creado, Parent perteneciente a cambio ".$i[1])),$this->planificador->userData->username);
						}
                    }
					else
					{
						
						$id 	= $cliente->getClienteIdByName($i[5]);
						unset($temp2['tasks']);
                        $temp 	= array_merge($temp2,array('start' =>$i[3], 'end' =>$i[4],'cliente' => $id,'idTarea'=>$resTarea, 'status' => 'Pending'));
						$res 	=  $db->InsertEvent($temp);
                        
                        if(!empty($res))
						{
							$dba->InsertData(array('idEvento'=>$res,'username'=> 'Morning'));
							$dbceo->insert(array('idEvento'=>$res,'option'=>'className','value'=>"Journal".'_'."Pending"));
							$dbLog->log($res,json_encode(array('type'=>'SM9_Event_Creation','result'=>"Evento creado,  perteneciente a cambio ".$i[1])),$this->planificador->userData->username);
						}
                    }

            }
            catch(Exception $e) {
                
                file_put_contents ( "createeventsfromchangeException.txt" ,  'Message: ' .$e->getMessage());
            }
           
           
        }

        die();
    }
    
    /**
     * Obtiene los eventos de un cambio
     */
    public function geteventsbytaskAction()
    {
        $eventos = new Application_Model_DbTable_Calendar_Eventos();
        $idtarea = $this->_getParam('idtarea');
        $ev = $eventos->getEventsByTask($idtarea);
        $this->_helper->json->sendJson(json_encode($ev));
    }

    /**
     * Inicia tareas 
     */
    public function starttareaeasyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idtarea = $this->_getParam('idtarea');
        $passCode = "0Tdy3AsS2N2BxRDI";
        $projects = new Application_Model_Calendar_ExportProjectsEvents();
        $user = $this->planificador->userData->display_name != null ? $this->planificador->userData->display_name : "";
        $res = $projects->iniciaTareaProjects($passCode, $idtarea,$user);
        $this->_helper->json->sendJson(json_encode($res));
    }

    public function mngtemplatesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $dbvar = $this->_helper->Myparams();
        $execvar = $this->_helper->myparams->cleanexec();
        $execvar['OU_ID'] =$this->planificador->userData->OU_ID;
        $execvar['owner'] = $this->planificador->userData->username;
        $db = new Application_Model_DbTable_Calendar_Tareas();
        $new_tarea = $db->InsertTarea($execvar);
        $var_rec = json_decode($execvar['programacion'],true);

        if($var_rec['recur'] != 'false')
        {


            if($var_rec['fr_tmp']['P1D'])
            {
                $start = new DateTime( "now" );
                $end = new DateTime( "now +1 month" );
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                //$mydays = array('1', '3', '5');
                foreach($period as $date) {
                    if(in_array($date->format('N'), $var_rec['fr_tmp']['P1D'])) {
                        $da_arr[] = $date->format( "Y/m/d\n" );
                    //do something for Monday, Wednesday and Friday
                        }
                    }
                $res['date'] = $da_arr;
            }

            if(isset($res['date']))
            {
                $d= $res['date'][count($res['date'])-1] .' 00:05:00';
                $db->update(array('schedule' => $d),  'id = '.$new_tarea);
                foreach ($res['date'] as $rec_dat)
                {
                    //die(print_r($recur_arr));
                    $this->checklistnew->NewEventR($new_tarea, $rec_dat,$execvar);
                }
            }

        }
        else
        {

        }

    }

/*
* Modify array keys
*@param array $array
*@param string $old_key
*@param string $new_key
*@return array 
*/
function change_key( $array, $old_key, $new_key ) {

    if( ! array_key_exists( $old_key, $array ) )
        return $array;

    $keys = array_keys( $array );
    $keys[ array_search( $old_key, $keys ) ] = $new_key;

    return array_combine( $keys, $array );
}


    public function serversideAction()
    {
        $this->_helper->layout->disableLayout();
        $tabla = $this->_getParam('tabla');

        $dbvar = $this->_helper->Myparams();
        switch($tabla)
        {
            case 'templates':
                $this->view->output = Application_Model_Datatables_Datatables::generatetemplatesTable();

                //$this->view->output = Application_Model_Datatables_Datatables::generateEventsTable();
                Break;

            case 'jtemplates':
			//file_put_contents ( "info.txt" ,  $tabla);
                $this->view->output = Application_Model_Datatables_Datatables::generatejtemplatesTable();

                Break;
            case 'searchjournal':
                $this->view->output = Application_Model_Datatables_Datatables::generateEventsJournalTableNoDelete();
                break;

            case 'events':
                //file_put_contents("TIPO.txt" ,json_encode($dbvar['type']) );

                if(isset($dbvar['type']) && $dbvar['type']=='journal'):
                    $this->view->output = Application_Model_Datatables_Datatables::generateEventsJournalTable();
                else:
                    $this->view->output = Application_Model_Datatables_Datatables::generateEventsTable();
                endif;
                Break;

            case 'getevlog':
                $db1 = new Application_Model_DbTable_Calendar_EventosLog();
                $db1->__set('username',$this->planificador->userData->username);
                $db1->__set('OU_ID',$this->planificador->userData->OU_ID);
                $res = $db1->getlog($dbvar['id']);
				//file_put_contents("params.txt",json_encode($dbvar));
                $this->view->output = json_encode($res);
                Break;

            case 'savedatafromexcel':

                $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
                $UsuarioTareasDb = new Application_Model_DbTable_Calendar_UsersTareas();
                $TareasOptDb = new Application_Model_DbTable_Calendar_TareasOpciones();
                $results = $this->_getParam('dataExcel');
                $resid = 0;

				$eventos =  new Application_Model_DbTable_Calendar_Eventos();

                    //Eliminamos los ya creados, y los recreamos mas abajo
                   foreach ($results as $res) {

                            $idChange = json_encode($res['idChange']);
                            $existing = $res['existing'];

                        if ($res['existing'] == "true"){

                                $TareasDb->deleteTareaJournal($idChange);
                                $eventos->deleteEventJournal($idChange);
                            }
                        }

                foreach ($results as $res) {

                    if(isset($res['data']) && array_filter($res['data'],'strlen'))
                    {
                        if(isset($res['parent']) && $res['parent'] == "parent" )
                        {
                            $res['id'] = $TareasDb->InsertTarea($res['data']);
                            $resid =$res['id'];
                        }

                    }


                    if(isset($res['parent']) && $res['parent'] == "parent" ){
                        if(isset($res['data']['OU_ID']) && isset($res['id']) && $res['data']['turno']){
                            $UsuarioTareasDb->AsignarUsuario($res['id'], $res['data']['turno'], $res['data']['OU_ID']);
                        }


                        $TareasOptDb->SetTareasValue($res['id'], 'className', $type.'_Pending');

                    }

                    if(isset($res['date']))
                    {

                            $d= $res['date'][0][0].' 00:05:00';

                               $TareasDb->update(array('schedule' => $d),  'id = '.$resid);

                            foreach ($res['date'] as $kid => $rec_dat)
                            {
                                if($res['idChange'])
                                {
                                    //$res['data']['refer'] = ($kid == 0 )?'Parent_'.$res['idChange']:$res['idChange'];
                                }
                                $this->checklistnew->NewEventJournalExcel($resid, $rec_dat,$res['data'],$res['dateend']);
                            }

                     }




                }


                break;

            case 'crevent':
                $db = new Application_Model_DbTable_Calendar_Eventos();
                $db1 = new Application_Model_DbTable_Calendar_UsuariosEventos();
                $db2 = new Application_Model_DbTable_Calendar_EventosOpciones();


                // status evento segun 'open-close-ticket'
                if(isset($dbvar['open-close-ticket'])){
                    //$dbvar['status'] = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
                      $dbvar['status'] = 'Pending';
                }

                try{
                   // file_put_contents("intentadoInsertEvent.txt", json_encode($dbvar));
                	$result['creada'] = date("Y-m-d H:i:s");
                    $result['usuario'] = $this->planificador->userData->username;
                    $result['idEvento'] = $db->InsertEvent($dbvar);
                	//$this->view->output = $result['idEvento'];
                }
                catch(Exception $ex){
                	$this->view->output = $ex;

                }
                //break;
                $result['username'] = $dbvar['turno'];
                
                $result['OU_ID'] = $this->planificador->userData->OU_ID;
                //file_put_contents("1.txt", "data");
                unset($result['creada']);
                unset($result['usuario']);
                $db1->InsertData($result);
                //file_put_contents("2.txt", "data");
                $db2->SetEventValue($result['idEvento'], 'className', 'Checklist_Pending');
                //file_put_contents("3.txt", "data");
                $this->view->output = $result['idEvento'];
                break;

            case 'upevent':
                $db = new Application_Model_DbTable_Calendar_Eventos();
                
                if($dbvar['remark']){
                    $db1 = new Application_Model_DbTable_Calendar_EventosLog();
                    $db1->__set('username',$this->planificador->userData->username);
                    $db1->__set('OU_ID',$this->planificador->userData->OU_ID);
                    $db1->log($dbvar['id'],json_encode(array('type'=>'comment','result'=>$dbvar['remark'])));
                    unset($dbvar['remark']);
                }

                if (!isset($dbvar['open-close-ticket'])){
                    $dbvar['open-close-ticket'] = null;
                    $dbvar['minutes-close-ticket'] = null;
                    $dbvar['status'] = 'Pending';

                } else {
                    $dbvar['open-close-ticket'] = true;
                    //$dbvar['status'] = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
                     $dbvar['status'] = 'Pending';
                }
				//file_put_contents("dbvarupdate.txt",json_encode($dbvar));
				if($dbvar['origen']== 'Journal'){
					unset($dbvar['group']);
					unset($dbvar['type']);
				}

				if($dbvar["origen"] == "Checklist")
				{
					$dbvar["Centro"] = "BCN - 22@";
				}
                    
				
				//file_put_contents("dbvarupdate.txt",json_encode($dbvar));
                $result = $db->UpdateEvent($dbvar,'id ='.$dbvar['id']);
                        // header('HTTP/1.1 500 Internal Server Booboo');
                        // header('Content-Type: application/json; charset=UTF-8');
                        // die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
                break;

            case 'rmevent':
                $db = new Application_Model_DbTable_Calendar_Eventos();
                
                //die();
               
                if(in_array("Admin", $this->planificador->userData->role) || in_array("Portal_Task_Admin", $this->planificador->userData->role))
                $result = $db->rmEvent($dbvar,'id ='.$dbvar['id']);
                else $result = "KO";
                $this->view->output = $result;
                break;
                

            case 'creventJournal':
                try{
                    
                    $dynamicTasks = $this->_getParam('dynamictasks');
                    
                    $db = new Application_Model_DbTable_Calendar_Tareas();
                                $dbev = new Application_Model_DbTable_Calendar_Eventos();
                                $dbEveOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
                                $clientedb = new Application_Model_DbTable_Clientes();
                                $dbLog = new Application_Model_DbTable_Calendar_EventosLog();
                    
                 

                    //TODO  Crear tarea, preparación de las variables (adaptarlas a tabla cal_tareas)
                    
                    $info =  isset($dbvar['info']) && $dbvar['info'] == "checked" ? true : false;
                    //$info = false;            
                    $params = array("idChange"=>"XXXXX","idTarea"=>"Parent","Status"=>"all tasks confirmed","Service"=>"","Environment"=>"Pro","Coordinator"=>"XXX","Approval Status"=>"","CBI"=>"MINOR","CI"=>"XXX");
                     $dbvar['params'] = json_encode($params); //Add parameters
                    
                    $dbvar['client'] = $dbvar['cliente']; //change cliente by client
					$dbvar['group'] = $dbvar['rgroup'];
                    unset($dbvar['cliente']);
                    $start = $dbvar['start'];
                    $end = $dbvar['end'];
                    $cliente = $dbvar['client'];
                    $dbvar['t_start'] = explode(" ",$dbvar['start'])[1]; //save time start and end
                    $dbvar['t_end'] = explode(" ",$dbvar['end'])[1];
                    unset($dbvar['start']);
                    unset($dbvar['end']);
                   
                    if(isset($dbvar['info']))
                    {
                        $info = true;
                        unset($dbvar['info']);
                    }
                    $dbvar['owner'] = "Morning";
                    $dbvar['type'] = $dbvar['group'];

                    
                   
                    $dbvar['customer_affected'] = $clientedb->getNombre($dbvar['client']);
                    $idtarea = $db->InsertTarea($dbvar);
                  

                    //TODO Crear eventos
                    //preparar variables
                    

                     $dbvar['cliente'] = $dbvar['client']; 
                        unset($dbvar['client']);
                        $dbvar['start'] = $start;
                        unset($dbvar['t_start']);
                        $dbvar['end'] = $end;
                        unset($dbvar['t_end']);
                        unset($dbvar['owner']);
                        $dbvar['idTarea'] = $idtarea;
                        $dbvar['customer_affected'] = $clientedb->getNombre($dbvar['cliente']);
                        $dbvar['creada'] = date("Y-m-d H:i:s");;
                        $dbvar['usuario'] = $this->planificador->userData->username;
                    
                    if($info) //solo parent
                    {
                        $paramsEvento = array("idChange"=>$dbvar['refer'],"idTarea"=>"Parent","Status"=>"XX","Service"=>"XX","Environment"=>"PRO","Approval Status"=>"","CI"=>"XX","Downtime?"=>"Total","Affected Groups"=>$this->planificador->userData->OU_ID,"Petitioner"=>$this->planificador->userData->display_name,"SDM"=>"XX","critical_services"=>null,"CBI"=>"MINOR");
                        $dbvar['params'] = json_encode($paramsEvento);
                       $idEvento = $dbev->InsertEvent($dbvar);   
                       $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Pending");  
                       
                       $dbLog->log($idEvento,json_encode(array('type'=>'MANUAL_INFO_Event_Creation','result'=>"Evento creado, Evento tipo INFO, Cambio (refer): ".$dbvar['refer'])),$this->planificador->userData->username);
                    }
                    else //We have to create Parent and child events
                    {
                        //TODO PARENT
                        $title = $dbvar['title'];
                        $description = $dbvar['description'];
                        $dbvar['description']  = $dbvar['group']." - ".$dbvar['description'];
                        $dbvar['title']  = $dbvar['rgroup']." - ".$dbvar['title'];
                       
                        $paramsEvento = array("idChange"=>$dbvar['refer'],"idTarea"=>"Parent","Status"=>"XX","Service"=>"XX","Environment"=>"PRO","Approval Status"=>"","CI"=>"XX","Downtime?"=>"Total","Affected Groups"=>$this->planificador->userData->OU_ID,"Petitioner"=>$this->planificador->userData->display_name,"SDM"=>"XX","critical_services"=>null,"CBI"=>"MINOR");
                        $dbvar['params'] = json_encode($paramsEvento);
                        $idEvento = $dbev->InsertEvent($dbvar);     
                        
                        $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Parent");  
                        

                        //TODO CHILD
                        //TODO modificar variables, especialmente params
                        $dbvar['description']  = $description;
                        $dbvar['title']  = $title;

                        $paramsEvento = array("idChange"=>$dbvar['refer'],"idTarea"=>$idtarea."_1","Status"=>"XX","Service"=>"XX","Environment"=>"PRO","Approval Status"=>"","CI"=>"XX","Downtime?"=>"Total","Affected Groups"=>$this->planificador->userData->OU_ID,"Petitioner"=>$this->planificador->userData->display_name,"SDM"=>"XX","critical_services"=>null,"CBI"=>"MINOR");
                        $dbvar['params'] = json_encode($paramsEvento);

                        for($i = 0; $i < count(); $i++){
                            
                        }
                        
                        $idEvento = $dbev->InsertEvent($dbvar);
                        
                        $dbEveOpciones->SetEventValue($idEvento,"className","Journal_Pending");  
                        $dbLog->log($idEvento,json_encode(array('type'=>'Event_Creation','result'=>"Evento creado, Cambio (refer): ".$dbvar['refer'])),$this->planificador->userData->username);           

                    }
                    

                   
                    
                    //TODO eventos opciones
                    
                    //TODO log
                }
                catch(Exception $ex){
                    file_put_contents("creartarea_exception.txt", $ex);
            }
                



            break;

           case 'rmtemp':
                $db = new Application_Model_DbTable_Calendar_Tareas();
                $dbev = new Application_Model_DbTable_Calendar_Eventos();
                $res = $dbev->countEvents($dbvar['id']);
                $result = $db->rmTemplate($res,'id ='.$dbvar['id']);
                break;

           case 'uptemp':

try{


                    $execvar = $this->_helper->myparams->cleanexec();
               
                $execvar['OU_ID'] =$this->planificador->userData->OU_ID;
                $execvar['owner'] = $this->planificador->userData->username;
                //UNSET($execvar['programacion']);


                if (!isset($execvar['open-close-ticket'])){
                    $execvar['open-close-ticket'] = null;
                    $execvar['minutes-close-ticket'] = null;
                }


                $execvar['schedule'] = date('Y-m-d' ).' 12:00:00';
                $db = new Application_Model_DbTable_Calendar_Tareas();
                $dbev = new Application_Model_DbTable_Calendar_Eventos();
                $res = $dbev->countEvents($execvar['id'],true);
                $r= $db->update($execvar,'id='.$execvar['id'] );

                $CRON = New Application_Model_CronProcess();
                $calendar = New Application_Model_Calendar_Calendar();
                $this->checklistnew = new Application_Model_Calendar_Calendar($this->_getAllParams());
                $result = $CRON->scheduleEvents('ok');
                  
                if($result){
                    foreach ($result as $res){
                        if(isset($res['date']))
                        {
                            echo 'Create events for template Id: '. $res['id'];
                                        $d= $res['date'][count($res['date'])-1] .' 00:05:00';
                                        $db->update(array('schedule' => $d),  'id = '.$res['id']);

                            foreach ($res['date'] as $rec_dat){

                               $this->checklistnew->NewEventR($res['id'], $rec_dat,$res['data']);
                            }
                         }
                    }

                }

                die();
}


catch(Exception $ex){
    file_put_contents("uptemp_exception.txt", $ex);
}

                //$result = $db->rmTemplate($res,'id ='.$dbvar['id']);
                Break;

            case 'files':
                // ini_set('set_time_limit', 0);
                // ini_set('ignore_user_abort', 1);
                // ini_set('max_execution_time', 0);

                $r = '';
                $task = New Application_Model_DbTable_Calendar_Tareas();
                set_include_path(get_include_path() . PATH_SEPARATOR . MODELS_PATH.'/PHPExcel/');
                $param['name'] = $this->_getParam('name');
                $param['path'] = dirname($_SERVER['SCRIPT_FILENAME']).'/raw_temp/files/';

                $inputFileName = $param['path'].$param['name'];
                $excel = new Application_Model_Calendar_ExcelImport(array(),$inputFileName);
                if($this->_getParam('type') ==='events')
                {
                    $result = $excel->GetExcelEvents();
                }
                elseif ($this->_getParam('type') ==='templates') {
                    $result = $excel->GetExcelTemplates();
                }
                elseif ($this->_getParam('type') ==='Jtemplate') {
                    ob_start();
                    $result = array();
				    $result = $excel->GetExcelJTemplates();
				   
				    ob_end_clean();
					
					//TODO borrar excel
	
					
					
				    $this->view->output = json_encode($result);
				    break;
                }

                if (isset($result['error'])){
                    $this->view->output = $r = json_encode(array('err' => 'E1666','errMsg' => $result['error']));;

                } else {


							$this->view->output = false;
                }

            Break;

            default:

                Break;
        }
    }
}
