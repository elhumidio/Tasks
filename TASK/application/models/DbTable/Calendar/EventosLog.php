<?php
class Application_Model_DbTable_Calendar_EventosLog extends Zend_Db_Table {
	
	protected $_name = "cal_eventos_log";
	protected $_primary = array('idEvento','username');
	
	private $username;
	private $OU_ID;
		
		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	public function __set($propiedad,$valor) {
		$this->$propiedad = $valor;
	}
	
	/**
	 * Gets users from Log
	 */
	public function getUsersFromLog()
	{
		$select = parent::select()->from($this,array('username'=>'username'))
		->distinct()->order(array("username asc"));
		$results = self::fetchAll($select)->toArray();
		$res = array();
		
		return $results;
	}
	
	/**
	 * Recupera los mensajes de checklist (comentarios)
	 * @param string $idEvento
	 */
	public function getMessagesChecklist($idEvento)
	{
	    try{
	        
	        $select = parent::select()->where('idEvento =?',$idEvento);
	        $res = parent::fetchAll($select)->toArray();
	        $result = array();
	        
	        foreach($res as $r){
	            if (self::isJSON($r['msj'])) // Si el mensaje es json
	            {
	                $msg = json_decode($r['msj']);
	                 
	                if($msg->type)
	                {
	                    if($msg->type == "comment")
	                        $result[] =$r['username'].": ".$r['fecha']." - ".$msg->result;
	                }
	            }
	            
	        }
	        return $result;
	    }
	    catch(Zend_Exception $e){
	        return array('fail'=>array($e->getCode()=>$e->getMessage()));
	    }
	    
	}
	
	
		/**
	 * Get Closure messages
	 * @param string $idEvento
	 */
	public function getClosureMessagesJournal($idEvento)
	{
	    try{
	         
	        $select = parent::select()->where('idEvento =?',$idEvento);
	        $res = parent::fetchAll($select)->toArray();
	        $result = array();
	         
	        foreach($res as $r){
	            if (self::isJSON($r['msj'])) // Si el mensaje es json
	            {
	                $msg = json_decode($r['msj']);
	    
	                if(isset($msg->type))
	                {
	                    if($msg->type == "closure"){
	                        $result[] =$r['username'].": ".$r['fecha']." - ".$msg->result;
	                    }
	                        
	                }
	            }
	             
	        }
	        return $result;
	    }
	    catch(Zend_Exception $e){
	        return array('fail'=>array($e->getCode()=>$e->getMessage()));
	    }
	}
	
	
	/**
	 * Inserta una fila
	 * @param int $idEvento
	 * @param string $msj
	 */
	public function log($idEvento,$msj,$user=false,$OU_ID = false)
	{
		try{
			//file_put_contents ( "LOG.txt" , json_encode($idEvento)." - ".$msj);
			if($this->username)
			{	
				if(is_array($idEvento))
					return parent::insert(array('idEvento'=>$idEvento['id'],'username'=>$this->username,'OU_ID'=>$this->OU_ID,'msj'=>$msj));
				else 
					return parent::insert(array('idEvento'=>$idEvento,'username'=>$this->username,'OU_ID'=>$this->OU_ID,'msj'=>$msj));

			}		
			else{
				if($user){
					if(is_array($idEvento))
						return parent::insert(array('idEvento'=>$idEvento['id'],'username'=>$user,'OU_ID'=>$OU_ID,'msj'=>$msj));	
					else 
						return parent::insert(array('idEvento'=>$idEvento,'username'=>$user,'OU_ID'=>$OU_ID,'msj'=>$msj));	
				}

				
			}
		}catch(Zend_Exception $e){
			file_put_contents ( "LOG_Exception.txt" , $e->getMessage()." - - - - ".$idEvento." - - ".$msj,FILE_APPEND);
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
	
	/**
	 * Retrieves logs by event id
	 * @param int $eventid
	 */
	public function recoverLogsbyEventId($eventid)
	{
		$select = parent::select()->where('idEvento =?',$eventid)->order('id asc');
		$res = parent::fetchAll($select)->toArray();
		return $res;
	}
	
	public function recoverDatesLogsFromEventsChange($idsArray)
	{
		$select = parent::select()->from($this->_name,"fecha")
		->where("idEvento IN (?)",$idsArray)->where("( msj like '%startevent%'")->orwhere("msj like '%-assignment%')");
		
		//file_put_contents("sqllogdays.txt",$select);
		$res = parent::fetchAll($select)->toArray();
		$ret = array();
		foreach ($res as $r) {
			array_push($ret,$r['fecha']);
		}
		return $ret;
	}
	
     /**
     * recupera los mensajes de log para un evento
     * @param int $idEvento
     */
    public function getlog($idEvento)
    {
        try{
             $select = parent::select()->where('idEvento =?',$idEvento)->order('id asc');
             $res = parent::fetchAll($select)->toArray();
             $result = array();
             
			 foreach($res as $r){
                 if (self::isJSON($r['msj'])) // Si el mensaje es json
				 {
                    $msg = json_decode($r['msj']);
                 	if(isset($msg->type))
                 	{
                 		if($msg->type) 
                    {
						 if($msg->type == "comment")
                            $result[] =$r['username'].": ".$r['fecha']." - ".$msg->result;
                        else{
						$result[] = $r['fecha']." - ".$msg->result;}
					}	
                 	}
				       
                   
                 }
				 else // Si el mensaje no es json
				 {
					 $result[] = $r['fecha']." - ".$r['msj'];
				 }
				  
             }
             //file_put_contents ( "LOG.txt" , json_encode($result),FILE_APPEND);
        return $result;
        }catch(Zend_Exception $e){
            return array('fail'=>array($e->getCode()=>$e->getMessage()));
        }
    }
    
    function isJSON($string){
        return is_string($string) && is_object(json_decode($string)) ? true : false;
    }
}

?>