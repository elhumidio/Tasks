<?php
class Application_Model_DbTable_Calendar_Tareas extends Zend_Db_Table {
	
	protected $_name = "cal_tareas";
	protected $_primary = 'id';
	
	/* Actualiza la fecha de asignación */
	public function SetFecha($idtarea,$fecha = 'NOW()',$type='asignada')
	{
		try{
			return parent::update(array($type=>new Zend_Db_Expr($fecha)),array('id=?'=>$idtarea));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	
	/**
	 * Devuelve la programacion de una tarea
	 */
	public function GetProgramTaskById($idTarea)
	{
	    $select = parent::select()->from($this->_name,array('programacion'))->where('id = '.$idTarea);
	    
	    $result = parent::fetchAll($select);
	    
	    return $result->count()>0?$result->toArray():false;
	}
	
	/**
	 * Devuelve todas las tareas del grupo
	 * @param string $OU_ID
	 * @param string $type
	 * @param string $dateLimit
	 */
	public function GetTaskGroup($OU_ID,$type='all',$dateStart='all',$dateEnd='all')
	{
		$UsusarioDb= new Application_Model_DbTable_Calendar_UsuariosTareas();
		$EventosDb= new Application_Model_DbTable_Calendar_Eventos();
		
		$selectc = parent::select()
					->from(array('a'=>$this->_name),array('idtarea'=>'id','creada','limite','title','origen','OU_ID','description'))
					->where('a.finalizada IS NULL')
					->order('a.limite ASC')->order('a.origen ASC');

		switch($type):
		case 'huerfanas':
			$selectc->setIntegrityCheck(false)
					->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',NULL)
					->where('a.OU_ID=?',$OU_ID)->where('c.username IS NULL');
			Break;
		case 'asignadas':
			$selectc->setIntegrityCheck(false)
					->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
					->where('a.OU_ID=?',$OU_ID)->where('c.username IS NOT NULL');
			Break;
			
		case 'programadas':
			$selectc->setIntegrityCheck(false)
					->joinLeft(array('c'=>$UsusarioDb->__get('_name')),'a.id=c.idtarea',array('username'))
					->joinLeft(array('b'=>$EventosDb->__get('_name')),'a.id=b.idTarea',array('idEvento'=>'id'))
					->where('a.OU_ID=?',$OU_ID)->where('c.username IS NOT NULL')->where('b.id IS NOT NULL');
			Break;
		case 'all':
		default: Break;
		endswitch;
		
		switch($dateEnd):
		case 'all': Break;
		default: $selectc->where('a.limite<=?',$dateEnd);Break;
		endswitch;
		
		$result = parent::fetchAll($selectc);
		return $result->count()>0?$result->toArray():array();
	}

    public function InsertTarea($data)
    {
    	try
    	{
	    	$data['creada'] = new Zend_Db_Expr('NOW()');
	        return parent::insert($data);	
    	}
    	catch(Exception $ex)
    	{
    		file_put_contents("exception_InsertTarea.txt", $ex);
    	}
        
    }
    
     public function rmTemplate($data,$where)
    {
        if ( $data)
        {
            return parent::update(array('type' => 'Cancel_'), $where);            
        }
        else {
            return parent::delete($where);
        }
    }


    public function deleteTareaJournal($refer)
    {
    	$refertrimmed = trim($refer);
    	$where = "refer  = ".$refertrimmed;
        return parent::delete($where);

    }

    public function GetNotProgramedEvents()
    {
    	$start_date_formatted = date('Y-m-d' ).' 00:05:00';
    	$query = parent::select()
    	->where("schedule <= ?",$start_date_formatted)
    	->where("lower(origen) = ?","checklist")
    	->where("type <> 'Cancel_'");
    	//file_put_contents("querygnpe.txt", $query);
    	$result = parent::fetchAll($query)->toArray();

    	
    	if(count($result) > 0)
    		return $result;
    	else return "Se han encontrado ".count($result)." tareas sin programar";
    }
    
    /**
     * Devuelve las tareas cuya programación finalice mañana 
     * @param string $OU_ID
     * @param string $type
     * @param string $dateLimit
     */
    public function GetTmrowTemplates()
    {
        try{
                $start_date_formatted = date('Y-m-d' ).' 00:00:00';          
                $end_date_formatted = date('Y-m-d').' 23:59:59';
                //$end_date_formatted = date('Y-m-d', strtotime("tomorrow")).' 23:59:59';
                $select = parent::select()->from($this->_name,array(
                                                                    'id','turno','centro',
                                                                    't_start' => new Zend_Db_Expr("DATE_FORMAT(`t_start`,'%H:%i')"), 
                                                                    't_end' => new Zend_Db_Expr("DATE_FORMAT(`t_end`,'%H:%i')"),
                                                                    'group','client','title','description','origen',
                                                                    'OU_ID','programacion','owner', 'open-close-ticket', 'minutes-close-ticket'))
                													//->where("lower(origen) = ?","'checklist'")
                                                                    ->where("schedule >= ?",  $start_date_formatted)
                                                                    ->where("schedule <= ?",  $end_date_formatted)
                                                                    ->where("type <> 'Cancel_'");
                file_put_contents("Query_Get_Templates_Schedule_Checklist.txt", $select);
                $result = parent::fetchAll($select)->toArray();
                foreach ($result as $res) {
                    $id = $res['id'];
                    unset($res['id']);
                    $r[] = array('data' => $res,'id'=>$id);
                }
            return count($result)>0?$r:false;
        }catch(Zend_Exception $e){
            return array('fail'=>array($e->getCode()=>$e->getMessage()));
        }
    }
	
	public function GetOrigenes()
	{
		try{
			
			$select = parent::select()->from($this->_name,array('id','origen'))->group('origen')->order('origen ASC');
			$result = parent::fetchAll($select);
			return $result->count()>0?$result->toArray():false;
			
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
    
    public function CheckIs($wh)
    {
        try{
            
            $select = parent::select()->from($this->_name,array('refer'))->where('refer =?',$wh);
            $result = parent::fetchAll($select);
            return $result->count() > 0 ? true : false;
            
        }catch(Zend_Exception $e){
            return array('fail'=>array($e->getCode()=>$e->getMessage()));
        }
    }
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
}

?>