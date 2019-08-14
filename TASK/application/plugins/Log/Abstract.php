<?php
/**
 * @author      Juan Ignacio Badia <juanignacio.badia@t-systems.es>
 * @name        Application_Plugin_Log_Abstract
 * @filesource  application/plugins/Log/Abstract.php
 */
class Application_Plugin_Log_Abstract extends Zend_Controller_Plugin_Abstract
{
	protected $portalName = 'Portal ea.TASK';
	protected $dbName = false;
	protected $columnMapping = false;
	
	public function setLog ($msj, $lvl=6, $params=NULL)
    {
    	if(!$this->dbName){
    		return 1;
    	}
    	if(!$this->columnMapping){
    		return 2;
    	}
    	
    	if(!isset($params['session_id'])){
    		$params['session_id']=Zend_Session::getId();
    	}
    	
    	if(!isset($this->columnMapping['session_id'])){
    		$this->columnMapping['session_id']='session_id';
    	}
    	
    	// Log BBDD
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$writera = new Zend_Log_Writer_Db($db, $this->dbName, $this->columnMapping);
    	$logger = new Zend_Log($writera);
    	
    	// Log Mail
    	if(false && !in_array(APPLICATION_ENV,array('local','development'))){
    	
	    	$mail = new Zend_Mail();
	    	$mail->setFrom('alerts.easya@t-systems.es',$this->portalName)->addTo('automation@t-systems.es');
	    	$writerb = new Zend_Log_Writer_Mail($mail);
	    	$writerb->setSubjectPrependText("Errors - $msj");
	    	$writerb->addFilter(Zend_Log::WARN);
    		$logger->addWriter($writerb);
    	}
    	
		switch ($lvl)
		{
			case 0:$logger->emerg($msj,$params);break;
			case 1:$logger->alert($msj,$params);break;
			case 2:$logger->crit($msj,$params);break;
			case 3:$logger->err($msj,$params);break;
			case 4:$logger->warn($msj,$params);break;
			case 5:$logger->notice($msj,$params);break;
			case 6:$logger->info($msj,$params);break;
			default:$logger->debug($msj,$params);break;
		}
		
		
			
		
	}
	
		
}