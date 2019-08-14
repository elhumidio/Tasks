<?php
class Application_Model_DbTable_Calendar_EventosJournal extends Zend_Db_Table {
    
    protected $_name = "cal_eventos_journal";
    protected $_primary = 'id';
    
    /**
     * Verifica si una tarea está programada
     * @param int $idTarea
     */
    public function CheckTarea($idTarea)
    {
        return parent::fetchRow(array('idTarea=?'=>$idTarea));
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

        return parent::update($data, $where);
    }
    
    public function rmEvent($data,$where)
    {
        $today_dt = new DateTime(date("Y-m-d"));
        $data_dat = new DateTime($data['st']); 
         // var_dump($data_dat);
         // die();
        if ($data['sts'] === 'Pending' && $today_dt < $data_dat)
        {
            parent::delete($where);
            return FALSE;
        }
        else {
            parent::update(array('type' => 'Cancel_'), $where);
            return true;
        }
    }

    public function deleteEventJournal($refer)
    {
        $where = "refer = ".$refer;
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
        $sql = parent::select()->where('not `ticket-start` is null')->where('`ticket-end` is null')->where('now() > (`ticket-start`+ INTERVAL `minutes-close-ticket` MINUTE)')->where("`status` not like '%Error%'");
        //file_put_contents("sqleventos.txt", $sql);
        $result = self::fetchAll($sql)->toArray();
        return $result;
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
    
    
}

?>