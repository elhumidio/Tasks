<?php
/**
 * 
 * @author Juan Ignacio Badia Capparrucci - juanignacio.badia@t-systems.es
 * @version 1.0 - 24/07/2013
 *
 */

class Application_Model_Calendar_Calendar
{

    /**
     * Variable que contiene un objeto con los datos del usuario logueado
     * @var object
     */
    private $userData;
    
    /**
     * Variable que contiene los parámetros enviados
     */
    private $params;
    
    /**
     * Contiene el log de evento
     * @var object
     */
    private $eLog;
    private $tLog;
    
    private $Notificaciones;
    
    
    public function __construct($params=array())
    {
        $this->params = $params;
        
        $storage = new Zend_Auth_Storage_Session (NAMESPACE_APP,NAMESPACE_MEMBER);
        $namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT.NAMESPACE_APP;
        $this->userData = $storage->read($namespace);
        
        $this->eLog = new Application_Model_DbTable_Calendar_EventosLog();
        $this->eLog->__set('username',((isset($this->userData->username))?$this->userData->username:NULL));
        $this->eLog->__set('OU_ID',((isset($this->userData->OU_ID))?$this->userData->OU_ID:NULL));
        
        $this->tLog = new Application_Model_DbTable_Calendar_TareasLog();
        $this->tLog->__set('username',((isset($this->userData->username))?$this->userData->username:NULL));
        $this->tLog->__set('OU_ID',((isset($this->userData->OU_ID))?$this->userData->OU_ID:NULL));
    }
    
    
    /**
     * Devuelve las tareas del uaurio
     * @return array
     */
    public function GetTareas()
    {
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosTareas();
        
        $select = $TareasDb->select()
                            ->setIntegrityCheck(false)
                            ->from(array('a'=>$TareasDb->__get('_name')),array('id','title','description','origen','minimo','maximo','limite','creada'))
                            ->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
                            ->where('c.username = ?',$this->userData->username)
                            ->where('a.programada IS NULL');
        
        $Tareas = $TareasDb->fetchAll($select)->toArray();
        
        return $Tareas;
    }
    
	 /**
     * Devuelve los eventos del usuario
     * @return json
     */
    public function GetEvents($users = false, $wh = false, $wh1 = false)
    {
        $date = date('d');
   
       // $filter_user=($users)?$users:$this->userData->username;
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
    
          $select = $EventsDb->select()
        ->setIntegrityCheck(false)
        ->from(array('a'=>$EventsDb->__get(  '_name')),array('id','start','status','end','allDay','title','description','origen','turno','open-close-ticket','minutes-close-ticket','comentarios',
                'idtarea', 'form_4' =>'title','form_5' =>'description','form_6' =>'start',
                'form_8' =>'origen','form_7' =>'end','form_12' =>'centro','form_14' =>'group','form_25' =>'cliente',
                'form_15' =>'rgroup','ticket-id'=>'ticket-id','Type' =>'type','form_1' =>'id','form_10' =>'refer','form_9' =>'status','form_18' =>'type','form_17' =>'turno', 'params' => 'params','customer_affected' => 'customer_affected',
                'form_23' =>'open-close-ticket','form_24' =>'minutes-close-ticket','ticket_start'=>'ticket-start','time_user'=>'time_user','time_app'=>'time_app'
    
        ))
    
        ->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idevento',array('username','form_19' =>'username'))
        ->joinLeft(array('d'=>'clientes'),'a.cliente=d.ID',array('cliente_nombre' =>'nombre'));
		
    
    
        $select->where("type <> 'Cancel_'");
        if($wh1)
        {
            $select->where("origen =?",$wh1);
        }
        if(isset($this->params['start']))
            $start = getdate($this->params['start']);
		$START_DAY =$start['mday'];
		
		$fechaNueva = date("Y-m-d", $start["0"]);
        
        $select->where("(((DAY(start)) <= ? ",$START_DAY); //1
		$select->where("MONTH(start) <= ?",$start['mon']);
        $select->where("YEAR(start) = ?",$start['year']);
		$select->where("DAY(end) >=  $START_DAY");//3
		$startplus  = $start['mon']+1;
		$select->where("MONTH(end) BETWEEN ? and $startplus", $start['mon']);
        $fnch = (!empty($wh))?'':')';

        if(isset($this->params['end']))
            $end = getdate($this->params['end']);
        
        $END_DAY = $end['mday'];

			if(strtolower($wh1)  == "journal")
			{
		        $select->orWhere("(date(start) <= ? ",$fechaNueva);
      	        $select->where("date(end) >= ?",$fechaNueva); 
        	}
		$select->orWhere("(DAY(start) BETWEEN 1 and 31");
      	$select->where("DAY(end) BETWEEN  $START_DAY AND 31"); 
        $select->where("MONTH(end) >= ?",($end['mon']));
		$select->where("MONTH(start) = ?",($end['mon'])-1);
        $select->where("YEAR(end) = ? ))$fnch",$end['year']);
		
	  $i = 0;
        $len = count($wh);



       
        if(!empty($wh))
        {
    
            foreach ($wh as $k => $v)
            {
                if($k == 'a.cliente') $k = 'd.nombre';
                $new_arr = array();
                foreach($v as $vv)
                {
                    /*if($vv == "REUS") //#>dblanc parche para ver los eventos con centro en Barcelona
                    {
                        $vv = "BCN - 22@";
                    }*/
        
                    $new_arr[] = $vv;
                }
    
                if ($i == $len - 1) //Ultima vuelta del for
                {$select->Where("$k in (?))",$new_arr);
                }
    
                else{$select->Where("$k in (?)",$new_arr);
                }
                $i++;
            }
             
        }
     
        $select->orWhere('(c.username in (?)',$this->userData->username);
        if(isset($this->params['start']))
            $start = getdate($this->params['start']);
        
        $select->where("((DAY(start) = ? ",$start['mday']);
        $select->where("MONTH(start) = ?",$start['mon']);
		if($wh1 == "journal")
			$select->where("YEAR(start) = ?)",$start['year']);
		else
        $select->where("YEAR(start) = ?)",$start['year'])->order('start');
       
    
        if(isset($this->params['end']))
            $START_DAY =$start['mday'];
        $END_DAY = $end['mday'];
		
		if(strtolower($wh1)  == "journal")
			{
		        $select->orWhere("(date(start) <= ? ",$fechaNueva);
      	        $select->where("date(end) >= ? ))",$fechaNueva); 
        	}
			else{$select->orWhere("(DAY(start) = $START_DAY");
        $select->where("DAY(end) BETWEEN  1 AND 31");//#>DBLANC - Changed end day verification
        
        $select->where("MONTH(end) >= ?",$end['mon']);
        $select->where("YEAR(end) = ? ))",$end['year']);}
        

		
		
        if($wh1)
        {
            $select->where("origen =?",$wh1);
        }
        
        if($wh1 == "checklist")
        $select->where("type <> 'Cancel_')");
		else $select->where("type <> 'Cancel_'))");
        
			if($wh1 == "checklist"){
		    $select->order(array('start asc','end asc'));
		    }
			if(strtolower($wh1)  == "journal")
			{
				$select->order(array('id asc '));
			}
			
					
		else $select->order(array('id asc'));
			file_put_contents("selectevents.txt", $select);
        $Events = $EventsDb->fetchAll($select)->toArray();
        
        if(strtolower($wh1)  == "journal")
        {
            $nuevoArray = array();
            
            for($i=0;$i<count($Events);$i++){
            
                $posicion = $this->posicionArray($nuevoArray, $Events[$i]);
                if ($posicion !== false) {
                    $nuevoArray = $this->insertaArray($nuevoArray, $posicion, $Events[$i]);
                } else{
                    $nuevoArray[] = $Events[$i];
                }
                 
            }
            
            $Events = $nuevoArray;
        }
            $Events = self::getEventoptions($Events);
            $log = new Application_Model_DbTable_Calendar_EventosLog();		
	      
            if($wh1 == "checklist"){
          
					for ($i = 0; $i < count($Events); $i++) {
						//GET MENSAJES
					   $mensajes =  $log->getMessagesChecklist($Events[$i]['id']);
					   $Events[$i]['mensajes'] = $mensajes;                               
					}
           
			}
		           else if (strtolower($wh1)== "journal") {
               for ($m = 0; $m < count($Events); $m++) {
                
                   //GET MENSAJES
                   $mensajes =  $log->getClosureMessagesJournal($Events[$m]['id']);
                   $Events[$m]['mensajes'] = $mensajes;
               }
           }
       
        return $Events;
    }
 
     /**
     * Devuelve los eventos del usuario
     * @return json
     */
    /*public function GetEvents($users = false, $wh = false, $wh1 = false)
    {
        $date = date('d');
        //      die(var_dump($users));
        $filter_user=($users)?$users:$this->userData->username;
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
    
        $select = $EventsDb->select()
        ->setIntegrityCheck(false)
        ->from(array('a'=>$EventsDb->__get(  '_name')),array('id','start','status','end','allDay','title','description','origen','turno','open-close-ticket','minutes-close-ticket','comentarios',
                'idtarea', 'form_4' =>'title','form_5' =>'description','form_6' =>'start',
                'form_8' =>'origen','form_7' =>'end','form_12' =>'centro','form_14' =>'group','form_25' =>'cliente',
                'form_15' =>'rgroup','Type' =>'type','form_1' =>'id','form_10' =>'refer','form_9' =>'status','form_18' =>'type','form_17' =>'turno', 'params' => 'params','customer_affected' => 'customer_affected',
                'form_23' =>'open-close-ticket','form_24' =>'minutes-close-ticket','ticket_start'=>'ticket-start'
    
        ))
    
        ->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idevento',array('username','form_19' =>'username'))
        ->joinLeft(array('d'=>'clientes'),'a.cliente=d.ID',array('cliente_nombre' =>'nombre'));
		//->joinLeft(array('l'=>'cal_eventos_log'),'a.id = l.idEvento',array('mensaje'=>'msj'));
    
    
        $select->where("type <> 'Cancel_'");
        if($wh1)
        {
            $select->where("origen =?",$wh1);
        }
        if(isset($this->params['start']))
            $start = getdate($this->params['start']);
		$START_DAY =$start['mday'];
        
		

				$select->where("(((DAY(start)) <= ? ",$START_DAY); //1
		$select->where("MONTH(start) <= ?",$start['mon']);
        $select->where("YEAR(start) = ?",$start['year']);
		$select->where("DAY(end) >=  $START_DAY");//3
		$startplus  = $start['mon']+1;
		$select->where("MONTH(end) BETWEEN ? and $startplus", $start['mon']);
		
        $fnch = (!empty($wh))?'':')';
        if(isset($this->params['end']))
            $end = getdate($this->params['end']);
        
        $END_DAY = $end['mday'];
			if(strtolower($wh1)  == "journal")
			{
				
		$select->orWhere("(DAY(start)<= ? ",$START_DAY);
      	$select->where("DAY(end) BETWEEN  1 AND 31"); 
        $select->where("MONTH(end) = ?",($end['mon']+1));
		$select->where("MONTH(start) <= ?",($start['mon']));
        $select->where("YEAR(end) = ? $fnch",$end['year']);
			}
		$select->orWhere("(DAY(start) BETWEEN 1 and 31");
      	$select->where("DAY(end) BETWEEN  $START_DAY AND 31"); 
        $select->where("MONTH(end) >= ?",($end['mon']));
		$select->where("MONTH(start) = ?",($end['mon'])-1);
        $select->where("YEAR(end) = ? ))$fnch",$end['year']);
		
		
		


      

	  $i = 0;
        $len = count($wh);
        if(!empty($wh))
        {
    
            foreach ($wh as $k => $v)
            {
                if($k == 'a.cliente') $k = 'd.nombre';
                $new_arr = array();
                foreach($v as $vv)
                {
                    if($vv == "REUS") //#>dblanc parche para ver los eventos con centro en Barcelona
                    {
                        $vv = "BCN - 22@";
                    }
                    //file_put_contents ( "valoresdelfor" , $vv."  -   ",FILE_APPEND);
                    $new_arr[] = $vv;
                }
    
                if ($i == $len - 1) //Ultima vuelta del for
                {$select->Where("$k in (?))",$new_arr);
                }
    
                else{$select->Where("$k in (?)",$new_arr);
                }
                $i++;
            }
             
        }
     
        $select->orWhere('(c.username in (?)',$this->userData->username);
        if(isset($this->params['start']))
            $start = getdate($this->params['start']);
        
        $select->where("((DAY(start) = ? ",$start['mday']);
        $select->where("MONTH(start) = ?",$start['mon']);
		if($wh1 == "journal")
			$select->where("YEAR(start) = ?)",$start['year']);
		else
        $select->where("YEAR(start) = ?)",$start['year'])->order('start');
       
    
        if(isset($this->params['end']))
            $START_DAY =$start['mday'];
        $END_DAY = $end['mday'];
        $select->orWhere("(DAY(start) <= $START_DAY");
        $select->where("DAY(end) BETWEEN  1 AND 31");//#>DBLANC - Changed end day verification
        
        $select->where("MONTH(end) >= ?",$end['mon']);
        $select->where("YEAR(end) = ? ))",$end['year']);
    
    
        //$select->where("UNIX_TIMESTAMP(STR_TO_DATE(end, '%Y-%m-%d %H:%i:%s')) <= ? )",$this->params['end'])->order('start');
        if($wh1)
        {
            $select->where("origen =?",$wh1);
        }
        $select->where("type <> 'Cancel_')");
		
			if($wh1 == "checklist"){
		    $select->order(array('start asc','end asc'));
		    }
			if(strtolower($wh1)  == "journal")
			{
				$select->order(array('id asc'));
			}
			
		

			
		else $select->order(array('id asc'));
			file_put_contents("selectevents.txt", $select);
        $Events = $EventsDb->fetchAll($select)->toArray();
        
        if(strtolower($wh1)  == "journal")
        {
            $nuevoArray = array();
            
            for($i=0;$i<count($Events);$i++){
            
                $posicion = $this->posicionArray($nuevoArray, $Events[$i]);
                if ($posicion !== false) {
                    $nuevoArray = $this->insertaArray($nuevoArray, $posicion, $Events[$i]);
                } else{
                    $nuevoArray[] = $Events[$i];
                }
                 
            }
            
            $Events = $nuevoArray;
            
        }
        
        
            $Events = self::getEventoptions($Events);
            $log = new Application_Model_DbTable_Calendar_EventosLog();		
	      
            if($wh1 == "checklist"){
          
					for ($i = 0; $i < count($Events); $i++) {
						//GET MENSAJES
					   $mensajes =  $log->getMessagesChecklist($Events[$i]['id']);
					   $Events[$i]['mensajes'] = $mensajes;                               
					}
           
			}
		           else if (strtolower($wh1)== "journal") {
               for ($m = 0; $m < count($Events); $m++) {
                
                   //GET MENSAJES
                   $mensajes =  $log->getClosureMessagesJournal($Events[$m]['id']);
                   $Events[$m]['mensajes'] = $mensajes;
               }
           }
        
       
        return $Events;
    }*/
    
    private function posicionArray($array, $elem){
    
        if (count($array) == 0) return false;
    
        $existe = false;
        for($i=0;$i<count($array);$i++){
            if ($elem['form_10'] == $array[$i]['form_10']){
                $existe = true;
            } else {
                if ($existe) return $i;
            }
        }
        if ($existe) return false;
    
        $ant = '';
        for($i=0;$i<count($array);$i++){
    
            if ($elem['start'] < $array[$i]['start']){
    
                if ($i > 0 && $array[$i]['form_10'] == $ant){
                } else {
                    return $i;
                }
            }
    
            $ant = $array[$i]['form_10'];
        }
         
        return false;
    
    }
    
    private function insertaArray($array, $posicion, $valor){
    
        $arrayResult = array();
    
        for($i=0;$i<count($array);$i++){
            if($i==$posicion) $arrayResult[]=$valor;
            $arrayResult[]=$array[$i];
        }
    
        return $arrayResult;
    }
    
	
 
    
    public function GetTags($term)
    {
        $TagsDb = new Application_Model_DbTable_Calendar_Tags();
    
        $select = $TagsDb->select()
        ->from(array('a'=>$TagsDb->__get('_name')),array('id','tag'))
        ->where('tag LIKE ?',"%$term%");
    
        $Tags = $TagsDb->fetchAll($select)->toArray();
    
        //$Tags = self::getEventoptions($Tags);
            //if(!is_null($this->params['idtarea'])) $this->tLog->log($this->params['idtarea'],'Tarea programada.');
        return $Tags;
    }
    
    /**
     *Inserta nuevos tags a la BBDD
     * @return json
     */
    public function InsertTags($term)
    {
        $TagsDb = new Application_Model_DbTable_Calendar_Tags();
        try 
        {
            $result = $TagsDb->insert(array('tag'=>$term));
        }
     catch (Zend_Db_Exception $e) {
        echo('Fail');
        echo($result);
  // ignored 
        }
        echo $result;
                
    }
    
    
    /**
     * Devuelve los eventos del grupo del usuario
     * @return json
     */
    public function GetGroupEvents()
    {
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
        
        $select = $EventsDb->select()
                            ->setIntegrityCheck(false)
                            ->from(array('a'=>$EventsDb->__get('_name')),array('id','start','end','allDay','title','description','origen'))
                            ->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idevento',array('username'))
                            ->where('c.OU_ID = ?',$this->userData->OU_ID);
        
        if(isset($this->params['start']))
            $select->where("UNIX_TIMESTAMP(STR_TO_DATE(start, '%Y-%m-%d %H:%i:%s')) >= ? ",$this->params['start']);
        
        if(isset($this->params['end']))
            $select->where("UNIX_TIMESTAMP(STR_TO_DATE(end, '%Y-%m-%d %H:%i:%s')) <= ? ",$this->params['end']);
        
        $Events = $EventsDb->fetchAll($select)->toArray();
        $Events = self::getEventoptions($Events);
        
        return json_encode($Events);
    }
    
    
    private function getEventoptions($Events)
    {
        $OpcionesDb = new Application_Model_DbTable_Calendar_EventosOpciones();
        
        for($i=0;$i<count($Events);$i++){
            $Events[$i]['description'] = htmlspecialchars($Events[$i]['description']); // Escapamos la variable para evitar ataques XSS
            $Events[$i]['description'] = nl2br($Events[$i]['description']); // Ejecutamos esto después del escape para que no escape los <br />
            $Events[$i] = $OpcionesDb->getEventValues($Events[$i]);
        }
        
        
        
        return $Events;
    }
    
    /**
     * Actualiza un Evento
     * @param array $params
     */
    public function MoveEvent()
    {
        if(!isset($this->params['id'])) return json_encode(array('error'=>array('id'=>'Falta dato.')));
        if(!isset($this->params['allDay'])) return json_encode(array('error'=>array('allDay'=>'Falta dato.')));
        if(!isset($this->params['start'])) return json_encode(array('error'=>array('start'=>'Falta dato.')));
        if(!isset($this->params['end'])) return json_encode(array('error'=>array('end'=>'Falta dato.')));
        
        if(! is_numeric($this->params['id'])) return json_encode(array('error'=>array('id'=>'Formato incorrecto.')));
        
        $start = new DateTime($this->params['start']);
        
        if(is_null($this->params['end']) || $this->params['end']=='false' || $this->params['end']=='null' || $this->params['end']=='') $end = new Zend_Db_Expr('NULL');
        else {
            $end = new DateTime($this->params['end']);
            $end = $end->format('Y-m-d H:i:s');
        }
                
        
        $allDay = is_null($this->params['allDay']) || $this->params['allDay']=='false' || $this->params['allDay']=='null'?new Zend_Db_Expr('NULL'):1;

        $data = array('allDay'=>$allDay,'start'=>$start->format('Y-m-d H:i:s'),'end'=>$end);
        
        $this->eLog->log($this->params['id'],sprintf('Evento modificado. Fecha inicio: %s, Fecha fin: %s.',$data['start'],$data['end']));
        
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        return $EventsDb->update($data, 'id = '.$this->params['id']);
    }
	

    	 /**

    /**
     * Crea un Evento Recurrente proveniente de Excel (journal)
     * @param array $params
     */
    public function NewEventJournalExcel($new_tarea, $rec_dat, $dbvar,$dateend)
    {
        try{
            
            $dbvar['start'] =  substr($rec_dat[0],0,4).'-'.substr($rec_dat[0],5,2).'-'.substr($rec_dat[0],8,2). ' ' .$dbvar['t_start'].':00';
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
                //$status = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
                $status = 'Pending';
            
            } else {
                $status = 'Pending';
                $dbvar['open-close-ticket'] = null;
                $dbvar['minutes-close-ticket'] = null;
            
            }
            
            $enddata = explode(' ',$dbvar['end']);
            if($enddata[1] == "00:00:00")
                $endmod = $enddata[0]." 23:59:00";
            else $endmod = $dbvar['end'];
            
            $data = array('title'=>$dbvar['title'],'start'=>$startData,'end'=>$endmod,'description'=>($dbvar['description']),
                    'origen'=>$dbvar['origen'],'idtarea'=>$dbvar['idtarea'],'turno'=>$dbvar['turno'] ,'refer' =>(is_null($dbvar['refer']))?'':$dbvar['refer'],
                    'centro'=>$dbvar['centro'], 'cliente'=>$dbvar['client'], 'group'=>$dbvar['group'], 'rgroup'=>$dbvar['rgroup'],'type' =>(is_null($dbvar['type']))?'Manual':$dbvar['type'] ,
                    'params' =>(is_null($dbvar['params']))?'':$dbvar['params'],'customer_affected' =>(is_null($dbvar['customer_affected']))?'':$dbvar['customer_affected'],
                    'open-close-ticket' => $dbvar['open-close-ticket'],'minutes-close-ticket' => $dbvar['minutes-close-ticket'],
                    'status' => $status,'creada'=>  date("Y-m-d H:i:s"), 'usuario' =>   $this->userData->username
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
            $end = $enddata[0]." ".$enddata[1];
            $this->eLog->log($idevento,sprintf('Excel_Event_Creation. Fecha inicio %s, Fecha fin: %s.',$startData,$end));
            //$this->eLog->log($idevento,json_encode(array('type'=>'Excel_Event_Creation','result'=>'Evento creado'));
            if(!is_null($dbvar['idtarea'])) $this->tLog->log($dbvar['idtarea'],'Tarea programada.');
            
            $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
            $TareasDb->SetFecha($dbvar['idtarea'],'NOW()','programada');
             
            return $data;
        }
        catch(Exception $ex){
            file_put_contents("creareventoexcel.txt",$ex);
        }
          
       
        
    }
    
 
  /**
     * Verifica si un evento con fecha de inicio y fin determinadas existe
     * @param string $startdata
     * @param string $enddata
     * @param int $idtarea
     * @return boolean
     */
    public function checkEventExists($startdata,$enddata,$idtarea)
    {
       $eventos =  new Application_Model_DbTable_Calendar_Eventos();
       $result = $eventos->checkEventExists($startdata,$enddata,$idtarea);
       return $result;
    }

    /**
     * Crea un Evento Recurrente
     * @param array $params
     */
    public function NewEventR($new_tarea, $rec_dat, $dbvar)
    {
		
		
        if(!isset($dbvar['title']))  return json_encode(array('error'=>array('title'=>'Falta dato.')));
        if(!isset($dbvar['allDay']))  $dbvar['allDay'] = null;
        if(!isset($dbvar['rgroup']))  $dbvar['rgroup'] = '';
        if(!isset($dbvar['t_start']))  return json_encode(array('error'=>array('start'=>'Falta dato.')));
        //if(!isset($dbvar['minimo']))  return json_encode(array('error'=>array('minimo'=>'Falta dato.')));
        if(!isset($dbvar['origen']))  return json_encode(array('error'=>array('origen'=>'Falta dato.')));
        if(!isset($new_tarea))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        if(!is_numeric($new_tarea)) return json_encode(array('error'=>array('id'=>'Formato incorrecto.')));
        /*echo ($rec_dat);
        echo ('<br />');
        echo ($new_tarea);
        echo ('<br />');
        die(print_r($dbvar));*/
        
        $dbvar['start'] = $rec_dat.' '.$dbvar['t_start'].':00';
        $dbvar['end'] = $rec_dat.' '.$dbvar['t_end'].':00';
        
        $dbvar['idtarea'] = $new_tarea;
        
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
        
        // Parche para la la fecha de google Chrome
        if(strpos($dbvar['start'],'(')===false) $start = new DateTime($dbvar['start']);
        else $start = DateTime::createFromFormat('D M d Y H:i:s e+', $dbvar['start']);
        
        $startData = $start->format('Y-m-d H:i:s');
        $startDataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        
        if(isset($dbvar['end']) && $dbvar['end']!='null') {
            $end = new DateTime($dbvar['end']);
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
            //$status = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
            $status = 'Pending';
            
        } else {
            $status = 'Pending';
            $dbvar['open-close-ticket'] = null;
            $dbvar['minutes-close-ticket'] = null;
            
        }

        
        $data = array('title'=>$dbvar['title'],'start'=>$startData,'end'=>$enddata,'description'=>($dbvar['description']),
        'origen'=>$dbvar['origen'],'idtarea'=>$dbvar['idtarea'],'turno'=>$dbvar['turno'] ,'refer' =>(is_null($dbvar['refer']))?'':$dbvar['refer'],
        'centro'=>/*$dbvar['centro']*/"BCN - 22@", 'cliente'=>$dbvar['client'], 'group'=>$dbvar['group'], 'rgroup'=>$dbvar['rgroup'],'type' =>(is_null($dbvar['type']))?'Manual':$dbvar['type'] ,
        'params' =>(is_null($dbvar['params']))?'':$dbvar['params'],'customer_affected' =>(is_null($dbvar['customer_affected']))?'':$dbvar['customer_affected'],
        'open-close-ticket' => $dbvar['open-close-ticket'],'minutes-close-ticket' => $dbvar['minutes-close-ticket'],
        'status' => $status,    
        );
	  
        if($allDay===1){
            $data['allDay'] = $allDay;
        }
     
        $data['creada'] = new Zend_Db_Expr('NOW()');
        //$data['usuario'] = Zend_Registry::get('usuario')->username;
        $data['usuario'] = Zend_Registry::get('usuario')->username != NULL ? Zend_Registry::get('usuario')->username : "MULTI";
        $idevento = $EventsDb->insert($data);
        
        // parches para que se muestre el evento y la tarea cuando se hace el drop
        $data['id']=$idevento;
        $data['username'] = $dbvar['turno'];//$this->userData->username;
        $data['OU_ID'] = isset($this->userData->OU_ID)?$this->userData->OU_ID:NULL;
        $data['start'] = $startDataBis;
        $data['end'] = $enddataBis;
        $data['open-close-ticket'] = $dbvar['open-close-ticket'];
        
        $UsusarioDb->insert(array('idevento'=>$idevento,'username'=>$dbvar['turno'],'OU_ID'=>$data['OU_ID']));
        
        self::SetEventosOptions($idevento,$data);
        
        $this->eLog->log($idevento,sprintf('Evento creado. Fecha inicio %s, Fecha fin: %s.',$startData,$enddata));
        
        if(!is_null($dbvar['idtarea'])) $this->tLog->log($dbvar['idtarea'],'Tarea programada.');
        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $TareasDb->SetFecha($dbvar['idtarea'],'NOW()','programada');
        //return $idevento;
        return $data;
        
    }

    
    /**
     * Crea un Evento
     * @param array $params
     */
    public function NewEvent()
    {
        if(!isset($this->params['list']['title']))  return json_encode(array('error'=>array('title'=>'Falta dato.')));
        if(!isset($this->params['allDay'])) return json_encode(array('error'=>array('allDay'=>'Falta dato.')));
        if(!isset($this->params['start']))  return json_encode(array('error'=>array('start'=>'Falta dato.')));
        if(!isset($this->params['list']['minimo']))  return json_encode(array('error'=>array('minimo'=>'Falta dato.')));
        if(!isset($this->params['list']['origen']))  return json_encode(array('error'=>array('origen'=>'Falta dato.')));
        if(!isset($this->params['list']['id']))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        
        if(! is_numeric($this->params['list']['id'])) return json_encode(array('error'=>array('id'=>'Formato incorrecto.')));
        
        $this->params['idtarea'] = $this->params['list']['id'];
        
        unset($this->params['list']['id']);
        
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $UsusarioDb = new Application_Model_DbTable_Calendar_UsuariosEventos();
        
        // Parche para la la fecha de google Chrome
        if(strpos($this->params['start'],'(')===false) $start = new DateTime($this->params['start']);
        else $start = DateTime::createFromFormat('D M d Y H:i:s e+', $this->params['start']);
        
        $startData = $start->format('Y-m-d H:i:s');
        $startDataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        
        if(isset($this->params['end']) && $this->params['end']!='null') {
            $end = new DateTime($this->params['end']);
            $enddata = $end->format('Y-m-d H:i:s');
            $enddataBis = $end->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
        else {
            //P0001-00-04T00:00:00
            $start->add(new DateInterval('P0000-00-00T'.$this->params['list']['minimo']));
            $enddata = $start->format('Y-m-d H:i:s');
            $enddataBis = $start->format('Y/m/d H:i:s'); // parches para que se muestre el evento y la tarea cuando se hace el drop
        }
        
        $allDay = is_null($this->params['allDay']) || $this->params['allDay']=='false' || $this->params['allDay']=='null'?new Zend_Db_Expr('NULL'):1;
        
        // status evento segun 'open-close-ticket'
         //$status = $dbvar['open-close-ticket'] ? 'Pending_ticket' : 'Pending';
         $status =  'Pending';
        
        $data = array('title'=>$this->params['list']['title'],'start'=>$startData,'end'=>$enddata,'description'=>($this->params['list']['description']),'origen'=>$this->params['list']['origen'],'idtarea'=>$this->params['idtarea'], 'status' => $status);
        
        if($allDay===1){
            $data['allDay'] = $allDay;
        }
        
        $idevento = $EventsDb->insert($data);
        
        // parches para que se muestre el evento y la tarea cuando se hace el drop
        $data['id']=$idevento;
        $data['username'] = $this->userData->username;
        $data['OU_ID'] = $this->userData->OU_ID;
        $data['start'] = $startDataBis;
        $data['end'] = $enddataBis;
        
        $UsusarioDb->insert(array('idevento'=>$idevento,'username'=>$this->userData->username,'OU_ID'=>$this->userData->OU_ID));
        
        self::SetEventosOptions($idevento,$data);
        
        $this->eLog->log($idevento,sprintf('Evento creado. Fecha inicio %s, Fecha fin: %s.',$startData,$enddata));
        
        if(!is_null($this->params['idtarea'])) $this->tLog->log($this->params['idtarea'],'Tarea programada.');
        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $TareasDb->SetFecha($this->params['idtarea'],'NOW()','programada');
        
        
        //return $idevento;
        return $data;
    }
    
    public function AddTarea()
    {
        if($this->params["recur"] ==true){
            $jsonar = array('key_recur'=>$this->params["key_recur"],'value_recur'=>$this->params["value_recur"],'value1_recur'=> $this->params["value1_recur"]);
        }
        else 
        {
            $jsonar = array();
        }
        
        $sql = "INSERT INTO cal_tareas ( `OU_ID`,`title`, `description`, `origen`,  `type`, `estado`, `creada`, `limite`, `asignada`,
                                            `template`, `refer`,    `programacion`, `rgroup`,
                                            `turno`,    `group`, `centro`, `environment`, `client`, `t_start`, `t_end`)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        
        ON DUPLICATE KEY UPDATE `OU_ID` = ?,`title` = ?, `description` = ?, `origen` = ?, `type` = ?, `estado` = ?, `creada` = ?, `limite` = ?, `asignada` = ?, `template` = ?,
                                             `refer` = ?, `programacion` = ?, `rgroup` = ?, `turno` = ?, `group` = ?, `centro` = ?, `environment` = ?, 
                                             `client` = ?, `t_start` = ?, `t_end` = ?";
        $values = array( $this->userData->OU_ID, $this->params["title"], $this->params["description"], $this->params["origen"], $this->params["type"],
                        'Pending assignment', date("Y-m-d H:i:s"), date("Y-m-d H:i:s"),null, $this->params["template"], null , json_encode($jsonar),$this->params["rgroup"], $this->params["turno"], $this->params["group"],
                        $this->params["centro"], $this->params["environment"], $this->params["client"], $this->params["t_start"], $this->params["t_end"], $this->userData->OU_ID, $this->params["title"], $this->params["description"], $this->params["origen"], $this->params["type"],
                        'Pending assignment', date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), null, $this->params["template"], null , json_encode($jsonar),$this->params["rgroup"], $this->params["turno"], $this->params["group"],
                        $this->params["centro"], $this->params["environment"], $this->params["client"], $this->params["t_start"], $this->params["t_end"]);
        
        $adapter = Zend_Db_Table::getDefaultAdapter();
        
        
        $qy = $adapter->query($sql,$values);
        /*die(var_dump($this->userData->OU_ID));
        var_dump($values);
        echo "<br />";
        var_dump($this->params);
        echo "<br />";
        die('Fin');*/
        return $adapter->lastInsertId(); 
    }
    public function DeleteEvent()
    {
        if(!isset($this->params['id'])) return json_encode(array('error'=>array('idevento'=>'Falta dato.')));
        if(!isset($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        
        if(! is_numeric($this->params['id'])) return json_encode(array('error'=>array('idevento'=>'Formato incorrecto.')));
        if(! is_numeric($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Formato incorrecto.')));
        
        $this->eLog->log($this->params['id'],'Evento eliminado.');
        
        $EventsDb = new Application_Model_DbTable_Calendar_Eventos();
        $e = $EventsDb->delete(array('id=?'=>$this->params['id']));
        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $t = $TareasDb->update(array('programada'=>new Zend_db_Expr('NULL')), array('id=?'=>$this->params['idtarea']));
        
        return (is_int($e) && is_int($t))?true:false;
    }
    
    /**
     * Asigna una tarea a un usuario.
     * Se se recibe el parametro allgroup asigna la tarea al grupo entero del usuario
     * @return json array
     */
    public function AsignarTarea()
    {
        if(!isset($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        if(! is_numeric($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Formato incorrecto.')));
        
        if(!isset($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Falta dato.')));
        if(! is_string($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Formato incorrecto.')));
        
        if(!isset($this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Falta dato.')));
        if(! preg_match('/^([a-zA-Z]{2})([0-9]{6})+$/',$this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Formato incorrecto.')));
        
        return self::AsignarProceso($this->params['idtarea'],$this->params['uid']);
    }
    
    public function AsignarEvent()
    {
        if(!isset($this->params['eventoid'])) return json_encode(array('error'=>array('eventoid'=>'Falta dato.')));
        if(! is_numeric($this->params['eventoid'])) return json_encode(array('error'=>array('eventoid'=>'Formato incorrecto.')));
        
        if(!isset($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Falta dato.')));
        if(! is_string($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Formato incorrecto.')));
        
        if(!isset($this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Falta dato.')));
        if(! preg_match('/^([a-zA-Z]{2})([0-9]{6})+$/',$this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Formato incorrecto.')));
       
        return self::AsignarProceso($this->params['eventoid'],$this->params['uid']);
    }


     /**
     * Asigna evento Journal y lo pone en estado Progress
     */
    public function StartEventJournal()
    {
       
        //$this->eLog->log($this->params['eventoid'],json_encode(array('type'=>'startevent','result'=>'success')));
        $dbEventos = new Application_Model_DbTable_Calendar_Eventos();
        $dbEventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
        $whereOpciones = "idEvento = ".$this->params['eventoid'];
        $arr_dat = array();
        $where = "id = ".$this->params['eventoid'];
        $arr_dat['Status'] = "Progress";
        $this->eLog->log($this->params['eventoid'],json_encode(array('type'=>'start-assignment','result'=>'Cambiando estado a process, asignado al usuario '.$this->userData->username)));
        $dbEventos->UpdateEvent($arr_dat, $where); //TODO
        $this->eLog->log($this->params['eventoid'],json_encode(array('type'=>'end-assignment','result'=>'Cambiado estado con éxito a process, asignado al usuario '.$this->userData->username)));
        $dbEventosOpciones->update(array('value'=>"Journal_Progress"), $whereOpciones); //TODO
        
        return "Progress";
    }
    
    
    
    /**
     * Start every event of a change which is in state Work in Progress
     * @param int $id
     */
    public function StartsEventsChangeSynchro($id)
    {
        $sm9 = New Application_Model_SM9Change();
        
        $eventosDB = new Application_Model_DbTable_Calendar_Eventos();
        $evento = $eventosDB->getEvento($id);
        $eventsChange = $eventosDB->getEventsByTask($evento['idTarea']);
        
        foreach ($eventsChange as $event) {
            if(json_decode($event['params'],true)['idTarea'] != 'Parent')
            {
                $res= $sm9->RetrieveChangeTaskXML(json_decode($event['params'],true)['idTarea'], "vs");
                if($res['TaskStatus'] == "Work in Progress")
                {
                   // $this->eLog->log($event['id'],json_encode(array('type'=>'startevent','result'=>'Evento asignado al usuario'.$this->userData->username)));
                    $dbEventos = new Application_Model_DbTable_Calendar_Eventos();
                    $dbEventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
                    $whereOpciones = "idEvento = ".$event['id'];
                    $arr_dat = array();
                    $where = "id = ".$event['id'];
                    $arr_dat['Status'] = "Progress";
                 
                 //start-assignment
                    $this->eLog->log($event['id'],json_encode(array('type'=>'start-assignment','result'=>'Comienza asignacion al usuario'.$this->userData->username)));
                    $dbEventos->UpdateEvent($arr_dat, $where);
                    $this->eLog->log($event['id'],json_encode(array('type'=>'end-assignment','result'=>'Asignado con éxito al usuario'.$this->userData->username)));

                    $dbEventosOpciones->update(array('value'=>"Journal_Progress"), $whereOpciones);
                
                }    
            }
        }
    }
    
    
    /**
     * Updates event with SM9 dates
     * @param array $data
     * @param int $id
     * @return string
     */
    public function updateDatesEvent($id)
    {
        try{
            $sm9 = New Application_Model_SM9Change();
            $eventosDB = new Application_Model_DbTable_Calendar_Eventos();
            $evento = $eventosDB->getEvento($id);
            $eventsChange = $eventosDB->getEventsByTask($evento['idTarea']);
           
           
            $idParent = 0;
    
            foreach ($eventsChange as $event) { //mientras recorro voy verificando en sm9 y actualizo las fechas
    
                if(json_decode($event['params'],true)['idTarea'] == 'Parent'){
                     
                    $idParent = $event['id'];
                    
                }
                else{
                    //Si es distinto de Parent
                     
                    $data = array();
                    $res= $sm9->RetrieveChangeTaskXML(json_decode($event['params'],true)['idTarea'], "vs");
    
                    $plannedStart = new DateTime($res['PlannedStart'],new DateTimeZone('UTC'));
                    $plannedStart->setTimezone(new DateTimeZone('Europe/Madrid'));
                    $plannedEnd = new DateTime($res['PlannedEnd'],new DateTimeZone('UTC'));
                    $plannedEnd->setTimezone(new DateTimeZone('Europe/Madrid'));
                    $data['start'] = $plannedStart->format('Y-m-d H:i:s');
                    $data['end'] = $plannedEnd->format('Y-m-d H:i:s');
                    
                    $res = $eventosDB->updateDatesEvent($data,$event['id']);
    
                     
                }
    
            }
            //update parent
            //retrieve change de SM9
            $res = $sm9->RetrieveChangeXML($evento['refer'],'vs');
            $plannedStartParent = new DateTime($res['PlannedStart'],new DateTimeZone('UTC'));
            $plannedStartParent->setTimezone(new DateTimeZone('Europe/Madrid'));
            $plannedEndParent = new DateTime($res['PlannedEnd'],new DateTimeZone('UTC'));
            $plannedEndParent->setTimezone(new DateTimeZone('Europe/Madrid'));
            $dataParent = array();
            $dataParent['start'] = $plannedStartParent->format('Y-m-d H:i:s');
            $dataParent['end'] = $plannedEndParent->format('Y-m-d H:i:s');
            
            $eventosDB->updateDatesEvent($dataParent,$idParent);
    
            return "OK";
    
        }
    
        catch (Exception $ex){
            file_put_contents("exception_updateDatesEvent.txt",$ex);
            return "KO";
        }
    
    }
    

   /**
   * Actualiza el evento en caso de error
   */
    public function UpdateEventError($id)
    {
         $this->eLog->log($this->params['eventoid'],json_encode(array('type'=>"Error",'result'=>"Error, se intentó asignar evento Revoked")));
        $dbEventos = new Application_Model_DbTable_Calendar_Eventos();
        $dbEventosOpciones = new Application_Model_DbTable_Calendar_EventosOpciones();
        $whereOpciones = "idEvento = ".$id;
        $arr_dat = array();
        $where = "id = ".$id;
        $arr_dat['Status'] = "Error";
        $dbEventos->UpdateEvent($arr_dat, $where); //TODO
        $dbEventosOpciones->update(array('value'=>"Journal_Error"), $whereOpciones); //TODO
    }


 /**
     * Replanifica un evento Parent y sus hijos
     * @param string $id
     */
    public function replanificaJournalEvent($id,$refer)
    {
        $db = new Application_Model_DbTable_Calendar_Eventos();
        //search parent and other children
        
        $events = $this->getParentAndChildrenIdsByRefer($refer);
        
       
        for ($i = 0; $i < count($events); $i++) {
            //TODO replanificar
            $this->changeStateReplanificado($events[$i]);
        }
        
    }
    
    
    /**
     * Replanifica eventos Parent e hijos, modifica fechas y pone en estado pending
     * @param string $id
     * @param string $refer
     * @param string $start
     * @param string $end
     */
   public function replanificaJournalToPending($id,$refer,$start,$end)
    {
        $db = new Application_Model_DbTable_Calendar_Eventos();
        $events = $this->getParentAndChildrenIdsByRefer($refer);
        for($i=0; $i < count($events); $i++)
        {
            $this->changeStateReplanifPending($events[$i],$start,$end);
        }
    }
    


    /**
     * Cancela un evento Journal
     */
    public function cancelJournalEvent($id,$closurecomments)
    {
       
        $db = new Application_Model_DbTable_Calendar_Eventos();
        $db->UpdateCancelJournalEvent($id,$closurecomments);
        $dbceo = new Application_Model_DbTable_Calendar_EventosOpciones();
        $where['`idEvento` = ?'] = $id;
        $where['`option` = ?']  = 'className';
        
        
        $dbceo->update(array('value'=>"Journal".'_'."Cancel"), $where);
        $this->eLog->log($id,json_encode(array('type'=>"closure",'result'=>$closurecomments)));
        return "Cancel";
    }
    
 
   /**
     * Actualiza el evento bitácora
     * @return boolean
     */
  public function UpdateEvent()
    {
       // CANCEL JOURNAL #>DBLANC
    	//$eventoid = $this->params['eventoid'];
    	$updateType = "";
    	
    	$log = new Application_Model_DbTable_Calendar_EventosLog();
    	
		if (isset($this->params['type'])  && isset($this->params['tyt']) && $this->params['type'] == "Journal" && $this->params['tyt'] == "Cancel" )
    	{
            $updateType = "CancelJournal";
    	}		
    	
    	switch ($updateType)
    	{
    		
    		//#>DBLANC Aqui podemos ir agregando casos especiales, modificando la variable updateType
    		case "CancelJournal":
    			$db = new Application_Model_DbTable_Calendar_Eventos();
    		    $db->UpdateCancelJournalEvent($this->params['eventoid'],$this->params['datos_update']['log']['ClosureComments']); // ver aqui que parametros necesita para actualizar event journal
    			$dbceo = new Application_Model_DbTable_Calendar_EventosOpciones();
    			$where['`idEvento` = ?'] = $this->params['eventoid'];
    			$where['`option` = ?']  = 'className';
    			$dbceo->update(array('value'=>"Journal".'_'."Cancel"), $where);
    			$this->eLog->log($this->params['eventoid'],json_encode(array('type'=>"closure",'result'=>$this->params['datos_update']['log']['ClosureComments']))); 
    			return $this->params['datos_update']['datos']['status'];
    			break;
    		
    		default:
    			
    			$arr_dat =array();
    			foreach ($this->params['datos_update'] as $mkey => $mvalue) {
    				foreach($mvalue as $key => $value){
    					if ($test = str_ireplace('__log_','',stristr($key,'__log_'))) {
    						if($test != null && $value != null)
    						$this->eLog->log($this->params['eventoid'],json_encode(array('type'=>$test,'result'=>$value)));
    						if(!isset($this->params['datos_update']['datos']['status'])){
    							return false;
    						}
    					}
    					else{
    						$arr_dat[$mkey][$key] = $value;
    					}
    			
    				}
    			}
                if(isset($this->params['datos_update']['datos']['__log_comment'])){
					$arr_dat['datos']['comentarios'] = $this->params['datos_update']['datos']['__log_comment'];
				}
				
				//SOLO EN EL CASO DE CIERRE DE JOURNAL
				if(strtolower($this->params['type']) == "journal"/* && isset($this->params['minutestasklast'])*/)
				{
					$eventsSameChange = $this->relatedEvents($this->params['eventoid']);
					$allfinished = $this->isLastResolvedTask($eventsSameChange,$this->params['eventoid']);
					if(isset($this->params['minutestasklast']))
					$arr_dat['datos']['time_user']= $this->params['minutestasklast'];

                    if(isset($this->params['resolution_code']))
                        $arr_dat['datos']['resolution_code']= $this->params['resolution_code'];
					//calculation of time-app
					
					$time_app = $this->calculateTaskDuration($this->params['eventoid']);
					$arr_dat['datos']['time_app'] = $time_app;
				
					$this->assignTimeValuesToParent($log,$eventsSameChange,$allfinished,$arr_dat);
					
				}
			
                    if(isset($this->params['tyt']) && $this->params['tyt'] == "closure_checklist"){
                            
                            $time_app = $this->calculateTaskDuration($this->params['eventoid']);
                            $arr_dat['datos']['time_app'] = $time_app;
                            $this->eLog->log($this->params['eventoid'],json_encode(array('type'=>"closure",'result'=>"Cierre evento checklist")));
                    }
				
				
    			
    			
				$db = new Application_Model_DbTable_Calendar_Eventos();
				
				
    			
                $db->UpdateEvent($arr_dat['datos'], $arr_dat['where']);
    			if($arr_dat['datos']['status']){
    				$where['`idEvento` = ?'] = $this->params['eventoid'];
    				$where['`option` = ?']  = 'className';
    				$db = new Application_Model_DbTable_Calendar_EventosOpciones();
    				try{
    					$db->update(array('value'=>$this->params['type'].'_'.$arr_dat['datos']['status']), $where);
    				}catch(Zend_Exception $e){
    					 
    				}
    			}
    			if(isset($this->params['tyt'])){
    				if($this->params['tyt'] === 'Schedule'){
    					$db = new Application_Model_DbTable_Calendar_EventosOpciones();
    					try{
    						$db->insert(array('idEvento'=>$this->params['eventoid'],'option'=>'borderColor','value'=>'#FF00FF'));
    					}catch(Zend_Exception $e){
    						 
    					}
    				}
    			}

    			return $arr_dat['datos']['status'];
    			
    			break;
    	}
        
    }

	/**
	 * @param log
	 * @param eventsSameChange
	 * @param allfinished
	 */
	private function assignTimeValuesToParent($log, $eventsSameChange, $allfinished,$arr_dat) {
        $db = new Application_Model_DbTable_Calendar_Eventos();
		if($allfinished){
			//crear array fechas
			$datesArray = array();
			$idsArray = array();
			
			foreach ($eventsSameChange as $ev) {
			 array_push($idsArray,$ev['id']);	
			}
			
			$logsDates = $log->recoverDatesLogsFromEventsChange($idsArray);
			$min = min($logsDates);
			$max = date("Y-m-d H:i:s", time());
			$dateEarlier = strtotime($min);
			$dateLatter = time();
			$intervalParent = round(abs($dateLatter - $dateEarlier) / 60,2);
				
			//UPDATE PARENT
			$sumTimeUser = $this->sumTimeUser($eventsSameChange,$arr_dat['datos']['time_user']);
			foreach ($eventsSameChange as $ev) {
				$params = json_decode($ev['params'],true);
				if($params['idTarea']== "Parent")
				{
				
					//Update PARENT
					$datos = array();
					$datos['time_user'] = $sumTimeUser;
					$datos['time_app']	= $intervalParent;
					$where['id = ?'] = $ev['id'];
					$db->UpdateEvent($datos, $where);
				}
			}
			}
	}

    
	/**
	 * Do the sum of all user times iterating through change's events 
	 * @param array $arrayEventos
	 * @param int $timeUserCurrentEvent
	 */
    private function sumTimeUser($arrayEventos,$timeUserCurrentEvent){
    	$sumTimeUser = 0;
    	foreach ($arrayEventos as $e) {
    		$params = json_decode($e['params'],true);
    		$idTarea = $params['idTarea'];
    		if($idTarea != "Parent")
    		{
    			$sumTimeUser += $e['time_user'];
    		}
    	}
    	$sumTimeUser += $timeUserCurrentEvent;
    	return $sumTimeUser;
    }
    
   /**
   * Verifies whether an event is the last resolved among its siblings
   * @param int $idevento
   * @return array
   */
   public function  relatedEvents($idevento)
   {
   		$events = new Application_Model_DbTable_Calendar_Eventos();
   		$data = $events->taskFromChange($idevento);
   		return $data;
   }
   
   /**
    * Determines if we are treating with last event not finished
    * @param array $arrayEvents
    */
   private function isLastResolvedTask($arrayEvents,$idevent){
   	$allFinished = true;
   	$progress = 0;
   	if(count($arrayEvents)==2)
   	{
   		return true;
   	}
   	
   	foreach ($arrayEvents as $event) {
   		
   			$params = json_decode($event['params'],true); 
   			if(($event['status'] == "Progress" || $event['status'] == "Pending")  && $event['id'] != $idevent && $params['idTarea'] != "Parent")
   			{
   				$progress ++;
   			}
   		
   	}
   	
   	if($progress == 0)
   		$allFinished = true;
   	else $allFinished = false;
   	
   	return $allFinished;	
   }
   
   
	/**
	 * Calculates lapse between work in progress and finish states
	 * @param int $idevento
	 */
   public function calculateTaskDuration($idevento)
   {
   	
   		$start="";$end="";
   		$log = new Application_Model_DbTable_Calendar_EventosLog();
   		$logs = $log->recoverLogsbyEventId($idevento);
   		
   		foreach ($logs as $l) {
   			if(strpos($l["msj"],"end-assignment") != 0 || strpos($l["msj"],"startevent") != 0)
   			{
   				$start = $l["fecha"];
   			}
   			if(strpos($l["msj"],"closure") != 0)
   			{
   				$end = $l["fecha"];
   			}
   		}
   		if($start == null || $start == "")
        {
            //recover evento 
                $db = new Application_Model_DbTable_Calendar_Eventos();
                $ev = $db->getEvento($idevento);   
                
                $start = $ev['creada'];
        }
   		$rta = array();
   		$date1 = strtotime($start);
   		$date2 = time(); 
   		
   		
   		$interval = round(abs($date2 - $date1) / 60,2);
   		
   		return $interval;
   }
 
 
 /**
  * 
  * @param int $idtarea
  * @param int $uid
  * @param string $subgrupo
  * @param string $allgroup
  */
    private function AsignarProceso($idtarea,$uid,$subgrupo=0,$allgroup=0)
    {
        $db = new Application_Model_DbTable_Calendar_UsuariosEventos();
        
        if($allgroup==1 && $subgrupo!=0){
            
            $r = $db->AsignarSubgrupo($idtarea,$subgrupo);
            if(!$r){
                $r = array('error'=>array('Error'=>'Fallo al asignar tarea al grupo '.$subgrupo.'.'));
            }
            
            else{
                $this->tLog->log($idtarea,'Tarea asignada al Grupo '.$subgrupo.'.');
            }
        }

            else{
                $json = json_encode(array(  'type'   =>'start-assignment',
                        'result' =>'Inicia asignacion de tarea al usuario '.$uid.'.'
                ));
                $this->eLog->log($this->params['eventoid'],$json);
                $r = $db->AsignarUsuario($idtarea,$uid);
                if(!$r){
                    $r = array('error'=>array('Error'=>'Fallo al asignar tarea al usuario '.$uid.'.'));
                }
                
                else{
                    
                    $json = json_encode(array(  'type'   =>'end-assignment',
                            'result' =>'Asignado con éxito al usuario '.$uid.'.'
                    ));
                    $this->eLog->log($this->params['eventoid'],$json);
                    
                }
               }
      
        return $r;
    }

    
    
    
    /**
     * Abre un ticket en SM9.
     * @return json array
     */
    public function OpenTicket($usuario, $grupoUsuario, $eventoid,$cliente_nombre="")
    {
		if(!isset($usuario)) return json_encode(array('error'=>array('user'=>'Falta dato.')));
        if(!is_string($usuario)) return json_encode(array('error'=>array('user'=>'Formato incorrecto.')));
        
        if(!isset($grupoUsuario)) return json_encode(array('error'=>array('grupoUser'=>'Falta dato.')));
        if(!is_string($grupoUsuario)) return json_encode(array('error'=>array('grupoUser'=>'Formato incorrecto.')));
        
        if(!isset($eventoid)) return json_encode(array('error'=>array('eventid'=>'Falta dato.')));
        if(!is_numeric($eventoid)) return json_encode(array('error'=>array('eventid'=>'Formato incorrecto.')));
        

        $dbEventos = new Application_Model_DbTable_Calendar_Eventos();
        $evento = $dbEventos->getEvento($eventoid);
        $dbClientes = new Application_Model_DbTable_Clientes();
		$nombreCliente = $dbClientes->getNombre($evento['cliente']);
		$sm9 = new Application_Model_SM9Client();
		$this->eLog->log($eventoid,$usuario." creará el Incidente para el evento ".$eventoid);
		$res = $sm9->createIncidentCheckList($evento['title'], $evento['description'], $nombreCliente, $usuario,$cliente_nombre);
			
        if ($res['returnCode'] > 0){
            $r = array('fail'=>array('Fallo'=>$usuario.' No ha podido crear el ticket del evento '.$eventoid.'. '.$res['message']));
            $this->eLog->log($eventoid,'Ticket Erroneo');
            
        } else {
            $this->eLog->log($eventoid,'Ticket '.$res['IncidentID'].' creado para evento '.$eventoid.' por '.$usuario);
            
            $res = $dbEventos->UpdateEvent(array('ticket-start'=>date("Y-m-d H:i:s"), 'ticket-id'=>$res['IncidentID']),'id ='.$eventoid);
            if ($res){
                $r=$res;
            
            } else {
                $r = array('fail'=>array('Fallo'=>'No se ha podido actualizar fecha de apertura de ticket del evento '.$eventoid));
            
            }
            
        }
        
        return $r;
    
    }
    
        
    
    /**
     * Desasigna una tarea a un usuario.
     * Se se recibe el parametro allgroup desasigna la tarea al grupo entero del usuario
     * @return json array
     */
    public function DesasignarTarea()
    {
        if(!isset($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        if(! is_numeric($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Formato incorrecto.')));
        
        if(!isset($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Falta dato.')));
        if(! is_string($this->params['uid'])) return json_encode(array('error'=>array('uid'=>'Formato incorrecto.')));
        
        if(!isset($this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Falta dato.')));
        if(! preg_match('/^([a-zA-Z]{2})([0-9]{6})+$/',$this->params['OU_ID'])) return json_encode(array('error'=>array('OU_ID'=>'Formato incorrecto.')));
        
        $db = new Application_Model_DbTable_Calendar_UsuariosTareas();
        
        $r = $db->DesasignarUsuario($this->params['idtarea'],$this->params['uid']);
        if($r==0):
            $r = array('fail'=>array('Fallo'=>'Imposible de realizar'));
        else:
            $this->tLog->log($this->params['idtarea'],'Tarea desasignada del usuario '.$this->params['uid']);
        endif;

        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $TareasDb->SetFecha($this->params['idtarea'],'NULL');
        
        return $r;
    }
    
    /**
     * Marca una tarea como finalizada
     */
    public function FinalizarTarea()
    {
        if(!isset($this->params['eventoid'])) return json_encode(array('error'=>array('eventoid'=>'Falta dato.')));
        if(! is_numeric($this->params['eventoid'])) return json_encode(array('error'=>array('eventoid'=>'Formato incorrecto.')));
        
        if(!isset($this->params['type'])) return json_encode(array('error'=>array('type'=>'Falta dato.')));
        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        
        switch($this->params['type']):
        case 'Finish': $type = 'NOW()'; Break;
        case 'reabierta': $type = 'NULL'; Break;
        endswitch;
        
        $r = $TareasDb->SetFecha($this->params['idtarea'],$type,'finalizada');
        
        if($r==0):$r = array('error'=>array('Error'=>'Imposible de realizar'));
        else:
            $this->tLog->log($this->params['idtarea'],'Tarea '.$this->params['type'].'.');
            $dbEvento = new Application_Model_DbTable_Calendar_Eventos();
            $evento = $dbEvento->CheckTarea($this->params['idtarea']);
            if($evento):
                $dbOpEv = new Application_Model_DbTable_Calendar_EventosOpciones();
                if($type=='NULL'):$ra=$dbOpEv->RemoveEventValue($evento['id'], 'className', 'tareaFinalizada');
                else:$ra=$dbOpEv->SetEventValue($evento['id'], 'className', 'tareaFinalizada');
                endif;  
            endif;
            // Verifico si está programada y añado la clase tareaFinalizada
            
        endif;
        
        if(isset($ra['fail'])){$r=$ra;}
                
        return json_encode($r);
    }
    
    public function NewTarea()
    {
        if(!isset($this->params['title']))  return json_encode(array('error'=>array('title'=>'Falta dato.')));
        if(!isset($this->params['description']))  return json_encode(array('error'=>array('description'=>'Falta dato.')));
        if(!isset($this->params['max']))  return json_encode(array('error'=>array('max'=>'Falta dato.')));
        if(!isset($this->params['min']))  return json_encode(array('error'=>array('min'=>'Falta dato.')));
        if(!isset($this->params['limite']))  return json_encode(array('error'=>array('limite'=>'Falta dato.')));
        
        $tarea = array();
        $tarea['title'] = $this->params['title'];
        $tarea['description'] = $this->params['description'];
        $tarea['OU_ID'] = $this->userData->OU_ID;
        $tarea['origen'] = 'Portal';
        $tarea['creada'] = new Zend_Db_Expr('NOW()');
        $limite = DateTime::createFromFormat('d/m/Y H:i', $this->params['limite']);
        $tarea['limite'] = $limite->format('Y-m-d H:i:s');
        
//      $tarea['max'] = $this->params['max'];
//      $tarea['min'] = $this->params['min'];
        
        $max = DateTime::createFromFormat('H:m', $this->params['max']);
        $tarea['maximo'] = $max->format('H:i:s');
        $min = DateTime::createFromFormat('H:i', $this->params['min']);
        $tarea['minimo'] = $min->format('H:i:s');

        
        $TareasDb = new Application_Model_DbTable_Calendar_Tareas();
        $nuevoId = $TareasDb->insert($tarea);
        
        $this->tLog->log($nuevoId,'Tarea creada.');
        
        
        if(isset($this->params['uid']) && $this->params['uid']!=-1)
        {
            self::AsignarProceso($nuevoId,$this->params['uid'],$this->params['subgrupo']);
        }
        else
        {
            self::AsignarProceso($nuevoId,$this->params['uid'],$this->params['subgrupo'],1);
        }
        
        return $nuevoId;
        
    }
    
    public function addComentario()
    {
//      if(!isset($this->params['idevento'])) return json_encode(array('error'=>array('idevento'=>'Falta dato.')));
        if(!isset($this->params['idtarea']))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        
//      if(! is_numeric($this->params['idevento'])) return json_encode(array('error'=>array('idevento'=>'Formato incorrecto.')));
        if(! is_numeric($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Formato incorrecto.')));
        
        if(!isset($this->params['comentario']))  return json_encode(array('error'=>array('comentario'=>'Falta o formato incorrecto.')));
        
        $ComentariosDb = new Application_Model_DbTable_Calendar_Comentarios();
        
        return $ComentariosDb->insert(array('idRelacion'=>self::getIdRelacion(),'comentario'=>$this->params['comentario'],'username'=>$this->userData->username,'apartado'=>self::getApartado()));
    }
    
    public function delComentario()
    {
        if(!isset($this->params['idComentario']))  return json_encode(array('error'=>array('idComentario'=>'Falta o formato incorrecto.')));
        
        $ComentariosDb = new Application_Model_DbTable_Calendar_Comentarios();
        
        $comentario = $ComentariosDb->find($this->params['idComentario']);
        $row = $comentario->current();
        
        if($row->username == $this->userData->username)
        {
            $row->borrado = 'Y';
            return $row->save();
        }
    }
    
    /**
     * Devuelve los comentarios asociados a un idevento
     * @return array
     */
    public function getComentario()
    {
        //if(!isset($this->params['idevento'])) return json_encode(array('error'=>array('idevento'=>'Falta dato.')));
        if(!isset($this->params['idtarea']))  return json_encode(array('error'=>array('idtarea'=>'Falta dato.')));
        
        //if(! is_numeric($this->params['idevento'])) return json_encode(array('error'=>array('idevento'=>'Formato incorrecto.')));
        if(! is_numeric($this->params['idtarea'])) return json_encode(array('error'=>array('idtarea'=>'Formato incorrecto.')));
        
        if(!isset($this->params['limit'])) $this->params['limit'] = 5;
        
        $ComentariosDb = new Application_Model_DbTable_Calendar_Comentarios();
        $UserEasyDb = new Application_Model_DbTable_Easya_User();
        
        $select = $ComentariosDb->select()
                                ->setIntegrityCheck(false)
                                ->from(array('a'=>$ComentariosDb->__get('_name')),array('id','idRelacion','comentario','username','fecha','apartado'))
                                ->join(array('b'=>'ea_core.user'),'a.username=b.username',array('display_name')) // <---------- OJO CON EL NOMBRE DE LA TABLA EN PRODUCCIÓN
                                ->where('a.borrado = ?','N')
                                ->where('a.idRelacion = ?', self::getIdRelacion())
                                ->where('a.apartado = ?', self::getApartado())
                                ->order('ID DESC');
        
        $result = $ComentariosDb->fetchAll($select);
        
        $salida = array();
        $salida['count'] = $result->count();
        
        if($this->params['limit']>0) {$select->limit($this->params['limit']);}
        
        $result = $ComentariosDb->fetchAll($select)->toArray();
        
        asort($result);
        
//      $for = $this->params['limit']==0?count($result):$this->params['limit'];
        
//      $for = count($result)<$this->params['limit'] && $this->params['limit']!=0?count($result):$this->params['limit'];
        
        $for = $this->params['limit']==0?count($result):(count($result)<=$this->params['limit']?count($result):$this->params['limit']);
        
//      return json_encode(array('error'=>array('FOR'=>$for)));
        
        for($i=0;$i<$for;$i++)
        {
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $helper = $view->getHelper('HaceTiempo');
            $result[$i]['tiempo'] = $helper->clacular($result[$i]['fecha'].' [Y-n-j H:i:s]');
            $result[$i]['comentario'] = $view->escape($result[$i]['comentario']);
        }
        
        $salida['comentarios'] = $result;
        
        return $salida;
    }
    
    /**
     * Obtiene los usuarios de un determinado subgrupo
     */
    public function getSubgroupUsers()
    {
        if(!isset($this->params['idGrupo'])) return json_encode(array('error'=>array('idsubgrupo'=>'Falta dato.')));
        
        $db = new Application_Model_DbTable_Calendar_SubgruposUsername();
        return $db->GetUsersInGroup($this->params['idGrupo']);
    }
    
    /**
     * Verifica los parámetros recibidos y devuelve el idRelacion
     */
    private function getIdRelacion()
    {
        if(isset($this->params['apartado']) && $this->params['apartado']=='eventos')return $this->params['idevento'];
        if(isset($this->params['apartado']) && $this->params['apartado']=='tareas') return $this->params['idtarea'];
        if(isset($this->params['idevento']) && !is_null($this->params['idevento']) && !empty($this->params['idevento']) && $this->params['idevento']!=0) return $this->params['idevento'];
        if(isset($this->params['idtarea']) && !is_null($this->params['idtarea']) && !empty($this->params['idtarea']) && $this->params['idtarea']!=0) return $this->params['idtarea'];
        die('Error: obteniendo el idRelacion');
    }
    
    /**
     * Verifica los parámetros recibidos y devuelve el apartado para el cual se guarda un comentario.
     */
    public function getApartado()
    {
        if(isset($this->params['apartado'])) return $this->params['apartado'];
        if(isset($this->params['idevento']) && !is_null($this->params['idevento']) && !empty($this->params['idevento']) && $this->params['idevento']!=0) return 'eventos';
        if(isset($this->params['idtarea']) && !is_null($this->params['idtarea']) && !empty($this->params['idtarea']) && $this->params['idtarea']!=0) return 'tareas';
        die('Error: obteniendo el apartado');
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
			
			Break; 
            case 'Checklist':
                
//                 $value = ($datos['open-close-ticket']) ? "Checklist_Pending_ticket" : 'Checklist_Pending';
//                 $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>$value));
                $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>'Checklist_Pending'));
                
                Break;
            default: $db->insert(array('idevento'=>$idevento,'option'=>'className','value'=>'Checklist_Pending')); Break;
        }
    }
    
    
    /**
     * Convert BR tags to nl
     *
     * @param string The string to convert
     * @return string The converted string
     */
    function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

      /**
     * Get ids of parent and children by refer
     * @param string $refer
     */
    function getParentAndChildrenIdsByRefer($refer)
    {
        $eventos = new Application_Model_DbTable_Calendar_Eventos();
        $ids = $eventos->GetIdsByRefer($refer);
        return $ids;
    }
    
    
    /**
     * Change state to replanificado
     * @param string $id
     */
    function changeStateReplanificado($id)
    {
        $data['status'] = 'Replanificado';  
        $whereEvent['`id` = ?'] = $id;      
        $db = new Application_Model_DbTable_Calendar_Eventos();
        $db->UpdateEvent($data,$whereEvent);
        $dbceo = new Application_Model_DbTable_Calendar_EventosOpciones();
        $where['`idEvento` = ?'] = $id;
        $where['`option` = ?']  = 'className';
        $dbceo->update(array('value'=>"Journal".'_'."Replanificado"), $where);
        $this->eLog->log($id,"Evento replanificado");
    }
    
    
    /**
     * Change state to pending, also change dates
     * @param string $id
     * @param string $start
     * @param string $end
     */
    function changeStateReplanifPending($id,$start,$end)
    {
        $data['status'] = 'Pending';
        $data['start'] = $start;
        $data['end'] = $end;
        $whereEvent['`id` = ?'] = $id;
        $db = new Application_Model_DbTable_Calendar_Eventos();
        $db->UpdateEvent($data,$whereEvent);
        $dbceo = new Application_Model_DbTable_Calendar_EventosOpciones();
        $where['`idEvento` = ?'] = $id;
        $where['`option` = ?']  = 'className';
        $dbceo->update(array('value'=>"Journal".'_'."Pending"), $where);
        $this->eLog->log($id,"Evento replanificado pending");
    }
    
    /**
     * Determina si un evento es Info
     * @param array $info
     * @param array $arrayData
     * @return boolean
     */
    public function isInfo($info,$arrayData)
    {
        $idChange  = $info[1];
        $count =0;
        for ($i = 0; $i < count($arrayData); $i++) {
             
            if($arrayData[$i][1] == $idChange)
            {
                $count ++;
            }
        }
        if($count == 1)
            return true;
        if($count > 1)
            return false;
    }

    
    public function __set($propiedad,$valor) {$this->$propiedad=$valor;}
    public function __get($propiedad) {return $this->$propiedad;}
    public function __isset($propiedad) {return isset($this->$propiedad);}
    public function __unset($propiedad) {unset ($this->$propiedad);}
    
}