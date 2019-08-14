<?php
class Application_Model_DbTable_Calendar_Eventos extends Zend_Db_Table {
    
    protected $_name = "cal_eventos";
    protected $_primary = 'id';
    
    /**
     * Verifica si una tarea está programada
     * @param int $idTarea
     */
    public function CheckTarea($idTarea)
    {
        return parent::fetchRow(array('idTarea=?'=>$idTarea));
    }
    
    /**
     * Gets intervention events
     * @return array
     */
    public function GetInterventionEvents()
    {
    	$eventos = new Application_Model_DbTable_Calendar_Eventos();
    	$query = "SELECT * from cal_eventos WHERE `type` = 'EASY_INTERVENCION'";
    	$stmt = $this->_db->query($query);
    	$rows = $stmt->fetchAll();
    	return $rows;
    }
    
    /**
     * Funcion para utilizar por unica vez, para actualizar campo params con INFO
     * @return array
     */
    public function updateeventosparent()
    {
        
        $query = "SELECT id, refer , count(*) as cant,params FROM cal_eventos 
                 WHERE origen = 'Journal'  
                 GROUP BY refer HAVING (cant > 1) 
                 ORDER BY start desc;";
        $stmt = $this->_db->query($query);
        $rows = $stmt->fetchAll();
        
        for ($i = 0; $i <count($rows); $i++) {
            $parsedParams = json_decode($rows[$i]['params'],true);
            $parsedParams['INFO'] = false;
            $rows[$i]['params'] = json_encode($parsedParams);
        }
        //update
        
        for($a = 0; $a < count($rows); $a++)
        {
            $data = array('params' => $rows[$a]['params']);
            $where['id = ?'] = $rows[$a]['id'];
            parent::update($data, $where);
        }
        
        return $rows;
    }
	
	public function GetMaxMinDatesByProjectId($id,$idTarea)
	{
		//file_put_contents("selectdates.txt" ,$id." - ".$idTarea);	
		$ret = array();
		 $like = "'%Parent%'";
		 $selectmin = parent::select()->from($this,array('start'=>'start'))->where('refer =?',$id)->where('params not like '.$like)->where('idTarea  =?',$idTarea);
		 $selectmax = parent::select()->from($this,array('end'=>'end'))->where('refer =?',$id)->where('params not like '.$like)->where('idTarea  =?',$idTarea);
		//  file_put_contents("selectdates.txt" ,$selectmin."             ------                ".$selectmax);	
		 $min = min(parent::fetchAll($selectmin)->toArray());
		 $max = max(parent::fetchAll($selectmax)->toArray());
		 array_push($ret,$min);
		 array_push($ret,$max);
		 
		 return $ret;	
	
	}
	
	/**
     * Set info about "significant" state
     * @param array $infoEvent
     * @return string
     */
	public function setSignificant($infoEvent,$user,$OU_ID)
	{
		  $eventosLog = new Application_Model_DbTable_Calendar_EventosLog();
		  $like = "%Parent%";
        $sql = parent::select()->where('idTarea= ?', $infoEvent['idtarea'] )->where('params like ?',$like);
		
		
		 $result = self::fetchAll($sql)->toArray();
		
        
        $params = json_decode($result[0]['params'],true);

		if(!isset($params['CBI']))
		{
			$params['CBI'] = "SIGNIFICANT";	
			$data = array("params" => json_encode($params));
            $where['id = ?'] = $result[0]['id'];
            parent::update($data, $where);
            return "ADDED";
		}	
		if($params['CBI'] == "MINOR")
            {
				$mje = 'CBI Significant asignado por el usuario '.$user;
                $params['CBI'] = "SIGNIFICANT";
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
				$eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "YES";
            }
            else{
				$mje = 'CBI Minor asignado por el usuario '.$user;
                $params['CBI'] = "MINOR";
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
				$eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "NO";
            }    
        
       return "NO";
	}
	
	/**
     * Set info about "protocolo critico" state
     * @param array $infoEvent
     * @return string
     */   
    public function setInfoProtocoloCritico($infoEvent,$user,$OU_ID)
    {
        $eventosLog = new Application_Model_DbTable_Calendar_EventosLog();
		//file_put_contents("datos.txt", json_encode($infoEvent));die;
        $like = "%Parent%";
        $sql = parent::select()->where('idTarea= ?', $infoEvent['idtarea'] )->where('params like ?',$like);
       // file_put_contents("SQL.txt", $sql);die;
        $result = self::fetchAll($sql)->toArray();
        
        $params = json_decode($result[0]['params'],true);
        $user = $user == null ? "" : $user; 
        if(!isset($params['critical_services']))
        {
            $mje = 'Protocolo Critico asignado por el usuario '.$user;
                $params['critical_services'] = "Yes";
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
                $eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "YES";
        }
            //file_put_contents("isset.txt",json_encode($params['critical_services']));
            if($params['critical_services'] == "Yes")
            {
				
				$mje = 'Protocolo Critico desasignado por el usuario '.$user;
                $params['critical_services'] = null;
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
				$eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "NO";
            }
            else{
				$mje = 'Protocolo Critico asignado por el usuario '.$user;
                $params['critical_services'] = "Yes";
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
                $eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
				return "YES";
            }    
        
       return "NO";
    }
	
	/**
     * Set info about "protocolo direccion" state
     * @param array $infoEvent
     * @return string
     */   
	public function setDireccion($infoEvent,$user,$OU_ID)
	{
         $like = "%Parent%";
		$eventosLog = new Application_Model_DbTable_Calendar_EventosLog();
        $sql = parent::select()->where('idTarea= ?', $infoEvent['idtarea'])->where('params like ?',$like);
        //file_put_contents("data.txt", $sql);
        $result = self::fetchAll($sql)->toArray();
        $params = json_decode($result[0]['params'],true);
        
         if($params['protocolo_direccion'] == "Yes")
            {
                
                $mje = 'Protocolo Direccion desasignado por el usuario '.$user;
                $params['protocolo_direccion'] = null;
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
                $eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "NO";
            }
            else{
                $mje = 'Protocolo Direccion asignado por el usuario '.$user;
                $params['protocolo_direccion'] = "Yes";
                $data = array("params" => json_encode($params));
                $where['id = ?'] = $result[0]['id'];
                parent::update($data, $where);
                $eventosLog->log($infoEvent['id'],$mje,$user,$OU_ID);
                return "YES";
            }    
        
       return "NO";

	}


	
	/**
     * Verifica la existencia de eventos de un cambio
     * @param string $idChange
     * @return bool
     */
    public function getEventoByIdChange($idChange)
    {
        $idChangeOr = "";
        $idChangeOr = "Parent_".$idChange;
        $sql = parent::select()->where('refer = ?',$idChange)->orWhere('refer = ?',$idChangeOr);
        //file_put_contents("sqlGETEvento.txt" ,$sql,FILE_APPEND);
        $result = self::fetchAll($sql)->toArray();
        if(count($result)>0)
        {return true;}
        else{return false;}
    }
	
	public function updateParentDates($datesArray, $idParent,$idTarea)
	{
		$like = "'%Parent%'";
		$selecParent = parent::select()->from($this,array('id'=>'id'))->where('refer =?',$idParent)->where('params like '.$like)->where('idTarea = ?',$idTarea);
		$parent = parent::fetchAll($selecParent)->toArray();
		$data = array("start" => $datesArray[0]['start'],"end"=>$datesArray[1]['end']);
    	$where['id = ?'] = $parent[0]['id'];
    	parent::update($data, $where);
	}
    
	 /**
     * Update Journal Events to cancel
     * @param string $id
     */
    public function UpdateCancelJournalEvent($id,$comments)
    {
      	$data = array("status" => "Cancel","comentarios"=>$comments);
    	$where['id = '.$id] = $id;
    	parent::update($data, $where);
    }
	
    public function UpdateEvent($data,$where)
    {
		//file_put_contents("datosendbINFO.txt",json_encode($data));
        return parent::update($data, $where);
    }
    
     /**
     * Removes event 
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function rmEvent($data,$where)
    {
        $today_dt       = new DateTime(date("Y-m-d"));
        $data_dat       = new DateTime($data['st']); 
        $event          = $this->getEvento($data["id"]);
        $paramsArray    = json_decode($event["params"],true);
        
        
        if ($data['sts'] === 'Pending' && $today_dt < $data_dat)
        {
            parent::delete($where);
            if($paramsArray["idTarea"] == "Parent")
            {
                $task->deleteTareaJournal($paramsArray["idChange"]);//Si eliminamos el Parent no tiene sentido conservar la tarea
            }
            return FALSE;
        }
        else {
            parent::update(array('type' => 'Cancel_'), $where);
            if($paramsArray["idTarea"] == "Parent")
            {
                $task->deleteTareaJournal($paramsArray["idChange"]);////Si eliminamos el Parent no tiene sentido conservar la tarea
            }
            return true;
        }
    }

    public function deleteEventJournal($refer)
    {
        $refertrimmed = trim($refer);
        $where = "refer = ".$refertrimmed;
        return parent::delete($where);
    }
    
    public function countEvents($where,$where1=false)
    {
        $select = parent::select()->from($this,array('id'=>'id','sts'=>'status','st'=>'start'))->where('idTarea =?',$where);
        if($where1)
        {
            $start = date("Y-m-d 00:00:00");
            $select->where("start >= ?",  $start);
        }
        $count = parent::fetchAll($select)->toArray();
        if(count($count) === 0){
            return FALSE;
        }else{
            $res = FALSE;
            foreach ($count as $value) {
                $r = self::rmEvent($value,'id ='.$value['id']);
                if($r)$res = true;
            }
            return $res;
        }
    }
    
    public function getevents($type='Checklist')
    {
        if ($type =='Journal')
        {
                    $dat = array('Turno'=>'turno','Centro'=>'centro',
                                            'Start'=>new Zend_Db_Expr("DATE_FORMAT(start,'%Y-%m-%d %H:%i:%s')"),
                                            'End'=>new Zend_Db_Expr("DATE_FORMAT(end,'%Y-%m-%d %H:%i:%s')"),
                                            'Customer Tenant'=>'cliente', 'customer_affected'=>'customer_affected',
                                            'Status'=>'Status', 'idChange' => 'params', 'Service' => 'idTarea',
                                            'Environment' => 'idTarea','Coordinator' => 'idTarea', 'CBI' => 'idTarea',
                                            'Titulo'=>'title','Descripcion'=>'description', 'CI'=> 'idTarea', 'Approval Status' => 'idTarea'
                                            );
        
            
           // array_merge($dat,array('customer_affected'=>'customer_affected'));
        } else {
                    $dat = array('Turno'=>'turno','Centro'=>'centro',
                                            'Dia'=>new Zend_Db_Expr("DATE_FORMAT(start,'%d/%m/%Y')"),
                                            'Hora IN'=>new Zend_Db_Expr("DATE_FORMAT(start,'%H:%i')"),
                                            'Hora FIN'=>new Zend_Db_Expr("DATE_FORMAT(end,'%H:%i')"),
                                            'Grupo'=>'group','Cliente'=>'cliente',
                                            'Titulo'=>'title','Descripcion'=>'description',
                                            'Estado'=>'Status');
        
        }
        
        $start = (date('w') =='1' )?date('Y-m-d'):date('Y-m-d',strtotime( "previous monday" ));
        $end = (date('w') =='0' )?date('Y-m-d'):date('Y-m-d',strtotime( "next sunday" ));
        $start_date_formatted = $start.' 00:00:00';
        $end_date_formatted =   $end.' 23:59:59';
		if(strtolower($type)=="journal")
		{
			 $sql = self::select()
                  ->from($this->_name,$dat)
                  ->where("start >= ?",  $start_date_formatted)
                 // ->where("end <= ?",  $end_date_formatted)
                  ->where("origen = ?",  $type);
        $res = self::fetchAll($sql)->toArray();
		}
		else{
			$sql = self::select()
                  ->from($this->_name,$dat)
                  ->where("start >= ?",  $start_date_formatted)
                  ->where("end <= ?",  $end_date_formatted)
                  ->where("origen = ?",  $type);
        $res = self::fetchAll($sql)->toArray();
		}
        
		//file_put_contents("sqleventos.txt" ,$sql);	

        if(count($res) === 0){
            return FALSE;
        }else{
            if( $type =='Journal')
            {
                foreach ($res as $key => $value) {
                    $ress[] = array_merge($value,json_decode($value['idChange'],true));
                }
                    $res = $ress;
            }
        }
        return $res;
    }
     
    public function GetClients()
    {
//         $select = parent::select()->from($this,array('name'=>'cliente'))->group('cliente');
//         return parent::fetchAll($select)->toArray();
        


//         $select = $db->select()->from(array('a'=> 'app_recursos'),array('IDR'=>'ID','controller','action'))
//         ->joinLeft(array('b'=>'app_permisos'), 'b.app_recursos_id=a.ID',array('ID','access'))
//         ->joinLeft(array('c'=>'app_roles'), 'c.ID=b.app_roles_id',array('name','hereda'))
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        //$select = $db->select()->from(array('a'=>'cal_eventos'),array('name_ev'=>'cliente'))->join(array('b'=>'clientes', 'a.cliente = b.ID',array('name'=>'nombre')));
        $select = $db->select()->from(array('a'=>'cal_eventos'), null)
            ->joinLeft(array('b'=>'clientes'), 'b.ID = a.cliente', array('name'=>'nombre'))
            ->group('b.nombre');
        $results = $db->fetchAll($select);
        
        return $results;

        
    }
    
    public function GetGroups()
    {
        $select = parent::select()->from($this,array('name'=>'group'))->group('group');
        return parent::fetchAll($select)->toArray();
    }
    
    public function GetLocation($where)
    {
        $select = parent::select()->from($this,array('name'=>'centro'))->where('centro <> ?',$where)->group('centro');
        return parent::fetchAll($select)->toArray();
    }
    
    public function InsertEvent($data)
    {
       // file_put_contents ( "eneventostryinginsert.txt" , json_encode($data));
        try{
            $data['creada'] = new Zend_Db_Expr('NOW()');
            $data['usuario'] = Zend_Registry::get('usuario')->username;
            
             return parent::insert($data);
        }catch(Exception $ex){
            file_put_contents ( "eneventostryinginsertEXCEPTION.txt" , $ex);
        }
	 
	}
    
    public function __get($propiedad) {
        return $this->$propiedad;
    }

     /**
     * Obtiene los eventos de una tarea 
     * @return Array|string
     */
    public function getEventsByTask($idtarea)
    {
        $sql = parent::select()->where('idTarea=?',$idtarea);
        $result = self::fetchAll($sql)->toArray();
      //  file_put_contents ( "eneventos.txt" , json_encode($result));
        return $result;   
    }
  
    public function getEvento($id)
    {
        $sql = parent::select()->where('id=?',$id);
        $result = self::fetchRow($sql)->toArray();
        return $result;
    }
  
    public function getEventosTicketAbierto()
    {
        // Obtiene eventos con ticket abiertos y que ha pasado el tiempo para cerrarlos  
        $sql = parent::select()->where('not `ticket-start` is null')->where('`ticket-end` is null')
      //  ->where('`status` <> "Cancel"')->where('`status` <> "Error"')
        ->where('now() > (`ticket-start`+ INTERVAL `minutes-close-ticket` MINUTE)');
        
        $result = self::fetchAll($sql)->toArray();
        return $result;
    }

        /**
     * Get ids (parent and children) by refer
     * @param string $refer
     */
    public function GetIdsByRefer($refer)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $comp = "'%".$refer."%'";
        $sql = parent::select()->from($this,array('id'=>'id'))->where('refer like '.$comp);
        
        $results = $db->fetchAll($sql);
        return $results;
    }
    
     /**
     * Obtiene los eventos no cerrados/cancelados de EASY 
     * @return Array|string
     */
    public function getEventsEasyNotFinish()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()->from(array('a'=>'cal_eventos'), array('id'=>'id','params'=>'params','refer'=>'refer','status'=>'status','origen'=>'origen','idTarea'=>'idTarea','turno'=>'turno','centro'=>'centro','cliente'=>'cliente','group'=>'group','customer_affected'=>'customer_affected','start'=>'start','end'=>'end'))


        ->where("status <> 'Finish'")
                ->where("status <> 'cancel'")
                ->where("status <> 'Error'")
                ->where("a.group = 'EASY'")
                ->where("type <> 'Cancel_'")
                ->where("params not like '%Parent%'");
        
        $results = $db->fetchAll($select);
       // file_put_contents ( "resultsEvents---.txt" , $select);
        if(count($results)>0)
            return $results;
        else return "KO";
    }
    
    /**
     * Gets events not treated and window closed
     * @return unknown|string
     */
    public function getEventsPendingTreated()
    {
         $select = parent::select()->where("origen = 'Journal'")
        ->where("end > DATE_SUB(NOW(), INTERVAL 24 HOUR)  AND end <= NOW()")
        ->where("(status = 'Pending'")
        ->orwhere("status = 'Error'")
        ->orwhere("status = 'Progress')")
        ->where("`type` <> 'Cancel_'")
        ->order(array('customer_affected asc'));
          //file_put_contents("consultaeventospendientes.txt", $select);
         $results = self::fetchAll($select)->toArray();
          
         $noparents  = array();
         
         for($i = 0; $i < count($results); $i++)
         {
            $idTarea = json_decode($results[$i]['params'])->idTarea;
            if($idTarea != "Parent") 
            {
                array_push($noparents,$results[$i]);
            }

         }
         
        if(count($noparents)>0)
            return $noparents;
        else return "NO RESULTS";        
    }
    
    /**
     * Updates dates of an event
     * @param array $data
     * @param int $id
     */
    public function updateDatesEvent($data,$id)
    {
        $where['id = ?'] = $id;
        parent::update($data, $where);
    }
    
    
    /**
     * Gets Customer affected list
     */
    public function getCustomer_AffectedFromEvents()
    {
    	$query = "SELECT distinct customer_affected FROM cal_eventos
					WHERE customer_affected is not null AND customer_affected <> ''
					ORDER BY customer_affected asc;";
    	$stmt = $this->_db->query($query);
    	
    	return $stmt->fetchAll();
    	
    }
    
    /**
     * Get Coordinators from eventos SM9
     */
    public function getCoordinatorFromEventosSM9()
    {
    	try{
    		$coordinators = array();
    		$query = "SELECT params from cal_eventos where (UPPER(`type`) = 'SM9' or  UPPER(`type`) = 'CTTI') AND params is not null";
    		
    		$stmt = $this->_db->query($query);
    		
    		$allparams = $stmt->fetchAll();
    		for($i = 0; $i < count($allparams); $i++)
    		{
    			
    			$arrayparams = json_decode(json_encode($allparams[$i]),true);
    			$array  = json_decode($arrayparams["params"],true);
    			
    	   if(isset($array["Coordinator"]) && ! in_array( trim(explode("->",$array["Coordinator"])[0]) , $coordinators))
                    
                array_push($coordinators,trim(explode("->",$array["Coordinator"])[0]));
            }
    		
    				return $coordinators;
    	}
    	catch(Exception $ex)
    	{
    		file_put_contents("coordinators_exception.txt",$ex);
    	}
    	
    }
    
  /**
     * Gets Customer affected list
     */
    public function getEventsFiltered($data)
    {
        
        $unsetstart =   -1;
        $unsetend   =   -1;
        
        for($e = 0; $e < count($data); $e++)
        {
            if($data[$e]["name"] == "start")
            {
                if($data[$e]["values"][0]=="")
                {
                    $unsetstart = $e;
                }
                else{
                    $date = strtotime($data[$e]["values"][0]);
                    $data[$e]["values"] = date('Y-m-d H:i:s', $date);
                }
                    }
            if($data[$e]["name"] == "end")
            {
                if($data[$e]["values"][0]=="")
                {
                    $unsetend = $e;
                }
                else{
                    $date = strtotime($data[$e]["values"][0]);
                    $data[$e]["values"] = date('Y-m-d H:i:s', $date);
                }
            }
                        
            if($data[$e]["name"] == "open-close-ticket")
            {
                $data[$e]["values"][0] = " IS NOT NULL ";
            }
            
        }
        
        //TODO convert if statements to SWITCH
        $query = " SELECT id,idTarea,`start`,`end`,`group`,allDay,title,description,origen,status,refer,turno,centro,cliente,rgroup,type,".
		" customer_affected,params,`open-close-ticket`,`minutes-close-ticket`,`ticket-id`,".
		" `ticket-start`,`ticket-end`,OU_ID,comentarios,cliente_id,creada,usuario,time_user,time_app,c.resolution_code ". 	
        " FROM cal_eventos c WHERE";
        $where = "";
        $joinFlagEv = "";
        $joinFlagLog = "";
        $tipoRegistro = "";
        $hasUsername = false;
         
           for($a = 0; $a < count($data); $a++){
        	
        	switch($data[$a]["name"]){
        		case "username":
        			if($data[$a]["values"] != "")
        			{
        				$hasUsername = true;
        				$joinFlagEv = "c.";
        				$joinFlagLog = "cl.";
        				//query change join with log table
        				
        				//TODO add fields manually distinguish between event and log !!!!!!
        				
        $selectevents = "SELECT c.id,c.idTarea,c.`start`,c.`end`,c.`group`,c.allDay,c.title,c.description,c.origen, ".
        " c.status,c.refer,c.turno,c.centro,c.cliente,c.rgroup,c.type,".
		" c.customer_affected,c.params,c.`open-close-ticket`,c.`minutes-close-ticket`,c.`ticket-id`,".
		" c.`ticket-start`,c.`ticket-end`,c.OU_ID,c.comentarios,c.cliente_id,c.creada,c.usuario,c.time_user,c.time_app,c.resolution_code "; 	
        
        
        $selectlog = " ,cl.id as idlog, cl.idEvento, cl.fecha, cl.username,cl.OU_ID, cl.msj ";
        				$query = $selectevents." ".$selectlog." FROM cal_eventos c INNER JOIN cal_eventos_log cl ON c.id = cl.idEvento WHERE";
        				//Add usuario from log
        				$where .= " AND ".$joinFlagLog.$data[$a]["name"]." = '".$data[$a]["values"][0]."'";
        				
        			}
        			break;
        		
        		case "action_usuario":
        			//case with action selected for a user
        			$value = "";
        			if($data[$a]["values"][0] == "creation")
        			{
        				$value = "Event_Creation";
        			}
        			else if($data[$a]["values"][0] == "start")
        				$value = "end-assignment";
        			else if($data[$a]["values"][0] == "finish")
        				$value = "closure";
        			$where .= " AND ".$joinFlagLog."msj like '%".$value."%'";
        			break;
        		//un solo elemento
        		case "tiporegistro":
        			$tipoRegistro = $data[$a]["values"][0];
        			break;
	       		case "origen"			:
	       			if($data[$a]["values"] != "")
	       				$where .= " ".$joinFlagEv.$data[$a]["name"]." = '".$data[$a]["values"][0]."'";
	       			break;
        		case "start"			:
        			$where.=" AND ";
        			if($data[$a]["values"] != "")
        				$where .= " ".$joinFlagEv.$data[$a]["name"]." >= '".$data[$a]["values"]."'";
        			break;
        			 
        		case "end"				:
        			$where.=" AND ";
        			
        			if($data[$a]["values"][0] != "")
        				$where .= " ".$joinFlagEv.$data[$a]["name"]." <= '".$data[$a]["values"]."'";
        			break;
        			
        		case "type"				:
        			$where.=" AND ";
        			if($data[$a]["values"] != "")
        				$where .= " ".$joinFlagEv.$data[$a]["name"]." = '".$data[$a]["values"][0]."'";
        			break;
        			        		
        		//params like
        		case "CI"				:
        		case "coordinator"		:
        			$where.=" AND ";
        			if($data[$a]["values"] != ""){
        				if(count($data[$a]["values"]) == 1 )
        				{
        					$where .= " ( ".$joinFlagEv."params like '%".$data[$a]["values"][0]."%' ) ";
        				}
        				else{
        					$where .= "  (".$joinFlagEv."params like ";
        					foreach ($data[$a]["values"] as $key => $value) {
        						if($key == 0)
        							$where .= " '%".$data[$a]["values"][$key]."%' ";
        						else $where .= " OR ".$joinFlagEv."params like '%".$data[$a]["values"][$key]."%' ";
        					}
        					$where .= " ) ";
        				}
        			}
        	
        			break;
        			
        		case "open-close-ticket":	
        			if($data[$a]["values"] != "")
        				$where .= " AND `".$data[$a]["name"]."`  ".$data[$a]["values"][0]." ";
        			break;
        			
        		default:
        				if(count($data[$a]["values"]) == 1)
        				{
        					if($data[$a]["values"][0] != ""){
        						$where.=" AND ";
        						$where .= $joinFlagEv.$data[$a]["name"]." = '".$data[$a]["values"][0]."'";
        					}
        		}
        				else
        				{
        					$where .= " AND  ( ";
        					foreach ($data[$a]["values"] as $key => $value) {
        						if($key == 0)
        							
        							$where .= $joinFlagEv.$data[$a]["name"].' =  "'.$data[$a]["values"][$key].'" ';
        						else $where .= " OR ".$joinFlagEv.$data[$a]["name"].' = "'.$data[$a]["values"][$key].'" ';
        					}
        					$where .= " ) ";
        				}
        				break;
        		
        	}
        	
        	  	
        }
        $order = " ORDER BY id desc";
        $wholequery = $query.$where;
        //file_put_contents("querycompleta.txt",$wholequery);
        
        $stmt = $this->_db->query($wholequery);
        $resultado =  $stmt->fetchAll();
        $resultadoFiltrado  = array();
        if($tipoRegistro != "")
        {
        	for($x = 0; $x < count($resultado); $x++)
        	{
	        	$params = json_decode($resultado[$x]['params'],true);
	        
	        	$idTarea = 	$params['idTarea'];
	        	
	        	$info ="";
				if(isset($params['INFO']))
	        		$info = $params['INFO'];
	        	 
		        	if($tipoRegistro == "info")
		        	{
			        	if($idTarea == "Parent" && $info)
			        	{
			        		array_push($resultadoFiltrado,$resultado[$x]);
			        	}
		        	}
	        	 
	        	if($tipoRegistro == "parent")
	        		{
	        			if($idTarea == "Parent" && !$info)
	        			{
	        				array_push($resultadoFiltrado,$resultado[$x]);
	        			}

	        			if($hasUsername)
	        			{
	        				array_push($resultadoFiltrado,$resultado[$x]);
	        			}
	        		}
	        	if($tipoRegistro == "task")
	        	{
		        	if($idTarea != "Parent" && !$info)
		        	{
		        		array_push($resultadoFiltrado,$resultado[$x]);
		        	}
	        	}
        	}
        	$resultado = $resultadoFiltrado;
        }
    
        
        $clientes = new Application_Model_DbTable_Clientes();
        
        for($a=0; $a < count($resultado); $a++)
        {
            $nameCliente = $clientes->getClienteNameByID($resultado[$a]['cliente']);
            $resultado[$a]['cliente'] = $nameCliente['nombre'];
        }
       // file_put_contents("rsultados.txt", json_encode($resultado));
        return $resultado;
         
    }
    
	/**
	 * Returns all events related
	 * @param int $idevento
	 * @return array
	 */
    public function taskFromChange($idevento)
    {
    	$sql = parent::select()->where("id = ".$idevento);
        $event = self::fetchRow($sql)->toArray();
		$sqlchange = parent::select()->where("idTarea = ".$event['idTarea']);
		$events = self::fetchAll($sqlchange)->toArray();
        return $events;
    	
    }
    
   /**
    * Modifies computed and manual times
    * @param int $time_app
    * @param int $time_user
    * @param int $id
    */
    public function updateTimes($time_app,$time_user,$id)
    {

    	$where['id = ?'] 	= $id;
    	$data['time_app'] 	= $time_app;
    	$data['time_user'] 	= $time_user;
    	//file_put_contents("dataupdatetime.txt", json_encode($data));
        parent::update($data, $where);
    }
    

    
    
}

?>