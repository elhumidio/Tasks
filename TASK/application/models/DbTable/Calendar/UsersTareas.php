<?php
class Application_Model_DbTable_Calendar_UsersTareas extends Zend_Db_Table {
	
	protected $_name = "cal_tareas_username";
	protected $_primary =array('idTarea','username','OU_ID');
	
	public function AsignarUsuario($idTarea,$username,$OU_ID)
	{
		try{
			return parent::insert(array('idTarea'=>$idTarea,'username'=>$username,'OU_ID'=>$OU_ID));
		}catch(Zend_Exception $e){
			return array('fail'=>array($e->getCode()=>$e->getMessage()));
		}
	}
    		
	public function __get($propiedad) {
		return $this->$propiedad;
	}
}

?>