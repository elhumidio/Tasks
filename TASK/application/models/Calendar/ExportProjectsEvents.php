<?php
class Application_Model_Calendar_ExportProjectsEvents
{
    
 public function   __construct(){
     
     $options = Zend_Registry::get ( 'configuracion.ini' );
     $this->urlportal = $options['urlportal'];	//'http://'.$_SERVER['HTTP_HOST'] no es válido porque en VMWZ422 está en http://10.49.162.33:880
     //$this->urlportalproyectos = str_replace("/task","/projects",$options['urlportal']);
	 $this->urlportalproyectos = $this->urlportal.'/projects';
     //$options = Zend_Registry::get ( 'configuracion.ini' );
     
      
 }
     /**
      * Get datos proyectos y tarea
      */
    public function GetDatosProject()
    {
	
        $projectIds = array();

        $wsdl =	$this->urlportalproyectos.'/soap?wsdl';
        $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
		//var_dump($client);die;
        $passCode = "rykeM8crwy";
        $grupo = "ES003691";
		$res =  $client->getTareasByGrupoResponsable($passCode,$grupo);
        //die(var_dump($res));
		//file_put_contents("jsonprojects.txt",$res);
        if(count(json_decode($res)) > 0)
        {
            $projectIds =  $this->insertTasksAndEvents($res);
           
        }
        
        return $projectIds; 
        
    }



      /**
     * Inserta tareas y eventos desde PROJECTS
     * @param unknown_type $resJSON
     */
    public function insertTasksAndEvents($resJSON)
    {
        $proj = new Application_Model_DbTable_Projects();
        $res = json_decode($resJSON,true);
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $UsuarioTareasDb = new Application_Model_DbTable_Calendar_UsersTareas();
        $TareasOptDb = new Application_Model_DbTable_Calendar_TareasOpciones();
        $calendar = new Application_Model_Calendar_Calendar();
        $arrayNoProcesados = array();
        
            //filtrar la lista con la tabla de proyectos ya tratados
            $filteredArray = array();
            foreach ($res as $r) {
               if(!$proj->checkProyecto($r['TAR_Codigo_Proyecto'],$r['TAR_Codigo']))
               {
                   array_push($arrayNoProcesados,$r);
               }
                  
            }
        
        $codproy = "";
       
        $arrayIDProyectos = array();
	
		 if(count($arrayNoProcesados) > 0)
        for ($i = 0; $i < count($arrayNoProcesados); $i++) {
            //continue;

            $idTarea = "";
            $contSufix = 0;
            $currentproj = "";
            
            if($codproy != $arrayNoProcesados[$i]['TAR_Codigo_Proyecto'])
            {
                //Insertar en tabla de verificación
                $contSufix = 0;
                $sufix = "";
                $currentproj = $arrayNoProcesados[$i]['TAR_Codigo_Proyecto'];
                //Crear parent, tarea y demas eventos únicos
                $dat = $this->getDataFromProjects($arrayNoProcesados[$i],true,$sufix);
                $dat['id'] = $TareasDb->InsertTarea($dat['data']);
                $idTarea = $dat['id']; 
                $UsuarioTareasDb->AsignarUsuario($dat['id'], $dat['data']['turno'], $dat['data']['OU_ID']);
                $TareasOptDb->SetTareasValue($idTarea, 'className','Journal_Pending');
                
                
                $d= $dat['date'][0][0].' 00:05:00';
               
                $TareasDb->update(array('schedule' => $d),  'id = '.$dat['id']);
                
                //Parent events
                $dat['data']['refer'] = $dat['idChange'];
                $this->NewEventJournalEasy($idTarea, $dat['date'] ,$dat['data'],$dat['dateend']);
				$dato = $arrayNoProcesados[$i]['TAR_Codigo_Proyecto'].'/'.$idTarea;
				$arrayNoProcesados[$i]['idTarea']= $idTarea;
				//file_put_contents("arrayPROYECTOSasdasd.txt" , $dato,FILE_APPEND);
				array_push($arrayIDProyectos,$arrayNoProcesados[$i]['TAR_Codigo_Proyecto']."/".$idTarea);
				$codproy = $arrayNoProcesados[$i]['TAR_Codigo_Proyecto'];
                
				
				//creamos los eventos children
				for ($m = 0; $m < count($arrayNoProcesados); $m++) 
				{
					
					if($arrayNoProcesados[$m]['TAR_Codigo_Proyecto'] == $codproy )
					{
						$sufix ++;
						//Children events
						//$contSufix ++;
						//file_put_contents("arrayPROYECTOSasdasd.txt" ,json_encode($dat));
						$dat = $this->getDataFromProjects($arrayNoProcesados[$m],false,$sufix);
						$dat['data']['refer'] = $dat['idChange'];
						//file_put_contents("arrayPROYECTOSasdasd.txt" ,json_encode($dat));die;
						$this->NewEventJournalEasyE($idTarea, $dat['date'] ,$dat['data'],$dat['dateend']);
					}
				}
				
            }
        
        }

        
        //Marcamos los procesados
        foreach ($arrayNoProcesados as $r) {
                $proj->insertIdProj($r['TAR_Codigo_Proyecto'],$r['TAR_Codigo']);
				$this->updateDatesParent($r['TAR_Codigo_Proyecto'],$r['idTarea']);
            }
       
        return $arrayNoProcesados;
    }
	
	 /**
     * Selecciona el estado de cierre en task según el estado a SM9
     * @param string $code
     * @return string
     */
    private function selectStateByCode($code)
    {
        switch ($code) {
            case "REVOKED":
                return "Cancelada";
            ;
            break;
            case "REVOKED_WITH_FALLBACK" :
                return "Cancelada";
            break;
            
            default:
                return "Finalizada";
                ;
            break;
        }
            
    }

    public function convertMinutesToDecimal($minutes)
    {
        return $minutes / 60;
    }

 function getClosestNr($array, $nr) {
  // PHP-MySQL Course - http://coursesweb.net/php-mysql/
  sort($array);      // Sorts the array from lowest to highest

  // will contain difference=>number (difference between $nr and the closest numbers which are lower than $nr)
  $diff_nr = array();

  // traverse the array with numbers
  // stores in $diff_nr the difference between the number immediately lower / higher and $nr; linked to that number
  foreach($array AS $num){
    if($nr > $num) $diff_nr[($nr - $num)] = $num;
    else if($nr <= $num){
      // if the current number from $array is equal to $nr, or immediately higher, stores that number and difference
      // and stops the foreach loop
      $diff_nr[($num - $nr)] = $num;
      break;
    }
  }
  krsort($diff_nr);        // Sorts the array by key (difference) in reverse order
  return end($diff_nr);    // returns the last element (with the smallest difference - which results to be the closest)
}
	
	 /**
     * Cierra una tarea del Project
     * @param string $passCode
     * @param string $CodTarea
     * @param string $comments
     * @param string $code
     * @param string $ActualStart
     * @param string $ActualEnd
     * @param string $user
     * @return string
     */
    public function cierraTareaProject($passCode,$CodTarea,$comments,$code,$ActualStart,$ActualEnd,$user)
    {    // file_put_contents("json.txt", "asdasda");
      
      try{
        $codeBy = $this->selectStateByCode($code);
        $pass = $passCode;
        $idtarea = $CodTarea;
        $comm = str_replace('\"', '', $comments);
        $start = $ActualStart;
        $end = $ActualEnd;
        $d1=DateTime::createFromFormat('D M d Y H:i:s e+', $ActualStart);
        $d2=DateTime::createFromFormat('D M d Y H:i:s e+', $ActualEnd); 
        /*$d1=new DateTime($ActualStart); 
        $d2=new DateTime($ActualEnd);*/ 
        $duracion_estimada_real=$d2->diff($d1);
        $format = "Y-m-d H:i:s";
        $possibleValues = array(0.1,0.15,0.2,0.3,0.4,0.5,0.75,1,1.25,1.5,1.75,2,2.25,2.5,2.75,3,3.5,4,4.5,5,5.5,6,6.5,7,7.5,8,9,10,11,12,13,14,15);
        $str_duracion_estimada = ($duracion_estimada_real->d * 24) +  $duracion_estimada_real->h + $this->convertMinutesToDecimal($duracion_estimada_real->i);
        $wsdl = $this->urlportalproyectos.'/soap?wsdl';
        $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
        

        $str_duracion_estimada_combo = $this->getClosestNr($possibleValues,$str_duracion_estimada);
      // file_put_contents("datos de cierra tarea.txt", $pass." - ".$idtarea." - ".$codeBy." - ".$comm." - ".date_format($d1,$format)." - ".date_format($d2,$format)." - ".$str_duracion_estimada_combo." - ".round($str_duracion_estimada,2)." - ".$user); 
        
        $data = array("passCode"=>trim($pass),"CodTarea"=>trim($idtarea),"status"=>trim($codeBy),"comments"=>trim($comm),"ActualStart"=>date_format($d1,$format),"ActualEnd"=>date_format($d2,$format),"duracion_real"=>round(trim($str_duracion_estimada),2),"duracion_estimada_real"=>trim($str_duracion_estimada_combo),"user"=>trim($user));
        //file_put_contents("jsonDATA.txt", json_encode($data));
        return $client->cierraTareaTask2(trim(json_encode($data)));
        //return $client->cierraTareaTask($pass,$idtarea,$codeBy,$comm,date_format($d1,$format),date_format($d2,$format),$str_duracion_estimada_combo,round($str_duracion_estimada,2),$user);
      
        //return "OK";
      }
      catch(SoapFault  $ex){
        file_put_contents('cierraTareaProject_Exception.txt', $ex);
        return "KO";
      }
        return "OK";
    }


	
	public function updateDatesParent($projectId,$idTarea)
	{
		$eventos = new Application_Model_DbTable_Calendar_Eventos();
		$values = $eventos->GetMaxMinDatesByProjectId($projectId,$idTarea);
		//file_put_contents("values.txt",json_encode($values));
		$eventos->updateParentDates($values,$projectId,$idTarea);
      
	}
    
    /**
     * Funcion que obtiene y ordena los datos del excel Journal
     * @param string $da array con los datos de la fila del excel
     * @param int $len, posicion.
     * @return array $result
     */
    public function getDataFromProjects($da,$parent,$sufix)
    {
     
        try{
    
          //  if (strlen(implode($da)) > 0)
          //  {
                 
                $xxx = array();
                $xx = 1;
                $tipoTarea = "";
                //$tipoTarea = $parent == 0 ? "Parent" : $da[0]."_". ($len +1);
                $tipoTarea = $parent  ? "Parent" : $da['TAR_Codigo'];
               // $idChange = $parent ? $da['TAR_Codigo_Proyecto'] : $da['TAR_Codigo_Proyecto']."_".$sufix;
                $k= array('turno','centro','t_start','t_end','client', 'customer_affected', 'title', 'description','origen','OU_ID','creada','programacion','owner','params','group','type','refer');
                $i = json_encode(array(
                        'idChange' => $da['TAR_Codigo_Proyecto'], 'idTarea' => $tipoTarea, 'Status'=> 'Pending',
                        'Service' => '', 'Environment' => '',
                        'Approval Status' => '', 'CI' => '', 'Downtime?' => '',
                        'Affected Groups' => '', 'Petitioner' => $da['JefeProyecto'], 'SDM' => ''
                ));
                 
                $da_arr[] = array(date("Y/m/d", strtotime($da['TAR_Fecha_inicio'])));
                $da_arrend[] = array(date("Y/m/d", strtotime($da['TAR_Fecha_limite'])));
                $res['idChange'] = $da['TAR_Codigo_Proyecto'];
                
                $rs[0] = 'Morning';
                $rs[1] = 'BCN - 22@';
                $rs[2] = date("H:i", strtotime($da['TAR_Fecha_inicio']));
                $rs[3] = date("H:i", strtotime($da['TAR_Fecha_limite']));
                $rs[4] = '87'; // CTTI
                //$rs[5] = 'T-Systems Iberia/T-Systems Iberia';
				
				$rs[5] = 'T-SYSTEMS IBERIA/T-SYSTEMS IBERIA';
                $rs[6] =   $parent ? "EASY - ".$da['INV_Cliente']." - ".$da['nomProyecto'] : $da['TAR_Titulo']; 
                $rs[7] = $parent ? $da['nomProyecto'] : $da['TAR_Descripcion'];
                $rs[8] = "Journal";
                $rs[9] = $da['PRO_Creador_Grupo']; 
                $rs[10] = date("Y-m-d H:i:s");
                $rs[11] = (isset($xxx))?json_encode ($xxx):json_encode (array());
                $rs[12] = $da['PRO_Creador'];
                $rs[13] = $i;
                $rs[14] = 'EASY';
                $rs[15] = 'EASY';
                $rs[16] = $da['TAR_Codigo_Proyecto'];
    
                $res['data'] = array_combine($k, $rs);
                if($parent == 0){
                    $res['parent'] = "parent";
                }
                $res['dateend'] = $da_arrend;
                $res['date'] = $da_arr;
                $res['source'] = "JournalEasy";
    
          //  }
    
            return $res;
        }
        catch(Zend_Exception $e)
        {
    
            file_put_contents("exc1.txt" , "Caught exception: " . get_class($e) . "\n");
    
            file_put_contents("exc2.txt" , "Message: " . $e->getMessage());
        }
    
    
    }
	    /**
     * Crea un Evento Recurrente proveniente de Easy (journal)
     * @param array $params
     */
    public function NewEventJournalEasyE($new_tarea, $rec_dat, $dbvar,$dateend)
    {
		try{
			
		/*	if(!isset($dbvar['title']))  return json_encode(array('error'=>array('title'=>'Falta dato.')));
        if(!isset($dbvar['allDay']))  $dbvar['allDay'] = null;
        if(!isset($dbvar['rgroup']))  $dbvar['rgroup'] = '';
        if(!isset($dbvar['t_start']))  return json_encode(array('error'=>array('start'=>'Falta dato.')));
         
        if(!isset($dbvar['origen']))  return json_encode(array('error'=>array('origen'=>'Falta dato.')));
        if(!isset($new_tarea))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        if(!is_numeric($new_tarea)) return json_encode(array('error'=>array('id'=>'Formato incorrecto.')));*/
    
      //file_put_contents("fechas.txt",json_encode($rec_dat)." - ".json_encode($dbvar['start'])." - ".json_encode($dateend));	
        $dbvar['start'] =  substr($rec_dat[0][0],0,4).'-'.substr($rec_dat[0][0],5,2).'-'.substr($rec_dat[0][0],8,2). ' ' .$dbvar['t_start'].':00';
        $dbvar['end'] =  substr($dateend[0][0],0,4).'-'.substr($dateend[0][0],5,2).'-'.substr($dateend[0][0],8,2). ' ' .$dbvar['t_end'].':00';
		
        $dbvar['idtarea'] = $new_tarea;
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $UsuarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
 
        // Parche para la la fecha de google Chrome
        if(strpos($dbvar['start'],'(')===false)
        {
            	
            $start = new DateTime(substr($dbvar['start'], 2));
            	
        }
        else{
            $start = DateTime::createFromFormat('D M d Y H:i:s e+', substr($dbvar['start'], 2));
        }
    
         
        $startData = $start->format('Y-m-d H:i:s');
        $startDataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
    
        if(isset($dbvar['end']) && $dbvar['end']!='null') {
            $end = new DateTime(substr($dbvar['end'], 2));
            $enddata = $end->format('Y-m-d H:i:s');
            $enddataBis = $end->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
        else {
            //P0001-00-04T00:00:00
            $start->add(new DateInterval('P0000-00-00T'.$dbvar['minimo']));
            $enddata = $start->format('Y-m-d H:i:s');
            $enddataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
    
        $allDay = is_null($dbvar['allDay']) || $dbvar['allDay']=='false' || $dbvar['allDay']=='null'?new Zend_Db_Expr('NULL'):1;
        // status evento segun 'open-close-ticket'
        if (isset($dbvar['open-close-ticket'])){
            $status = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
    
        } else {
            $status = 'Pending';
            $dbvar['open-close-ticket'] = null;
            $dbvar['minutes-close-ticket'] = null;
    
        }
    
          $enddataArray = explode(' ',$enddata);  
          if($enddataArray[1] == "00:00:00"){
            $endmod = $enddataArray[0]." 23:59:00";
          }
              

        $data = array('id'=>null,'title'=>$dbvar['title'],'start'=>$startData,'end'=>$endmod,'description'=>($dbvar['description']),
                'origen'=>$dbvar['origen'],'idtarea'=>$dbvar['idtarea'],'turno'=>$dbvar['turno'] ,'refer' =>(is_null($dbvar['refer']))?'':$dbvar['refer'],
                'centro'=>$dbvar['centro'], 'cliente'=>$dbvar['client'], 'group'=>$dbvar['group'], 'rgroup'=>$dbvar['rgroup'],'type' =>(is_null($dbvar['type']))?'Manual':$dbvar['type'] ,
                'params' =>(is_null($dbvar['params']))?'':$dbvar['params'],'customer_affected' =>(is_null($dbvar['customer_affected']))?'':$dbvar['customer_affected'],
                'open-close-ticket' => $dbvar['open-close-ticket'],'minutes-close-ticket' => $dbvar['minutes-close-ticket'],
                'status' => $status,'creada'=>  date("Y-m-d H:i:s"), 'usuario' => "EASY"
        );
    
        if($allDay===1){
            $data['allDay'] = $allDay;
        }
		
	
			$idevento = $EventsDb->insert($data);
	
	
        
    
        // parches para que se muestre el evento y la tarea cuando se hace el drop
        $data['id']=$idevento;
        $data['username'] = $dbvar['turno'];//$this->userData->username;
        $data['OU_ID'] = isset($this->userData->OU_ID)?$this->userData->OU_ID:NULL;
        $data['start'] = $startDataBis;
        $data['end'] = $enddataBis;
        $data['open-close-ticket'] = $dbvar['open-close-ticket'];
    
        $UsuarioDb->insert(array('idevento'=>$idevento,'username'=>$dbvar['turno'],'OU_ID'=>$data['OU_ID']));
    
        self::SetEventosOptions($idevento,$data);
        $eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $eLog->log($idevento,sprintf('Evento creado. Fecha inicio %s, Fecha fin: %s.',$startData,$enddata));
    
        $tareasLog = new Application_Model_DbTable_Calendar_TareasLog();
        if(!is_null($dbvar['idtarea'])) $tareasLog->log($dbvar['idtarea'],'Tarea programada.');
    
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $TareasDb->SetFecha($dbvar['idtarea'],'NOW()','programada');
        return $data;
    
		}
		catch(Exception $ex) {
			file_put_contents("exceptionEASY.txt",$ex);
		}
return null;
    }
    
    
    /**
     * Crea un Evento Recurrente proveniente de Easy (journal)
     * @param array $params
     */
    public function NewEventJournalEasy($new_tarea, $rec_dat, $dbvar,$dateend)
    {
		try{
			
			if(!isset($dbvar['title']))  return json_encode(array('error'=>array('title'=>'Falta dato.')));
        if(!isset($dbvar['allDay']))  $dbvar['allDay'] = null;
        if(!isset($dbvar['rgroup']))  $dbvar['rgroup'] = '';
        if(!isset($dbvar['t_start']))  return json_encode(array('error'=>array('start'=>'Falta dato.')));
         
        if(!isset($dbvar['origen']))  return json_encode(array('error'=>array('origen'=>'Falta dato.')));
        if(!isset($new_tarea))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        if(!is_numeric($new_tarea)) return json_encode(array('error'=>array('id'=>'Formato incorrecto.')));
    
      
        $dbvar['start'] =  substr($rec_dat[0][0],0,4).'-'.substr($rec_dat[0][0],5,2).'-'.substr($rec_dat[0][0],8,2). ' ' .$dbvar['t_start'].':00';
        $dbvar['end'] =  substr($dateend[0][0],0,4).'-'.substr($dateend[0][0],5,2).'-'.substr($dateend[0][0],8,2). ' ' .$dbvar['t_end'].':00';
		
        $dbvar['idtarea'] = $new_tarea;
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $UsuarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
 
        // Parche para la la fecha de google Chrome
        if(strpos($dbvar['start'],'(')===false)
        {
            	
            $start = new DateTime(substr($dbvar['start'], 2));
            	
        }
        else{
            $start = DateTime::createFromFormat('D M d Y H:i:s e+', substr($dbvar['start'], 2));
        }
    
         
        $startData = $start->format('Y-m-d H:i:s');
        $startDataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
    
        if(isset($dbvar['end']) && $dbvar['end']!='null') {
            $end = new DateTime(substr($dbvar['end'], 2));
            $enddata = $end->format('Y-m-d H:i:s');
            $enddataBis = $end->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
        else {
            //P0001-00-04T00:00:00
            $start->add(new DateInterval('P0000-00-00T'.$dbvar['minimo']));
            $enddata = $start->format('Y-m-d H:i:s');
            $enddataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
    
        $allDay = is_null($dbvar['allDay']) || $dbvar['allDay']=='false' || $dbvar['allDay']=='null'?new Zend_Db_Expr('NULL'):1;
        // status evento segun 'open-close-ticket'
        if (isset($dbvar['open-close-ticket'])){
            $status = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
    
        } else {
            $status = 'Pending';
            $dbvar['open-close-ticket'] = null;
            $dbvar['minutes-close-ticket'] = null;
    
        }
    
          $enddataArray = explode(' ',$enddata);  
          if($enddataArray[1] == "00:00:00"){
            $endmod = $enddataArray[0]." 23:59:00";
          }
              

        $data = array('id'=>null,
              'title'=>$dbvar['title'],
              'start'=>$startData,
              'end'=>$endmod,
              'description'=>($dbvar['description']),
              'origen'=>$dbvar['origen'],
              'idtarea'=>$dbvar['idtarea'],
              'turno'=>$dbvar['turno'] ,
              'refer' =>(is_null($dbvar['refer']))?'':$dbvar['refer'],
              'centro'=>$dbvar['centro'], 
              'cliente'=>$dbvar['client'], 
              'group'=>$dbvar['group'], 
              'rgroup'=>$dbvar['rgroup'],
              'type' =>(is_null($dbvar['type']))?'Manual':$dbvar['type'] ,
              'params' =>(is_null($dbvar['params']))?'':$dbvar['params'],
              'customer_affected' =>(is_null($dbvar['customer_affected']))?'':$dbvar['customer_affected'],
              'open-close-ticket' => $dbvar['open-close-ticket'],'minutes-close-ticket' => $dbvar['minutes-close-ticket'],
              'status' => $status,
              'creada'=>  date("Y-m-d H:i:s"), 
              'usuario' => 'EASY'
        );
    
        if($allDay===1){
            $data['allDay'] = $allDay;
        }
		
	
			$idevento = $EventsDb->insert($data);
	
	
        
    
        // parches para que se muestre el evento y la tarea cuando se hace el drop
        $data['id']=$idevento;
        $data['username'] = $dbvar['turno'];//$this->userData->username;
        $data['OU_ID'] = isset($this->userData->OU_ID)?$this->userData->OU_ID:NULL;
        $data['start'] = $startDataBis;
        $data['end'] = $enddataBis;
        $data['open-close-ticket'] = $dbvar['open-close-ticket'];
    
        $UsuarioDb->insert(array('idevento'=>$idevento,'username'=>$dbvar['turno'],'OU_ID'=>$data['OU_ID']));
    
        self::SetEventosOptions($idevento,$data);
        $eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $eLog->log($idevento,sprintf('Evento creado. Fecha inicio %s, Fecha fin: %s.',$startData,$enddata));
    
        $tareasLog = new Application_Model_DbTable_Calendar_TareasLog();
        if(!is_null($dbvar['idtarea'])) $tareasLog->log($dbvar['idtarea'],'Tarea programada.');
    
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $TareasDb->SetFecha($dbvar['idtarea'],'NOW()','programada');
        return $data;
    
		}
		catch(Exception $ex) {
			file_put_contents("exceptionEASY.txt",$ex);
		}
			return null;
    }
    
    /**
     * Asigna diferentes opciones al evento dependiendo de su origen
     *
     * @param int $idevento
     * @param array $datos
     */
    private function SetEventosOptions ($idevento,$datos)
    {
        $db = new Application_Model_DbTable_Calendar_EventosOpciones();
        switch ($datos['origen'])
        {
            case 'Cosima':
            case 'Projects':
            case 'Portal':
            case 'Journal':$db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>'Journal_Pending'));
            //file_put_contents ( "eventos_opciones.txt" , $idevento." - ".json_encode($datos['origen']),FILE_APPEND);
            Break;
            case 'Checklist':
    
                //                 $value = ($datos['open-close-ticket']) ? "Checklist_Pending_ticket" : 'Checklist_Pending';
                //                 $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>$value));
                $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>'Checklist_Pending'));
    
                Break;
            default: $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>'Cosima')); Break;
        }
    }
	
	

    /**
     * Inicia tarea en EASY, añade responsable y cambia estado
     * @param string $passCode
     * @param string $idTarea
     * @param string $resp
	 * @return string
     */
    public function iniciaTareaProjects($passCode,$idTarea,$resp)
    {
      $pc = $passCode;
      $id = $idTarea;
      $r = $resp;
          try{
          $wsdl =   $this->urlportalproyectos.'/soap?wsdl';
          $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
         // die;
		    $res = $client->iniciaTareaProjectsFromTask($pc,$id,$r); 
		// file_put_contents ( "exportprojectseventsANTES.txt" , $idTarea);
        
         if($res = "OK")
             return "OK";
           else return "KO";
		}
		catch(Exception $ex){
			file_put_contents ( "ExcepcionInvocacion_iniciaTareaProjectsFromTask.txt" , $ex);
			return "KO";
		}
        return "";
    }

      /**
     * Close EASY events automatically when correspondent task in PROJECTS turns to "Finalizada"
     * @param array $Tarea
     * @param array $event
     */
    public function closeEasyTaskTicket($Tarea,$event)
    {
        //file_put_contents("valores en closeEasyTaskTicketTASKcloseEasyTaskTicketTASK.txt",json_encode($Tarea));
		//file_put_contents("valores en closeEasyTaskTicketEVENT.txt",json_encode($event));
        $eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $json = json_encode(array(  'type'   =>'closeevent_automatically', 'result' =>'evento cerrado automaticamente'));
        
            //file_put_contents("cierreautoIDLog.txt", json_encode($event));
            $id =trim($event['id'],'"');
            $eLog->log($id,$json);
            $format = 'Y-m-d H:i:s';
            $now = new DateTime("now");
            $sm9 = New Application_Model_SM9Change();
            $timezone = date_default_timezone_get();
          
           
                $dateTmp = new DateTime($Tarea['TAR_Fecha_Pla_Inicio'], new DateTimeZone('UTC'));
                $dateTmp->setTimeZone(new DateTimeZone($timezone));
                $dateTmp = $dateTmp->format(DATE_ATOM);
                $term['TaskID'] = $Tarea['ID'];
                $term['ActualStart'] = $dateTmp;
        
                $dateTmp = new DateTime($now->format('Y-m-d H:i:s'), new DateTimeZone('UTC'));
                $dateTmp->setTimeZone(new DateTimeZone($timezone));
                $dateTmp = $dateTmp->format(DATE_ATOM);
                $term['ActualEnd'] = $dateTmp;
                    
                $term['ClosureComments'] = "Evento cerrado automaticamente";
                $term['ResolutionCode'] = "COMPLETE";
                $test = $sm9->CloseTsiChangeTaskXML2($term,'Journal');
             
            $eve = new Application_Model_DbTable_Calendar_Eventos();
        
           
            $arrayParams = json_decode($event['params'],true);
            $arrayParams['closingmode'] = "automatic";
            $paramsjson = json_encode($arrayParams);
			
            $ret = $eve->UpdateEvent(array('status'=>'Finish','params'=>$paramsjson	),'id = '.$event['id'] );
             
            $db = new Application_Model_DbTable_Calendar_EventosOpciones();
            $where['`idEvento` = ?'] = $event['id'];
            $where['`option` = ?']  = 'className';
            $db->update(array('value'=>'Journal_Finish'), $where);
    }
    
    /**
     * Update events from EASY
     * @return string
     */
    function UpdateEasyEvents()
    {
    	$eventos = new Application_Model_DbTable_Calendar_Eventos();
      $wsdl = $this->urlportalproyectos.'/soap?wsdl';
     
     
    	
      $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
    	$openEvents = $eventos->getEventsEasyNotFinish();
      //file_put_contents("openevents.txt", $openEvents);      
        if($openEvents == "KO")
            return "There is no modifiable events";
        try{

	        foreach ($openEvents as $event) {
	        		 $arraydatos = json_decode($event['params'],true);
          			 $passCode = "2VJyqA9QmWv0";
					       $idtarea = trim($arraydatos['idTarea']);
          			
            	 $easyTask = $client->consultaTarea(trim($passCode),trim($idtarea));
                 $easyProy = $client->consultaProyectoDB("751af18477dffe",trim($easyTask[0]['TAR_Codigo_Proyecto']));
            		 
					 
            		 $this->UpdateSingleEvent($event,$easyTask,$easyProy[0]['JefeProyecto']);
	        }	

        }catch(Exception $ex){
        	file_put_contents("excepcionNuevoMetodo.txt", $ex);
        }
        
    }

      /**
     * Update event from EASY
     * @return string
     */
    function UpdateSingleEvent($event,$easy,$jefeproyecto)
    {
    	//file_put_contents("testevent.txt", json_encode($event).$easy[0]['TAR_Estado']);
		$cont = 0;
    	$easyTask = $easy[0];
      //Equivalencia entre EASY status y Bitacora Status
      $updatedStatus = $easyTask['TAR_Estado'];
      $updatedStatusEASY = "";
      switch ($updatedStatus) {
        case 'Finalizada':
          $updatedStatusEASY = "Finish";
          break;
            case 'Cancelada':
          $updatedStatusEASY = "Cancel";
          break;
            case 'Abierta':
          $updatedStatusEASY = "Pending";
          break;
            case 'Pausada':
          $updatedStatusEASY = $updatedStatusEASY;
          break;
            case 'En Curso':
          $updatedStatusEASY = "Progress";
          break;
            case 'Fatal':
          $updatedStatusEASY = "Error";
          break;
        
        
      }
//TODO SI es Progress falta iniciar correctamente la TASK (SM9) CONSULTAR
    	
    	$status ="";


    	$idTarea = $easyTask['TAR_Codigo'];
    	 
                $params = json_encode(array(
                        'idChange' => $easyTask['TAR_Codigo_Proyecto'], 'idTarea' => $idTarea, 'Status'=> $event['status'],
                        'Service' => '', 'Environment' => '',
                        'Approval Status' => '', 'CI' => '', 'Downtime?' => '',
                        'Affected Groups' => '', 'Petitioner' => $jefeproyecto, 'SDM' => ''
                ));
				
				$start = $easyTask['TAR_Fecha_inicio']." "."00:00:00";
				$end   = $easyTask['TAR_Fecha_limite']." "."23:59:00";
								if($easyTask['TAR_Fecha_limite'] == "" || $easyTask['TAR_Fecha_limite'] == null)
									$end = gmdate('Y-m-d 23:59:00', time());
                $data  = array('title'=>$easyTask['TAR_Titulo'],'start'=>$start,'end'=>$end,'description'=>$easyTask['TAR_Descripcion'],
                'origen'=>$event['origen'],'idTarea'=>$event['idTarea'],'turno'=>$event['turno'] ,'refer' =>$event['refer'],
                'centro'=>$event['centro'], 'cliente'=>$event['cliente'], 'group'=>"EASY", 'type' =>"EASY",'params' =>$params,'customer_affected' =>$event['customer_affected'],
                'status' => $updatedStatusEASY );
                
		      $eve = new Application_Model_DbTable_Calendar_Eventos();
				  $where['`id` = ?'] = $event['id'];
       
          $ret = $eve->UpdateEvent($data,$where);
          $eventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
		  //file_put_contents("updateevent.txt",$event['id']."Journal_".$updatedStatusEASY);
		  $eventosOpciones->UpdateEventValue($event['id'],"Journal_".$updatedStatusEASY);
		  
          //$eventosOpciones->SetEventValue($event['id'],"className","Journal_".$updatedStatusEASY);
       
  				$eventtime = explode(" ",$event['end']);
  				$eventtimecomp = strtotime($eventtime[0]);
  				$tasktimecomp = strtotime($easyTask['TAR_Fecha_limite']);
				
				  if($tasktimecomp > $eventtimecomp)
            	{
                 $this->updateDatesParent($easyTask['TAR_Codigo_Proyecto'],$event['idTarea']);
					  	}
			
    }


    /**
     * Consulta estado de una tarea
     * @param string $pass
     * @param string $idtarea
     */
    public function consultaEstadoTarea($idtarea)
    {
       
      try
      {
          $this->urlportalproyectos = $this->urlportal.'/projects';
          $wsdl = $this->urlportalproyectos.'/soap?wsdl';
          $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
          $estado = $client->consultaEstadoTarea("319e5f0524fed0",$idtarea);
        return $estado;
      }
      catch(Exception $ex)
      {
        return $ex;
      }
     
    }

    
    /**
     * Busca tareas cerradas y cierra el evento correspondiente
     * @return string
     */
    public function CloseEventsByFinishedTasks()
    {
        $eLog = new Application_Model_DbTable_Calendar_EventosLog();
       
        $wsdl = $this->urlportalproyectos.'/soap?wsdl';
       
        $client = new SoapClient($wsdl, array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
        $eventos = new Application_Model_DbTable_Calendar_Eventos();
        $ids = "";
        $cont = 0;
        $openEvents = $eventos->getEventsEasyNotFinish();
       
        if($openEvents == "KO")
            return "There is no closable events";
        try{
            foreach ($openEvents as $event) {
            
            $arraydatos = json_decode($event['params'],true);
			$idtarea = $arraydatos['idTarea'];
            $passCode = "2VJyqA9QmWv0";
            $Tarea = $client->consultaTarea($passCode,$idtarea);
            
              if(isset($Tarea[0]['TAR_Estado']) && ($Tarea[0]['TAR_Estado'] == "Finalizada" || $Tarea[0]['TAR_Estado'] == "Cancelada"))
              {
                 
                  $this->closeEasyTaskTicket($Tarea[0],$event);
                  $eLog->log($event['id'],'Evento cerrado automaticamente.',"EASY");
                  if($cont == 0)
                      $ids = $ids.$event['id'];
                  else  $ids .= " - ".$event['id'];
                  $cont++;
              }
            
          }
          return $ids;
        }
        catch(Exception $e){
       
        }
        
    }
    
    
}